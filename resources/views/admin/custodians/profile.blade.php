<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $custodian->first_name }} {{ $custodian->last_name }} | Custodian Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,800&display=swap" rel="stylesheet">
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f5f7; }
        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #dde1e7; border-radius: 10px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #c00000; }
        [x-cloak] { display: none !important; }

        /* Animations */
        .anim-0 { animation: fadeUp 0.45s cubic-bezier(.22,.68,0,1.2) forwards; opacity: 0; }
        .anim-1 { animation: fadeUp 0.45s cubic-bezier(.22,.68,0,1.2) 0.08s forwards; opacity: 0; }
        .anim-2 { animation: fadeUp 0.45s cubic-bezier(.22,.68,0,1.2) 0.16s forwards; opacity: 0; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
        .tab-fade { animation: tabIn 0.28s ease-out forwards; }
        @keyframes tabIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

        /* Cards */
        .glass-card { background: #fff; border: 1px solid #eaecf0; border-radius: 1.5rem; box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
        .stat-card { border-radius: 1.25rem; padding: 1.1rem 1.25rem; position: relative; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; cursor: default; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.07); }

        /* Asset Cards */
        .asset-card { background: #fff; border: 1px solid #eaecf0; border-radius: 1.25rem; overflow: hidden; transition: box-shadow 0.2s, border-color 0.2s; }
        .asset-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,0.07); border-color: #d1d5db; }
        .asset-card-header { background: linear-gradient(to right, #fafafa, #f7f8fa); border-bottom: 1px solid #f0f2f5; padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap; }

        /* Timeline */
        .timeline-line { width: 2px; background: linear-gradient(to bottom, #e2e8f0, transparent); border-radius: 9999px; }
        .timeline-dot { width: 10px; height: 10px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 0 2px currentColor; flex-shrink: 0; }

        /* Info Rows */
        .info-row { display: flex; align-items: center; justify-content: space-between; padding: 0.45rem 0; border-bottom: 1px dashed #f0f2f5; }
        .info-row:last-child { border-bottom: none; }

        /* Dark mode */
        html.dark body { background: #0b0f19; }
        html.dark .glass-card, html.dark .asset-card { background: #1e293b; border-color: #334155; }
        html.dark .asset-card-header { background: #1a2540; border-color: #334155; }
        html.dark .info-row { border-color: #334155; }
        html.dark .bg-white { background-color: #1e293b !important; border-color: #334155 !important; }
        html.dark .border-slate-200 { border-color: #334155 !important; }
        html.dark .border-slate-100 { border-color: #334155 !important; }
        html.dark .text-slate-900 { color: #f8fafc !important; }
        html.dark .text-slate-800 { color: #e2e8f0 !important; }
        html.dark .text-slate-700 { color: #cbd5e1 !important; }
        html.dark .bg-slate-50 { background: #0f172a !important; }
    </style>
</head>
<body class="flex min-h-screen text-slate-800 overflow-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll p-4 lg:p-8 gap-5" x-data="{ activeTab: 'assets' }">

        {{-- ===== STICKY HEADER ===== --}}
        <header class="bg-white rounded-2xl shadow-sm border border-slate-200 px-6 py-5 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 sticky top-0 z-50 anim-0">
            <div class="flex items-center gap-4">
                {{-- Avatar --}}
                <div class="w-14 h-14 bg-deped_light rounded-2xl flex items-center justify-center border border-deped/15 shadow-sm shrink-0 text-deped font-black text-xl uppercase select-none">
                    {{ substr($custodian->first_name, 0, 1) }}{{ substr($custodian->last_name, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-900 tracking-tight leading-none uppercase italic">
                        {{ $custodian->first_name }}
                        @if($custodian->middle_name) {{ substr($custodian->middle_name,0,1) }}. @endif
                        {{ $custodian->last_name }}
                    </h1>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">{{ $custodian->employee_id }}</span>
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">{{ $custodian->position ?? 'Personnel' }}</span>
                        @php $isActive = strtolower($custodian->status ?? '') === 'active'; @endphp
                        <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg flex items-center gap-1.5 {{ $isActive ? 'text-emerald-700 bg-emerald-50 border border-emerald-200' : 'text-slate-500 bg-slate-100 border border-slate-200' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $isActive ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                            {{ ucfirst($custodian->status ?? 'Unknown') }}
                        </span>
                    </div>
                </div>
            </div>

            <a href="{{ route('admin.custodians') }}" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 uppercase tracking-widest hover:border-deped hover:text-deped hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-sm hover:shadow-md flex items-center gap-2 group shrink-0">
                <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                Back to Registry
            </a>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 flex-grow pb-10">

            {{-- ===== LEFT SIDEBAR ===== --}}
            <aside class="lg:col-span-3 flex flex-col gap-5 anim-1">

                {{-- Info Card --}}
                <div class="glass-card p-5 space-y-5">
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.18em] mb-3">Personnel Details</p>
                        <div class="space-y-3">
                            @foreach([
                                ['icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z', 'label' => 'Full Name', 'value' => $custodian->first_name . ' ' . ($custodian->middle_name ?? '') . ' ' . $custodian->last_name],
                                ['icon' => 'M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.17-.789 3.376 3.376 0 0 1 6.34 0Z', 'label' => 'Employee ID', 'value' => $custodian->employee_id ?: '—'],
                                ['icon' => 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z', 'label' => 'Contact', 'value' => $custodian->contact_number ?: '—'],
                            ] as $row)
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5 text-deped" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $row['icon'] }}"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">{{ $row['label'] }}</p>
                                    <p class="text-[11px] font-black text-slate-800 uppercase leading-tight mt-0.5">{{ trim($row['value']) }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Stats --}}
                    <div class="pt-4 border-t border-slate-100 space-y-3">
                        <div class="stat-card bg-deped_light border border-red-100">
                            <p class="text-[9px] font-black text-deped uppercase tracking-[0.16em] mb-1">Assets in Custody</p>
                            <p class="text-3xl font-black text-deped leading-none">{{ $stats->total_assets }}</p>
                            <p class="text-[9px] font-bold text-deped/50 mt-1.5 uppercase">Assigned Items</p>
                        </div>
                        <div class="stat-card bg-emerald-50 border border-emerald-100">
                            <p class="text-[9px] font-black text-emerald-600 uppercase tracking-[0.16em] mb-1">Total Value</p>
                            <p class="text-xl font-black text-emerald-700 leading-none italic">₱ {{ number_format($stats->total_value, 2) }}</p>
                            <p class="text-[9px] font-bold text-emerald-600/50 mt-1.5 uppercase">Cumulative Cost</p>
                        </div>
                    </div>

                    {{-- Schools --}}
                    @if($schools->count() > 0)
                    <div class="pt-4 border-t border-slate-100">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.18em] mb-3">Assigned School(s)</p>
                        <div class="space-y-2">
                            @foreach($schools as $school)
                            <div class="flex items-center justify-between px-3 py-2.5 bg-slate-50 border border-slate-100 rounded-xl">
                                <p class="text-[10px] font-bold text-slate-700 uppercase leading-tight truncate max-w-[140px]">{{ $school->name }}</p>
                                <span class="text-[9px] font-black text-deped bg-deped_light border border-red-100 px-2 py-0.5 rounded-full shrink-0 ml-2">{{ $school->asset_count }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

            </aside>

            {{-- ===== MAIN CONTENT ===== --}}
            <div class="lg:col-span-9 flex flex-col glass-card overflow-hidden anim-2">
                {{-- Tabs --}}
                <div class="flex border-b border-slate-100 bg-slate-50/60 px-3 pt-3">
                    <button @click="activeTab = 'assets'"
                        :class="{'bg-white border-slate-200 border-b-white text-deped shadow-sm': activeTab === 'assets', 'border-transparent text-slate-400 hover:text-slate-600 hover:bg-slate-100': activeTab !== 'assets'}"
                        class="px-6 py-3 text-[10px] font-black uppercase tracking-[0.14em] border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        Assigned Equipment
                    </button>
                </div>

                <div class="p-5 flex-grow overflow-y-auto custom-scroll">
                    <div x-show="activeTab === 'assets'" class="tab-fade">
                        @if($assets->count() > 0)
                        <div class="space-y-4">
                            @foreach($assets as $asset)
                            @php
                                $assetTransfers = $transfers->get($asset->id, collect());
                                $hasTransfers   = $assetTransfers->count() > 0;
                                $lastTransfer   = $hasTransfers ? $assetTransfers->first() : null;

                                $statusLabel = 'Under Custody';
                                $statusDot   = 'bg-emerald-500';
                                $statusBg    = 'bg-emerald-50 text-emerald-700 border-emerald-200';

                                if ($lastTransfer) {
                                    $type = strtolower($lastTransfer->transfer_type ?? '');
                                    if (in_array($type, ['permanent', 'loan'])) {
                                        $statusLabel = 'Transferred';
                                        $statusDot   = 'bg-blue-500';
                                        $statusBg    = 'bg-blue-50 text-blue-700 border-blue-200';
                                    } elseif ($type === 'return') {
                                        $statusLabel = 'Returned';
                                        $statusDot   = 'bg-amber-500';
                                        $statusBg    = 'bg-amber-50 text-amber-700 border-amber-200';
                                    } elseif ($type === 'repair') {
                                        $statusLabel = 'Out for Repair';
                                        $statusDot   = 'bg-orange-500';
                                        $statusBg    = 'bg-orange-50 text-orange-700 border-orange-200';
                                    }
                                }
                                if (strtolower($asset->condition ?? '') === 'unserviceable') {
                                    $statusLabel = 'Unserviceable';
                                    $statusDot   = 'bg-red-500';
                                    $statusBg    = 'bg-red-50 text-red-700 border-red-200';
                                }

                                $condGood  = in_array($asset->condition, ['Good', 'Serviceable']);
                                $condBadge = $condGood ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200';
                            @endphp

                            <div class="asset-card">

                                {{-- Header --}}
                                <div class="asset-card-header">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-9 h-9 bg-white border border-slate-200 rounded-xl flex items-center justify-center shrink-0 shadow-sm">
                                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="text-xs font-black text-slate-800 uppercase leading-none tracking-wide">{{ $asset->item_name }}</h4>
                                            <p class="text-[9px] font-bold text-slate-400 uppercase mt-1 tracking-wide">{{ $asset->category_name }}{{ $asset->brand ? ' · ' . $asset->brand : '' }}{{ $asset->model ? ' · ' . $asset->model : '' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 flex-wrap shrink-0">
                                        <span class="flex items-center gap-1.5 text-[8.5px] font-black uppercase tracking-wide px-2.5 py-1 rounded-lg border {{ $statusBg }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $statusDot }}"></span>
                                            {{ $statusLabel }}
                                        </span>
                                        <span class="text-[8.5px] font-black uppercase tracking-wide px-2.5 py-1 rounded-lg border {{ $condBadge }}">{{ $asset->condition ?: 'Good' }}</span>
                                        <span class="text-[11px] font-black text-deped italic font-mono">₱ {{ number_format($asset->asset_cost, 2) }}</span>
                                    </div>
                                </div>

                                {{-- Body: 2-col grid —— info | timeline --}}
                                <div class="grid grid-cols-1 md:grid-cols-5 divide-y md:divide-y-0 md:divide-x divide-slate-100">

                                    {{-- Left: Asset Info (2 of 5) --}}
                                    <div class="md:col-span-2 p-4 space-y-0">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.16em] mb-3">Asset Details</p>
                                        <div class="info-row">
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Property No.</span>
                                            <span class="text-[10px] font-black text-slate-700 uppercase font-mono">{{ $asset->property_number }}</span>
                                        </div>
                                        @if($asset->serial_number)
                                        <div class="info-row">
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Serial No.</span>
                                            <span class="text-[10px] font-black text-slate-700 font-mono">{{ $asset->serial_number }}</span>
                                        </div>
                                        @endif
                                        <div class="info-row">
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Location</span>
                                            <span class="text-[10px] font-bold text-slate-700 uppercase text-right max-w-[140px] leading-tight">{{ $asset->school_name ?: '—' }}@if($asset->office_name)<br><span class="text-[9px] text-slate-400">{{ $asset->office_name }}</span>@endif</span>
                                        </div>
                                        <div class="info-row">
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Acquired</span>
                                            <span class="text-[10px] font-bold text-slate-700">{{ $asset->acquisition_date ? \Carbon\Carbon::parse($asset->acquisition_date)->format('M d, Y') : '—' }}</span>
                                        </div>
                                    </div>

                                    {{-- Right: Movement Timeline (3 of 5) --}}
                                    <div class="md:col-span-3 p-4">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.16em] mb-4">Movement History</p>

                                        <div class="relative pl-4">
                                            {{-- Vertical line --}}
                                            <div class="absolute left-[7px] top-2 bottom-2 w-px bg-gradient-to-b from-slate-200 to-transparent"></div>

                                            <div class="space-y-4">
                                                {{-- STEP 1: Assigned --}}
                                                <div class="relative flex items-start gap-3">
                                                    <div class="timeline-dot bg-emerald-500 text-emerald-500 mt-0.5 absolute -left-4"></div>
                                                    <div class="pl-1">
                                                        <p class="text-[10px] font-black text-slate-800 uppercase leading-none">Assigned to Custodian</p>
                                                        <p class="text-[9px] font-semibold text-slate-400 mt-1">{{ $asset->assigned_at ? \Carbon\Carbon::parse($asset->assigned_at)->format('M d, Y') : '—' }}</p>
                                                    </div>
                                                </div>

                                                @if($hasTransfers)
                                                    @foreach($assetTransfers->reverse() as $tr)
                                                    @php
                                                        $trType = $tr->transfer_type ?? 'Transfer';
                                                        $trDot  = match(strtolower($trType)) {
                                                            'return'            => 'bg-amber-500 text-amber-500',
                                                            'permanent', 'loan' => 'bg-blue-500 text-blue-500',
                                                            'repair'            => 'bg-orange-500 text-orange-500',
                                                            default             => 'bg-slate-400 text-slate-400',
                                                        };
                                                        $trLabel = match(strtolower($trType)) {
                                                            'return'    => 'Returned',
                                                            'permanent' => 'Permanently Transferred',
                                                            'loan'      => 'Loaned Out',
                                                            'repair'    => 'Sent for Repair',
                                                            default     => $trType,
                                                        };
                                                    @endphp
                                                    <div class="relative flex items-start gap-3">
                                                        <div class="timeline-dot {{ $trDot }} mt-0.5 absolute -left-4"></div>
                                                        <div class="pl-1 min-w-0">
                                                            <p class="text-[10px] font-black text-slate-800 uppercase leading-none">{{ $trLabel }}</p>
                                                            @if($tr->to_office || $tr->to_custodian)
                                                            <p class="text-[9px] font-semibold text-slate-500 mt-0.5 uppercase">→ {{ $tr->to_office ?: $tr->to_custodian }}</p>
                                                            @endif
                                                            @if($tr->remarks)
                                                            <p class="text-[9px] text-slate-400 italic mt-0.5 truncate max-w-xs">{{ $tr->remarks }}</p>
                                                            @endif
                                                            <p class="text-[9px] font-semibold text-slate-400 mt-1">{{ $tr->transfer_date ? \Carbon\Carbon::parse($tr->transfer_date)->format('M d, Y') : '—' }}</p>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                @else
                                                {{-- Still in custody --}}
                                                <div class="relative flex items-start gap-3">
                                                    <div class="timeline-dot bg-deped text-deped mt-0.5 absolute -left-4"></div>
                                                    <div class="pl-1">
                                                        <p class="text-[10px] font-black text-deped uppercase leading-none">Currently Under Custody</p>
                                                        <p class="text-[9px] font-semibold text-slate-400 mt-1">No transfers on record</p>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="flex flex-col items-center justify-center py-16 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
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
