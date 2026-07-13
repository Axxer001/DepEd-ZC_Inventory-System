<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\BlockedAccount;
use App\Models\PendingRegistration;
use App\Models\User;
use App\Mail\AdminRegistrationNotification;
use App\Mail\OtpVerification;
use App\Mail\RegistrationApproved;
use App\Mail\RegistrationRejected;


class RegistrationController extends Controller
{
    /**
     * Handle registration form submission.
     * Stores a pending registration and emails the admin.
     */
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'system_type' => 'required|in:main,school',
            'school_id' => 'required_if:system_type,school|nullable|exists:schools,id',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]+$/'
            ],
        ], [
            'password.regex' => 'The password must contain uppercase and lowercase letters, numbers, and no special characters.',
        ]);

        $email = strtolower(trim($request->email));

        // Check if email was verified via OTP
        if (session('otp_verified_email') !== $email) {
            return back()->with('error', 'Please verify your email address first.');
        }

        // Check if blocked
        if (BlockedAccount::where('email', $email)->exists()) {
            return back()->with('error', 'This email has been blocked from requesting access.');
        }

        // Check if already an approved user
        if (User::where('email', $email)->where('approved', true)->exists()) {
            return back()->with('error', 'This email is already registered and approved.');
        }

        // Check if already pending
        if (PendingRegistration::where('email', $email)->exists()) {
            return back()->with('info', 'A registration request for this email is already pending review.');
        }

        // Create pending registration with a UUID token and 48h expiration
        $token = (string) Str::uuid();

        PendingRegistration::create([
            'email' => $email,
            'password' => $request->password,
            'token' => $token,
            'system_type' => $request->system_type,
            'school_id' => $request->system_type === 'school' ? $request->school_id : null,
            'expires_at' => now()->addHours(48),
        ]);

        // Send admin notification email
        $adminEmail = config('mail.admin_email', 'admin@deped.gov.ph');
        Mail::to($adminEmail)->send(new AdminRegistrationNotification($email, $token));

        // Notify super admins via system notification
        $dummyUser = (object)[
            'title' => 'New User Registration',
            'message' => 'A new user has registered and is pending approval.',
            'detailed_message' => "User {$email} has registered and is awaiting admin approval."
        ];
        
        $admins = User::where('approved', true)
            ->where('system_type', 'main')
            ->whereIn('role', ['admin', 'super_admin'])
            ->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\NewUserRegistered($dummyUser));
        }

        // Clear the OTP verification
        session()->forget('otp_verified_email');

        return back()->with('success', 'Registration request submitted! Please wait for administrator approval.');
    }

    /**
     * Handle admin's accept/reject/block decision via URL click.
     *
     * Issue 2 fix: The entire decision is wrapped in a DB::transaction() with
     * lockForUpdate() on the pending_registrations row. If an admin clicks the
     * link twice, or two admins click simultaneously, only the first request
     * proceeds — the second sees status != 'pending' and returns an error view.
     */
    public function verify(Request $request)
    {
        $action = $request->query('action');
        $token  = $request->query('token');

        if (!in_array($action, ['accept', 'reject', 'block']) || empty($token)) {
            return view('auth.verify-result', [
                'status'  => 'error',
                'title'   => 'Invalid Request',
                'message' => 'The link you followed is invalid or missing parameters.',
            ]);
        }

        // Variables populated inside the transaction, used after commit for side-effects
        $result      = null; // 'accepted' | 'rejected' | 'blocked' | 'already_processed' | 'expired' | 'not_found'
        $resultEmail = null;

        DB::transaction(function () use ($action, $token, &$result, &$resultEmail) {
            // Lock the row for the duration of this transaction
            $pending = PendingRegistration::where('token', $token)
                ->lockForUpdate()
                ->first();

            if (!$pending) {
                $result = 'not_found';
                return;
            }

            // Idempotency guard — already processed by a concurrent or duplicate request
            if ($pending->status !== 'pending') {
                $result = 'already_processed';
                return;
            }

            if ($pending->isExpired()) {
                $pending->update(['status' => 'rejected']);
                $pending->delete();
                $result = 'expired';
                return;
            }

            $email = $pending->email;
            $resultEmail = $email;

            if ($action === 'accept') {
                // Mark status atomically BEFORE creating the user
                $pending->update(['status' => 'approved']);

                User::create([
                    'name'     => Str::before($email, '@'),
                    'email'    => $email,
                    'password' => $pending->password ?? bcrypt(Str::random(32)),
                    'approved' => true,
                    'system_type' => $pending->system_type ?? 'main',
                    'school_id' => $pending->system_type === 'school' ? $pending->school_id : null,
                ]);

                $pending->delete();
                $result = 'accepted';
                return;
            }

            if ($action === 'reject') {
                $pending->update(['status' => 'rejected']);
                $pending->delete();
                $result = 'rejected';
                return;
            }

            if ($action === 'block') {
                $pending->update(['status' => 'rejected']);
                BlockedAccount::firstOrCreate(
                    ['email' => $email],
                    ['blocked_at' => now()]
                );
                $pending->delete();
                $result = 'blocked';
            }
        });

        // Handle cases that don't require any side-effect emails
        if ($result === 'not_found' || $result === 'already_processed') {
            return view('auth.verify-result', [
                'status'  => 'error',
                'title'   => 'Link Expired',
                'message' => 'This registration link has already been used or has expired.',
            ]);
        }

        if ($result === 'expired') {
            return view('auth.verify-result', [
                'status'  => 'error',
                'title'   => 'Link Expired',
                'message' => 'This request link has expired. The user will need to submit a new registration request.',
            ]);
        }

        // Side-effects (mail) are intentionally OUTSIDE the transaction — mail
        // failures should not roll back a successfully committed approval.
        if ($result === 'accepted') {
            try {
                Mail::to($resultEmail)->send(new RegistrationApproved($resultEmail));
            } catch (\Exception $e) {}

            return view('auth.verify-result', [
                'status'  => 'accepted',
                'title'   => 'User Approved',
                'message' => "The account for {$resultEmail} has been created. A welcome email has been sent.",
            ]);
        }

        if ($result === 'rejected') {
            try {
                Mail::to($resultEmail)->send(new RegistrationRejected($resultEmail));
            } catch (\Exception $e) {}

            return view('auth.verify-result', [
                'status'  => 'rejected',
                'title'   => 'Registration Declined',
                'message' => "The registration for {$resultEmail} has been declined. A notification email has been sent.",
            ]);
        }

        if ($result === 'blocked') {
            return view('auth.verify-result', [
                'status'  => 'blocked',
                'title'   => 'User Blocked',
                'message' => "The email {$resultEmail} has been permanently blocked from submitting access requests.",
            ]);
        }
    }

    /**
     * Send OTP to the user's email for verification.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = strtolower(trim($request->email));

        // Pre-check: blocked?
        if (BlockedAccount::where('email', $email)->exists()) {
            return response()->json(['success' => false, 'message' => 'This email has been blocked from requesting access.']);
        }

        // Pre-check: already approved?
        if (User::where('email', $email)->where('approved', true)->exists()) {
            return response()->json(['success' => false, 'message' => 'This email is already registered and approved.']);
        }

        // Pre-check: already pending?
        if (PendingRegistration::where('email', $email)->exists()) {
            return response()->json(['success' => false, 'message' => 'A registration request for this email is already pending review.']);
        }

        // Check MX records for valid domain
        $domain = substr($email, strpos($email, '@') + 1);
        if (!checkdnsrr($domain, 'MX')) {
            return response()->json(['success' => false, 'message' => 'Invalid email domain. Please use a valid email address.']);
        }

        // Generate 6-digit OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store in session with 10 min expiry
        session([
            'otp_code' => $otp,
            'otp_email' => $email,
            'otp_expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        // Clear any previous verification
        session()->forget('otp_verified_email');

        // Send OTP email
        Mail::to($email)->send(new OtpVerification($otp));

        return response()->json(['success' => true, 'message' => 'Verification code sent to your email.']);
    }

    /**
     * Verify the OTP entered by the user.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $email = strtolower(trim($request->email));
        $enteredOtp = $request->otp;

        $storedOtp = session('otp_code');
        $storedEmail = session('otp_email');
        $expiresAt = session('otp_expires_at');

        if (!$storedOtp || !$storedEmail || !$expiresAt) {
            return response()->json(['success' => false, 'message' => 'No verification code found. Please request a new one.']);
        }

        if ($email !== $storedEmail) {
            return response()->json(['success' => false, 'message' => 'Email mismatch. Please request a new code.']);
        }

        if (now()->timestamp > $expiresAt) {
            session()->forget(['otp_code', 'otp_email', 'otp_expires_at']);
            return response()->json(['success' => false, 'message' => 'Verification code has expired. Please request a new one.']);
        }

        if ($enteredOtp !== $storedOtp) {
            return response()->json(['success' => false, 'message' => 'Incorrect verification code.']);
        }

        // Mark email as verified in session
        session(['otp_verified_email' => $email]);
        session()->forget(['otp_code', 'otp_email', 'otp_expires_at']);

        return response()->json(['success' => true, 'message' => 'Email verified successfully!']);
    }
}
