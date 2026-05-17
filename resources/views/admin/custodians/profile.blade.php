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

        /* Filter & Sort toolbar */
        .f-chip { padding: 5px 14px; font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; border-radius: 9999px; border: 1.5px solid #e5e7eb; background: #fff; color: #64748b; cursor: pointer; transition: all 0.15s; white-space: nowrap; }
        .f-chip:hover { border-color: #94a3b8; color: #334155; }
        .f-chip.active { background: #0f172a; color: #fff; border-color: #0f172a; }
        .f-chip.active-red { background: #c00000; color: #fff; border-color: #c00000; }
        .f-chip.active-blue { background: #2563eb; color: #fff; border-color: #2563eb; }
        .f-chip.active-amber { background: #d97706; color: #fff; border-color: #d97706; }
        .f-chip.active-gray { background: #64748b; color: #fff; border-color: #64748b; }
        .sort-select { font-size: 10px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.06em; background: #fff; border: 1.5px solid #e5e7eb; border-radius: 0.75rem; padding: 6px 12px; outline: none; cursor: pointer; transition: border-color 0.15s; }
        .sort-select:focus { border-color: #94a3b8; }

        /* Calendar Date Picker */
        .cal-panel { position: absolute; top: calc(100% + 8px); left: 0; z-index: 9999; background: #fff; border: 1.5px solid #e5e7eb; border-radius: 1.25rem; box-shadow: 0 12px 40px rgba(0,0,0,0.12); padding: 1.1rem; width: 260px; }
        .cal-year-nav { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem; }
        .cal-year-btn { width: 28px; height: 28px; border-radius: 8px; border: 1.5px solid #e5e7eb; background: #f8fafc; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #64748b; font-weight: 900; font-size: 12px; transition: all 0.15s; }
        .cal-year-btn:hover { background: #0f172a; color: #fff; border-color: #0f172a; }
        .cal-year-label { font-size: 11px; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: 0.12em; }
        .cal-month-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; }
        .cal-month-btn { padding: 7px 4px; border-radius: 10px; border: 1.5px solid #f1f5f9; background: #f8fafc; font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.07em; color: #64748b; cursor: pointer; text-align: center; transition: all 0.15s; }
        .cal-month-btn:hover { border-color: #cbd5e1; background: #f1f5f9; color: #334155; }
        .cal-month-btn.selected { background: #c00000; border-color: #c00000; color: #fff; box-shadow: 0 2px 8px rgba(192,0,0,0.25); }
        .cal-month-btn.has-data { border-color: #e2e8f0; }
        .cal-trigger { display: flex; align-items: center; gap: 6px; padding: 6px 14px; background: #fff; border: 1.5px solid #e5e7eb; border-radius: 9999px; font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; color: #64748b; cursor: pointer; transition: all 0.15s; white-space: nowrap; }
        .cal-trigger:hover { border-color: #94a3b8; color: #334155; }
        .cal-trigger.has-selection { background: #0f172a; color: #fff; border-color: #0f172a; }
        .date-chip { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px 3px 10px; background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 9999px; font-size: 8.5px; font-weight: 900; color: #c00000; text-transform: uppercase; letter-spacing: 0.08em; }
        .date-chip button { background: none; border: none; cursor: pointer; color: #c00000; font-size: 10px; line-height: 1; padding: 0; font-weight: 900; opacity: 0.7; }
        .date-chip button:hover { opacity: 1; }

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

                        {{-- ===== FILTER & SORT TOOLBAR ===== --}}
                        @php
                            $availableYears = $assets
                                ->map(fn($a) => $a->acquisition_date ? (int)date('Y', strtotime($a->acquisition_date)) : null)
                                ->filter()
                                ->unique()
                                ->sort()
                                ->values();
                        @endphp
                        <div class="space-y-3 mb-5 pb-4 border-b border-slate-100">

                            {{-- Row 1: Status chips --}}
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mr-1 shrink-0">Status:</span>
                                <button onclick="setCustodianFilter('all','all')" id="f-all" class="f-chip active">All</button>
                                <button onclick="setCustodianFilter('under-custody','')" id="f-under-custody" class="f-chip">Under Custody</button>
                                <button onclick="setCustodianFilter('transferred','active-blue')" id="f-transferred" class="f-chip">Transferred</button>
                                <button onclick="setCustodianFilter('returned','active-amber')" id="f-returned" class="f-chip">Returned</button>
                                <button onclick="setCustodianFilter('repair','active-gray')" id="f-repair" class="f-chip">Out for Repair</button>
                                <button onclick="setCustodianFilter('unserviceable','active-red')" id="f-unserviceable" class="f-chip">Unserviceable</button>
                            </div>

                            {{-- Row 2: Calendar Date Picker + Sort --}}
                            <div class="flex flex-wrap items-center gap-3">

                                {{-- Calendar Trigger + Floating Panel --}}
                                <div class="relative" id="cal-wrap">
                                    <button onclick="toggleCalPanel(event)" id="cal-trigger-btn" class="cal-trigger">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        <span id="cal-trigger-label">Filter by Date</span>
                                        <span id="cal-badge" class="bg-red-200 text-deped rounded-full px-1.5 py-0.5 text-[7px] font-black" style="display:none">0</span>
                                    </button>

                                    {{-- Floating Calendar Panel --}}
                                    <div id="cal-panel" class="cal-panel" style="display:none">
                                        {{-- Year Navigation --}}
                                        <div class="cal-year-nav">
                                            <button class="cal-year-btn" onclick="calNavYear(-1)">&#8249;</button>
                                            <span class="cal-year-label" id="cal-year-display">{{ $availableYears->sort()->first() ?? date('Y') }}</span>
                                            <button class="cal-year-btn" onclick="calNavYear(1)">&#8250;</button>
                                        </div>
                                        {{-- Month Grid (rendered by JS) --}}
                                        <div class="cal-month-grid" id="cal-month-grid"></div>
                                        {{-- Footer --}}
                                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100">
                                            <button onclick="clearAllDates()" class="text-[9px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors">Clear All</button>
                                            <button onclick="toggleCalPanel()" class="text-[9px] font-black text-slate-800 uppercase tracking-widest bg-slate-100 px-3 py-1.5 rounded-lg hover:bg-slate-200 transition-colors">Done</button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Selected Date Chips --}}
                                <div id="date-chips-container" class="flex flex-wrap gap-1.5"></div>

                                {{-- Sort --}}
                                <div class="ml-auto flex items-center gap-2">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Sort:</span>
                                    <select onchange="setCustodianSort(this.value)" class="sort-select">
                                        <option value="date-desc">Date: Newest First</option>
                                        <option value="date-asc">Date: Oldest First</option>
                                        <option value="cost-desc">Cost: High &rarr; Low</option>
                                        <option value="cost-asc">Cost: Low &rarr; High</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3" id="custodian-asset-list">
                            @foreach($assets as $asset)
                            @php
                                $assetTransfers = $transfers->get($asset->id, collect());
                                $hasTransfers   = $assetTransfers->count() > 0;
                                $lastTransfer   = $hasTransfers ? $assetTransfers->first() : null;
                                $totalEvents    = $assetTransfers->count() + 1; // +1 for the initial assignment

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

                            {{-- Each card has its own Alpine state --}}
                            @php
                                $statusKey = 'under-custody';
                                if ($statusLabel === 'Transferred') $statusKey = 'transferred';
                                elseif ($statusLabel === 'Returned') $statusKey = 'returned';
                                elseif ($statusLabel === 'Out for Repair') $statusKey = 'repair';
                                elseif ($statusLabel === 'Unserviceable') $statusKey = 'unserviceable';
                                $ts    = $asset->acquisition_date ? strtotime($asset->acquisition_date) : 0;
                                $yr    = $asset->acquisition_date ? date('Y', $ts) : 0;
                                $mo    = $asset->acquisition_date ? date('n', $ts) : 0;
                            @endphp
                            <div class="asset-card"
                                 x-data="{ showHistory: false }"
                                 data-status="{{ $statusKey }}"
                                 data-cost="{{ $asset->asset_cost }}"
                                 data-date="{{ $ts }}"
                                 data-year="{{ $yr }}"
                                 data-month="{{ $mo }}">

                                {{-- ===== MAIN ASSET ROW ===== --}}
                                <div class="flex flex-col sm:flex-row sm:items-center gap-4 px-4 py-3.5">

                                    {{-- Icon + Name --}}
                                    <div class="flex items-center gap-3 flex-grow min-w-0">
                                        <div class="w-9 h-9 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="text-xs font-black text-slate-800 uppercase leading-none">{{ $asset->item_name }}</h4>
                                            <p class="text-[9px] font-bold text-slate-400 uppercase mt-1">
                                                {{ $asset->category_name }}
                                                {{ $asset->brand ? ' · ' . $asset->brand : '' }}
                                                {{ $asset->model ? ' · ' . $asset->model : '' }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Meta columns --}}
                                    <div class="hidden md:flex items-center gap-6 text-right shrink-0">
                                        <div>
                                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Property No.</p>
                                            <p class="text-[10px] font-black text-slate-700 uppercase mt-0.5 font-mono">{{ $asset->property_number }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Location</p>
                                            <p class="text-[10px] font-bold text-slate-700 uppercase mt-0.5 max-w-[150px] truncate">{{ $asset->school_name ?: '—' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Cost</p>
                                            <p class="text-[11px] font-black text-deped italic mt-0.5">₱ {{ number_format($asset->asset_cost, 2) }}</p>
                                        </div>
                                    </div>

                                    {{-- Badges + Toggle --}}
                                    <div class="flex items-center gap-2 flex-wrap shrink-0">
                                        <span class="flex items-center gap-1.5 text-[8px] font-black uppercase tracking-wide px-2 py-1 rounded-lg border {{ $statusBg }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $statusDot }}"></span>
                                            {{ $statusLabel }}
                                        </span>
                                        <span class="text-[8px] font-black uppercase tracking-wide px-2 py-1 rounded-lg border {{ $condBadge }}">{{ $asset->condition ?: 'Good' }}</span>

                                        {{-- Movement History Toggle Button --}}
                                        <button
                                            @click="showHistory = !showHistory"
                                            :class="showHistory ? 'bg-slate-800 text-white border-slate-700' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400'"
                                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-[8px] font-black uppercase tracking-widest transition-all duration-200">
                                            <svg class="w-3 h-3 transition-transform duration-200" :class="showHistory ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                            </svg>
                                            <span x-text="showHistory ? 'Hide History' : 'View History'"></span>
                                            <span class="bg-slate-200 text-slate-600 rounded-full px-1.5 py-0.5 text-[7px] font-black" :class="showHistory ? 'bg-slate-600 text-white' : ''">{{ $totalEvents }}</span>
                                        </button>
                                    </div>
                                </div>

                                {{-- ===== COLLAPSIBLE HISTORY PANEL ===== --}}
                                <div
                                    x-show="showHistory"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-1"
                                    x-cloak
                                    class="border-t border-slate-100 bg-slate-50/60 px-5 py-4">

                                    <div class="flex items-center gap-2 mb-4">
                                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-[0.16em]">Movement History</p>
                                        <div class="flex-grow h-px bg-slate-200"></div>
                                        <span class="text-[8px] font-black text-slate-400 uppercase">{{ $totalEvents }} Event(s)</span>
                                    </div>

                                    <div class="relative pl-5">
                                        {{-- Vertical connector --}}
                                        <div class="absolute left-[9px] top-2 w-px bg-gradient-to-b from-slate-300 to-transparent" style="height: calc(100% - 1rem);"></div>

                                        <div class="space-y-4">

                                            {{-- EVENT: Assigned --}}
                                            <div class="relative flex items-start gap-3">
                                                <div class="timeline-dot bg-emerald-500 text-emerald-500 mt-0.5 absolute -left-5 shrink-0"></div>
                                                <div class="pl-1 pb-1">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <p class="text-[10px] font-black text-slate-800 uppercase">Assigned to Custodian</p>
                                                        <span class="text-[8px] font-black text-emerald-600 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded-md uppercase">Initial</span>
                                                    </div>
                                                    <p class="text-[9px] font-semibold text-slate-400 mt-0.5">{{ $asset->assigned_at ? \Carbon\Carbon::parse($asset->assigned_at)->format('M d, Y') : '—' }}</p>
                                                </div>
                                            </div>

                                            @if($hasTransfers)
                                                @foreach($assetTransfers->reverse() as $tr)
                                                @php
                                                    $trType  = $tr->transfer_type ?? 'Transfer';
                                                    $trDot   = match(strtolower($trType)) {
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
                                                    $trBadge = match(strtolower($trType)) {
                                                        'return'            => 'text-amber-600 bg-amber-50 border-amber-200',
                                                        'permanent', 'loan' => 'text-blue-600 bg-blue-50 border-blue-200',
                                                        'repair'            => 'text-orange-600 bg-orange-50 border-orange-200',
                                                        default             => 'text-slate-600 bg-slate-100 border-slate-200',
                                                    };
                                                @endphp
                                                <div class="relative flex items-start gap-3">
                                                    <div class="timeline-dot {{ $trDot }} mt-0.5 absolute -left-5 shrink-0"></div>
                                                    <div class="pl-1 pb-1 min-w-0 flex-grow">
                                                        <div class="flex items-center gap-2 flex-wrap">
                                                            <p class="text-[10px] font-black text-slate-800 uppercase">{{ $trLabel }}</p>
                                                            <span class="text-[8px] font-black {{ $trBadge }} border px-1.5 py-0.5 rounded-md uppercase">{{ $trType }}</span>
                                                        </div>
                                                        @if($tr->to_office || $tr->to_custodian)
                                                        <p class="text-[9px] font-semibold text-slate-600 uppercase mt-0.5">→ {{ $tr->to_office ?: $tr->to_custodian }}</p>
                                                        @endif
                                                        @if($tr->remarks)
                                                        <p class="text-[9px] text-slate-400 italic mt-0.5">{{ $tr->remarks }}</p>
                                                        @endif
                                                        <p class="text-[9px] font-semibold text-slate-400 mt-0.5">{{ $tr->transfer_date ? \Carbon\Carbon::parse($tr->transfer_date)->format('M d, Y') : '—' }}</p>
                                                    </div>
                                                </div>
                                                @endforeach
                                            @else
                                            <div class="relative flex items-start gap-3">
                                                <div class="timeline-dot bg-deped text-deped mt-0.5 absolute -left-5 shrink-0"></div>
                                                <div class="pl-1 pb-1">
                                                    <p class="text-[10px] font-black text-deped uppercase">Currently Under Custody</p>
                                                    <p class="text-[9px] font-semibold text-slate-400 mt-0.5">No transfers on record</p>
                                                </div>
                                            </div>
                                            @endif

                                        </div>
                                    </div>
                                </div>

                            </div>
                            @endforeach
                        </div>
                        {{-- Empty message when filter matches nothing --}}
                        <div id="custodian-filter-empty" style="display:none;" class="flex flex-col items-center justify-center py-10 bg-slate-50 rounded-2xl border border-dashed border-slate-200 mt-4">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest italic">No assets match the selected filter.</p>
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

<script>
    // =========================================================
    // DATA: Available years from PHP
    // =========================================================
    const CAL_YEARS = @json($availableYears->sort()->values());
    const MONTHS_SHORT = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const MONTHS_LONG  = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    // =========================================================
    // STATE
    // =========================================================
    let custodianActiveFilter = 'all';
    let custodianSortOrder    = 'date-desc';
    let selectedDates         = []; // array of "YYYY-M" strings
    let calViewYear           = CAL_YEARS.length ? parseInt(CAL_YEARS[0]) : new Date().getFullYear();
    let calPanelOpen          = false;

    // =========================================================
    // STATUS FILTER
    // =========================================================
    function setCustodianFilter(filter, activeClass) {
        custodianActiveFilter = filter;
        document.querySelectorAll('.f-chip').forEach(b => {
            b.classList.remove('active', 'active-red', 'active-blue', 'active-amber', 'active-gray');
        });
        const btn = document.getElementById('f-' + filter);
        if (btn) btn.classList.add(activeClass || 'active');
        applyCustodianFilters();
    }

    // =========================================================
    // SORT
    // =========================================================
    function setCustodianSort(val) {
        custodianSortOrder = val;
        applyCustodianFilters();
    }

    // =========================================================
    // CALENDAR PANEL
    // =========================================================
    function toggleCalPanel(e) {
        if (e) e.stopPropagation();
        calPanelOpen = !calPanelOpen;
        document.getElementById('cal-panel').style.display = calPanelOpen ? '' : 'none';
        if (calPanelOpen) renderCalMonthGrid();
    }

    function calNavYear(dir) {
        calViewYear += dir;
        document.getElementById('cal-year-display').textContent = calViewYear;
        renderCalMonthGrid();
    }

    function renderCalMonthGrid() {
        const grid = document.getElementById('cal-month-grid');
        if (!grid) return;
        grid.innerHTML = '';

        // Collect months that have actual asset data for this year
        const container = document.getElementById('custodian-asset-list');
        const dataMonths = new Set();
        if (container) {
            container.querySelectorAll('.asset-card').forEach(card => {
                if (card.dataset.year === String(calViewYear)) dataMonths.add(card.dataset.month);
            });
        }

        MONTHS_SHORT.forEach((name, idx) => {
            const m    = idx + 1;
            const key  = `${calViewYear}-${m}`;
            const isSel = selectedDates.includes(key);
            const hasData = dataMonths.has(String(m));

            const btn = document.createElement('button');
            btn.textContent = name;
            btn.className   = 'cal-month-btn' + (hasData ? ' has-data' : '') + (isSel ? ' selected' : '');
            btn.title       = MONTHS_LONG[idx] + ' ' + calViewYear + (hasData ? '' : ' (no assets)');
            btn.onclick     = () => toggleDateKey(key, btn);
            grid.appendChild(btn);
        });
    }

    function toggleDateKey(key, btn) {
        const idx = selectedDates.indexOf(key);
        if (idx >= 0) {
            selectedDates.splice(idx, 1);
            btn.classList.remove('selected');
        } else {
            selectedDates.push(key);
            btn.classList.add('selected');
        }
        updateCalUI();
        applyCustodianFilters();
    }

    function clearAllDates() {
        selectedDates = [];
        updateCalUI();
        applyCustodianFilters();
        renderCalMonthGrid(); // re-render to clear highlights
    }

    function updateCalUI() {
        const count  = selectedDates.length;
        const badge  = document.getElementById('cal-badge');
        const label  = document.getElementById('cal-trigger-label');
        const trigger = document.getElementById('cal-trigger-btn');
        const chipsContainer = document.getElementById('date-chips-container');

        // Badge
        if (badge)  { badge.textContent = count; badge.style.display = count > 0 ? '' : 'none'; }
        // Label
        if (label)  label.textContent = count > 0 ? count + ' Date' + (count > 1 ? 's' : '') + ' Selected' : 'Filter by Date';
        // Trigger style
        if (trigger) trigger.classList.toggle('has-selection', count > 0);

        // Chips
        if (chipsContainer) {
            chipsContainer.innerHTML = '';
            selectedDates.forEach(key => {
                const [yr, mo] = key.split('-').map(Number);
                const chip = document.createElement('div');
                chip.className = 'date-chip';
                chip.innerHTML = `<span>${MONTHS_SHORT[mo - 1]} ${yr}</span><button onclick="removeDateChip('${key}')" title="Remove">&times;</button>`;
                chipsContainer.appendChild(chip);
            });
        }
    }

    function removeDateChip(key) {
        selectedDates = selectedDates.filter(d => d !== key);
        updateCalUI();
        applyCustodianFilters();
        if (calPanelOpen) renderCalMonthGrid();
    }

    // Close panel on outside click
    document.addEventListener('click', e => {
        const wrap = document.getElementById('cal-wrap');
        if (calPanelOpen && wrap && !wrap.contains(e.target)) {
            calPanelOpen = false;
            document.getElementById('cal-panel').style.display = 'none';
        }
    });

    // =========================================================
    // MAIN FILTER + SORT ENGINE
    // =========================================================
    function applyCustodianFilters() {
        const container = document.getElementById('custodian-asset-list');
        if (!container) return;
        const cards = [...container.querySelectorAll('.asset-card')];

        // Sort
        cards.sort((a, b) => {
            const aDate = parseFloat(a.dataset.date || 0);
            const bDate = parseFloat(b.dataset.date || 0);
            const aCost = parseFloat(a.dataset.cost || 0);
            const bCost = parseFloat(b.dataset.cost || 0);
            switch (custodianSortOrder) {
                case 'date-asc':  return aDate - bDate;
                case 'date-desc': return bDate - aDate;
                case 'cost-asc':  return aCost - bCost;
                case 'cost-desc': return bCost - aCost;
                default:          return bDate - aDate;
            }
        });
        cards.forEach(c => container.appendChild(c));

        // Filter
        let visible = 0;
        cards.forEach(card => {
            const matchStatus = custodianActiveFilter === 'all' || card.dataset.status === custodianActiveFilter;
            const cardKey     = `${card.dataset.year}-${parseInt(card.dataset.month)}`;
            const matchDate   = selectedDates.length === 0 || selectedDates.includes(cardKey);
            const show = matchStatus && matchDate;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        const empty = document.getElementById('custodian-filter-empty');
        if (empty) empty.style.display = visible === 0 ? '' : 'none';
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Init calendar view year to most recent year with data
        if (CAL_YEARS.length) calViewYear = parseInt(CAL_YEARS[0]); // oldest year first
        document.getElementById('cal-year-display').textContent = calViewYear;
        applyCustodianFilters();
    });
</script>
</body>
</html>
