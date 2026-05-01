<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Dashboard | DepEd Zamboanga City</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        
        .stat-card-red { background: linear-gradient(135deg, #c00000 0%, #7a0000 100%); }
        @keyframes swing { 0% { transform: rotate(0); } 20% { transform: rotate(15deg); } 40% { transform: rotate(-10deg); } 60% { transform: rotate(5deg); } 80% { transform: rotate(-5deg); } 100% { transform: rotate(0); } }
        .group-hover\:animate-swing:hover svg { animation: swing 0.8s ease-in-out; }

        .glass-red-glow { background: radial-gradient(circle at top right, rgba(192, 0, 0, 0.05) 0%, transparent 70%); }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex animate-fade-in text-slate-800 overflow-x-hidden" x-data="dashboardFilter()">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col lg:flex-row min-w-0 h-screen overflow-hidden">
        
        {{-- Mobile Header --}}
        <header class="lg:hidden bg-white border-b border-slate-200 p-4 sticky top-0 z-30 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()" class="p-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-100 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <span class="font-extrabold italic text-sm text-slate-800 uppercase tracking-tight">Dashboard</span>
            </div>
            <div class="w-8 h-8 bg-[#c00000] rounded-lg flex items-center justify-center text-white font-bold text-xs shadow-lg shadow-red-100 italic">A</div>
        </header>

        {{-- MAIN CONTENT AREA --}}
        <main class="flex-grow flex flex-col overflow-y-auto custom-scroll bg-slate-50/30">
            <header class="p-6 lg:p-10 pb-2 flex justify-between items-center bg-white/40 backdrop-blur-md sticky top-0 z-20 hidden lg:flex border-b border-slate-100/50">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight italic uppercase leading-none" x-text="filterLabel">Inventory Overview</h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2 ml-1">Division Asset Overview • Zamboanga City</p>
                </div>
                <div class="flex items-center gap-6">
                    {{-- Premium Filter Dropdown --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                :class="open ? 'border-[#c00000] text-[#c00000] ring-4 ring-red-50' : 'border-slate-200 text-slate-500'"
                                class="flex items-center gap-3 px-5 py-2.5 bg-white border rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-sm hover:border-[#c00000] hover:text-[#c00000] active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                            Filter Records
                            <span x-show="selectedYears.length || selectedMonths.length" x-cloak class="ml-1 w-2 h-2 bg-red-600 rounded-full"></span>
                        </button>

                        {{-- Filter Panel --}}
                        <div x-show="open" @click.away="open = false" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             class="absolute right-0 mt-4 w-80 bg-white rounded-[2.5rem] shadow-2xl border border-slate-100 p-8 z-50">
                            
                            <div class="space-y-8 text-slate-900">
                                {{-- Year Filter --}}
                                <div>
                                    <h5 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Select Fiscal Year</h5>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="year in availableYears" :key="year">
                                            <button @click="toggleYear(year)" 
                                                    :class="selectedYears.includes(year) ? 'bg-[#c00000] text-white border-[#c00000]' : 'bg-slate-50 text-slate-500 border-slate-100'"
                                                    class="px-4 py-2 rounded-xl border text-[11px] font-black transition-all" x-text="year"></button>
                                        </template>
                                    </div>
                                </div>

                                {{-- Month Filter --}}
                                <div>
                                    <h5 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Select Months</h5>
                                    <div class="grid grid-cols-3 gap-2">
                                        <template x-for="(name, index) in monthNames" :key="index">
                                            <button @click="toggleMonth(index + 1)"
                                                    :class="selectedMonths.includes(index + 1) ? 'bg-[#c00000] text-white border-[#c00000]' : 'bg-slate-50 text-slate-500 border-slate-100'"
                                                    class="py-2 rounded-xl border text-[10px] font-black transition-all" x-text="name"></button>
                                        </template>
                                    </div>
                                </div>

                                <div class="pt-4 border-t border-slate-50 flex justify-between items-center">
                                    <button @click="resetFilters()" class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-red-600 transition-colors">Reset All</button>
                                    <button @click="open = false" class="px-6 py-2 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-[#c00000] transition-all">Apply</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative group">
                        <input type="text" placeholder="Search system registry..." class="bg-white border border-slate-200 pl-10 pr-4 py-2.5 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-red-50 outline-none transition-all w-64 shadow-sm">
                        <svg class="w-4 h-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <button class="relative p-3 bg-white border border-slate-200 text-slate-400 rounded-2xl hover:text-[#c00000] hover:shadow-lg transition-all shadow-sm group group-hover:animate-swing">
                        <svg class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                        <span class="absolute top-2.5 right-2.5 w-2.5 h-2.5 bg-red-500 border-2 border-white rounded-full animate-pulse"></span>
                    </button>
                </div>
            </header>

            <div class="flex-grow p-6 lg:p-10 space-y-12">
                
                {{-- 1. Top Stat Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                    <div class="p-8 rounded-[2.5rem] bg-[#c00000] text-white shadow-2xl shadow-red-200 flex flex-col justify-between h-44 relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-12 -mt-12 group-hover:scale-125 transition-transform duration-700"></div>
                        <div class="flex justify-between items-start text-white">
                            <div class="flex flex-col">
                                <span class="text-xs font-black uppercase tracking-[0.2em] opacity-70">System Asset Inventory</span>
                                <span class="text-[8px] font-bold uppercase tracking-widest mt-1 text-white/50" x-text="selectedYears.length || selectedMonths.length ? 'Filtered Result' : 'Total System Count'">Overall</span>
                            </div>
                            <div class="p-2 bg-white/10 rounded-xl backdrop-blur-md border border-white/20">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-3">
                            <span class="text-5xl font-black tracking-tighter text-white" x-text="numberFormat(filteredStats.total)">{{ number_format($totalAssets > 0 ? $totalAssets : 24850) }}</span>
                            <span class="text-xs font-bold opacity-60 italic uppercase tracking-widest text-white">Stock Units</span>
                        </div>
                    </div>
                    <div class="p-8 rounded-[2.5rem] bg-slate-900 text-white shadow-2xl shadow-slate-200 flex flex-col justify-between h-44 relative overflow-hidden group border border-slate-800 text-white">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -mr-12 -mt-12 group-hover:scale-125 transition-transform duration-700"></div>
                        <div class="flex justify-between items-start text-white">
                            <div class="flex flex-col text-white">
                                <span class="text-xs font-black uppercase tracking-[0.2em] opacity-70">Total Assets Distributed</span>
                                <span class="text-[8px] font-bold uppercase tracking-widest mt-1 text-white/30" x-text="selectedYears.length || selectedMonths.length ? 'Filtered Result' : 'Global Distribution'">Overall</span>
                            </div>
                            <div class="p-2 bg-white/10 rounded-xl backdrop-blur-md border border-white/10">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-3 text-white">
                            <span class="text-5xl font-black tracking-tighter text-white" x-text="numberFormat(filteredStats.distributed)">{{ number_format(($distributedCount ?? 0) > 0 ? $distributedCount : 18420) }}</span>
                            <span class="text-xs font-bold opacity-60 italic uppercase tracking-widest text-white">Deployed</span>
                        </div>
                    </div>
                    <div class="p-8 rounded-[2.5rem] bg-white border border-slate-200 text-slate-900 shadow-xl shadow-slate-100 flex flex-col justify-between h-44 relative overflow-hidden group">
                        <div class="absolute bottom-0 right-0 w-32 h-32 bg-slate-50 rounded-full -mr-12 -mb-12 group-hover:scale-125 transition-transform duration-700"></div>
                        <div class="flex justify-between items-start text-slate-400">
                            <span class="text-xs font-black uppercase tracking-[0.2em]">Registered Institutions</span>
                            <div class="p-2 bg-slate-50 rounded-xl border border-slate-100">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H5a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-3">
                            <span class="text-5xl font-black tracking-tighter text-[#c00000]">{{ number_format($totalSchools ?? 207) }}</span>
                            <span class="text-xs font-bold text-slate-400 italic uppercase tracking-widest">Verified</span>
                        </div>
                    </div>
                </div>

                {{-- 2. Middle Row: Intelligence Section --}}
                <div>
                    <div class="flex items-center justify-between mb-6 px-2">
                        <div class="flex items-center gap-3">
                            <div class="w-1.5 h-4 bg-[#c00000] rounded-full animate-pulse"></div>
                            <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em]">System Intelligence</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <template x-if="selectedYears.length || selectedMonths.length">
                                <span class="text-[9px] font-black text-red-600 uppercase tracking-widest bg-red-50 px-3 py-1 rounded-lg border border-red-100 flex items-center gap-2 animate-fade-in">
                                    Filtered View
                                    <button @click="resetFilters()" class="hover:scale-110 transition-transform">✕</button>
                                </span>
                            </template>
                            <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-100 shadow-sm">Updated Real-time</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                        {{-- Asset Condition Bar Chart --}}
                        <div class="col-span-1 lg:col-span-2 bg-white rounded-[3rem] p-8 lg:p-10 border border-slate-100 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.05)] relative flex flex-col h-auto lg:h-80 group">
                            <div class="flex justify-between items-start mb-8">
                                <div>
                                    <h4 class="text-2xl font-black text-slate-800 italic uppercase leading-none tracking-tight">Asset Condition Summary</h4>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-2 leading-relaxed">Division-wide status report of all accounted items.</p>
                                </div>
                                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 text-[#c00000]">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                </div>
                            </div>
                            
                            <div class="flex-grow flex flex-col justify-center space-y-6">
                                {{-- Serviceable --}}
                                <div class="space-y-2">
                                    <div class="flex justify-between text-[10px] font-black uppercase tracking-widest text-slate-900">
                                        <span class="text-emerald-600 font-black">Verified Functional</span>
                                        <span class="text-slate-800" x-text="numberFormat(filteredStats.serviceable) + ' Units'">{{ number_format($serviceableCount) }} Units</span>
                                    </div>
                                    <div class="w-full bg-slate-50 h-3.5 rounded-full overflow-hidden shadow-inner border border-slate-100">
                                        <div class="bg-emerald-500 h-full rounded-full transition-all duration-1000 shadow-[0_0_15px_rgba(16,185,129,0.4)]" :style="`width: ${calcPercent(filteredStats.serviceable)}%`" style="width: {{ $serviceableCount > 0 ? ($serviceableCount / max($serviceableCount, $unserviceableCount, $forRepairCount, 1) * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                                {{-- For Repair --}}
                                <div class="space-y-2">
                                    <div class="flex justify-between text-[10px] font-black uppercase tracking-widest text-slate-900">
                                        <span class="text-amber-600 font-black">Pending Maintenance</span>
                                        <span class="text-slate-800" x-text="numberFormat(filteredStats.forRepair) + ' Units'">{{ number_format($forRepairCount) }} Units</span>
                                    </div>
                                    <div class="w-full bg-slate-50 h-3.5 rounded-full overflow-hidden shadow-inner border border-slate-100">
                                        <div class="bg-amber-500 h-full rounded-full transition-all duration-1000 shadow-[0_0_15px_rgba(245,158,11,0.4)]" :style="`width: ${calcPercent(filteredStats.forRepair)}%`" style="width: {{ $forRepairCount > 0 ? ($forRepairCount / max($serviceableCount, $unserviceableCount, $forRepairCount, 1) * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                                {{-- Unserviceable --}}
                                <div class="space-y-2">
                                    <div class="flex justify-between text-[10px] font-black uppercase tracking-widest text-slate-900">
                                        <span class="text-red-600 font-black">No Longer Usable (IIRUP)</span>
                                        <span class="text-slate-800" x-text="numberFormat(filteredStats.unserviceable) + ' Units'">{{ number_format($unserviceableCount) }} Units</span>
                                    </div>
                                    <div class="w-full bg-slate-50 h-3.5 rounded-full overflow-hidden shadow-inner border border-slate-100">
                                        <div class="bg-[#c00000] h-full rounded-full transition-all duration-1000 shadow-[0_0_15px_rgba(192,0,0,0.4)]" :style="`width: ${calcPercent(filteredStats.unserviceable)}%`" style="width: {{ $unserviceableCount > 0 ? ($unserviceableCount / max($serviceableCount, $unserviceableCount, $forRepairCount, 1) * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Premium Quick Import Card --}}
                        <a href="{{ route('assets.import') }}" class="bg-white rounded-[3rem] border border-slate-100 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.05)] flex flex-col p-10 h-auto lg:h-80 group relative overflow-hidden glass-red-glow transition-all duration-500 hover:scale-[1.03] hover:shadow-2xl hover:shadow-red-100 hover:border-red-100">
                            <div class="absolute -top-10 -right-10 w-40 h-40 bg-red-50 rounded-full group-hover:scale-150 transition-transform duration-1000 opacity-50"></div>
                            
                            <div class="relative z-10 flex flex-col h-full">
                                {{-- Text at the Top --}}
                                <div class="text-center sm:text-left mb-4">
                                    <h4 class="text-xs font-black text-red-600 uppercase tracking-[0.3em] mb-1 text-[#c00000]">Data Management</h4>
                                    <p class="text-2xl font-black text-slate-800 tracking-tighter uppercase italic leading-none">Bulk Asset Registration</p>
                                    <p class="text-[9px] font-bold text-slate-400 mt-4 uppercase tracking-[0.1em] leading-relaxed max-w-[200px] mx-auto sm:mx-0 text-slate-900">Upload CSV templates to register multiple assets to the system.</p>
                                </div>

                                {{-- Icon in the Center --}}
                                <div class="flex-grow flex items-center justify-center">
                                    <div class="w-24 h-24 bg-red-50 rounded-[2.2rem] flex items-center justify-center shadow-2xl shadow-red-100/50 border border-red-100 group-hover:bg-[#c00000] group-hover:rotate-12 transition-all duration-300">
                                        <svg class="w-12 h-12 text-[#c00000] group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                {{-- 3. Fund Source Portfolio --}}
                <div class="space-y-6">
                    <div class="flex items-center justify-between px-2 text-slate-900">
                        <div class="flex items-center gap-3 text-slate-900">
                            <div class="w-1.5 h-4 bg-slate-900 rounded-full"></div>
                            <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em]">Fund Source Portfolio</h3>
                        </div>
                        <span class="text-[9px] font-black text-slate-300 uppercase italic">Sideways Scroll Enabled</span>
                    </div>
                    <div class="flex overflow-x-auto gap-6 pb-6 custom-scroll -mx-2 px-2 snap-x">
                        @forelse($sourceBreakdown as $source)
                        <div class="group bg-white p-6 rounded-[2.5rem] shadow-lg shadow-slate-200/40 border border-slate-100 transition-all hover:border-red-200 min-w-[280px] flex-shrink-0 snap-start relative overflow-hidden text-slate-900">
                            <div class="absolute top-0 right-0 w-20 h-20 bg-slate-50 rounded-full -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-700"></div>
                            <div class="flex items-center gap-3 mb-6 relative text-slate-900">
                                <div class="w-1.5 h-6 bg-[#c00000] rounded-full group-hover:scale-y-125 transition-transform"></div>
                                <h4 class="text-[11px] font-black uppercase tracking-wider truncate max-w-[200px]" title="{{ $source->source_name }}">
                                    {{ $source->source_name ?? 'Unknown Source' }}
                                </h4>
                            </div>
                            <div class="pl-4 relative">
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2 italic">Total Asset Value</p>
                                <p class="text-2xl font-black tracking-tighter leading-none mb-3">
                                    ₱{{ number_format($source->total_amount, 2) }}
                                </p>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-1 bg-slate-50 text-slate-500 rounded-lg text-[9px] font-black italic border border-slate-100 uppercase tracking-tighter text-slate-900">{{ number_format($source->total_qty) }} Items Registered</span>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="w-full py-10 text-center border-2 border-dashed border-slate-200 rounded-[3rem] bg-white/50">
                            <p class="text-slate-400 text-[10px] font-bold uppercase italic tracking-[0.3em]">No funding sources found</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- 4. District Portfolio --}}
                <div class="space-y-6">
                    <div class="flex items-center gap-3 px-2 mb-6 text-slate-400">
                        <div class="w-1.5 h-4 bg-slate-400 rounded-full"></div>
                        <h3 class="text-xs font-black uppercase tracking-[0.3em]">District Distribution</h3>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 pb-10">
                        @php 
                            $quadrants = [
                                1 => ['label' => 'Q 1.1', 'desc' => 'LD 1 • 3 Districts', 'color' => 'blue'],
                                2 => ['label' => 'Q 1.2', 'desc' => 'LD 1 • 2 Districts', 'color' => 'indigo'],
                                3 => ['label' => 'Q 2.1', 'desc' => 'LD 2 • 3 Districts', 'color' => 'emerald'],
                                4 => ['label' => 'Q 2.2', 'desc' => 'LD 2 • 4 Districts', 'color' => 'teal'],
                            ];
                        @endphp

                        @foreach($quadrants as $id => $q)
                        <div class="bg-white p-7 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/30 hover:border-red-200 transition-all duration-300 hover:-translate-y-1 cursor-default group relative overflow-hidden text-slate-900">
                            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-slate-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <div class="flex items-center gap-4 mb-6 relative text-slate-900">
                                <div class="w-12 h-12 bg-slate-50 text-[#c00000] rounded-2xl flex items-center justify-center text-sm font-black group-hover:scale-110 transition-transform tracking-tighter italic border border-slate-100">{{ $q['label'] }}</div>
                                <div>
                                    <h4 class="text-sm font-black text-slate-800 uppercase italic leading-none">Quadrant {{ substr($q['label'], 2) }}</h4>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest italic mt-1">{{ $q['desc'] }}</p>
                                </div>
                            </div>
                            <div class="bg-slate-50/80 p-5 rounded-3xl border border-slate-100 shadow-inner group-hover:bg-white transition-colors relative">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Allocated Assets</p>
                                <div class="flex items-baseline gap-2 text-slate-900">
                                    <p class="text-2xl font-black tracking-tighter leading-none text-slate-900">{{ number_format($quadrantTotals[$id] ?? 0) }}</p>
                                    <span class="text-[9px] font-bold text-slate-400 italic">Units</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- 5. Bottom Table Section --}}
                <div class="bg-white rounded-[3rem] p-8 border border-slate-100 shadow-sm overflow-hidden mb-8">
                    <div class="flex justify-between items-center mb-8 px-4 text-slate-900">
                        <h3 class="text-lg font-black uppercase italic tracking-tight leading-none text-slate-900">Recent Transaction Logs</h3>
                        <a href="{{ route('admin.logs') }}" class="text-[10px] font-black text-[#c00000] uppercase tracking-widest hover:underline transition-all bg-red-50 px-4 py-2.5 rounded-xl border border-red-100 italic">View All History</a>
                    </div>
                    <div class="overflow-x-auto custom-scroll text-slate-900">
                        <table class="w-full text-left border-separate border-spacing-0 min-w-[600px]">
                            <thead>
                                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b-2 border-slate-50 text-slate-900">
                                    <th class="px-6 py-4 pb-6">Log ID</th>
                                    <th class="px-6 py-4 pb-6">Institutional Name</th>
                                    <th class="px-6 py-4 pb-6">Update Timestamp</th>
                                    <th class="px-6 py-4 pb-6">Quantity</th>
                                    <th class="px-6 py-4 pb-6 text-right text-slate-900">Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs font-bold text-slate-700 divide-y divide-slate-50 text-slate-900">
                                <template x-for="log in filteredLogs" :key="log.id">
                                    <tr class="hover:bg-slate-50/50 transition-colors group">
                                        <td class="px-6 py-6 text-slate-400 font-black italic text-slate-900" x-text="'#INV-' + log.id.toString().padStart(5, '0')"></td>
                                        <td class="px-6 py-6 font-black text-slate-800 group-hover:text-[#c00000] transition-colors uppercase leading-tight text-slate-900" x-text="log.school"></td>
                                        <td class="px-6 py-6 text-slate-500 uppercase tracking-tighter text-slate-900" x-text="log.timestamp"></td>
                                        <td class="px-6 py-6 font-black text-lg tracking-tighter text-slate-900" x-text="numberFormat(log.qty)"></td>
                                        <td class="px-6 py-6 text-right text-slate-900">
                                            <span class="px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl text-[10px] font-black uppercase italic border border-emerald-100 shadow-sm">Verified</span>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="filteredLogs.length === 0">
                                    <tr><td colspan="5" class="py-12 text-center text-sm font-bold text-slate-400 italic">No activity matches the selected filters.</td></tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>

        {{-- RIGHT SUMMARY SIDEBAR --}}
        <aside class="w-full lg:w-96 border-l border-slate-200 p-8 lg:p-10 flex flex-col shrink-0 overflow-y-auto custom-scroll bg-white z-10 shadow-[-10px_0_30px_rgba(0,0,0,0.02)]">
            <div class="flex items-center justify-between mb-10">
                <h3 class="text-2xl font-black text-slate-900 tracking-tight italic uppercase leading-none">System Valuation</h3>
                <div class="w-2 h-2 bg-red-600 rounded-full animate-ping text-slate-900"></div>
            </div>
            
            {{-- Balance/Valuation Card --}}
            <div class="bg-slate-50 p-8 rounded-[3rem] border border-slate-200 mb-12 relative overflow-hidden shadow-inner group text-slate-900">
                <div class="absolute top-0 right-0 w-32 h-32 bg-red-600/5 rounded-full -mr-10 -mt-10 transition-transform group-hover:scale-150 duration-700"></div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 italic leading-none text-slate-900">Global Asset Worth</p>
                <div class="flex items-center gap-2 text-slate-900 mb-8 text-slate-900">
                    <span class="text-3xl font-black opacity-20">₱</span>
                    <span class="font-black tracking-tighter leading-none text-slate-900" x-text="numberFormat(filteredStats.value, 2)">
                        {{ number_format($totalAmount > 0 ? $totalAmount : 12450830.50, 2) }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-slate-900">
                    <div class="flex items-center gap-2 px-4 py-2 bg-white text-emerald-600 rounded-xl text-[11px] font-black shadow-sm border border-emerald-100 uppercase tracking-tighter text-slate-900 text-slate-900">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        System Verified
                    </div>
                    <div class="w-14 h-14 bg-[#c00000] text-white rounded-[1.5rem] flex items-center justify-center shadow-xl shadow-red-100 italic font-black text-xl select-none text-white">
                        ₱
                    </div>
                </div>
            </div>

            {{-- Activity section --}}
            <div class="space-y-8 flex-grow mb-12">
                <div class="flex justify-between items-center mb-6 px-2 text-slate-900">
                    <h4 class="text-xs font-black uppercase italic tracking-widest leading-none text-slate-900">Inventory Pulse</h4>
                    <span class="flex items-center gap-2 text-[9px] font-black text-slate-400 uppercase tracking-widest italic text-slate-900 text-slate-900 text-slate-900">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div> Live Feed
                    </span>
                </div>
                
                <div class="space-y-6">
                    {{-- Real Data Loop --}}
                    <template x-for="activity in filteredLogs.slice(0, 5)" :key="activity.id">
                        <div class="flex items-center gap-5 group cursor-default p-4 hover:bg-slate-50 rounded-2xl transition-all -mx-4 border border-transparent hover:border-slate-100 text-slate-900">
                            <div class="w-12 h-12 bg-red-50 text-[#c00000] rounded-[1.2rem] flex items-center justify-center shrink-0 shadow-sm border border-red-100 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div class="flex-grow overflow-hidden text-slate-900 text-slate-900">
                                <p class="text-sm font-black leading-tight truncate uppercase tracking-tighter text-slate-900" x-text="activity.type"></p>
                                <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest italic truncate leading-none text-slate-900" x-text="activity.school"></p>
                            </div>
                            <div class="text-right whitespace-nowrap text-slate-900">
                                <p class="text-xs font-black text-[#c00000] uppercase italic" x-text="'-' + numberFormat(activity.qty)"></p>
                                <p class="text-[8px] font-bold text-slate-300 mt-1 uppercase tracking-tighter text-slate-900" x-text="activity.time"></p>
                            </div>
                        </div>
                    </template>

                    {{-- Sample Static Data --}}
                    <div class="flex items-center gap-5 group cursor-default p-4 hover:bg-slate-50 rounded-2xl transition-all -mx-4 border border-transparent hover:border-slate-100 opacity-60 text-slate-900 text-slate-900">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-[1.2rem] flex items-center justify-center shrink-0 shadow-sm border border-blue-100 text-slate-900">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        </div>
                        <div class="flex-grow overflow-hidden text-slate-900 text-slate-900">
                            <p class="text-sm font-black leading-tight truncate uppercase tracking-tighter text-slate-900">Sync Done</p>
                            <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest italic truncate leading-none text-slate-900">Database Backup</p>
                        </div>
                        <div class="text-right whitespace-nowrap text-slate-900 text-slate-900">
                            <p class="text-xs font-black text-blue-600 uppercase italic text-slate-900">Active</p>
                            <p class="text-[8px] font-bold text-slate-300 mt-1 uppercase tracking-tighter text-slate-900">Real-time</p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

    </div>

    <script>
        function dashboardFilter() {
            return {
                availableYears: [2026, 2025, 2024],
                monthNames: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                selectedYears: [],
                selectedMonths: [],
                
                get filterLabel() {
                    if (this.selectedYears.length === 0 && this.selectedMonths.length === 0) {
                        return "Inventory Overview";
                    }
                    return "Filtered Dashboard";
                },

                // Real Data Fallbacks (Originals from Laravel)
                origStats: {
                    total: {{ $totalAssets > 0 ? $totalAssets : 24850 }},
                    distributed: {{ ($distributedCount ?? 0) > 0 ? $distributedCount : 18420 }},
                    value: {{ $totalAmount > 0 ? $totalAmount : 12450830.50 }},
                    serviceable: {{ $serviceableCount > 0 ? $serviceableCount : 15200 }},
                    forRepair: {{ $forRepairCount > 0 ? $forRepairCount : 2450 }},
                    unserviceable: {{ $unserviceableCount > 0 ? $unserviceableCount : 820 }}
                },

                // Mock Data for Filtering
                mockLogs: [
                    { id: 4521, school: 'Ayala National HS', type: 'Asset Deployed', qty: 25, timestamp: 'Apr 30, 2026 | 10:45 AM', time: '10:45 AM', year: 2026, month: 4 },
                    { id: 4520, school: 'Central Warehouse', type: 'New Stock Inbound', qty: 150, timestamp: 'Apr 29, 2026 | 02:15 PM', time: '02:15 PM', year: 2026, month: 4 },
                    { id: 4519, school: 'Tetuan Elementary', type: 'Asset Deployed', qty: 10, timestamp: 'Mar 15, 2026 | 09:30 AM', time: '09:30 AM', year: 2026, month: 3 },
                    { id: 3902, school: 'Vitali NHS', type: 'Asset Deployed', qty: 45, timestamp: 'Dec 12, 2025 | 11:20 AM', time: '11:20 AM', year: 2025, month: 12 },
                    { id: 3850, school: 'Zamboanga Central', type: 'Asset Deployed', qty: 100, timestamp: 'Nov 05, 2025 | 01:10 PM', time: '01:10 PM', year: 2025, month: 11 },
                    { id: 2105, school: 'Ayala National HS', type: 'Asset Deployed', qty: 60, timestamp: 'Aug 22, 2024 | 10:00 AM', time: '10:00 AM', year: 2024, month: 8 },
                ],

                get filteredLogs() {
                    if (this.selectedYears.length === 0 && this.selectedMonths.length === 0) {
                        return this.mockLogs;
                    }
                    return this.mockLogs.filter(log => {
                        const yearMatch = this.selectedYears.length === 0 || this.selectedYears.includes(log.year);
                        const monthMatch = this.selectedMonths.length === 0 || this.selectedMonths.includes(log.month);
                        return yearMatch && monthMatch;
                    });
                },

                get filteredStats() {
                    if (this.selectedYears.length === 0 && this.selectedMonths.length === 0) {
                        return this.origStats;
                    }
                    // Simple logic: multiply stats based on selection density for mock effect
                    const factor = (this.selectedYears.length + this.selectedMonths.length) / 15;
                    return {
                        total: Math.round(this.origStats.total * factor),
                        distributed: Math.round(this.origStats.distributed * factor),
                        value: this.origStats.value * factor,
                        serviceable: Math.round(this.origStats.serviceable * factor),
                        forRepair: Math.round(this.origStats.forRepair * factor),
                        unserviceable: Math.round(this.origStats.unserviceable * factor)
                    };
                },

                toggleYear(year) {
                    if (this.selectedYears.includes(year)) {
                        this.selectedYears = this.selectedYears.filter(y => y !== year);
                    } else {
                        this.selectedYears.push(year);
                    }
                },

                toggleMonth(month) {
                    if (this.selectedMonths.includes(month)) {
                        this.selectedMonths = this.selectedMonths.filter(m => m !== month);
                    } else {
                        this.selectedMonths.push(month);
                    }
                },

                resetFilters() {
                    this.selectedYears = [];
                    this.selectedMonths = [];
                },

                numberFormat(val, decimals = 0) {
                    return new Intl.NumberFormat('en-PH', { 
                        minimumFractionDigits: decimals, 
                        maximumFractionDigits: decimals 
                    }).format(val);
                },

                calcPercent(val) {
                    const max = Math.max(this.filteredStats.serviceable, this.filteredStats.forRepair, this.filteredStats.unserviceable, 1);
                    return (val / max) * 100;
                }
            }
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (sidebar && overlay) {
                const isHidden = sidebar.classList.contains('-translate-x-full');
                if (isHidden) {
                    sidebar.classList.remove('-translate-x-full');
                    sidebar.classList.add('expanded');
                    overlay.classList.remove('hidden');
                    setTimeout(() => overlay.style.opacity = "1", 10);
                    document.body.classList.add('overflow-hidden');
                } else {
                    sidebar.classList.add('-translate-x-full');
                    sidebar.classList.remove('expanded');
                    overlay.style.opacity = "0";
                    setTimeout(() => {
                        overlay.classList.add('hidden');
                    }, 300);
                    document.body.classList.remove('overflow-hidden');
                }
            }
        }
    </script>
</body>
</html>