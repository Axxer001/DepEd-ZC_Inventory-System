<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $school->name }} | School Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        [x-cloak] { display: none !important; }
        .animate-fade { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="flex min-h-screen text-slate-800 overflow-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll p-4 lg:p-8" x-data="{ activeTab: 'buildings' }">
        
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

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 flex-grow pb-10">
            
            {{-- Left Sidebar: School Identity --}}
            <aside class="lg:col-span-3 flex flex-col gap-6 z-40 relative">
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
            <div class="lg:col-span-9 flex flex-col bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="flex border-b border-slate-200 bg-slate-50/50 px-2 pt-2">
                    <button @click="activeTab = 'buildings'" :class="{'bg-white border-slate-200 border-b-white text-deped shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'buildings', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'buildings'}" class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        School Buildings
                    </button>
                    <button @click="activeTab = 'assets'" :class="{'bg-white border-slate-200 border-b-white text-deped shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'assets', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'assets'}" class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        Inventory Assets
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
                    <div x-show="activeTab === 'assets'" class="animate-fade space-y-4" x-cloak>
                        @if($recentAssets->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="border-b border-slate-100">
                                        <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Item / Category</th>
                                        <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Property Number</th>
                                        <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Acq. Date</th>
                                        <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Unit Cost</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach($recentAssets as $asset)
                                    <tr class="group hover:bg-slate-50 transition-colors">
                                        <td class="py-4 pr-4">
                                            <p class="text-xs font-bold text-slate-800 uppercase">{{ $asset->item_name }}</p>
                                            <p class="text-[9px] font-bold text-slate-400 uppercase">{{ $asset->category_name }}</p>
                                        </td>
                                        <td class="py-4">
                                            <span class="text-[10px] font-black text-slate-500 uppercase">{{ $asset->property_number }}</span>
                                        </td>
                                        <td class="py-4">
                                            <p class="text-[10px] font-bold text-slate-700">{{ $asset->acquisition_date ? \Carbon\Carbon::parse($asset->acquisition_date)->format('M d, Y') : 'N/A' }}</p>
                                        </td>
                                        <td class="py-4 text-right">
                                            <p class="text-xs font-black text-emerald-600">₱ {{ number_format($asset->asset_cost, 2) }}</p>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="flex flex-col items-center justify-center py-12 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest italic">No distributed assets found for this school</p>
                        </div>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>

</body>
</html>
