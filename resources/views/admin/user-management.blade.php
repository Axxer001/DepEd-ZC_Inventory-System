<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management | DepEd ZC</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
        .animate-fade-in { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

        /* ── Tab bar ── */
        .tab-active { border-bottom: 2px solid #c00000; color: #c00000; font-weight: 700; }

        /* ══════════════════════════════════════════════
           DARK MODE — User Management page overrides
        ══════════════════════════════════════════════ */

        /* Page body + main */
        html.dark #umMain                    { background-color: #0f172a !important; }

        /* ── Page header ── */
        html.dark #umHeader .text-slate-900  { color: #f1f5f9 !important; }
        html.dark #umHeader .text-slate-400  { color: #475569 !important; }
        html.dark #umHeader .bg-\[#c00000\]\/10 { background-color: rgba(192,0,0,0.18) !important; }

        /* ── Flash banners ── */
        html.dark #umFlash .bg-green-50      { background-color: rgba(5,46,22,0.45) !important; border-color: #14532d !important; }
        html.dark #umFlash .text-green-700   { color: #34d399 !important; }
        html.dark #umFlash .bg-red-50        { background-color: rgba(69,10,10,0.45) !important; border-color: rgba(192,0,0,0.3) !important; }
        html.dark #umFlash .text-red-700     { color: #f87171 !important; }

        /* ── Tab navigation ── */
        html.dark #umTabs                    { border-color: #334155 !important; }
        html.dark #umTabs button             { color: #475569 !important; }
        html.dark #umTabs button:hover       { color: #94a3b8 !important; }
        html.dark #umTabs button.tab-active  { color: #c00000 !important; }

        /* ── Pending cards ── */
        html.dark .pending-card              { background-color: #1e293b !important; border-color: #334155 !important; }
        html.dark .pending-card .text-slate-800 { color: #e2e8f0 !important; }
        html.dark .pending-card .text-slate-400 { color: #475569 !important; }
        html.dark .pending-card .bg-amber-100   { background-color: rgba(69,26,3,0.6) !important; color: #fbbf24 !important; }
        html.dark .pending-card .bg-amber-50    { background-color: rgba(69,26,3,0.4) !important; border-color: rgba(180,83,9,0.4) !important; color: #fbbf24 !important; }
        html.dark .pending-card .bg-slate-50    { background-color: #0f172a !important; color: #94a3b8 !important; }
        html.dark .pending-card .hover\:bg-\[#c00000\]:hover { background-color: #c00000 !important; color: #fff !important; }

        /* ── Pending expand panel ── */
        html.dark .pending-expand            { background-color: rgba(15,23,42,0.6) !important; border-color: #334155 !important; }
        html.dark .pending-expand select     { background-color: #1e293b !important; border-color: #334155 !important; color: #e2e8f0 !important; }
        html.dark .pending-expand .bg-green-600 { background-color: #15803d !important; }
        html.dark .pending-expand .hover\:bg-green-700:hover { background-color: #166534 !important; }
        html.dark .pending-expand .bg-red-50 { background-color: rgba(69,10,10,0.4) !important; border-color: rgba(192,0,0,0.25) !important; color: #f87171 !important; }

        /* ── Users table ── */
        html.dark #umUsersTable              { background-color: #1e293b !important; border-color: #334155 !important; }
        html.dark #umUsersTable thead tr     { background-color: #0f172a !important; border-color: #334155 !important; }
        html.dark #umUsersTable thead th     { color: #475569 !important; }
        html.dark #umUsersTable tbody tr     { border-color: #1e293b !important; }
        html.dark #umUsersTable tbody tr:hover { background-color: rgba(15,23,42,0.6) !important; }

        /* Avatar chips in users table */
        html.dark #umUsersTable .bg-purple-100  { background-color: rgba(46,16,101,0.5) !important; color: #c084fc !important; }
        html.dark #umUsersTable .bg-blue-100    { background-color: rgba(23,37,84,0.5) !important; color: #60a5fa !important; }
        html.dark #umUsersTable .bg-slate-100   { background-color: #0f172a !important; color: #64748b !important; }
        html.dark #umUsersTable .text-slate-800 { color: #e2e8f0 !important; }
        html.dark #umUsersTable .text-slate-400 { color: #475569 !important; }

        /* Role dropdown in table */
        html.dark #umUsersTable .bg-purple-50   { background-color: rgba(46,16,101,0.4) !important; border-color: rgba(139,92,246,0.3) !important; color: #c084fc !important; }
        html.dark #umUsersTable .bg-blue-50     { background-color: rgba(23,37,84,0.4) !important; border-color: rgba(59,130,246,0.3) !important; color: #60a5fa !important; }
        html.dark #umUsersTable .bg-slate-50    { background-color: #0f172a !important; border-color: #334155 !important; color: #94a3b8 !important; }

        /* Status badges */
        html.dark #umUsersTable .bg-green-50    { background-color: rgba(5,46,22,0.4) !important; border-color: #14532d !important; color: #34d399 !important; }
        html.dark #umUsersTable .bg-red-50      { background-color: rgba(69,10,10,0.4) !important; border-color: rgba(192,0,0,0.25) !important; color: #f87171 !important; }

        /* Action buttons in users table */
        html.dark #umUsersTable .bg-amber-50    { background-color: rgba(69,26,3,0.35) !important; border-color: rgba(180,83,9,0.3) !important; color: #fbbf24 !important; }
        html.dark #umUsersTable .hover\:bg-amber-500:hover { background-color: #d97706 !important; color: #fff !important; }
        html.dark #umUsersTable .bg-red-50      { background-color: rgba(69,10,10,0.35) !important; }
        html.dark #umUsersTable .hover\:bg-red-600:hover { background-color: #c00000 !important; color: #fff !important; }
        html.dark #umUsersTable .text-slate-300 { color: #334155 !important; }

        /* ── Blocked table ── */
        html.dark #umBlockedTable            { background-color: #1e293b !important; border-color: #334155 !important; }
        html.dark #umBlockedTable thead tr   { background-color: #0f172a !important; border-color: #334155 !important; }
        html.dark #umBlockedTable thead th   { color: #475569 !important; }
        html.dark #umBlockedTable tbody tr   { border-color: #1e293b !important; }
        html.dark #umBlockedTable tbody tr:hover { background-color: rgba(15,23,42,0.6) !important; }
        html.dark #umBlockedTable .bg-red-100   { background-color: rgba(69,10,10,0.6) !important; color: #f87171 !important; }
        html.dark #umBlockedTable .text-slate-700 { color: #e2e8f0 !important; }
        html.dark #umBlockedTable .text-slate-400 { color: #475569 !important; }
        html.dark #umBlockedTable .bg-green-50   { background-color: rgba(5,46,22,0.35) !important; border-color: #14532d !important; color: #34d399 !important; }
        html.dark #umBlockedTable .hover\:bg-green-600:hover { background-color: #15803d !important; color: #fff !important; }

        /* ── Empty states ── */
        html.dark .um-empty .text-slate-400  { color: #334155 !important; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex">

    @include('partials.sidebar')

    <main class="flex-1 min-w-0 p-6 lg:p-8 animate-fade-in" id="umMain">

        {{-- Header --}}
        <div class="mb-8" id="umHeader">
            <div class="flex items-center gap-3 mb-1">
                <div class="w-8 h-8 rounded-xl bg-[#c00000]/10 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#c00000" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">User Management</h1>
                    <p class="text-xs text-slate-400">Super Admin — Manage accounts, roles, and access control</p>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        <div id="umFlash">
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-2xl text-sm font-semibold animate-fade-in">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition class="mb-6 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-sm font-semibold animate-fade-in">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                {{ session('error') }}
            </div>
        @endif
        </div>{{-- /umFlash --}}

        {{-- Tabs --}}
        <div x-data="{ tab: 'pending', showCorrectModal: false, correctUser: { id: null, email: '', system_type: 'main', school_id: '' } }">
            <div class="flex gap-0 border-b border-slate-200 mb-6" id="umTabs">
                <button @click="tab = 'pending'"
                        :class="tab === 'pending' ? 'tab-active' : 'text-slate-500 hover:text-slate-700'"
                        class="px-5 py-3 text-sm font-semibold transition-colors relative flex items-center gap-2">
                    Pending Registrations
                    @if($pending->count() > 0)
                        <span class="bg-[#c00000] text-white text-[10px] font-black rounded-full px-1.5 py-0.5 min-w-[18px] text-center leading-none">{{ $pending->count() }}</span>
                    @endif
                </button>
                <button @click="tab = 'users'"
                        :class="tab === 'users' ? 'tab-active' : 'text-slate-500 hover:text-slate-700'"
                        class="px-5 py-3 text-sm font-semibold transition-colors">
                    Active Accounts
                    <span class="ml-1 text-slate-400 font-normal text-xs">({{ $users->count() }})</span>
                </button>
                <button @click="tab = 'blocked'"
                        :class="tab === 'blocked' ? 'tab-active' : 'text-slate-500 hover:text-slate-700'"
                        class="px-5 py-3 text-sm font-semibold transition-colors">
                    Blocked
                    <span class="ml-1 text-slate-400 font-normal text-xs">({{ $blocked->count() }})</span>
                </button>
            </div>

            {{-- ====================== PENDING TAB ====================== --}}
            <div x-show="tab === 'pending'" x-transition.opacity.duration.200ms>
                @if($pending->isEmpty())
                    <div class="um-empty text-center py-16 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto mb-3 opacity-30">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                        </svg>
                        <p class="font-semibold text-sm">No pending registrations</p>
                        <p class="text-xs mt-1">All registration requests have been processed.</p>
                    </div>
                @else
                    <div class="grid gap-4">
                        @foreach($pending as $reg)
                        <div x-data="{ open: false }" class="pending-card bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                            <div class="flex items-center justify-between px-5 py-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 font-black text-sm uppercase">
                                        {{ substr($reg->email, 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-bold text-slate-800 truncate">{{ $reg->email }}</p>
                                            @if($reg->hasDomainMismatch())
                                                <span class="px-2 py-0.5 bg-red-50 text-red-600 border border-red-200 rounded text-[9px] font-black animate-pulse uppercase tracking-wider shrink-0">
                                                    ⚠️ Mismatch
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-slate-500 font-medium">
                                            Type: <span class="font-bold uppercase text-slate-600">{{ $reg->system_type }}</span>
                                            @if($reg->system_type === 'school' && $reg->school)
                                                • School: <span class="font-bold text-slate-600">{{ $reg->school->name }}</span>
                                            @endif
                                        </p>
                                        <p class="text-[11px] text-slate-400">Submitted {{ \Carbon\Carbon::parse($reg->created_at)->timezone('Asia/Manila')->diffForHumans() }}
                                            @if($reg->isExpired()) <span class="text-red-500 font-bold ml-1">• Expired</span> @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="px-2.5 py-1 bg-amber-50 text-amber-600 border border-amber-200 rounded-lg text-[10px] font-bold uppercase tracking-wide">Pending</span>
                                    <button @click="open = !open"
                                            class="flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-50 hover:bg-[#c00000] hover:text-white text-slate-600 text-xs font-bold transition-all duration-200">
                                        <span x-text="open ? 'Close' : 'Review'"></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Expand: Approve or Reject panel --}}
                            <div x-show="open" x-transition.opacity x-cloak class="pending-expand border-t border-slate-100 px-5 py-4 bg-slate-50/60 flex flex-col sm:flex-row gap-3">
                                {{-- Approve with role selector --}}
                                <form method="POST" action="{{ route('admin.users.approve', $reg->id) }}" class="flex items-center gap-2 flex-1">
                                    @csrf
                                    <select name="role" class="flex-1 text-sm px-3 py-2 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-300 font-semibold text-slate-700">
                                        <option value="user">👤 User</option>
                                        <option value="admin">🛠️ Admin</option>
                                        <option value="super_admin">🛡️ Super Admin</option>
                                    </select>
                                    <button type="submit"
                                            onclick="return confirm('Approve this account?')"
                                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-xl transition-colors whitespace-nowrap">
                                        ✓ Approve
                                    </button>
                                </form>

                                {{-- Reject --}}
                                <form method="POST" action="{{ route('admin.users.reject', $reg->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            onclick="return confirm('Reject this registration?')"
                                            class="w-full sm:w-auto px-4 py-2 bg-red-50 hover:bg-red-600 hover:text-white text-red-600 border border-red-200 text-xs font-bold rounded-xl transition-all">
                                        ✗ Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ====================== USERS TAB ====================== --}}
            <div x-show="tab === 'users'" x-transition.opacity.duration.200ms x-cloak>
                @if($users->isEmpty())
                    <div class="um-empty text-center py-16 text-slate-400">
                        <p class="font-semibold text-sm">No active accounts yet.</p>
                    </div>
                @else
                    <div id="umUsersTable" class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-100 border-b border-slate-200">
                                    <th class="text-left px-5 py-3 text-[11px] font-bold uppercase tracking-widest text-slate-400">User</th>
                                    <th class="text-left px-5 py-3 text-[11px] font-bold uppercase tracking-widest text-slate-400">Email</th>
                                    <th class="text-left px-5 py-3 text-[11px] font-bold uppercase tracking-widest text-slate-400">Role</th>
                                    <th class="text-left px-5 py-3 text-[11px] font-bold uppercase tracking-widest text-slate-400">Status</th>
                                    <th class="text-right px-5 py-3 text-[11px] font-bold uppercase tracking-widest text-slate-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($users as $u)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-xl flex items-center justify-center font-black text-xs uppercase shrink-0
                                                @if($u->isSuperAdmin()) bg-purple-100 text-purple-600
                                                @elseif($u->isAdmin()) bg-blue-100 text-blue-600
                                                @else bg-slate-100 text-slate-500 @endif">
                                                {{ substr($u->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <span class="font-semibold text-slate-800 text-sm">
                                                    {{ $u->name }}
                                                    @if($u->id === auth()->id())
                                                        <span class="ml-1 text-[10px] text-slate-400 font-normal">(you)</span>
                                                    @endif
                                                </span>
                                                <div class="text-[10px] text-slate-400 font-medium">
                                                    Scope: <span class="uppercase font-bold text-slate-500 text-[9px]">{{ $u->system_type }}</span>
                                                    @if($u->system_type === 'school' && $u->school)
                                                        • {{ $u->school->name }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-500 text-xs">{{ $u->email }}</td>
                                    <td class="px-5 py-3.5">
                                        @if($u->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.role', $u->id) }}" class="flex items-center gap-1.5">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role" onchange="this.form.submit()"
                                                    class="text-xs px-2.5 py-1.5 border rounded-lg font-bold focus:outline-none focus:ring-2 focus:ring-[#c00000]/20 transition-colors cursor-pointer
                                                    @if($u->isSuperAdmin()) bg-purple-50 border-purple-200 text-purple-700
                                                    @elseif($u->isAdmin()) bg-blue-50 border-blue-200 text-blue-700
                                                    @else bg-slate-50 border-slate-200 text-slate-600 @endif">
                                                <option value="user" @selected($u->role === 'user')>👤 User</option>
                                                <option value="admin" @selected($u->role === 'admin')>🛠️ Admin</option>
                                                <option value="super_admin" @selected($u->role === 'super_admin')>🛡️ Super Admin</option>
                                            </select>
                                        </form>
                                        @else
                                            <span class="text-xs px-2.5 py-1.5 bg-purple-50 border border-purple-200 text-purple-700 rounded-lg font-bold">🛡️ Super Admin</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if($u->approved)
                                            <span class="px-2 py-1 bg-green-50 border border-green-200 text-green-700 text-[10px] font-bold rounded-lg uppercase tracking-wide">Active</span>
                                        @else
                                            <span class="px-2 py-1 bg-red-50 border border-red-200 text-red-600 text-[10px] font-bold rounded-lg uppercase tracking-wide">Suspended</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if($u->id !== auth()->id())
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- Correct Scope --}}
                                            <button type="button"
                                                     @click="correctUser = { id: {{ $u->id }}, email: '{{ $u->email }}', system_type: '{{ $u->system_type }}', school_id: '{{ $u->school_id ?? '' }}' }; showCorrectModal = true"
                                                     class="px-3 py-1.5 text-[11px] font-bold rounded-lg bg-blue-50 hover:bg-blue-600 hover:text-white text-blue-600 border border-blue-200 transition-all">
                                                 Correct
                                             </button>
                                            {{-- Block --}}
                                            <form method="POST" action="{{ route('admin.users.block', $u->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        onclick="return confirm('Block {{ $u->email }}? They will not be able to log in.')"
                                                        class="px-3 py-1.5 text-[11px] font-bold rounded-lg bg-amber-50 hover:bg-amber-500 hover:text-white text-amber-600 border border-amber-200 transition-all">
                                                    Block
                                                </button>
                                            </form>
                                            {{-- Delete --}}
                                            <form method="POST" action="{{ route('admin.users.destroy', $u->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        onclick="return confirm('Permanently delete {{ $u->email }}? This cannot be undone.')"
                                                        class="px-3 py-1.5 text-[11px] font-bold rounded-lg bg-red-50 hover:bg-red-600 hover:text-white text-red-600 border border-red-200 transition-all">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                        @else
                                            <p class="text-right text-[11px] text-slate-300">—</p>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- ====================== BLOCKED TAB ====================== --}}
            <div x-show="tab === 'blocked'" x-transition.opacity.duration.200ms x-cloak>
                @if($blocked->isEmpty())
                    <div class="um-empty text-center py-16 text-slate-400">
                        <p class="font-semibold text-sm">No blocked accounts.</p>
                    </div>
                @else
                    <div id="umBlockedTable" class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-100 border-b border-slate-200">
                                    <th class="text-left px-5 py-3 text-[11px] font-bold uppercase tracking-widest text-slate-400">Email</th>
                                    <th class="text-left px-5 py-3 text-[11px] font-bold uppercase tracking-widest text-slate-400">Blocked</th>
                                    <th class="text-right px-5 py-3 text-[11px] font-bold uppercase tracking-widest text-slate-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($blocked as $b)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-xl bg-red-100 text-red-500 flex items-center justify-center font-black text-xs uppercase shrink-0">
                                                {{ substr($b->email, 0, 1) }}
                                            </div>
                                            <span class="font-semibold text-slate-700">{{ $b->email }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-slate-400">
                                        {{ $b->blocked_at ? \Carbon\Carbon::parse($b->blocked_at)->timezone('Asia/Manila')->format('M d, Y H:i') : '—' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <form method="POST" action="{{ route('admin.users.unblock', $b->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    onclick="return confirm('Unblock {{ $b->email }}?')"
                                                    class="px-3 py-1.5 text-[11px] font-bold rounded-lg bg-green-50 hover:bg-green-600 hover:text-white text-green-600 border border-green-200 transition-all">
                                                Unblock
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- ================= SCOPE CORRECTION MODAL ================= -->
            <div x-show="showCorrectModal" 
                 x-cloak 
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm transition-all"
                 @keydown.escape.window="showCorrectModal = false">
                <div class="bg-white rounded-3xl shadow-xl w-full max-w-md overflow-hidden border border-slate-100"
                     @click.away="showCorrectModal = false">
                    <div class="h-1.5 bg-blue-600 w-full"></div>
                    <div class="p-6 text-left">
                        <h3 class="text-lg font-bold text-slate-800 mb-1">Correct Account Scope</h3>
                        <p class="text-xs text-slate-400 mb-4" x-text="'Adjust system type or school for: ' + correctUser.email"></p>

                        <form :action="'{{ url('/admin/users') }}/' + correctUser.id + '/correct-scope'" method="POST" class="space-y-4">
                            @csrf
                            
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">System Type</label>
                                <select name="system_type" 
                                        x-model="correctUser.system_type"
                                        required
                                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-300 font-semibold text-slate-700 text-sm">
                                    <option value="main">Main System (SDO)</option>
                                    <option value="school">School Account</option>
                                </select>
                            </div>

                            <div class="space-y-2" x-show="correctUser.system_type === 'school'" x-cloak>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">Select School</label>
                                <select name="school_id" 
                                        x-model="correctUser.school_id"
                                        :required="correctUser.system_type === 'school'"
                                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-300 font-semibold text-slate-700 text-sm">
                                    <option value="">-- Choose a School --</option>
                                    @foreach($schools as $school)
                                        <option value="{{ $school->id }}">{{ $school->name }} ({{ $school->school_id }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2">
                                <button type="button" 
                                        @click="showCorrectModal = false" 
                                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition-colors">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-colors">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>{{-- /x-data tabs --}}
    </main>

</body>
</html>
