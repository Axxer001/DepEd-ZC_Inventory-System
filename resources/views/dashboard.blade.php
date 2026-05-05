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
                <span class="font-extrabold italic text-sm text-slate-800 uppercase tracking-tight">DepEd ZC Inventory Management</span>
            </div>
            <div class="w-8 h-8 bg-[#c00000] rounded-lg flex items-center justify-center text-white font-bold text-xs shadow-lg shadow-red-100 italic">A</div>
        </header>

        {{-- MAIN CONTENT AREA --}}
        <main class="flex-grow flex flex-col overflow-y-auto custom-scroll bg-slate-50/50">
            <header class="py-4 px-6 lg:py-5 lg:px-8 flex justify-between items-center bg-white/70 backdrop-blur-xl sticky top-0 z-20 hidden lg:flex border-b border-slate-100">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight italic uppercase leading-none" x-text="filterLabel">Inventory Overview</h2>
                    <p class="text-[10px] font-bold text-[#c00000] uppercase tracking-widest mt-2 ml-1">Deped ZC Inventory Management System</p>
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

                    <button class="relative p-3 bg-white border border-slate-200 text-slate-400 rounded-2xl hover:text-[#c00000] hover:border-[#c00000]/30 hover:shadow-lg hover:shadow-red-50 transition-all shadow-sm group active:scale-90">
                        <svg class="w-5 h-5 group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                        <span class="absolute top-2.5 right-2.5 w-2.5 h-2.5 bg-red-500 border-2 border-white rounded-full animate-pulse group-hover:scale-125 transition-transform"></span>
                    </button>
                </div>
            </header>

            <div class="flex-grow p-6 lg:p-10 space-y-12">
                
                {{-- 1. Top Stat Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                    {{-- Total Inventory --}}
                    <div class="p-7 rounded-[2rem] bg-white border-l-8 border-[#c00000] shadow-xl flex flex-col justify-between h-48 group hover:scale-[1.01] hover:shadow-2xl transition-all duration-300 ease-out cursor-default relative overflow-hidden border-r border-y border-slate-50">
                        <div class="flex justify-between items-start relative z-10">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 group-hover:text-[#c00000] transition-colors">System Asset Inventory</span>
                                <span class="text-[8px] font-bold uppercase tracking-widest mt-1.5 text-[#c00000]" x-text="selectedYears.length || selectedMonths.length ? 'Filtered Result' : 'Total System Count'">Overall</span>
                            </div>
                            <div class="p-2 bg-white rounded-2xl shadow-sm group-hover:rotate-12 transition-all duration-300">
                                <img src="{{ asset('images/asset.png') }}" alt="Asset Inventory" class="w-12 h-12 object-contain">
                            </div>
                        </div>
                        <div class="relative z-10">
                            <div class="flex items-baseline gap-3">
                                <span class="text-4xl font-black tracking-tighter text-slate-900 group-hover:tracking-tight transition-all" x-text="numberFormat(filteredStats.total)">{{ number_format($totalAssets > 0 ? $totalAssets : 24850) }}</span>
                                <span class="text-[10px] font-bold text-slate-400 italic uppercase tracking-widest">Stock Units</span>
                            </div>
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mt-2 italic opacity-60">Total registered units in the system</p>
                        </div>
                    </div>

                    {{-- Not Yet Distributed Assets --}}
                    <div class="p-7 rounded-[2rem] bg-white border-l-8 border-[#c00000] shadow-xl flex flex-col justify-between h-48 group hover:scale-[1.01] hover:shadow-2xl transition-all duration-300 ease-out cursor-default relative overflow-hidden border-r border-y border-slate-50">
                        <div class="flex justify-between items-start relative z-10">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 group-hover:text-[#c00000] transition-colors">Assets Not Yet Distributed</span>
                                <span class="text-[8px] font-bold uppercase tracking-widest mt-1.5 text-[#c00000]" x-text="selectedYears.length || selectedMonths.length ? 'Filtered Result' : 'Warehouse Stock'">Overall</span>
                            </div>
                            <div class="p-2 bg-white rounded-2xl shadow-sm group-hover:rotate-12 transition-all duration-300">
                                <img src="{{ asset('images/not_yet_distributed.png') }}" alt="Not Yet Distributed" class="w-12 h-12 object-contain">
                            </div>
                        </div>
                        <div class="relative z-10">
                            <div class="flex items-baseline gap-3">
                                <span class="text-4xl font-black tracking-tighter text-slate-900 group-hover:tracking-tight transition-all" x-text="numberFormat(filteredStats.total - filteredStats.distributed)">{{ number_format(($totalAssets ?? 24850) - ($distributedCount ?? 18420)) }}</span>
                                <span class="text-[10px] font-bold text-slate-400 italic uppercase tracking-widest">Stock Units</span>
                            </div>
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mt-2 italic opacity-60">Total units pending for school deployment</p>
                        </div>
                    </div>

                    {{-- Total Amount --}}
                    <div class="p-7 rounded-[2rem] bg-white border-l-8 border-[#c00000] shadow-xl flex flex-col justify-between h-48 group hover:scale-[1.01] hover:shadow-2xl transition-all duration-300 ease-out cursor-default overflow-hidden relative border-r border-y border-slate-50">
                        <div class="flex justify-between items-start relative z-10">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 group-hover:text-[#c00000] transition-colors">TOTAL AMOUNT OF ASSETS</span>
                                <div class="flex items-center gap-2 mt-1.5">
                                    <span class="text-[8px] font-bold uppercase tracking-widest text-[#c00000]" x-text="cardFilter === 'Overall' ? 'System Verified' : (cardFilter === 'SemiExpendable' ? 'Semi-Expendable' : cardFilter) + ' Value'">System Verified</span>
                                    <select x-model="cardFilter" class="bg-red-50 border-none text-[#c00000] text-[7px] font-black uppercase tracking-widest rounded-lg px-2 py-0.5 focus:ring-0 cursor-pointer hover:bg-red-100 transition-colors">
                                        <option value="Overall">All</option>
                                        <option value="Items">Items</option>
                                        <option value="Buildings">Buildings</option>
                                        <option value="PPE">PPE</option>
                                        <option value="SemiExpendable">Semi-Exp</option>
                                    </select>
                                </div>
                            </div>
                            <div class="p-2 bg-white rounded-2xl shadow-sm group-hover:scale-110 transition-all duration-300 flex items-center justify-center relative overflow-hidden">
                                <img src="{{ asset('images/pesos.png') }}" alt="Total Amount" class="w-12 h-12 object-contain">
                            </div>
                        </div>
                        <div class="relative z-10">
                            <div class="flex items-baseline gap-1">
                                <span class="text-xs font-black text-[#c00000] mb-2">₱</span>
                                <span class="text-3xl font-black tracking-tighter text-[#c00000] group-hover:tracking-tight transition-all" x-text="numberFormat(filteredStats.value, 2)">{{ number_format($totalAmount ?? 12450830.50, 2) }}</span>
                            </div>
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mt-2 italic opacity-60">Total system asset valuation in PHP</p>
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
                        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 group hover:scale-[1.02] hover:shadow-xl transition-all duration-300 ease-out cursor-default relative overflow-hidden">
                            <div class="flex justify-between items-start mb-6">
                                <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest italic group-hover:translate-x-1 transition-transform">Serviceable</span>
                                <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl group-hover:bg-emerald-500 group-hover:text-white transition-all duration-300 shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-black tracking-tighter text-slate-900" x-text="numberFormat(filteredStats.serviceable)">{{ number_format($serviceableCount) }}</span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase italic">Units</span>
                            </div>
                            <div class="mt-4 w-full bg-slate-50 h-1.5 rounded-full overflow-hidden shadow-inner">
                                <div class="bg-emerald-500 h-full rounded-full group-hover:bg-emerald-400 transition-colors" :style="`width: ${calcPercent(filteredStats.serviceable)}%`" style="width: 100%"></div>
                            </div>
                        </div>

                        {{-- For Repair --}}
                        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 group hover:scale-[1.02] hover:shadow-xl transition-all duration-300 ease-out cursor-default relative overflow-hidden">
                            <div class="flex justify-between items-start mb-6">
                                <span class="text-[10px] font-black text-amber-600 uppercase tracking-widest italic group-hover:translate-x-1 transition-transform">For Repair</span>
                                <div class="p-2.5 bg-amber-50 text-amber-600 rounded-xl group-hover:bg-amber-500 group-hover:text-white transition-all duration-300 shadow-sm">
                                    {{-- Clean Wrench Icon --}}
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-black tracking-tighter text-slate-900" x-text="numberFormat(filteredStats.forRepair)">{{ number_format($forRepairCount) }}</span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase italic">Units</span>
                            </div>
                            <div class="mt-4 w-full bg-slate-50 h-1.5 rounded-full overflow-hidden shadow-inner">
                                <div class="bg-amber-500 h-full rounded-full group-hover:bg-amber-400 transition-colors" :style="`width: ${calcPercent(filteredStats.forRepair)}%`" style="width: 60%"></div>
                            </div>
                        </div>

                        {{-- Unserviceable --}}
                        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 group hover:scale-[1.02] hover:shadow-xl transition-all duration-300 ease-out cursor-default relative overflow-hidden">
                            <div class="flex justify-between items-start mb-6">
                                <span class="text-[10px] font-black text-[#c00000] uppercase tracking-widest italic group-hover:translate-x-1 transition-transform">Unserviceable</span>
                                <div class="p-2.5 bg-red-50 text-[#c00000] rounded-xl group-hover:bg-[#c00000] group-hover:text-white transition-all duration-300 shadow-sm">
                                    {{-- Clean Circle-X (Out of Service) Icon --}}
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-black tracking-tighter text-slate-900" x-text="numberFormat(filteredStats.unserviceable)">{{ number_format($unserviceableCount) }}</span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase italic">Units</span>
                            </div>
                            <div class="mt-4 w-full bg-slate-50 h-1.5 rounded-full overflow-hidden shadow-inner">
                                <div class="bg-[#c00000] h-full rounded-full group-hover:bg-red-500 transition-colors" :style="`width: ${calcPercent(filteredStats.unserviceable)}%`" style="width: 30%"></div>
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 pb-6">
                        @php
                            $assetSources = [
                                [
                                    'title' => 'Deped Central Assets', 
                                    'qty' => 12450, 
                                    'value' => 5200000.00,
                                    'image' => 'central.png'
                                ],
                                [
                                    'title' => 'Deped Regional Assets', 
                                    'qty' => 8320, 
                                    'value' => 3150000.50,
                                    'image' => 'regional.png'
                                ],
                                [
                                    'title' => 'Donated Assets', 
                                    'qty' => 2100, 
                                    'value' => 1850000.00,
                                    'image' => 'donated.png'
                                ],
                                [
                                    'title' => 'Transferred Assets', 
                                    'qty' => 1980, 
                                    'value' => 2250830.00,
                                    'image' => 'transferred.png'
                                ],
                            ];
                        @endphp

                        @foreach($assetSources as $source)
                        <div class="bg-white p-7 rounded-[2rem] shadow-xl border-l-8 border-[#c00000] group hover:scale-[1.01] hover:shadow-2xl transition-all duration-500 ease-out cursor-default relative overflow-hidden">
                            <div class="relative z-10">
                                <div class="flex justify-between items-start mb-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-1.5 h-6 bg-[#c00000] rounded-full group-hover:h-10 transition-all duration-500"></div>
                                        <h4 class="text-xs font-black uppercase tracking-widest text-slate-400 group-hover:text-[#c00000] transition-colors">
                                            {{ $source['title'] }}
                                        </h4>
                                    </div>
                                    <div class="p-1 bg-red-50/50 rounded-xl group-hover:bg-white transition-all duration-300 shadow-sm overflow-hidden">
                                        <img src="{{ asset('images/' . $source['image']) }}" alt="{{ $source['title'] }}" class="w-10 h-10 object-contain group-hover:scale-110 transition-transform duration-500">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <div class="flex items-center gap-1.5 mb-1">
                                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic">Total Value</p>
                                            <span class="px-1.5 py-0.5 bg-red-50 text-[#c00000] text-[7px] font-black rounded uppercase border border-red-100/50">Verified</span>
                                        </div>
                                        <p class="text-2xl font-black tracking-tighter leading-none text-[#c00000] group-hover:scale-105 origin-left transition-transform">
                                            ₱{{ number_format($source['value'], 2) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 italic">Quantity</p>
                                        <div class="flex items-baseline gap-2">
                                            <p class="text-3xl font-black text-slate-800 tracking-tighter group-hover:text-slate-900 transition-colors">{{ number_format($source['qty']) }}</p>
                                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-slate-400">Units</p>
                                        </div>
                                    </div>
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 pb-10">
                        @php 
                            $quadrants = [
                                1 => ['label' => 'Q 1.1', 'short' => '1.1', 'desc' => 'LD 1 • 3 Districts', 'value' => 2450830.00],
                                2 => ['label' => 'Q 1.2', 'short' => '1.2', 'desc' => 'LD 1 • 2 Districts', 'value' => 1850000.50],
                                3 => ['label' => 'Q 2.1', 'short' => '2.1', 'desc' => 'LD 2 • 3 Districts', 'value' => 3120000.00],
                                4 => ['label' => 'Q 2.2', 'short' => '2.2', 'desc' => 'LD 2 • 4 Districts', 'value' => 5030000.00],
                            ];
                        @endphp

                        @foreach($quadrants as $id => $q)
                        <div class="bg-white p-7 rounded-[2rem] shadow-xl border-l-8 border-[#c00000] flex flex-col justify-between group hover:scale-[1.01] hover:shadow-2xl transition-all duration-500 ease-out cursor-default relative overflow-hidden">
                            <div class="relative z-10">
                                <div class="flex justify-between items-start mb-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-red-50 text-[#c00000] rounded-2xl flex items-center justify-center text-sm font-black tracking-tighter italic border border-red-100/50 group-hover:bg-[#c00000] group-hover:text-white transition-all duration-500 group-hover:rotate-6 shadow-sm">
                                            {{ $q['label'] }}
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-black uppercase italic leading-none group-hover:text-[#c00000] transition-colors">
                                                Quadrant {{ substr($q['label'], 2) }}
                                            </h4>
                                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest italic mt-1">{{ $q['desc'] }}</p>
                                        </div>
                                    </div>
                                    <div class="p-2.5 bg-red-50 text-[#c00000] rounded-xl group-hover:bg-[#c00000] group-hover:text-white transition-all duration-300 shadow-sm">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2L4.5 20.29l.71.71L12 18l6.79 3 .71-.71z"/></svg>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 italic">Total Amount</p>
                                        <p class="text-2xl font-black tracking-tighter leading-none text-[#c00000] group-hover:scale-105 origin-left transition-transform duration-500">₱{{ number_format($q['value'], 2) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 italic">Quantity</p>
                                        <div class="flex items-baseline gap-2">
                                            <p class="text-3xl font-black text-slate-800 tracking-tighter group-hover:text-slate-900 transition-colors duration-500">{{ number_format($quadrantTotals[$id] ?? 4500) }}</p>
                                            <span class="text-[8px] font-black text-slate-400 italic uppercase">Units</span>
                                        </div>
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
                                    <tr class="hover:bg-slate-50 transition-all duration-200 group cursor-default">
                                        <td class="px-6 py-6 text-slate-400 font-black italic group-hover:text-[#c00000] transition-colors" x-text="'#INV-' + log.id.toString().padStart(5, '0')"></td>
                                        <td class="px-6 py-6 font-black text-slate-800 transition-colors uppercase leading-tight group-hover:translate-x-1 transition-transform" x-text="log.school"></td>
                                        <td class="px-6 py-6 text-slate-500 uppercase tracking-tighter" x-text="log.timestamp"></td>
                                        <td class="px-6 py-6 font-black text-lg tracking-tighter text-slate-900 group-hover:scale-110 transition-transform" x-text="numberFormat(log.qty)"></td>
                                        <td class="px-6 py-6 text-right">
                                            <span class="px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl text-[10px] font-black uppercase italic border border-emerald-100 shadow-sm group-hover:bg-emerald-500 group-hover:text-white transition-all">Verified</span>
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
                <div class="bg-[#c00000] p-7 rounded-[2.5rem] shadow-xl shadow-red-200 group hover:shadow-red-300 hover:scale-[1.01] transition-all duration-500 cursor-default relative overflow-hidden border border-white/10">
                    {{-- Top Section: Icon & Badge --}}
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-14 h-14 bg-white rounded-[1.5rem] shadow-lg flex items-center justify-center group-hover:rotate-6 transition-transform duration-500">
                             <img src="{{ asset('images/megaphone-3d.webp') }}" alt="Megaphone" class="w-10 h-10 object-contain">
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md text-white text-[8px] font-black uppercase tracking-widest rounded-full mb-2 italic border border-white/20">System Alert</span>
                            <p class="text-[8px] font-black text-white/40 uppercase italic leading-none">Ref: #NOTICE-2026</p>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="space-y-4 relative z-10">
                        <h3 class="text-lg font-black text-white uppercase italic leading-tight">Quarterly Inventory Audit Coming Up</h3>
                        <p class="text-[10px] font-bold text-white/70 leading-relaxed uppercase pr-4">
                            All institution heads are required to verify their current asset counts by the end of the month.
                        </p>
                    </div>

                    {{-- Footer --}}
                    <div class="mt-8 pt-6 border-t border-white/10 flex justify-between items-center relative z-10">
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 bg-white rounded-full animate-pulse shadow-[0_0_8px_rgba(255,255,255,0.8)]"></div>
                            <span class="text-[8px] font-black text-white/60 uppercase italic">Priority: High</span>
                        </div>
                        <span class="text-[9px] font-black text-white/40 uppercase italic">Today • 10:45 AM</span>
                    </div>

                    {{-- Background Decoration --}}
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-white/5 rounded-full blur-3xl group-hover:bg-white/10 transition-colors"></div>
                </div>

                <div class="space-y-6">
                    <div class="flex items-center gap-3 px-2">
                        <div class="w-1 h-3 bg-[#c00000] rounded-full group-hover:h-5 transition-all"></div>
                        <h5 class="text-[10px] font-black uppercase tracking-widest text-slate-400">System Notifications</h5>
                    </div>

                    <div class="space-y-4">
                        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 group cursor-default hover:shadow-lg hover:bg-white hover:scale-[1.02] transition-all duration-300">
                            <div class="flex items-center gap-4 mb-3">
                                <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center border border-emerald-100 group-hover:bg-emerald-500 group-hover:text-white transition-all duration-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div>
                                    <p class="text-[11px] font-black text-slate-800 uppercase italic group-hover:text-[#c00000] transition-colors">Database Sync</p>
                                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">Successful • 5 mins ago</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 group cursor-default hover:shadow-lg hover:bg-white hover:scale-[1.02] transition-all duration-300">
                            <div class="flex items-center gap-4 mb-3">
                                <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center border border-amber-100 group-hover:bg-amber-500 group-hover:text-white transition-all duration-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                </div>
                                <div>
                                    <p class="text-[11px] font-black text-slate-800 uppercase italic group-hover:text-[#c00000] transition-colors">Maintenance Alert</p>
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
                cardFilter: 'Overall',
                
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
                    let stats = { ...this.origStats };
                    
                    if (this.selectedYears.length > 0 || this.selectedMonths.length > 0) {
                        const factor = (this.selectedYears.length + this.selectedMonths.length) / 15;
                        stats.total = Math.round(stats.total * factor);
                        stats.distributed = Math.round(stats.distributed * factor);
                        stats.value = stats.value * factor;
                        stats.serviceable = Math.round(stats.serviceable * factor);
                        stats.forRepair = Math.round(stats.forRepair * factor);
                        stats.unserviceable = Math.round(stats.unserviceable * factor);
                    }

                    // Apply card-level specific filter for the Amount card
                    if (this.cardFilter === 'Items') {
                        stats.value = stats.value * 0.65; 
                    } else if (this.cardFilter === 'Buildings') {
                        stats.value = stats.value * 0.35; 
                    } else if (this.cardFilter === 'PPE') {
                        stats.value = stats.value * 0.75; // Mock: PPE is 75% of value
                    } else if (this.cardFilter === 'SemiExpendable') {
                        stats.value = stats.value * 0.25; // Mock: Semi-expendable is 25% of value
                    }

                    return stats;
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