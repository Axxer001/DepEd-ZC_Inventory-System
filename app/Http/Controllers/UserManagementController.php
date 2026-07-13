<?php

namespace App\Http\Controllers;

use App\Models\BlockedAccount;
use App\Models\PendingRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\RegistrationApproved;
use App\Mail\RegistrationRejected;


class UserManagementController extends Controller
{
    /**
     * Main dashboard — lists pending, active users, and blocked accounts.
     */
    public function index()
    {
        $pending  = PendingRegistration::orderByDesc('created_at')->get();
        $users    = User::where('approved', true)->orderBy('role')->orderBy('name')->get();
        $blocked  = BlockedAccount::orderByDesc('blocked_at')->get();

        return view('admin.user-management', compact('pending', 'users', 'blocked'));
    }

    /**
     * Approve a pending registration and assign a role.
     *
     * Uses DB::transaction() + lockForUpdate() to prevent race conditions when
     * two super-admins click approve simultaneously — the second request will
     * see status='approved' inside the lock and exit cleanly.
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:super_admin,admin,user',
        ]);

        $user = null;
        $pendingEmail = null;

        DB::transaction(function () use ($request, $id, &$user, &$pendingEmail) {
            // Lock the row — second concurrent request blocks here until we commit
            $pending = PendingRegistration::lockForUpdate()->findOrFail($id);

            // Idempotency guard — already processed by a concurrent request
            if ($pending->status !== 'pending') {
                return;
            }

            if ($pending->isExpired()) {
                $pending->update(['status' => 'rejected']);
                $pending->delete();
                return;
            }

            // Mark as approved atomically before creating the user
            $pending->update(['status' => 'approved']);
            $pendingEmail = $pending->email;

            $user = User::create([
                'name'     => Str::before($pending->email, '@'),
                'email'    => $pending->email,
                'password' => $pending->password ?? bcrypt(Str::random(32)),
                'approved' => true,
                'role'     => $request->role,
            ]);

            $dummyData = (object)[
                'title' => 'Account Approved',
                'message' => 'Your account has been approved by the administrator.',
                'detailed_message' => "Your account ({$user->email}) has been approved with the role of: {$user->role}. You can now login and access the system."
            ];
            $user->notify(new \App\Notifications\AccountApprovedNotification($dummyData));

            $pending->delete();
        });

        if (!$user || !$pendingEmail) {
            return back()->with('error', 'This registration has already been processed or has expired.');
        }

        try {
            Mail::to($pendingEmail)->send(new RegistrationApproved($pendingEmail));
        } catch (\Exception $e) {}

        return back()->with('success', "Account for {$pendingEmail} approved as " . ucfirst(str_replace('_', ' ', $request->role)) . ".");
    }

    /**
     * Reject a pending registration.
     *
     * Uses DB::transaction() + lockForUpdate() to prevent a reject running
     * after an approve has already created the user (double-click or dual-admin).
     */
    public function reject($id)
    {
        $email = null;

        DB::transaction(function () use ($id, &$email) {
            $pending = PendingRegistration::lockForUpdate()->findOrFail($id);

            // Idempotency guard
            if ($pending->status !== 'pending') {
                return;
            }

            $email = $pending->email;
            $pending->update(['status' => 'rejected']);
            $pending->delete();
        });

        if (!$email) {
            return back()->with('error', 'This registration has already been processed.');
        }

        try {
            Mail::to($email)->send(new RegistrationRejected($email));
        } catch (\Exception $e) {}

        return back()->with('success', "Registration for {$email} rejected.");
    }

    /**
     * Change an existing user's role.
     */
    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:super_admin,admin,user',
        ]);

        $user = User::findOrFail($id);

        // Prevent demoting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own role.');
        }

        $user->update(['role' => $request->role]);

        return back()->with('success', "Role updated for {$user->email}.");
    }

    /**
     * Block an existing user account (disable + prevent re-registration).
     */
    public function blockUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot block your own account.');
        }

        BlockedAccount::firstOrCreate(
            ['email' => $user->email],
            ['blocked_at' => now()]
        );

        $user->update(['approved' => false]);

        return back()->with('success', "Account {$user->email} has been blocked.");
    }

    /**
     * Unblock a previously blocked email.
     */
    public function unblock($id)
    {
        $blocked = BlockedAccount::findOrFail($id);
        $email   = $blocked->email;
        $blocked->delete();

        // Re-enable user if they exist
        User::where('email', $email)->update(['approved' => true]);

        return back()->with('success', "{$email} has been unblocked.");
    }

    /**
     * Delete a user account permanently.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $email = $user->email;
        $user->delete();

        return back()->with('success', "Account {$email} has been permanently deleted.");
    }
}
