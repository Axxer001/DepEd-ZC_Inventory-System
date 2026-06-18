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
        $users    = User::orderBy('role')->orderBy('name')->get();
        $blocked  = BlockedAccount::orderByDesc('blocked_at')->get();

        return view('admin.user-management', compact('pending', 'users', 'blocked'));
    }

    /**
     * Approve a pending registration and assign a role.
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:super_admin,admin,user',
        ]);

        $pending = PendingRegistration::findOrFail($id);

        if ($pending->isExpired()) {
            $pending->delete();
            return back()->with('error', "Registration for {$pending->email} has expired.");
        }

        User::create([
            'name'     => Str::before($pending->email, '@'),
            'email'    => $pending->email,
            'password' => $pending->password ?? bcrypt(Str::random(32)),
            'approved' => true,
            'role'     => $request->role,
        ]);

        $pending->delete();

        $actor = auth()->user()->name;
        DB::table('system_logs')->insert([
            'user'        => $actor,
            'activity'    => "Approved registration for: {$pending->email} as {$request->role}",
            'module'      => 'User Management',
            'action_type' => 'Create',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        try {
            Mail::to($pending->email)->send(new RegistrationApproved($pending->email));
        } catch (\Exception $e) {}

        return back()->with('success', "Account for {$pending->email} approved as " . ucfirst(str_replace('_', ' ', $request->role)) . ".");
    }

    /**
     * Reject a pending registration.
     */
    public function reject($id)
    {
        $pending = PendingRegistration::findOrFail($id);
        $email   = $pending->email;
        $pending->delete();

        $actor = auth()->user()->name;
        DB::table('system_logs')->insert([
            'user'        => $actor,
            'activity'    => "Rejected registration for: {$email}",
            'module'      => 'User Management',
            'action_type' => 'Others',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

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

        $oldRole = $user->role;
        $user->update(['role' => $request->role]);

        $actor = auth()->user()->name;
        DB::table('system_logs')->insert([
            'user'        => $actor,
            'activity'    => "Changed role of {$user->email} from {$oldRole} to {$request->role}",
            'module'      => 'User Management',
            'action_type' => 'Update',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

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

        $actor = auth()->user()->name;
        DB::table('system_logs')->insert([
            'user'        => $actor,
            'activity'    => "Blocked user account: {$user->email}",
            'module'      => 'User Management',
            'action_type' => 'Update',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

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

        $actor = auth()->user()->name;
        DB::table('system_logs')->insert([
            'user'        => $actor,
            'activity'    => "Unblocked email: {$email}",
            'module'      => 'User Management',
            'action_type' => 'Update',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

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

        $actor = auth()->user()->name;
        DB::table('system_logs')->insert([
            'user'        => $actor,
            'activity'    => "Deleted user account: {$email}",
            'module'      => 'User Management',
            'action_type' => 'Delete',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return back()->with('success', "Account {$email} has been permanently deleted.");
    }
}
