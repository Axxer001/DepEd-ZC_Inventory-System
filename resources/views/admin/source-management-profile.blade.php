<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $source->name }} | Source Profile</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,800&display=swap" rel="stylesheet">
    
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

        /* Info Rows */
        .info-row { display: flex; align-items: center; justify-content: space-between; padding: 0.45rem 0; border-bottom: 1px dashed #f0f2f5; }
        .info-row:last-child { border-bottom: none; }

        /* Dark mode */
        html.dark body { background: #0b0f19; }
        html.dark .glass-card, html.dark .asset-card { background: #1e293b; border-color: #334155; }
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

    <div class="flex-grow flex flex-col min-w-0 h-screen lg:overflow-hidden overflow-y-auto custom-scroll p-4 lg:p-8 gap-5" x-data="{ activeTab: 'assets' }">

        {{-- ===== STICKY HEADER ===== --}}
        <header class="bg-white rounded-2xl shadow-sm border border-slate-200 px-6 py-5 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 sticky top-0 z-50 anim-0">
            <div class="flex items-center gap-4">
                {{-- Avatar --}}
                <div class="w-14 h-14 bg-deped_light rounded-2xl flex items-center justify-center border border-deped/15 shadow-sm shrink-0 text-deped font-black text-xl uppercase select-none">
                    {{ substr($source->name, 0, 2) }}
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-900 tracking-tight leading-none uppercase italic">
                        {{ $source->name }}
                    </h1>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        @php $isInternal = $source->source_type === 'Internal'; @endphp
                        <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg flex items-center gap-1.5 {{ $isInternal ? 'text-green-700 bg-green-50 border border-green-200' : 'text-blue-700 bg-blue-50 border border-blue-200' }}">
                            {{ $source->source_type }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                @if(auth()->check() && auth()->user()->isSuperAdmin() && auth()->user()->isMainSystem())
                <button onclick="document.getElementById('editSourceModal').classList.remove('hidden')" class="px-5 py-2.5 bg-red-700 text-white border border-red-700 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-red-800 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-md shadow-red-500/20 flex items-center gap-2 group">
                    <svg class="w-4 h-4 group-hover:-translate-y-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.89 1.12l-2.828.941.941-2.828a4.5 4.5 0 011.12-1.89L16.862 4.487zM19.5 7.125L16.862 4.487"/></svg>
                    Edit Source
                </button>
                @endif
                <a href="{{ route('admin.sources') }}" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 uppercase tracking-widest hover:border-deped hover:text-deped hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-sm hover:shadow-md flex items-center gap-2 group shrink-0">
                    <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                    Back
                </a>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 flex-grow lg:min-h-0 pb-10">

            {{-- ===== LEFT SIDEBAR ===== --}}
            <aside class="lg:col-span-3 flex flex-col gap-5 anim-1 lg:h-full lg:overflow-y-auto custom-scroll pr-1">

                {{-- Info Card --}}
                <div class="glass-card p-5 space-y-5">
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.18em] mb-3">Contact Details</p>
                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5 text-deped" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Contact Person</p>
                                    <p class="text-[11px] font-black text-slate-800 uppercase leading-tight mt-0.5">{{ $source->contact_person ?: '—' }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5 text-deped" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Position</p>
                                    <p class="text-[11px] font-black text-slate-800 uppercase leading-tight mt-0.5">{{ $source->contact_position ?: '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Stats --}}
                    <div class="pt-4 border-t border-slate-100 space-y-3">
                        <div class="stat-card bg-deped_light border border-red-100">
                            <p class="text-[9px] font-black text-deped uppercase tracking-[0.16em] mb-1">Assets Sourced</p>
                            <p class="text-3xl font-black text-deped leading-none">{{ number_format($stats->total_assets ?? 0) }}</p>
                            <p class="text-[9px] font-bold text-deped/50 mt-1.5 uppercase">Registered Items</p>
                        </div>
                        <div class="stat-card bg-emerald-50 border border-emerald-100">
                            <p class="text-[9px] font-black text-emerald-600 uppercase tracking-[0.16em] mb-1">Total Value</p>
                            <p class="text-xl font-black text-emerald-700 leading-none italic">₱ {{ number_format($stats->total_value ?? 0, 2) }}</p>
                            <p class="text-[9px] font-bold text-emerald-600/50 mt-1.5 uppercase">Cumulative Cost</p>
                        </div>
                    </div>
                </div>

            </aside>

            {{-- ===== MAIN CONTENT ===== --}}
            <div class="lg:col-span-9 flex flex-col glass-card overflow-hidden anim-2 lg:h-full">
                {{-- Tabs --}}
                <div class="flex border-b border-slate-100 bg-slate-50/60 px-3 pt-3">
                    <button @click="activeTab = 'assets'"
                        :class="{'bg-white border-slate-200 border-b-white text-deped shadow-sm': activeTab === 'assets', 'border-transparent text-slate-400 hover:text-slate-600 hover:bg-slate-100': activeTab !== 'assets'}"
                        class="px-6 py-3 text-[10px] font-black uppercase tracking-[0.14em] border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        Active Assets
                    </button>
                    <button @click="activeTab = 'history'"
                        :class="{'bg-white border-slate-200 border-b-white text-deped shadow-sm': activeTab === 'history', 'border-transparent text-slate-400 hover:text-slate-600 hover:bg-slate-100': activeTab !== 'history'}"
                        class="px-6 py-3 text-[10px] font-black uppercase tracking-[0.14em] border border-b-0 rounded-t-xl transition-all relative top-[1px] ml-1">
                        Sourcing History
                    </button>
                </div>

                <div class="p-5 flex-grow overflow-y-auto custom-scroll">
                    <div x-show="activeTab === 'assets'" class="tab-fade">
                        @if($assets->count() > 0)
                            <div class="space-y-3 max-h-[600px] overflow-y-auto pr-2 custom-scroll">
                                @foreach($assets as $asset)
                                    @php
                                        $condRaw = strtolower($asset->condition ?? 'good');
                                        if (str_contains($condRaw, 'good') || str_contains($condRaw, 'serviceable') && !str_contains($condRaw, 'unserviceable')) {
                                            $condBadge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                        } elseif (str_contains($condRaw, 'repair')) {
                                            $condBadge = 'bg-amber-50 text-amber-700 border-amber-200';
                                        } elseif (str_contains($condRaw, 'unserviceable')) {
                                            $condBadge = 'bg-rose-50 text-rose-700 border-rose-200';
                                        } else {
                                            $condBadge = 'bg-slate-50 text-slate-500 border-slate-200';
                                        }
                                    @endphp
                                    <div class="asset-card cursor-pointer hover:border-deped transition-colors" onclick="window.location.href='{{ route('assets.profile', $asset->id) }}'">
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
                                                    </p>
                                                </div>
                                            </div>

                                            {{-- Meta columns --}}
                                            <div class="hidden md:flex items-center gap-6 text-right shrink-0">
                                                <div>
                                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Property No.</p>
                                                    <p class="text-[10px] font-black text-slate-700 uppercase mt-0.5 font-mono">{{ $asset->property_number ?: '—' }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Serial No.</p>
                                                    <p class="text-[10px] font-black text-slate-700 uppercase mt-0.5 font-mono">{{ $asset->serial_number ?: '—' }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Custodian</p>
                                                    <p class="text-[10px] font-bold text-slate-700 uppercase mt-0.5 max-w-[120px] truncate">{{ $asset->custodian_name ?: 'Unassigned' }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Location</p>
                                                    <p class="text-[10px] font-bold text-slate-700 uppercase mt-0.5 max-w-[120px] truncate">{{ $asset->location_name ?: '—' }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Cost</p>
                                                    <p class="text-[11px] font-black text-deped italic mt-0.5">₱ {{ number_format($asset->asset_cost, 2) }}</p>
                                                </div>
                                            </div>

                                            {{-- Badges --}}
                                            <div class="flex items-center gap-2 flex-wrap shrink-0">
                                                <span class="text-[8px] font-black uppercase tracking-wide px-2 py-1 rounded-lg border {{ $condBadge }}">{{ $asset->condition ?: 'Good' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4">
                                {{ $assets->appends(request()->except('assets_page'))->links() }}
                            </div>
                        @else
                            <div class="py-12 flex flex-col items-center justify-center text-center">
                                <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                                </div>
                                <p class="text-sm font-black text-slate-800 uppercase tracking-wide">No Active Assets</p>
                                <p class="text-xs font-bold text-slate-400 mt-1 uppercase">This source has no assets assigned.</p>
                            </div>
                        @endif
                    </div>

                    <div x-show="activeTab === 'history'" class="tab-fade" x-cloak>
                        @if($history->count() > 0)
                            <div class="space-y-3 max-h-[600px] overflow-y-auto pr-2 custom-scroll">
                                @foreach($history as $h)
                                    <div class="asset-card">
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-4 px-4 py-3.5">
                                            {{-- Icon + Name --}}
                                            <div class="flex items-center gap-3 flex-grow min-w-0">
                                                <div class="w-9 h-9 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center shrink-0">
                                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                                </div>
                                                <div class="min-w-0 flex-grow pr-4">
                                                    <h4 class="text-xs font-black text-slate-800 uppercase leading-none">{{ $h->item_name }}</h4>
                                                    @php
                                                        $targetName = 'Unassigned';
                                                        if ($h->employee_name && trim($h->employee_name)) {
                                                            $targetName = $h->employee_name;
                                                        } elseif ($h->school_name) {
                                                            $targetName = $h->school_name;
                                                        } elseif ($h->office_name) {
                                                            $targetName = $h->office_name;
                                                        }
                                                        
                                                        $procName = $h->mode_of_procurement ?: 'N/A';
                                                        $personnel = $h->source_personnel ?: 'N/A';
                                                    @endphp
                                                    <p class="text-[10px] font-bold text-slate-500 uppercase mt-1.5 leading-normal">
                                                        {{ $h->source_name }} ({{ $personnel }}) procured via {{ $procName }} to {{ $targetName }}.
                                                    </p>
                                                </div>
                                            </div>

                                            {{-- Meta columns --}}
                                            <div class="hidden md:flex items-center gap-6 text-right shrink-0">
                                                <div>
                                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Date</p>
                                                    <p class="text-[10px] font-bold text-slate-700 uppercase mt-0.5">{{ \Carbon\Carbon::parse($h->created_at)->format('M d, Y') }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Quantity</p>
                                                    <p class="text-[10px] font-black text-slate-700 uppercase mt-0.5">{{ $h->quantity }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Unit Cost</p>
                                                    <p class="text-[10px] font-bold text-slate-700 uppercase mt-0.5">₱ {{ number_format($h->asset_cost, 2) }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Total</p>
                                                    <p class="text-[11px] font-black text-deped italic mt-0.5">₱ {{ number_format($h->asset_cost * $h->quantity, 2) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4">
                                {{ $history->appends(request()->except('history_page'))->links() }}
                            </div>
                        @else
                            <div class="py-12 flex flex-col items-center justify-center text-center">
                                <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                </div>
                                <p class="text-sm font-black text-slate-800 uppercase tracking-wide">No History Found</p>
                                <p class="text-xs font-bold text-slate-400 mt-1 uppercase">This source has no sourcing history recorded.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Source Modal -->
    <div id="editSourceModal" class="fixed inset-0 z-[100] flex items-center justify-center hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="document.getElementById('editSourceModal').classList.add('hidden')"></div>
        <form method="POST" action="{{ route('admin.sources.update', $source->id) }}" class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-xl mx-4 relative z-10 flex flex-col overflow-hidden border border-slate-100 dark:border-slate-700">
            @csrf
            
            {{-- Modal Header --}}
            <div class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-700 px-6 py-5 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-2xl flex items-center justify-center shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-[0.1em]">Edit Source</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase mt-0.5">Update source details</p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('editSourceModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-200/50 dark:hover:bg-slate-700 p-2.5 rounded-full transition-colors active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto custom-scroll bg-slate-50/50 dark:bg-slate-900/50">
                
                {{-- Always Editable --}}
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
                    <h4 class="text-[10px] font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Always Editable</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest mb-2 ml-1">Source Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required value="{{ $source->name }}" class="w-full bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-white rounded-xl px-4 py-3 text-xs font-black uppercase focus:border-red-500 focus:ring-4 focus:ring-red-500/10 outline-none transition-all shadow-sm hover:border-slate-300 dark:hover:border-slate-600">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest mb-2 ml-1">Source Type <span class="text-red-500">*</span></label>
                            <div class="relative group">
                                <select name="source_type" required class="w-full appearance-none bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-white rounded-xl px-4 py-3 text-xs font-black uppercase focus:border-red-500 focus:ring-4 focus:ring-red-500/10 outline-none transition-all shadow-sm hover:border-slate-300 dark:hover:border-slate-600 cursor-pointer">
                                    <option value="Internal" {{ $source->source_type === 'Internal' ? 'selected' : '' }}>Internal (System)</option>
                                    <option value="External" {{ $source->source_type === 'External' ? 'selected' : '' }}>External (Distributor)</option>
                                </select>
                                <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-hover:text-red-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Conditionally Editable --}}
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
                    <h4 class="text-[10px] font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Editable if Empty</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest mb-2 ml-1">Contact Person</label>
                            <input type="text" name="contact_person" value="{{ $source->contact_person }}" {{ $source->contact_person ? "readonly class='w-full bg-slate-50 dark:bg-slate-800/50 border-2 border-slate-100 dark:border-slate-700/50 text-slate-400 dark:text-slate-500 rounded-xl px-4 py-3 text-xs font-black uppercase cursor-not-allowed outline-none shadow-inner'" : "class='w-full bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-white rounded-xl px-4 py-3 text-xs font-black uppercase focus:border-red-500 focus:ring-4 focus:ring-red-500/10 outline-none transition-all shadow-sm hover:border-slate-300 dark:hover:border-slate-600'" }}>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest mb-2 ml-1">Contact Position</label>
                            <input type="text" name="contact_position" value="{{ $source->contact_position }}" {{ $source->contact_position ? "readonly class='w-full bg-slate-50 dark:bg-slate-800/50 border-2 border-slate-100 dark:border-slate-700/50 text-slate-400 dark:text-slate-500 rounded-xl px-4 py-3 text-xs font-black uppercase cursor-not-allowed outline-none shadow-inner'" : "class='w-full bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-white rounded-xl px-4 py-3 text-xs font-black uppercase focus:border-red-500 focus:ring-4 focus:ring-red-500/10 outline-none transition-all shadow-sm hover:border-slate-300 dark:hover:border-slate-600'" }}>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-700 p-6 flex items-center justify-end gap-3">
                <button type="button" onclick="document.getElementById('editSourceModal').classList.add('hidden')" class="px-6 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white rounded-xl text-xs font-black uppercase tracking-widest transition-colors shadow-sm active:scale-95">Cancel</button>
                <button type="submit" class="px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-emerald-600/30 transition-all active:scale-95 flex items-center justify-center gap-2">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</body>
</html>
