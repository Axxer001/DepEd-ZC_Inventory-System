<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DepEd ZC IMS | Inventory Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f8fafc;
        }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        
        .stat-card-red-accent {
            background: white;
            border-top: 4px solid #c00000;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen flex animate-fade-in text-slate-800 overflow-x-hidden" x-data="dashboardFilter()">

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
                <span class="font-extrabold italic text-sm text-slate-800 uppercase tracking-tight">DepEd ZC IMS</span>
            </div>
            <div class="w-8 h-8 bg-[#c00000] rounded-lg flex items-center justify-center text-white font-bold text-xs shadow-lg shadow-red-100 italic">A</div>
        </header>

        {{-- MAIN CONTENT AREA --}}
        <main class="flex-grow flex flex-col overflow-y-auto custom-scroll bg-slate-50/50">
            <header class="py-4 px-6 lg:py-5 lg:px-8 flex justify-between items-center bg-white/70 backdrop-blur-xl sticky top-0 z-20 hidden lg:flex border-b border-slate-100">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight italic uppercase leading-none" x-text="filterLabel">Inventory Overview</h2>
                    <p class="text-[10px] font-bold text-[#c00000] uppercase tracking-widest mt-2 ml-1">Zamboanga City Division • Asset Management</p>
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
                             class="absolute right-0 mt-4 w-80 bg-white rounded-[2rem] shadow-2xl border border-slate-100 p-8 z-50">
                            
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

                    <button class="relative p-3 bg-white border border-slate-200 text-slate-400 rounded-2xl hover:text-[#c00000] transition-all shadow-sm group">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                        <span class="absolute top-2.5 right-2.5 w-2.5 h-2.5 bg-red-500 border-2 border-white rounded-full animate-pulse"></span>
                    </button>
                </div>
            </header>

            <div class="flex-grow p-6 lg:p-10 space-y-12">
                
                {{-- 1. Top Stat Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                    <div class="p-8 rounded-[2rem] bg-white border-l-8 border-[#c00000] shadow-xl flex flex-col justify-between h-44 group">
                        <div class="flex justify-between items-start">
                            <div class="flex flex-col">
                                <span class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">System Asset Inventory</span>
                                <span class="text-[8px] font-bold uppercase tracking-widest mt-1 text-[#c00000]" x-text="selectedYears.length || selectedMonths.length ? 'Filtered Result' : 'Total System Count'">Overall</span>
                            </div>
                            <div class="p-2 bg-red-50 rounded-xl text-[#c00000]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-3">
                            <span class="text-5xl font-black tracking-tighter text-slate-900" x-text="numberFormat(filteredStats.total)">{{ number_format($totalAssets > 0 ? $totalAssets : 24850) }}</span>
                            <span class="text-xs font-bold text-slate-400 italic uppercase tracking-widest">Stock Units</span>
                        </div>
                    </div>
                    <div class="p-8 rounded-[2rem] bg-slate-900 text-white shadow-xl flex flex-col justify-between h-44 relative">
                        <div class="flex justify-between items-start text-white/50">
                            <div class="flex flex-col">
                                <span class="text-xs font-black uppercase tracking-[0.2em]">Total Assets Distributed</span>
                                <span class="text-[8px] font-bold uppercase tracking-widest mt-1 text-white/30" x-text="selectedYears.length || selectedMonths.length ? 'Filtered Result' : 'Global Distribution'">Overall</span>
                            </div>
                            <div class="p-2 bg-white/10 rounded-xl text-white">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-3">
                            <span class="text-5xl font-black tracking-tighter text-white" x-text="numberFormat(filteredStats.distributed)">{{ number_format(($distributedCount ?? 0) > 0 ? $distributedCount : 18420) }}</span>
                            <span class="text-xs font-bold text-white/40 italic uppercase tracking-widest">Deployed</span>
                        </div>
                    </div>
                    <div class="p-8 rounded-[2rem] bg-white border-l-8 border-[#c00000] shadow-xl flex flex-col justify-between h-44">
                        <div class="flex justify-between items-start">
                            <div class="flex flex-col">
                                <span class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">TOTAL AMOUNT OF ASSETS</span>
                                <span class="text-[8px] font-bold uppercase tracking-widest mt-1 text-[#c00000]">System Verified</span>
                            </div>
                            <div class="p-2 bg-red-50 rounded-xl text-[#c00000]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H5a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-3">
                            <span class="text-5xl font-black tracking-tighter text-[#c00000]">{{ number_format($totalSchools ?? 207) }}</span>
                            <span class="text-xs font-bold text-slate-400 italic uppercase tracking-widest">Institutions</span>
                        </div>
                    </div>
                </div>

                {{-- 2. Middle Row: Asset Condition Section --}}
                <div>
                    <div class="flex items-center justify-between mb-6 px-2">
                        <div class="flex items-center gap-3">
                            <div class="w-1.5 h-4 bg-[#c00000] rounded-full animate-pulse"></div>
                            <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em]">Asset Condition Summary</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-100 shadow-sm">Updated Real-time</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                        {{-- Serviceable --}}
                        <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                            <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-4 block italic">Serviceable</span>
                            <div class="flex items-baseline gap-2">
                                <span class="text-4xl font-black tracking-tighter text-slate-900" x-text="numberFormat(filteredStats.serviceable)">{{ number_format($serviceableCount) }}</span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase italic">Units</span>
                            </div>
                            <div class="mt-4 w-full bg-slate-50 h-1.5 rounded-full overflow-hidden shadow-inner">
                                <div class="bg-emerald-500 h-full rounded-full" :style="`width: ${calcPercent(filteredStats.serviceable)}%`" style="width: 100%"></div>
                            </div>
                        </div>

                        {{-- For Repair --}}
                        <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                            <span class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-4 block italic">For Repair</span>
                            <div class="flex items-baseline gap-2">
                                <span class="text-4xl font-black tracking-tighter text-slate-900" x-text="numberFormat(filteredStats.forRepair)">{{ number_format($forRepairCount) }}</span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase italic">Units</span>
                            </div>
                            <div class="mt-4 w-full bg-slate-50 h-1.5 rounded-full overflow-hidden shadow-inner">
                                <div class="bg-amber-500 h-full rounded-full" :style="`width: ${calcPercent(filteredStats.forRepair)}%`" style="width: 60%"></div>
                            </div>
                        </div>

                        {{-- Unserviceable --}}
                        <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                            <span class="text-[10px] font-black text-[#c00000] uppercase tracking-widest mb-4 block italic">Unserviceable</span>
                            <div class="flex items-baseline gap-2">
                                <span class="text-4xl font-black tracking-tighter text-slate-900" x-text="numberFormat(filteredStats.unserviceable)">{{ number_format($unserviceableCount) }}</span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase italic">Units</span>
                            </div>
                            <div class="mt-4 w-full bg-slate-50 h-1.5 rounded-full overflow-hidden shadow-inner">
                                <div class="bg-[#c00000] h-full rounded-full" :style="`width: ${calcPercent(filteredStats.unserviceable)}%`" style="width: 30%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. Asset Source Portfolio --}}
                <div class="space-y-6">
                    <div class="flex items-center justify-between px-2 text-slate-400">
                        <div class="flex items-center gap-3">
                            <div class="w-1.5 h-4 bg-slate-400 rounded-full"></div>
                            <h3 class="text-xs font-black uppercase tracking-[0.3em]">Asset Source Portfolio</h3>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 pb-6">
                        @php
                            $assetSources = [
                                ['title' => 'Deped Central Assets', 'qty' => 12450, 'value' => 5200000.00],
                                ['title' => 'Deped Regional Assets', 'qty' => 8320, 'value' => 3150000.50],
                                ['title' => 'Donated Assets', 'qty' => 2100, 'value' => 1850000.00],
                                ['title' => 'Transferred Assets', 'qty' => 1980, 'value' => 2250830.00],
                            ];
                        @endphp

                        @foreach($assetSources as $source)
                        <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100 hover:border-[#c00000]/30 transition-colors">
                            <div class="flex items-center gap-3 mb-6 relative">
                                <div class="w-1 h-5 bg-[#c00000] rounded-full"></div>
                                <h4 class="text-[12px] font-black uppercase tracking-wider truncate text-slate-800">
                                    {{ $source['title'] }}
                                </h4>
                            </div>
                            <div class="pl-4">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 italic">Total Asset Value</p>
                                <p class="text-2xl font-black tracking-tighter leading-none mb-6 text-[#c00000]">
                                    ₱{{ number_format($source['value'], 2) }}
                                </p>
                                
                                <div class="pt-4 border-t border-slate-50">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 italic">Asset Quantity</p>
                                    <p class="text-4xl font-black text-slate-800 tracking-tighter">{{ number_format($source['qty']) }}</p>
                                    <p class="text-[8px] font-black uppercase tracking-[0.2em] text-slate-400 mt-1 uppercase">Units Registered</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
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
                                1 => ['label' => 'Q 1.1', 'desc' => 'LD 1 • 3 Districts', 'value' => 2450830.00],
                                2 => ['label' => 'Q 1.2', 'desc' => 'LD 1 • 2 Districts', 'value' => 1850000.50],
                                3 => ['label' => 'Q 2.1', 'desc' => 'LD 2 • 3 Districts', 'value' => 3120000.00],
                                4 => ['label' => 'Q 2.2', 'desc' => 'LD 2 • 4 Districts', 'value' => 5030000.00],
                            ];
                        @endphp

                        @foreach($quadrants as $id => $q)
                        <div class="bg-white p-7 rounded-[2rem] shadow-sm border border-slate-100 flex flex-col justify-between hover:shadow-lg transition-shadow">
                            <div>
                                <div class="flex items-center gap-4 mb-6 relative">
                                    <div class="w-12 h-12 bg-slate-50 text-[#c00000] rounded-2xl flex items-center justify-center text-sm font-black tracking-tighter italic border border-slate-100">{{ $q['label'] }}</div>
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800 uppercase italic leading-none">Quadrant {{ substr($q['label'], 2) }}</h4>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest italic mt-1">{{ $q['desc'] }}</p>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Asset Quantity</p>
                                        <div class="flex items-baseline gap-2">
                                            <p class="text-3xl font-black tracking-tighter leading-none text-slate-900">{{ number_format($quadrantTotals[$id] ?? 4500) }}</p>
                                            <span class="text-[9px] font-bold text-slate-400 italic uppercase">Units</span>
                                        </div>
                                    </div>

                                    <div class="px-2">
                                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Amount Overall</p>
                                        <p class="text-xl font-black tracking-tighter text-[#c00000]">₱{{ number_format($q['value'], 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- 5. Bottom Table Section --}}
                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100 overflow-hidden mb-8">
                    <div class="flex justify-between items-center mb-8 px-4">
                        <h3 class="text-lg font-black uppercase italic tracking-tight leading-none text-slate-900">Recent Transaction Logs</h3>
                        <a href="{{ route('admin.logs') }}" class="text-[10px] font-black text-[#c00000] uppercase tracking-widest hover:underline transition-all bg-red-50 px-4 py-2.5 rounded-xl border border-red-100 italic">View All History</a>
                    </div>
                    <div class="overflow-x-auto custom-scroll">
                        <table class="w-full text-left border-separate border-spacing-0 min-w-[600px]">
                            <thead>
                                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b-2 border-slate-50">
                                    <th class="px-6 py-4 pb-6">Log ID</th>
                                    <th class="px-6 py-4 pb-6">Institutional Name</th>
                                    <th class="px-6 py-4 pb-6">Update Timestamp</th>
                                    <th class="px-6 py-4 pb-6">Quantity</th>
                                    <th class="px-6 py-4 pb-6 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs font-bold text-slate-700 divide-y divide-slate-50">
                                <template x-for="log in filteredLogs" :key="log.id">
                                    <tr class="hover:bg-slate-50/50 transition-colors group">
                                        <td class="px-6 py-6 text-slate-400 font-black italic" x-text="'#INV-' + log.id.toString().padStart(5, '0')"></td>
                                        <td class="px-6 py-6 font-black text-slate-800 transition-colors uppercase leading-tight" x-text="log.school"></td>
                                        <td class="px-6 py-6 text-slate-500 uppercase tracking-tighter" x-text="log.timestamp"></td>
                                        <td class="px-6 py-6 font-black text-lg tracking-tighter text-slate-900" x-text="numberFormat(log.qty)"></td>
                                        <td class="px-6 py-6 text-right">
                                            <span class="px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl text-[10px] font-black uppercase italic border border-emerald-100 shadow-sm">Verified</span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>

        {{-- RIGHT SUMMARY SIDEBAR --}}
        <aside class="w-full lg:w-96 border-l border-slate-200 p-8 lg:p-10 flex flex-col shrink-0 overflow-y-auto custom-scroll bg-white z-10">
            <div class="flex items-center justify-between mb-10 text-slate-900">
                <h3 class="text-2xl font-black tracking-tight italic uppercase leading-none">Notice Sidebar</h3>
                <div class="w-2 h-2 bg-[#c00000] rounded-full animate-ping"></div>
            </div>
            
            <div class="space-y-8">
                <div class="p-8 rounded-[2rem] bg-[#c00000] text-white shadow-xl shadow-red-100 relative overflow-hidden group">
                    <h4 class="text-xs font-black uppercase tracking-[0.2em] mb-4 opacity-70 italic">Heads Up</h4>
                    <p class="text-xl font-black tracking-tight leading-tight uppercase italic mb-4">Quarterly Inventory Audit Coming Up</p>
                    <p class="text-[10px] font-bold text-white/60 leading-relaxed uppercase">All institution heads are required to verify their current asset counts by the end of the month.</p>
                </div>

                <div class="space-y-6">
                    <div class="flex items-center gap-3 px-2">
                        <div class="w-1 h-3 bg-[#c00000] rounded-full"></div>
                        <h5 class="text-[10px] font-black uppercase tracking-widest text-slate-400">System Notifications</h5>
                    </div>

                    <div class="space-y-4">
                        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 group cursor-default hover:shadow-md transition-all">
                            <div class="flex items-center gap-4 mb-3">
                                <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center border border-emerald-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div>
                                    <p class="text-[11px] font-black text-slate-800 uppercase italic">Database Sync</p>
                                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">Successful • 5 mins ago</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 group cursor-default hover:shadow-md transition-all">
                            <div class="flex items-center gap-4 mb-3">
                                <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center border border-amber-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                </div>
                                <div>
                                    <p class="text-[11px] font-black text-slate-800 uppercase italic">Maintenance Alert</p>
                                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">Scheduled • Tonight 12:00 AM</p>
                                </div>
                            </div>
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

                origStats: {
                    total: {{ $totalAssets > 0 ? $totalAssets : 24850 }},
                    distributed: {{ ($distributedCount ?? 0) > 0 ? $distributedCount : 18420 }},
                    value: {{ $totalAmount > 0 ? $totalAmount : 12450830.50 }},
                    serviceable: {{ $serviceableCount > 0 ? $serviceableCount : 15200 }},
                    forRepair: {{ $forRepairCount > 0 ? $forRepairCount : 2450 }},
                    unserviceable: {{ $unserviceableCount > 0 ? $unserviceableCount : 820 }}
                },

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