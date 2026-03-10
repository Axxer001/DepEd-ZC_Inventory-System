<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
            'token' => $token,
            'expires_at' => now()->addHours(48),
        ]);

        // Send admin notification email
        $adminEmail = env('ADMIN_EMAIL', 'admin@deped.gov.ph');
        Mail::to($adminEmail)->send(new AdminRegistrationNotification($email, $token));

        // Clear the OTP verification
        session()->forget('otp_verified_email');

        return back()->with('success', 'Registration request submitted! Please wait for administrator approval.');
    }

    /**
     * Handle admin's accept/reject/block decision via URL click.
     */
    public function verify(Request $request)
    {
        $action = $request->query('action');
        $token = $request->query('token');

        // Validate parameters
        if (!in_array($action, ['accept', 'reject', 'block']) || empty($token)) {
            return view('auth.verify-result', [
                'status' => 'error',
                'title' => 'Invalid Request',
                'message' => 'The link you followed is invalid or missing parameters.',
            ]);
        }

        // Find the pending registration
        $pending = PendingRegistration::where('token', $token)->first();

        if (!$pending) {
            return view('auth.verify-result', [
                'status' => 'error',
                'title' => 'Link Expired',
                'message' => 'This registration link has already been used or has expired.',
            ]);
        }

        // Check if the token has expired (48h window)
        if ($pending->isExpired()) {
            $pending->delete();
            return view('auth.verify-result', [
                'status' => 'error',
                'title' => 'Link Expired',
                'message' => 'This request link has expired. The user will need to submit a new registration request.',
            ]);
        }

        $email = $pending->email;

        if ($action === 'accept') {
            // Create the user account
            User::create([
                'name' => Str::before($email, '@'),
                'email' => $email,
                'password' => bcrypt(Str::random(32)),
                'approved' => true,
            ]);

            // Remove from pending
            $pending->delete();

            // Send welcome email to user
            Mail::to($email)->send(new RegistrationApproved($email));

            return view('auth.verify-result', [
                'status' => 'accepted',
                'title' => 'User Approved',
                'message' => "The account for {$email} has been created. A welcome email has been sent.",
            ]);
        }

        if ($action === 'reject') {
            // Remove from pending
            $pending->delete();

            // Log action
            \Illuminate\Support\Facades\DB::table('system_logs')->insert([
                'user' => 'System',
                'activity' => "Rejected registration for: {$email}",
                'module' => 'Accounts',
                'action_type' => 'Others',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Send rejection email to user
            Mail::to($email)->send(new RegistrationRejected($email));

            return view('auth.verify-result', [
                'status' => 'rejected',
                'title' => 'Registration Declined',
                'message' => "The registration for {$email} has been declined. A notification email has been sent.",
            ]);
        }

        if ($action === 'block') {
            // Block the email permanently
            BlockedAccount::firstOrCreate(
                ['email' => $email],
                ['blocked_at' => now()]
            );

            // Log action
            \Illuminate\Support\Facades\DB::table('system_logs')->insert([
                'user' => 'System',
                'activity' => "Blocked email from registering: {$email}",
                'module' => 'Accounts',
                'action_type' => 'Others',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Remove from pending
            $pending->delete();

            return view('auth.verify-result', [
                'status' => 'blocked',
                'title' => 'User Blocked',
                'message' => "The email {$email} has been permanently blocked from submitting access requests.",
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
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

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
