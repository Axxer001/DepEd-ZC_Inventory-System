<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $custodian->first_name }} {{ $custodian->last_name }} | Custodian Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { deped: '#c00000', deped_light: '#fef2f2' }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #c00000; }
        [x-cloak] { display: none !important; }

        .anim-0 { animation: fadeUp 0.45s cubic-bezier(.22,.68,0,1.2) forwards; opacity: 0; }
        .anim-1 { animation: fadeUp 0.45s cubic-bezier(.22,.68,0,1.2) 0.07s forwards; opacity: 0; }
        .anim-2 { animation: fadeUp 0.45s cubic-bezier(.22,.68,0,1.2) 0.14s forwards; opacity: 0; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

        .tab-fade { animation: tabIn 0.28s ease-out forwards; }
        @keyframes tabIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

        .stat-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .stat-hover:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.07); }

        html.dark body { background-color: #0b0f19; }
        html.dark .bg-white { background-color: #1e293b !important; border-color: #334155 !important; }
        html.dark .bg-slate-50 { background-color: #0f172a !important; }
        html.dark .border-slate-200 { border-color: #334155 !important; }
        html.dark .border-slate-100 { border-color: #334155 !important; }
        html.dark .text-slate-900 { color: #f8fafc !important; }
        html.dark .text-slate-800 { color: #e2e8f0 !important; }
        html.dark .text-slate-700 { color: #cbd5e1 !important; }
        html.dark .divide-slate-50 > tr { border-color: #334155 !important; }
    </style>
</head>
<body class="flex min-h-screen text-slate-800 overflow-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll p-4 lg:p-8" x-data="{ activeTab: 'assets' }">

        {{-- ===== STICKY HEADER ===== --}}
        <header class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 sticky top-0 z-50 anim-0">
            <div class="flex items-center gap-5">
                {{-- Avatar Circle --}}
                <div class="w-12 h-12 bg-deped_light rounded-full flex items-center justify-center border border-deped/20 shadow-sm shrink-0 text-deped font-black text-lg uppercase">
                    {{ substr($custodian->first_name, 0, 1) }}{{ substr($custodian->last_name, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-none uppercase italic">
                        {{ $custodian->first_name }}
                        @if($custodian->middle_name) {{ substr($custodian->middle_name, 0, 1) }}. @endif
                        {{ $custodian->last_name }}
                    </h1>
                    <div class="flex flex-wrap items-center gap-3 mt-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-widest bg-slate-100 px-2.5 py-0.5 rounded-md border border-slate-200">ID: {{ $custodian->employee_id }}</span>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-widest bg-slate-100 px-2.5 py-0.5 rounded-md border border-slate-200">{{ $custodian->position ?? 'Personnel' }}</span>
                        @php $isActive = strtolower($custodian->status ?? '') === 'active'; @endphp
                        <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full flex items-center gap-1.5 shadow-sm {{ $isActive ? 'text-emerald-700 bg-emerald-100' : 'text-slate-500 bg-slate-100' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $isActive ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                            {{ ucfirst($custodian->status ?? 'Unknown') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('admin.custodians') }}" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 uppercase tracking-widest hover:border-deped hover:text-deped hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-sm hover:shadow-md flex items-center gap-2 group">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Back to Registry
                </a>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 flex-grow pb-10">

            {{-- ===== LEFT SIDEBAR ===== --}}
            <aside class="lg:col-span-3 flex flex-col gap-6 anim-1">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-6">

                    {{-- Personnel Info --}}
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Personnel Details</p>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 bg-slate-50 rounded-lg flex items-center justify-center shrink-0 border border-slate-100">
                                    <svg class="w-3.5 h-3.5 text-deped" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Full Name</p>
                                    <p class="text-xs font-black text-slate-800 uppercase leading-snug mt-0.5">{{ $custodian->first_name }} {{ $custodian->middle_name }} {{ $custodian->last_name }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 bg-slate-50 rounded-lg flex items-center justify-center shrink-0 border border-slate-100">
                                    <svg class="w-3.5 h-3.5 text-deped" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.17-.789 3.376 3.376 0 0 1 6.34 0Z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Employee ID</p>
                                    <p class="text-xs font-black text-slate-800 uppercase leading-snug mt-0.5">{{ $custodian->employee_id ?: '—' }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 bg-slate-50 rounded-lg flex items-center justify-center shrink-0 border border-slate-100">
                                    <svg class="w-3.5 h-3.5 text-deped" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Contact Number</p>
                                    <p class="text-xs font-black text-slate-800 leading-snug mt-0.5">{{ $custodian->contact_number ?: '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Stats --}}
                    <div class="pt-6 border-t border-slate-100 space-y-3">
                        <div class="stat-hover p-4 bg-deped_light rounded-xl border border-red-100 cursor-default">
                            <p class="text-[9px] font-black text-deped uppercase tracking-widest mb-1">Assets in Custody</p>
                            <p class="text-xl font-black text-deped leading-none">{{ $stats->total_assets }}</p>
                            <p class="text-[10px] font-bold text-deped/60 mt-1 uppercase">Assigned Items</p>
                        </div>
                        <div class="stat-hover p-4 bg-emerald-50 rounded-xl border border-emerald-100 cursor-default">
                            <p class="text-[9px] font-black text-emerald-600 uppercase tracking-widest mb-1">Total Asset Value</p>
                            <p class="text-xl font-black text-emerald-700 leading-none italic">₱ {{ number_format($stats->total_value, 2) }}</p>
                            <p class="text-[10px] font-bold text-emerald-600/70 mt-1 uppercase">Cumulative Cost</p>
                        </div>

                        {{-- Assigned Schools --}}
                        @if($schools->count() > 0)
                        <div class="pt-2">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Assigned School(s)</p>
                            <div class="space-y-2">
                                @foreach($schools as $school)
                                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                                    <p class="text-[10px] font-bold text-slate-700 uppercase leading-tight truncate max-w-[140px]">{{ $school->name }}</p>
                                    <span class="text-[9px] font-black text-deped bg-deped_light px-2 py-0.5 rounded-full">{{ $school->asset_count }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </aside>

            {{-- ===== MAIN CONTENT ===== --}}
            <div class="lg:col-span-9 flex flex-col bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden anim-2">
                {{-- Tabs --}}
                <div class="flex border-b border-slate-200 bg-slate-50/50 px-2 pt-2">
                    <button
                        @click="activeTab = 'assets'"
                        :class="{'bg-white border-slate-200 border-b-white text-deped shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'assets', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'assets'}"
                        class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        Assigned Equipment
                    </button>
                </div>

                <div class="p-6 flex-grow overflow-y-auto custom-scroll">

                    {{-- TAB: Assigned Equipment --}}
                    <div x-show="activeTab === 'assets'" class="tab-fade">
                        @if($assets->count() > 0)
                        <div class="space-y-4">
                            @foreach($assets as $asset)
                            @php
                                $assetTransfers = $transfers->get($asset->id, collect());
                                $hasTransfers   = $assetTransfers->count() > 0;
                                $lastTransfer   = $hasTransfers ? $assetTransfers->first() : null;

                                // Determine current custody status from condition + transfers
                                $isStillWithCustodian = true;
                                $statusLabel  = 'Under Custody';
                                $statusTheme  = 'bg-emerald-100 text-emerald-700';
                                if ($lastTransfer) {
                                    $type = strtolower($lastTransfer->transfer_type ?? '');
                                    if (in_array($type, ['permanent', 'loan'])) {
                                        $isStillWithCustodian = false;
                                        $statusLabel = 'Transferred';
                                        $statusTheme = 'bg-blue-100 text-blue-700';
                                    } elseif ($type === 'return') {
                                        $isStillWithCustodian = false;
                                        $statusLabel = 'Returned';
                                        $statusTheme = 'bg-amber-100 text-amber-700';
                                    } elseif ($type === 'repair') {
                                        $statusLabel = 'Out for Repair';
                                        $statusTheme = 'bg-orange-100 text-orange-700';
                                    }
                                }
                                $condLower = strtolower($asset->condition ?? '');
                                if ($condLower === 'unserviceable') {
                                    $statusLabel = 'Unserviceable';
                                    $statusTheme = 'bg-red-100 text-red-700';
                                }

                                $condGood = in_array($asset->condition, ['Good', 'Serviceable']);
                                $condTheme = $condGood ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700';
                            @endphp

                            <div class="border border-slate-100 rounded-2xl overflow-hidden hover:border-slate-200 transition-all hover:shadow-sm">
                                {{-- Asset Header Row --}}
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 bg-slate-50/50">
                                    <div class="flex items-start gap-3 min-w-0">
                                        <div class="w-8 h-8 bg-slate-100 rounded-xl flex items-center justify-center shrink-0 border border-slate-200">
                                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="text-xs font-black text-slate-800 uppercase leading-none">{{ $asset->item_name }}</h4>
                                            <p class="text-[9px] font-bold text-slate-400 uppercase mt-1">{{ $asset->category_name }}{{ $asset->brand ? ' · ' . $asset->brand : '' }}{{ $asset->model ? ' · ' . $asset->model : '' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0 flex-wrap">
                                        <span class="text-[8px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full {{ $statusTheme }}">{{ $statusLabel }}</span>
                                        <span class="text-[8px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full {{ $condTheme }}">{{ $asset->condition ?: 'Good' }}</span>
                                        <span class="text-[9px] font-black text-deped italic">₱ {{ number_format($asset->asset_cost, 2) }}</span>
                                    </div>
                                </div>

                                {{-- Asset Details + Timeline --}}
                                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {{-- Left: Asset Info --}}
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between text-[10px]">
                                            <span class="font-bold text-slate-400 uppercase tracking-wide">Property No.</span>
                                            <span class="font-black text-slate-700 uppercase">{{ $asset->property_number }}</span>
                                        </div>
                                        @if($asset->serial_number)
                                        <div class="flex items-center justify-between text-[10px]">
                                            <span class="font-bold text-slate-400 uppercase tracking-wide">Serial No.</span>
                                            <span class="font-black text-slate-700">{{ $asset->serial_number }}</span>
                                        </div>
                                        @endif
                                        <div class="flex items-center justify-between text-[10px]">
                                            <span class="font-bold text-slate-400 uppercase tracking-wide">Location</span>
                                            <span class="font-bold text-slate-600 uppercase text-right max-w-[180px] truncate">{{ $asset->school_name ?: '—' }}{{ $asset->office_name ? ' / ' . $asset->office_name : '' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between text-[10px]">
                                            <span class="font-bold text-slate-400 uppercase tracking-wide">Acq. Date</span>
                                            <span class="font-bold text-slate-600">{{ $asset->acquisition_date ? \Carbon\Carbon::parse($asset->acquisition_date)->format('M d, Y') : '—' }}</span>
                                        </div>
                                    </div>

                                    {{-- Right: Timeline --}}
                                    <div>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Movement History</p>
                                        <div class="space-y-2">
                                            {{-- Assigned Event (always first) --}}
                                            <div class="flex items-start gap-2">
                                                <div class="flex flex-col items-center shrink-0">
                                                    <div class="w-2 h-2 rounded-full bg-emerald-400 mt-1 ring-2 ring-emerald-100"></div>
                                                    @if($hasTransfers)<div class="w-px flex-grow bg-slate-200 mt-1 h-4"></div>@endif
                                                </div>
                                                <div>
                                                    <p class="text-[10px] font-black text-slate-700 uppercase leading-none">Assigned to Custodian</p>
                                                    <p class="text-[9px] font-semibold text-slate-400 mt-0.5">{{ $asset->assigned_at ? \Carbon\Carbon::parse($asset->assigned_at)->format('M d, Y') : '—' }}</p>
                                                </div>
                                            </div>

                                            {{-- Transfer Events --}}
                                            @if($hasTransfers)
                                                @foreach($assetTransfers->reverse() as $tr)
                                                @php
                                                    $trType = $tr->transfer_type ?? 'Transfer';
                                                    $isLast = $loop->last;
                                                    $dotColor = match(strtolower($trType)) {
                                                        'return' => 'bg-amber-400 ring-amber-100',
                                                        'permanent', 'loan' => 'bg-blue-400 ring-blue-100',
                                                        'repair' => 'bg-orange-400 ring-orange-100',
                                                        default => 'bg-slate-400 ring-slate-100',
                                                    };
                                                @endphp
                                                <div class="flex items-start gap-2">
                                                    <div class="flex flex-col items-center shrink-0">
                                                        <div class="w-2 h-2 rounded-full {{ $dotColor }} mt-1 ring-2"></div>
                                                        @if(!$isLast)<div class="w-px flex-grow bg-slate-200 mt-1 h-4"></div>@endif
                                                    </div>
                                                    <div>
                                                        <p class="text-[10px] font-black text-slate-700 uppercase leading-none">{{ $trType }}</p>
                                                        @if($tr->to_office || $tr->to_custodian)
                                                        <p class="text-[9px] font-semibold text-slate-500 mt-0.5 uppercase truncate max-w-[200px]">→ {{ $tr->to_office ?: $tr->to_custodian }}</p>
                                                        @endif
                                                        @if($tr->remarks)
                                                        <p class="text-[9px] font-semibold text-slate-400 mt-0.5 italic truncate max-w-[200px]">{{ $tr->remarks }}</p>
                                                        @endif
                                                        <p class="text-[9px] font-semibold text-slate-400 mt-0.5">{{ $tr->transfer_date ? \Carbon\Carbon::parse($tr->transfer_date)->format('M d, Y') : '—' }}</p>
                                                    </div>
                                                </div>
                                                @endforeach
                                            @else
                                            {{-- Still in custody --}}
                                            <div class="flex items-start gap-2">
                                                <div class="w-2 h-2 rounded-full bg-deped mt-1 ring-2 ring-red-100 shrink-0"></div>
                                                <div>
                                                    <p class="text-[10px] font-black text-deped uppercase leading-none">Still Under Custody</p>
                                                    <p class="text-[9px] font-semibold text-slate-400 mt-0.5">No transfers recorded</p>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="flex flex-col items-center justify-center py-12 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest italic">No assets assigned to this custodian yet.</p>
                        </div>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>

</body>
</html>
