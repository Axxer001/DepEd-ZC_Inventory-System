<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $school->name }} | School Profile</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        [x-cloak] { display: none !important; }
        .animate-fade { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        
        .asset-card { background: #fff; border: 1px solid #eaecf0; border-radius: 1.25rem; overflow: hidden; transition: box-shadow 0.2s, border-color 0.2s; }
        .asset-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,0.07); border-color: #d1d5db; }
        html.dark .asset-card { background: #1e293b; border-color: #334155; }
        html.dark .asset-card:hover { border-color: #475569; }
    </style>
</head>
<body class="flex min-h-screen text-slate-800 overflow-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen lg:overflow-hidden overflow-y-auto custom-scroll p-4 lg:p-8" x-data="{ activeTab: new URLSearchParams(window.location.search).has('assets_page') ? 'assets' : 'buildings' }">
        
        {{-- Global Header --}}
        <header class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 sticky top-0 z-50">
            <div class="flex items-center gap-5">
                <div class="w-12 h-12 bg-deped_light rounded-xl flex items-center justify-center border border-deped/20 shadow-sm shrink-0">
                    <svg class="w-6 h-6 text-deped" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-none uppercase italic">{{ $school->name }}</h1>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-widest bg-slate-100 px-2.5 py-0.5 rounded-md border border-slate-200">School ID: {{ $school->school_id }}</span>
                        <span class="text-[10px] font-black text-emerald-700 uppercase tracking-widest bg-emerald-100 px-2 py-0.5 rounded-full flex items-center gap-1.5 shadow-sm">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span> Active Institution
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('admin.schools') }}" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 uppercase tracking-widest hover:border-deped hover:text-deped hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-sm hover:shadow-md flex items-center gap-2 group">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Back to Registry
                </a>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 flex-grow lg:min-h-0 pb-10">
            
            {{-- Left Sidebar: School Identity --}}
            <aside class="lg:col-span-3 flex flex-col gap-6 z-40 relative lg:h-full lg:overflow-y-auto custom-scroll pr-1">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-6">
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Administrative Location</p>
                        <div class="space-y-3">
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-deped shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <div>
                                    <p class="text-xs font-black text-slate-700 uppercase leading-tight">{{ $school->district_name ?? 'N/A' }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase">District</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-deped shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.483V8.517a2 2 0 011.553-1.943L9 5.222m0 14.778l6-3.111m-6 3.111V5.222m6 11.667l5.447 2.724A2 2 0 0021 17.617V10.65a2 2 0 00-1.553-1.943L15 7.444m0 9.444V7.444M9 5.222l6-2.222m0 0l5.447 2.724A2 2 0 0121 7.617v.898"></path></svg>
                                <div>
                                    <p class="text-xs font-black text-slate-700 uppercase leading-tight">{{ $school->quadrant_name ?? 'N/A' }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase">Quadrant</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100 space-y-4">
                        <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                            <p class="text-[9px] font-black text-emerald-600 uppercase tracking-widest mb-1">Building Assets</p>
                            <p class="text-xl font-black text-emerald-700 leading-none">₱ {{ number_format($buildingStats->total_bldg_cost, 2) }}</p>
                            <p class="text-[10px] font-bold text-emerald-600/70 mt-1 uppercase">{{ $buildingStats->total_buildings }} Building(s)</p>
                        </div>
                        <div class="p-4 bg-blue-50 rounded-xl border border-blue-100">
                            <p class="text-[9px] font-black text-blue-600 uppercase tracking-widest mb-1">Equipment Assets</p>
                            <p class="text-xl font-black text-blue-700 leading-none">₱ {{ number_format($assetStats->total_asset_value, 2) }}</p>
                            <p class="text-[10px] font-bold text-blue-600/70 mt-1 uppercase">{{ $assetStats->total_assets }} Distributed Items</p>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Main Content --}}
            <div class="lg:col-span-9 flex flex-col bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden lg:h-full">
                <div class="flex border-b border-slate-200 bg-slate-50/50 px-2 pt-2">
                    <button @click="activeTab = 'buildings'" :class="{'bg-white border-slate-200 border-b-white text-deped shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'buildings', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'buildings'}" class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        School Buildings
                    </button>
                    <button @click="activeTab = 'assets'" :class="{'bg-white border-slate-200 border-b-white text-deped shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'assets', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'assets'}" class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        Inventory Assets
                    </button>
                    <button @click="activeTab = 'history'" :class="{'bg-white border-slate-200 border-b-white text-deped shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'history', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'history'}" class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px] ml-1">
                        Lifecycle & History
                    </button>
                </div>

                <div class="p-6 flex-grow overflow-y-auto custom-scroll">
                    
                    {{-- TAB: Buildings --}}
                    <div x-show="activeTab === 'buildings'" class="animate-fade space-y-4">
                        @if($buildings->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($buildings as $bldg)
                            <a href="{{ route('buildings.profile', $bldg->id) }}" class="group p-5 bg-white border border-slate-200 rounded-2xl hover:border-deped hover:shadow-md transition-all flex flex-col gap-3">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800 uppercase group-hover:text-deped transition-colors">{{ $bldg->spec_description ?: $bldg->type_name }}</h4>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase mt-0.5">{{ $bldg->property_number }}</p>
                                    </div>
                                    <span class="text-[9px] font-black bg-slate-100 text-slate-500 px-2 py-1 rounded uppercase">{{ $bldg->storeys }} Storey</span>
                                </div>
                                <div class="grid grid-cols-2 gap-4 mt-auto pt-3 border-t border-slate-50">
                                    <div>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Classrooms</p>
                                        <p class="text-xs font-bold text-slate-700">{{ $bldg->classrooms }} Units</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Acq. Cost</p>
                                        <p class="text-xs font-black text-emerald-600">₱ {{ number_format($bldg->acquisition_cost, 2) }}</p>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                        @else
                        <div class="flex flex-col items-center justify-center py-12 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest italic">No building records found for this school</p>
                        </div>
                        @endif
                    </div>

                    {{-- TAB: Assets --}}
                    <div x-show="activeTab === 'assets'" class="animate-fade space-y-3" x-cloak>
                        @if($recentAssets->count() > 0)
                            <div class="space-y-3 max-h-[600px] overflow-y-auto pr-2 custom-scroll">
                                @foreach($recentAssets as $asset)
                                    @php
                                        $condRaw = strtolower($asset->condition ?? 'good');
                                        if (str_contains($condRaw, 'good') || str_contains($condRaw, 'serviceable') && !str_contains($condRaw, 'unserviceable')) {
                                            $condBadge = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-800';
                                        } elseif (str_contains($condRaw, 'repair')) {
                                            $condBadge = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-800';
                                        } elseif (str_contains($condRaw, 'unserviceable')) {
                                            $condBadge = 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/20 dark:text-rose-400 dark:border-rose-800';
                                        } else {
                                            $condBadge = 'bg-slate-50 text-slate-500 border-slate-200 dark:bg-slate-900/50 dark:text-slate-400 dark:border-slate-800';
                                        }
                                    @endphp
                                    <div class="asset-card cursor-pointer hover:border-deped dark:hover:border-deped transition-colors" onclick="window.location.href='{{ route('assets.profile', $asset->id) }}'">
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-4 px-4 py-3.5">
                                            {{-- Icon + Name --}}
                                            <div class="flex items-center gap-3 flex-grow min-w-0">
                                                <div class="w-9 h-9 bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl flex items-center justify-center shrink-0">
                                                    <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                                                </div>
                                                <div class="min-w-0">
                                                    <h4 class="text-xs font-black text-slate-800 dark:text-white uppercase leading-none">{{ $asset->item_name }}</h4>
                                                    <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mt-1">
                                                        {{ $asset->category_name }}
                                                    </p>
                                                </div>
                                            </div>

                                            {{-- Meta columns --}}
                                            <div class="hidden md:flex items-center gap-6 text-right shrink-0">
                                                <div>
                                                    <p class="text-[8px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Property No.</p>
                                                    <p class="text-[10px] font-black text-slate-700 dark:text-slate-300 uppercase mt-0.5 font-mono">{{ $asset->property_number ?: '—' }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[8px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Custodian</p>
                                                    <p class="text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase mt-0.5 max-w-[120px] truncate">{{ $asset->custodian_name ?: 'School Direct' }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[8px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Acq. Date</p>
                                                    <p class="text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase mt-0.5">{{ $asset->acquisition_date ? \Carbon\Carbon::parse($asset->acquisition_date)->format('M d, Y') : '—' }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[8px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Cost</p>
                                                    <p class="text-[11px] font-black text-deped dark:text-red-400 italic mt-0.5">₱ {{ number_format($asset->asset_cost, 2) }}</p>
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
                            <div class="mt-6">
                                {{ $recentAssets->appends(request()->except('assets_page'))->links() }}
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-12 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700">
                                <p class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest italic">No distributed assets found for this school</p>
                            </div>
                        @endif
                    </div>
 
                    {{-- TAB: Lifecycle & History --}}
                    <div x-show="activeTab === 'history'" class="animate-fade space-y-4" x-cloak>
                        <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2 custom-scroll">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-2">
                                <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-deped"></span> School Asset Activity
                                </h3>
                                <span class="text-[9px] font-black text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">{{ $assetEvents->count() }}</span>
                            </div>

                            @if($assetEvents->count() > 0)
                            <div class="relative pl-5">
                                <div class="space-y-5">
                                    @foreach($assetEvents as $event)
                                    @php
                                        $isReceived = $event->type === 'received';
                                        $dotColor = $isReceived ? 'bg-emerald-500' : 'bg-orange-500';
                                        $badgeColor = $isReceived
                                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                            : 'bg-orange-50 text-orange-700 border-orange-200';
                                        $label = $isReceived ? 'Asset Sourced / Received' : 'Asset Transferred';
                                        $eventDate = $event->event_date
                                            ? \Carbon\Carbon::parse($event->event_date)->format('M d, Y · h:i A')
                                            : '—';
                                    @endphp
                                    <div class="relative flex items-start gap-3">
                                        <div class="w-[10px] h-[10px] rounded-full {{ $dotColor }} ring-2 ring-white absolute -left-5 top-1 shrink-0"></div>
                                        <div class="pl-1 pb-1 min-w-0 flex-grow">
                                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                                <span class="text-[9px] font-black uppercase tracking-wider border rounded-lg px-2 py-0.5 {{ $badgeColor }}">{{ $label }}</span>
                                                <span class="text-[9px] font-semibold text-slate-400">{{ $eventDate }}</span>
                                            </div>
                                            <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 mt-1 space-y-1.5">
                                                <div class="flex items-start gap-2">
                                                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider w-20 shrink-0 pt-0.5">Item</span>
                                                    <span class="text-[10px] font-bold text-slate-700">{{ $event->item_name ?? '—' }}</span>
                                                </div>
                                                <div class="flex items-start gap-2">
                                                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider w-20 shrink-0 pt-0.5">Category</span>
                                                    <span class="text-[10px] font-semibold text-slate-500">{{ $event->category_name ?? '—' }}</span>
                                                </div>
                                                @if($event->property_number)
                                                <div class="flex items-start gap-2">
                                                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider w-20 shrink-0 pt-0.5">Prop. No.</span>
                                                    <span class="text-[10px] font-mono font-bold text-slate-600">{{ $event->property_number }}</span>
                                                </div>
                                                @endif
                                                @if($event->serial_number)
                                                <div class="flex items-start gap-2">
                                                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider w-20 shrink-0 pt-0.5">Serial No.</span>
                                                    <span class="text-[10px] font-mono font-bold text-slate-600">{{ $event->serial_number }}</span>
                                                </div>
                                                @endif
                                                <div class="flex items-start gap-2">
                                                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider w-20 shrink-0 pt-0.5">Value</span>
                                                    <span class="text-[10px] font-bold text-slate-700">₱{{ number_format($event->asset_cost ?? 0, 2) }}</span>
                                                </div>
                                                @if(!$isReceived)
                                                <div class="border-t border-slate-200 pt-1.5 mt-1 flex items-start gap-2">
                                                    <span class="text-[8px] font-black text-orange-400 uppercase tracking-wider w-20 shrink-0 pt-0.5">Transferred To</span>
                                                    <span class="text-[10px] font-bold text-orange-600">
                                                        {{ $event->to_custodian ?: ($event->to_school ?: ($event->to_office ?: '—')) }}
                                                    </span>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @else
                            <div class="bg-slate-50 rounded-2xl border border-dashed border-slate-200 py-6 flex items-center justify-center">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">No asset activity recorded.</p>
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

</body>
</html>
