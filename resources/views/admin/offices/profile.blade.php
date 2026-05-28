<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $office->name }} | Office Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        deped: '#c00000',
                        deped_light: '#fef2f2',
                    }
                }
            }
        }
    </script>
    <style>
        /* === CSS Custom Properties for Light/Dark Theming === */
        :root {
            --bg-page:        #f8fafc;
            --bg-card:        #ffffff;
            --bg-secondary:   #f8fafc;
            --bg-hover:       rgba(241,245,249,0.6);
            --border-primary: #e2e8f0;
            --border-subtle:  #f1f5f9;
            --text-primary:   #0f172a;
            --text-secondary: #1e293b;
            --text-muted:     #64748b;
            --text-faint:     #94a3b8;
            --scrollbar-thumb: #cbd5e1;
        }
        html.dark {
            --bg-page:        #0f172a;
            --bg-card:        #1e293b;
            --bg-secondary:   #0f172a;
            --bg-hover:       rgba(51,65,85,0.3);
            --border-primary: #334155;
            --border-subtle:  #334155;
            --text-primary:   #f8fafc;
            --text-secondary: #e2e8f0;
            --text-muted:     #94a3b8;
            --text-faint:     #64748b;
            --scrollbar-thumb: #475569;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-page);
            color: var(--text-primary);
        }

        /* Adaptive card/panel surfaces */
        .card-surface {
            background-color: var(--bg-card);
            border-color: var(--border-primary);
        }
        .page-surface {
            background-color: var(--bg-secondary);
        }

        /* Scrollbar */
        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: var(--scrollbar-thumb); border-radius: 10px; }

        [x-cloak] { display: none !important; }
        .animate-fade { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

        /* --- Adaptive Tailwind overrides for bg-white / bg-slate-* --- */
        /* These override Tailwind's hardcoded color classes so they adapt */
        .bg-white  { background-color: var(--bg-card)  !important; }
        .bg-slate-50 { background-color: var(--bg-secondary) !important; }
        .bg-slate-100 { background-color: color-mix(in srgb, var(--bg-card) 70%, var(--border-primary)) !important; }

        .border-slate-200 { border-color: var(--border-primary) !important; }
        .border-slate-100 { border-color: var(--border-subtle)  !important; }
        .divide-slate-50  > * + * { border-color: var(--border-subtle) !important; }

        .text-slate-900 { color: var(--text-primary)   !important; }
        .text-slate-800 { color: var(--text-secondary) !important; }
        .text-slate-700 { color: var(--text-muted)     !important; }
        .text-slate-500, .text-slate-400 { color: var(--text-faint) !important; }

        /* Tab strip background */
        .tab-strip-bg {
            background-color: color-mix(in srgb, var(--bg-secondary) 80%, var(--bg-card) 20%);
        }

        /* Hover rows in tables */
        tr.group:hover td {
            background-color: var(--bg-hover) !important;
        }
    </style>
</head>
<body class="flex min-h-screen text-slate-800 overflow-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll p-4 lg:p-8" x-data="{ activeTab: 'assets' }">
        
        {{-- Global Header --}}
        <header class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 sticky top-0 z-50">
            <div class="flex items-center gap-5">
                <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center border border-deped/20 shadow-sm shrink-0 dark:bg-red-950/20 dark:border-red-900/40">
                    <!-- Briefcase/Office Icon -->
                    <svg class="w-6 h-6 text-deped dark:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 .966-.784 1.75-1.75 1.75H5.5a1.75 1.75 0 0 1-1.75-1.75V14.15M2.25 10.5h19.5M3 10.5V5.5c0-.966.784-1.75 1.75-1.75h14.5c.966 0 1.75.784 1.75 1.75v5M12 10.5v8.25" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight leading-none uppercase italic">{{ $office->name }}</h1>
                    <div class="flex flex-wrap items-center gap-3 mt-2">
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 rounded-md border border-slate-200 dark:border-slate-700">Office Code: {{ $office->office_code }}</span>
                        @if($office->room_number)
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 rounded-md border border-slate-200 dark:border-slate-700">Room: {{ $office->room_number }}</span>
                        @endif
                        <span class="text-[10px] font-black text-red-700 dark:text-red-400 uppercase tracking-widest bg-red-50 dark:bg-red-950/40 px-2 py-0.5 rounded-full flex items-center gap-1.5 shadow-sm">
                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span> DepEd Administrative Unit
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('admin.offices') }}" class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-black text-slate-600 dark:text-slate-300 uppercase tracking-widest hover:border-deped dark:hover:border-red-500 hover:text-deped dark:hover:text-red-500 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-sm hover:shadow-md flex items-center gap-2 group">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Back to Registry
                </a>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 flex-grow pb-10">
            
            {{-- Left Sidebar: Office Identity Details --}}
            <aside class="lg:col-span-3 flex flex-col gap-6 z-40 relative">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-6">
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Office Location Details</p>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 bg-slate-50 dark:bg-slate-800 rounded-lg flex items-center justify-center shrink-0 border border-slate-100 dark:border-slate-705">
                                    <svg class="w-4 h-4 text-deped dark:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Parent School Station</p>
                                    <p class="text-xs font-black text-slate-750 dark:text-slate-200 uppercase leading-snug truncate mt-0.5">{{ $office->school_name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 bg-slate-50 dark:bg-slate-800 rounded-lg flex items-center justify-center shrink-0 border border-slate-100 dark:border-slate-705">
                                    <svg class="w-4 h-4 text-deped dark:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Division District</p>
                                    <p class="text-xs font-black text-slate-750 dark:text-slate-200 uppercase leading-snug truncate mt-0.5">{{ $office->district_name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 bg-slate-50 dark:bg-slate-800 rounded-lg flex items-center justify-center shrink-0 border border-slate-100 dark:border-slate-705">
                                    <svg class="w-4 h-4 text-deped dark:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.483V8.517a2 2 0 011.553-1.943L9 5.222m0 14.778l6-3.111m-6 3.111V5.222m6 11.667l5.447 2.724A2 2 0 0021 17.617V10.65a2 2 0 00-1.553-1.943L15 7.444m0 9.444V7.444M9 5.222l6-2.222m0 0l5.447 2.724A2 2 0 0121 7.617v.898"></path></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Quadrant Area</p>
                                    <p class="text-xs font-black text-slate-750 dark:text-slate-200 uppercase leading-snug truncate mt-0.5">{{ $office->quadrant_name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100 dark:border-slate-700 space-y-4">
                        <div class="p-4 bg-blue-50 dark:bg-blue-950/20 rounded-xl border border-blue-100 dark:border-blue-900/30">
                            <p class="text-[9px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-1">Office Equipment Portfolio</p>
                            <p class="text-xl font-black text-blue-700 dark:text-blue-400 leading-none">₱ {{ number_format($assetStats->total_asset_value, 2) }}</p>
                            <p class="text-[10px] font-bold text-blue-600/70 dark:text-blue-400/70 mt-1 uppercase">{{ $assetStats->total_assets }} Assigned Items</p>
                        </div>
                        <div class="p-4 bg-red-50 dark:bg-red-950/20 rounded-xl border border-red-100 dark:border-red-900/30">
                            <p class="text-[9px] font-black text-red-600 dark:text-red-400 uppercase tracking-widest mb-1">Office Active Personnel</p>
                            <p class="text-xl font-black text-red-700 dark:text-red-400 leading-none">{{ $custodians->count() }}</p>
                            <p class="text-[10px] font-bold text-red-600/70 dark:text-red-400/70 mt-1 uppercase">Assigned Custodians</p>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Main Content --}}
            <div class="lg:col-span-9 flex flex-col bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                {{-- Office Custom Tabs --}}
                <div class="flex border-b border-slate-200 tab-strip-bg px-2 pt-2">
                    <button @click="activeTab = 'assets'" :class="{'bg-white border-slate-200 border-b-white text-deped shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'assets', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'assets'}" class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        Office Equipment
                    </button>
                    <button @click="activeTab = 'custodians'" :class="{'bg-white border-slate-200 border-b-white text-deped shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'custodians', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'custodians'}" class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        Office Custodians
                    </button>
                    <button @click="activeTab = 'buildings'" :class="{'bg-white border-slate-200 border-b-white text-deped shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'buildings', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'buildings'}" class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        School Buildings
                    </button>
                </div>

                <div class="p-6 flex-grow overflow-y-auto custom-scroll">
                    
                    {{-- TAB: Assets --}}
                    <div x-show="activeTab === 'assets'" class="animate-fade space-y-4">
                        @if($recentAssets->count() > 0)
                        <div class="overflow-x-auto w-full">
                            <table class="w-full text-left border-collapse" style="min-width: 600px;">
                                <thead>
                                    <tr class="border-b border-slate-100 dark:border-slate-800">
                                        <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Item / Category</th>
                                        <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Property Number</th>
                                        <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Acq. Date</th>
                                        <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Condition</th>
                                        <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Unit Cost</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                                    @foreach($recentAssets as $asset)
                                    <tr onclick="window.location='{{ route('assets.profile', $asset->id) }}'" class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors cursor-pointer">
                                        <td class="py-4 pr-4">
                                            <a href="{{ route('assets.profile', $asset->id) }}" class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase leading-none hover:text-deped dark:hover:text-red-500 transition-colors">
                                                {{ $asset->item_name }}
                                            </a>
                                            <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mt-1">{{ $asset->category_name }}</p>
                                        </td>
                                        <td class="py-4">
                                            <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase">{{ $asset->property_number }}</span>
                                        </td>
                                        <td class="py-4">
                                            <p class="text-[10px] font-bold text-slate-700 dark:text-slate-300">{{ $asset->acquisition_date ? \Carbon\Carbon::parse($asset->acquisition_date)->format('M d, Y') : 'N/A' }}</p>
                                        </td>
                                        <td class="py-4 text-center">
                                            @php
                                                $cond = $asset->condition ?: 'Good';
                                                $theme = $cond === 'Good' || $cond === 'Serviceable' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400';
                                            @endphp
                                            <span class="px-2.5 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider {{ $theme }}">{{ $cond }}</span>
                                        </td>
                                        <td class="py-4 text-right">
                                            <p class="text-xs font-black text-emerald-600 dark:text-emerald-400 italic">₱ {{ number_format($asset->asset_cost, 2) }}</p>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="flex flex-col items-center justify-center py-12 bg-slate-50 dark:bg-slate-900/20 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest italic">No distributed equipment found in this office</p>
                        </div>
                        @endif
                    </div>

                    {{-- TAB: Custodians --}}
                    <div x-show="activeTab === 'custodians'" class="animate-fade space-y-4" x-cloak>
                        @if($custodians->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($custodians as $cust)
                            <a href="{{ route('custodians.profile', $cust->id) }}" class="group p-5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl hover:border-deped dark:hover:border-red-500 hover:shadow-md transition-all flex flex-col gap-3">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-950/50 text-red-600 dark:text-red-400 flex items-center justify-center font-black text-sm uppercase shrink-0">
                                            {{ substr($cust->first_name, 0, 1) }}{{ substr($cust->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-black text-slate-800 dark:text-slate-200 uppercase group-hover:text-deped dark:group-hover:text-red-500 transition-colors leading-none">{{ $cust->first_name }} {{ $cust->last_name }}</h4>
                                            <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mt-1">ID: {{ $cust->employee_id }}</p>
                                        </div>
                                    </div>
                                    <span class="text-[8px] font-black bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-full uppercase tracking-wider">{{ $cust->total_assigned_assets }} Asset(s)</span>
                                </div>
                                <div class="pt-3 border-t border-slate-50 dark:border-slate-700/60 flex items-center justify-between">
                                    <div>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Position / Title</p>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-355 uppercase leading-none mt-0.5">{{ $cust->position ?? 'Personnel / Teacher' }}</p>
                                    </div>
                                    <svg class="w-4 h-4 text-slate-400 group-hover:translate-x-1 group-hover:text-deped transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </div>
                            </a>
                            @endforeach
                        </div>
                        @else
                        <div class="flex flex-col items-center justify-center py-12 bg-slate-50 dark:bg-slate-900/20 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest italic">No custodians registered with assets in this office</p>
                        </div>
                        @endif
                    </div>

                    {{-- TAB: Buildings --}}
                    <div x-show="activeTab === 'buildings'" class="animate-fade space-y-4" x-cloak>
                        @if($buildings->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($buildings as $bldg)
                            <a href="{{ route('buildings.profile', $bldg->id) }}" class="group p-5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl hover:border-deped dark:hover:border-red-500 hover:shadow-md transition-all flex flex-col gap-3">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800 dark:text-slate-200 uppercase group-hover:text-deped dark:group-hover:text-red-500 transition-colors">{{ $bldg->spec_description ?: $bldg->type_name }}</h4>
                                        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase mt-0.5">{{ $bldg->property_number }}</p>
                                    </div>
                                    <span class="text-[9px] font-black bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300 px-2 py-1 rounded uppercase">{{ $bldg->storeys }} Storey</span>
                                </div>
                                <div class="grid grid-cols-2 gap-4 mt-auto pt-3 border-t border-slate-50 dark:border-slate-700/60">
                                    <div>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Classrooms</p>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $bldg->classrooms }} Units</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Acq. Cost</p>
                                        <p class="text-xs font-black text-emerald-600 dark:text-emerald-400 italic">₱ {{ number_format($bldg->acquisition_cost, 2) }}</p>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                        @else
                        <div class="flex flex-col items-center justify-center py-12 bg-slate-50 dark:bg-slate-900/20 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest italic">No building records found for this school</p>
                        </div>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>

</body>
</html>
