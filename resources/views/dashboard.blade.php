<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DepEd ZC IMS | Inventory Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .notification-drawer {
            transform: translateX(100%);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .notification-drawer.open {
            transform: translateX(0);
        }
    </style>
</head>
<body class="min-h-screen flex animate-fade-in text-slate-800 overflow-x-hidden" x-data="dashboardFilter()">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-hidden relative">
        
        {{-- Notification Overlay --}}
        <div x-show="showNotifications" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showNotifications = false"
             class="fixed inset-0 bg-slate-900/40 z-[60] backdrop-blur-sm" x-cloak></div>

        {{-- Notification Drawer --}}
        <aside :class="showNotifications ? 'open' : ''" 
               class="notification-drawer fixed right-0 top-0 bottom-0 w-full md:w-96 bg-white z-[70] shadow-2xl flex flex-col overflow-hidden border-l border-slate-100">
            <div class="p-8 flex items-center justify-between border-b border-slate-50">
                <div>
                    <h3 class="text-2xl font-black tracking-tight italic uppercase leading-none text-slate-900">Notifications</h3>
                    <p class="text-[10px] font-bold text-[#c00000] uppercase tracking-widest mt-2">System Alerts & Notices</p>
                </div>
                <button @click="showNotifications = false" class="p-3 bg-slate-50 text-slate-900 rounded-2xl hover:text-red-600 hover:bg-red-50 transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            
            <div class="flex-grow overflow-y-auto custom-scroll p-8 space-y-8">
                <div class="bg-[#c00000] p-7 rounded-[2.5rem] shadow-xl shadow-red-200 group hover:shadow-red-300 hover:scale-[1.01] transition-all duration-500 cursor-default relative overflow-hidden border border-white/10">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-14 h-14 bg-white rounded-[1.5rem] shadow-lg flex items-center justify-center group-hover:rotate-6 transition-transform duration-500">
                             <img src="{{ asset('images/megaphone-3d.webp') }}" alt="Megaphone" class="w-10 h-10 object-contain">
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md text-white text-[8px] font-black uppercase tracking-widest rounded-full mb-2 italic border border-white/20">System Alert</span>
                            <p class="text-[8px] font-black text-white/40 uppercase italic leading-none">Ref: #NOTICE-2026</p>
                        </div>
                    </div>
                    <div class="space-y-4 relative z-10">
                        <h3 class="text-lg font-black text-white uppercase italic leading-tight">Quarterly Inventory Audit Coming Up</h3>
                        <p class="text-[10px] font-bold text-white/70 leading-relaxed uppercase pr-4">
                            All institution heads are required to verify their current asset counts by the end of the month.
                        </p>
                    </div>
                    <div class="mt-8 pt-6 border-t border-white/10 flex justify-between items-center relative z-10">
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 bg-white rounded-full animate-pulse shadow-[0_0_8px_rgba(255,255,255,0.8)]"></div>
                            <span class="text-[8px] font-black text-white/60 uppercase italic">Priority: High</span>
                        </div>
                        <span class="text-[9px] font-black text-white/40 uppercase italic">Today • 10:45 AM</span>
                    </div>
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-white/5 rounded-full blur-3xl group-hover:bg-white/10 transition-colors"></div>
                </div>

                <div class="space-y-6">
                    <div class="flex items-center gap-3 px-2">
                        <div class="w-1 h-3 bg-[#c00000] rounded-full group-hover:h-5 transition-all"></div>
                        <h5 class="text-[10px] font-black uppercase tracking-widest text-slate-900">System Notifications</h5>
                    </div>
                    <div class="space-y-4">
                        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 group cursor-default hover:shadow-lg hover:bg-white hover:scale-[1.02] transition-all duration-300">
                            <div class="flex items-center gap-4 mb-3">
                                <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center border border-emerald-100 group-hover:bg-emerald-500 group-hover:text-white transition-all duration-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div>
                                    <p class="text-[11px] font-black text-slate-800 uppercase italic group-hover:text-[#c00000] transition-colors">Database Sync</p>
                                    <p class="text-[8px] font-bold text-slate-900 uppercase tracking-widest">Successful • 5 mins ago</p>
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
                                    <p class="text-[8px] font-bold text-slate-900 uppercase tracking-widest">Scheduled • Tonight 12:00 AM</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-8 border-t border-slate-50">
                <button class="w-full py-4 bg-slate-900 text-white rounded-[1.5rem] font-black uppercase tracking-widest text-[11px] hover:bg-[#c00000] transition-all shadow-lg shadow-slate-200">Mark All as Read</button>
            </div>
        </aside>

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
                                    <h5 class="text-[10px] font-black text-slate-900 uppercase tracking-widest mb-4">Select Fiscal Year</h5>
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
                                    <h5 class="text-[10px] font-black text-slate-900 uppercase tracking-widest mb-4">Select Months</h5>
                                    <div class="grid grid-cols-3 gap-2">
                                        <template x-for="(name, index) in monthNames" :key="index">
                                            <button @click="toggleMonth(index + 1)"
                                                    :class="selectedMonths.includes(index + 1) ? 'bg-[#c00000] text-white border-[#c00000]' : 'bg-slate-50 text-slate-500 border-slate-100'"
                                                    class="py-2 rounded-xl border text-[10px] font-black transition-all" x-text="name"></button>
                                        </template>
                                    </div>
                                </div>

                                <div class="pt-4 border-t border-slate-50 flex justify-between items-center">
                                    <button @click="resetFilters()" class="text-[10px] font-black text-slate-900 uppercase tracking-widest hover:text-red-600 transition-colors">Reset All</button>
                                    <button @click="open = false" class="px-6 py-2 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-[#c00000] transition-all">Apply</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </header>

            <div class="flex-grow p-6 lg:p-10 space-y-12">
                
                {{-- 1. Top Stat Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-10">
                    {{-- Total Inventory --}}
                    <div class="p-8 rounded-[2.5rem] bg-white border-l-[12px] border-[#c00000] shadow-2xl flex flex-col justify-between h-56 group hover:-translate-y-2 hover:shadow-[0_30px_60px_-15px_rgba(0,0,0,0.1)] transition-all duration-500 ease-out cursor-default relative overflow-hidden border-r border-y border-slate-50">
                        <div class="flex justify-between items-start relative z-10">
                            <div class="flex flex-col">
                                <span class="text-xs font-black uppercase tracking-[0.2em] text-slate-900 group-hover:text-[#c00000] transition-colors">System Asset Inventory</span>
                                <span class="text-[10px] font-bold uppercase tracking-widest mt-2 text-[#c00000]" x-text="selectedYears.length || selectedMonths.length ? 'Filtered Result' : 'Total System Count'">Overall</span>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-[1.5rem] shadow-sm group-hover:shadow-[0_10px_25px_-5px_rgba(192,0,0,0.2)] group-hover:scale-110 transition-all duration-500">
                                <img src="{{ asset('images/asset.png') }}" alt="Asset Inventory" class="w-14 h-14 object-contain transition-transform duration-500 group-hover:rotate-3">
                            </div>
                        </div>
                        <div class="relative z-10">
                            <div class="flex items-baseline gap-3">
                                <span class="text-5xl font-black tracking-tighter text-slate-900" x-text="numberFormat(filteredStats.total)">{{ number_format($totalAssets > 0 ? $totalAssets : 24850) }}</span>
                                <span class="text-xs font-bold text-slate-900 italic uppercase tracking-widest">Stock Units</span>
                            </div>
                            <p class="text-[10px] font-black text-slate-900 uppercase tracking-widest mt-3 italic opacity-60">Total registered units in the system</p>
                        </div>
                    </div>

                    {{-- Not Yet Distributed Assets --}}
                    <div class="p-8 rounded-[2.5rem] bg-white border-l-[12px] border-[#c00000] shadow-2xl flex flex-col justify-between h-56 group hover:-translate-y-2 hover:shadow-[0_30px_60px_-15px_rgba(0,0,0,0.1)] transition-all duration-500 ease-out cursor-default relative overflow-hidden border-r border-y border-slate-50">
                        <div class="flex justify-between items-start relative z-10">
                            <div class="flex flex-col">
                                <span class="text-xs font-black uppercase tracking-[0.2em] text-slate-900 group-hover:text-[#c00000] transition-colors">Assets Not Yet Distributed</span>
                                <span class="text-[10px] font-bold uppercase tracking-widest mt-2 text-[#c00000]" x-text="selectedYears.length || selectedMonths.length ? 'Filtered Result' : 'Warehouse Stock'">Overall</span>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-[1.5rem] shadow-sm group-hover:shadow-[0_10px_25px_-5px_rgba(192,0,0,0.2)] group-hover:scale-110 transition-all duration-500">
                                <img src="{{ asset('images/not_yet_distributed.png') }}" alt="Not Yet Distributed" class="w-14 h-14 object-contain transition-transform duration-500 group-hover:-rotate-3">
                            </div>
                        </div>
                        <div class="relative z-10">
                            <div class="flex items-baseline gap-3">
                                <span class="text-5xl font-black tracking-tighter text-slate-900" x-text="numberFormat(filteredStats.total - filteredStats.distributed)">{{ number_format(($totalAssets ?? 24850) - ($distributedCount ?? 18420)) }}</span>
                                <span class="text-xs font-bold text-slate-900 italic uppercase tracking-widest">Stock Units</span>
                            </div>
                            <p class="text-[10px] font-black text-slate-900 uppercase tracking-widest mt-3 italic opacity-60">Total units pending for school deployment</p>
                        </div>
                    </div>

                    {{-- Total Amount --}}
                    <div class="p-8 rounded-[2.5rem] bg-white border-l-[12px] border-[#c00000] shadow-2xl flex flex-col justify-between h-56 group hover:-translate-y-2 hover:shadow-[0_30px_60px_-15px_rgba(0,0,0,0.1)] transition-all duration-500 ease-out cursor-default overflow-hidden relative border-r border-y border-slate-50">
                        <div class="flex justify-between items-start relative z-10">
                            <div class="flex flex-col">
                                <span class="text-xs font-black uppercase tracking-[0.2em] text-slate-900 group-hover:text-[#c00000] transition-colors">TOTAL AMOUNT OF ASSETS</span>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-900" x-text="cardFilter === 'Overall' ? 'System Verified' : (cardFilter === 'SemiExpendable' ? 'Semi-Expendable' : cardFilter) + ' Value'">System Verified</span>
                                    <select x-model="cardFilter" class="bg-slate-50 border-none text-slate-900 text-[8px] font-black uppercase tracking-widest rounded-lg px-2 py-0.5 focus:ring-0 cursor-pointer hover:bg-slate-100 transition-colors">
                                        <option value="Overall">All</option>
                                        <option value="Items">Items</option>
                                        <option value="Buildings">Buildings</option>
                                        <option value="PPE">PPE</option>
                                        <option value="SemiExpendable">Semi-Exp</option>
                                    </select>
                                </div>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-[1.5rem] shadow-sm group-hover:shadow-[0_10px_25px_-5px_rgba(192,0,0,0.2)] group-hover:scale-110 transition-all duration-500 flex items-center justify-center relative overflow-hidden">
                                <img src="{{ asset('images/pesos.png') }}" alt="Total Amount" class="w-14 h-14 object-contain transition-transform duration-500 group-hover:scale-110">
                            </div>
                        </div>
                        <div class="relative z-10">
                            <div class="flex items-baseline gap-1">
                                <span class="text-sm font-black text-slate-900 mb-2">₱</span>
                                <span class="text-4xl font-black tracking-tighter text-slate-900" x-text="numberFormat(filteredStats.value, 2)">{{ number_format($totalAmount ?? 12450830.50, 2) }}</span>
                            </div>
                            <p class="text-[10px] font-black text-slate-900 uppercase tracking-widest mt-3 italic opacity-60">Total system asset valuation in PHP</p>
                        </div>
                    </div>
                </div>

                {{-- 2. Middle Row: Analytics & Condition --}}
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    {{-- Condition Summary - Enhanced Visuals --}}
                    <div class="lg:col-span-3 space-y-6">
                        <div class="flex items-center gap-3 px-4">
                            <div class="w-1.5 h-4 bg-[#c00000] rounded-full animate-pulse shadow-[0_0_10px_rgba(192,0,0,0.5)]"></div>
                            <h3 class="text-xs font-black text-slate-900 uppercase tracking-[0.3em]">Asset Condition Summary</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            {{-- Serviceable --}}
                            <div class="bg-white p-6 rounded-[2.5rem] shadow-[0_10px_30px_rgba(0,0,0,0.03)] border-t border-slate-50 group hover:scale-[1.02] hover:shadow-emerald-50 transition-all duration-500 cursor-default overflow-hidden relative">
                                <div class="flex justify-between items-start mb-6 relative z-10">
                                    <div class="p-2 bg-emerald-50 rounded-2xl group-hover:bg-white transition-all duration-500 shadow-sm">
                                        <img src="{{ asset('images/serviceable.png') }}" alt="Serviceable" class="w-12 h-12 object-contain group-hover:scale-110 transition-transform">
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <span class="text-[8px] font-black text-emerald-600 uppercase tracking-widest italic bg-emerald-50 px-2 py-0.5 rounded">Operational</span>
                                    </div>
                                </div>
                                <div class="relative z-10">
                                    <p class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-1 italic">Serviceable</p>
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="text-4xl font-black tracking-tighter text-slate-900 group-hover:text-emerald-600 transition-colors" x-text="numberFormat(filteredStats.serviceable)">{{ number_format($serviceableCount) }}</span>
                                        <span class="text-[10px] font-bold text-slate-900 uppercase italic">Units</span>
                                    </div>
                                </div>
                                <div class="mt-6 w-full bg-slate-50 h-2 rounded-full overflow-hidden shadow-inner relative z-10">
                                    <div class="bg-emerald-500 h-full rounded-full transition-all duration-1000" :style="`width: ${calcPercent(filteredStats.serviceable)}%`" style="width: 100%"></div>
                                </div>
                            </div>

                            {{-- For Repair --}}
                            <div class="bg-white p-6 rounded-[2.5rem] shadow-[0_10px_30px_rgba(0,0,0,0.03)] border-t border-slate-50 group hover:scale-[1.02] hover:shadow-amber-50 transition-all duration-500 cursor-default overflow-hidden relative">
                                <div class="flex justify-between items-start mb-6 relative z-10">
                                    <div class="p-2 bg-amber-50 rounded-2xl group-hover:bg-white transition-all duration-500 shadow-sm">
                                        <img src="{{ asset('images/for_repair.png') }}" alt="For Repair" class="w-12 h-12 object-contain group-hover:scale-110 transition-transform">
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <span class="text-[8px] font-black text-amber-600 uppercase tracking-widest italic bg-amber-50 px-2 py-0.5 rounded">Pending</span>
                                    </div>
                                </div>
                                <div class="relative z-10">
                                    <p class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-1 italic">For Repair</p>
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="text-4xl font-black tracking-tighter text-slate-900 group-hover:text-amber-600 transition-colors" x-text="numberFormat(filteredStats.forRepair)">{{ number_format($forRepairCount) }}</span>
                                        <span class="text-[10px] font-bold text-slate-900 uppercase italic">Units</span>
                                    </div>
                                </div>
                                <div class="mt-6 w-full bg-slate-50 h-2 rounded-full overflow-hidden shadow-inner relative z-10">
                                    <div class="bg-amber-500 h-full rounded-full transition-all duration-1000" :style="`width: ${calcPercent(filteredStats.forRepair)}%`" style="width: 60%"></div>
                                </div>
                            </div>

                            {{-- Unserviceable --}}
                            <div class="bg-white p-6 rounded-[2.5rem] shadow-[0_10px_30px_rgba(0,0,0,0.03)] border-t border-slate-50 group hover:scale-[1.02] hover:shadow-red-50 transition-all duration-500 cursor-default overflow-hidden relative">
                                <div class="flex justify-between items-start mb-6 relative z-10">
                                    <div class="p-2 bg-red-50 rounded-2xl group-hover:bg-white transition-all duration-500 shadow-sm">
                                        <img src="{{ asset('images/unserviceable.png') }}" alt="Unserviceable" class="w-12 h-12 object-contain group-hover:scale-110 transition-transform">
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <span class="text-[8px] font-black text-[#c00000] uppercase tracking-widest italic bg-red-50 px-2 py-0.5 rounded">Critical</span>
                                    </div>
                                </div>
                                <div class="relative z-10">
                                    <p class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-1 italic">Unserviceable</p>
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="text-4xl font-black tracking-tighter text-slate-900 group-hover:text-[#c00000] transition-colors" x-text="numberFormat(filteredStats.unserviceable)">{{ number_format($unserviceableCount) }}</span>
                                        <span class="text-[10px] font-bold text-slate-900 uppercase italic">Units</span>
                                    </div>
                                </div>
                                <div class="mt-6 w-full bg-slate-50 h-2 rounded-full overflow-hidden shadow-inner relative z-10">
                                    <div class="bg-[#c00000] h-full rounded-full transition-all duration-1000" :style="`width: ${calcPercent(filteredStats.unserviceable)}%`" style="width: 30%"></div>
                                </div>
                            </div>
                        </div>

                        {{-- UPDATED: Total Inventory Growth - Gradient Area Chart --}}
                        <div class="bg-white p-8 rounded-[3rem] shadow-[0_10px_30px_rgba(0,0,0,0.03)] border-t border-slate-50 group hover:shadow-xl transition-all duration-500 relative z-0" x-data="growthChartFilter()">
                            <div class="flex items-center justify-between mb-8 relative z-30">
                                <div class="flex items-center gap-3">
                                    <div class="w-1.5 h-4 bg-[#c00000] rounded-full shadow-[0_0_8px_rgba(192,0,0,0.4)]"></div>
                                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-[0.3em]">Total Inventory Growth</h3>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="relative" x-data="{ open: false }">
                                        <button @click="open = !open" class="flex items-center gap-2 px-4 py-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-black uppercase tracking-widest text-[#c00000] hover:bg-[#c00000] hover:text-white transition-all shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                                            Year Filter
                                        </button>
                                        
                                        {{-- Filter Pop-over --}}
                                        <div x-show="open" @click.away="open = false" x-cloak
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                             @click.stop
                                             style="background-color: #0f172a !important;"
                                             class="year-filter-popover absolute right-0 mt-3 w-72 shadow-[0_30px_60px_-15px_rgba(0,0,0,0.5)] rounded-[2rem] border border-slate-800 p-6 z-[100]">
                                            
                                            <div class="space-y-6">
                                                <div>
                                                    <label class="text-[9px] font-black text-slate-100 uppercase tracking-widest mb-3 block italic">Filtering Mode</label>
                                                    <div class="flex p-1 bg-slate-800 rounded-xl border border-slate-700">
                                                        <button @click="mode = 'specific'" :class="mode === 'specific' ? 'bg-slate-700 text-white shadow-sm' : 'text-slate-400 hover:text-white'" class="flex-1 py-2 text-[9px] font-black uppercase tracking-widest rounded-lg transition-all">Specific Year</button>
                                                        <button @click="mode = 'gap'" :class="mode === 'gap' ? 'bg-slate-700 text-white shadow-sm' : 'text-slate-400 hover:text-white'" class="flex-1 py-2 text-[9px] font-black uppercase tracking-widest rounded-lg transition-all">Year Gap</button>
                                                    </div>
                                                </div>

                                                <div x-show="mode === 'specific'" x-transition x-cloak>
                                                    <label class="text-[9px] font-black text-slate-100 uppercase tracking-widest mb-2 block italic">Choose Year</label>
                                                    <select x-model="selectedYear" @click.stop class="w-full bg-slate-800 border-slate-700 text-white rounded-xl text-[11px] font-black uppercase py-2.5 px-4 focus:ring-[#c00000] focus:border-[#c00000]">
                                                        <template x-for="y in availableYears" :key="y">
                                                            <option :value="y" x-text="y"></option>
                                                        </template>
                                                    </select>
                                                </div>

                                                <div x-show="mode === 'gap'" x-transition x-cloak>
                                                    <div class="flex justify-between items-center mb-2">
                                                        <label class="text-[9px] font-black text-slate-200 uppercase tracking-widest block italic">Gap Range (Years)</label>
                                                        <span class="text-[10px] font-black text-white bg-[#c00000] px-2 py-0.5 rounded-md" x-text="selectedGap + ' yrs'"></span>
                                                    </div>
                                                    <input type="range" x-model="selectedGap" min="1" max="10" step="1" class="w-full h-1.5 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-[#c00000]">
                                                    <div class="flex justify-between mt-2 px-1">
                                                        <span class="text-[8px] font-bold text-slate-200 uppercase">1yr</span>
                                                        <span class="text-[8px] font-bold text-slate-200 uppercase">10yrs</span>
                                                    </div>
                                                </div>

                                                <button @click="applyFilter(); open = false" class="w-full py-3.5 bg-slate-900 text-white rounded-[1.25rem] text-[10px] font-black uppercase tracking-widest hover:bg-[#c00000] transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                                    Confirm Changes
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[9px] font-black text-slate-900 uppercase tracking-widest bg-slate-50 px-2 py-1 rounded-lg border border-slate-100">Value Accumulation</span>
                                    </div>
                                </div>
                            </div>
                            <div class="h-[200px] w-full relative z-10" :class="loading ? 'opacity-30' : ''">
                                <canvas id="inventoryGrowthChart"></canvas>
                                <div x-show="loading" class="absolute inset-0 flex items-center justify-center">
                                    <div class="w-6 h-6 border-4 border-red-200 border-t-red-600 rounded-full animate-spin"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Category Distribution - Glassmorphism Circle Graph --}}
                    <div class="bg-white p-8 rounded-[3rem] shadow-[0_20px_50px_rgba(0,0,0,0.05)] border border-slate-50 flex flex-col items-center justify-between group hover:shadow-xl transition-all duration-500 relative overflow-hidden">
                        <div class="w-full relative z-10">
                            <h3 class="text-xs font-black text-slate-900 uppercase tracking-[0.3em] italic text-center mb-1">Portfolio Analysis</h3>
                            <p class="text-[8px] font-bold text-slate-900 uppercase tracking-widest text-center">Category Distribution</p>
                        </div>
                        <div class="h-64 w-full relative z-10 py-4">
                            <canvas id="categoryDistributionChart"></canvas>
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none mt-4">
                                <span class="text-xs font-black text-slate-900 uppercase tracking-tighter">Total Assets</span>
                                <span class="text-xl font-black text-[#c00000] tracking-tighter" x-text="numberFormat(filteredStats.total)">{{ number_format($totalAssets) }}</span>
                            </div>
                        </div>
                        <div class="w-full space-y-2 relative z-10">
                            <div class="grid grid-cols-2 gap-2">
                                <div class="flex items-center justify-between p-2 bg-slate-50 rounded-xl border border-slate-100 hover:bg-white hover:border-[#c00000]/20 transition-all cursor-default">
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-900"></span>
                                        <span class="text-[8px] font-black uppercase text-slate-500">Buildings</span>
                                    </div>
                                    <span class="text-[8px] font-black text-slate-900">{{ $categoryPercents['buildings'] }}%</span>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-slate-50 rounded-xl border border-slate-100 hover:bg-white hover:border-[#c00000]/20 transition-all cursor-default">
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                                        <span class="text-[8px] font-black uppercase text-slate-500">Items</span>
                                    </div>
                                    <span class="text-[8px] font-black text-slate-900">{{ $categoryPercents['items'] }}%</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="flex items-center justify-between p-2 bg-slate-50 rounded-xl border border-slate-100 hover:bg-white hover:border-[#c00000]/20 transition-all cursor-default">
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        <span class="text-[8px] font-black uppercase text-slate-500">PPE</span>
                                    </div>
                                    <span class="text-[8px] font-black text-slate-900">{{ $categoryPercents['ppe'] }}%</span>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-slate-50 rounded-xl border border-slate-100 hover:bg-white hover:border-[#c00000]/20 transition-all cursor-default">
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span class="text-[8px] font-black uppercase text-slate-500">Semi-Exp</span>
                                    </div>
                                    <span class="text-[8px] font-black text-slate-900">{{ $categoryPercents['semi_exp'] }}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="absolute -left-10 -top-10 w-32 h-32 bg-red-50 rounded-full blur-3xl opacity-30 group-hover:scale-150 transition-all duration-700"></div>
                    </div>
                </div>

                {{-- 3. Asset Source Portfolio --}}
                <div class="space-y-6">
                    <div class="flex items-center justify-between px-2 text-slate-900">
                        <div class="flex items-center gap-3">
                            <div class="w-1.5 h-4 bg-slate-400 rounded-full"></div>
                            <h3 class="text-xs font-black uppercase tracking-[0.3em]">Asset Source Portfolio</h3>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 pb-6">
                        @foreach($assetSources as $source)
                        <div class="bg-white p-6 rounded-[2.5rem] shadow-xl border-l-8 border-[#c00000] group hover:scale-[1.01] hover:shadow-2xl transition-all duration-500 ease-out cursor-default relative overflow-hidden">
                            <div class="relative z-10">
                                <div class="flex justify-between items-start mb-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-1.5 h-5 bg-[#c00000] rounded-full group-hover:h-8 transition-all duration-500"></div>
                                        <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-900 group-hover:text-[#c00000] transition-colors leading-tight">
                                            {{ $source['title'] }}
                                        </h4>
                                    </div>
                                    <div class="p-1.5 bg-red-50/50 rounded-xl group-hover:bg-white transition-all duration-300 shadow-sm overflow-hidden">
                                        <img src="{{ asset('images/' . ($source['image'] ?? 'central.png')) }}" alt="{{ $source['title'] }}" class="w-8 h-8 object-contain group-hover:scale-110 transition-transform duration-500">
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-[8px] font-black text-slate-900 uppercase tracking-widest mb-1 italic">Total Value</p>
                                        <p class="text-xl font-black tracking-tighter leading-none text-[#c00000]">
                                            ₱{{ number_format($source['value'], 2) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-[8px] font-black text-slate-900 uppercase tracking-widest mb-1 italic">Quantity</p>
                                        <div class="flex items-baseline gap-2">
                                            <p class="text-2xl font-black text-slate-800 tracking-tighter">{{ number_format($source['qty']) }}</p>
                                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-slate-900">Units</p>
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
                    <div class="flex items-center gap-3 px-2 mb-6 text-slate-900">
                        <div class="w-1.5 h-4 bg-slate-400 rounded-full"></div>
                        <h3 class="text-xs font-black uppercase tracking-[0.3em]">District Distribution</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 pb-10">
                        @php 
                            $quadrants = [
                                1 => ['label' => 'Q 1.1', 'short' => '1.1', 'desc' => 'LD 1 • 3 Districts'],
                                2 => ['label' => 'Q 1.2', 'short' => '1.2', 'desc' => 'LD 1 • 2 Districts'],
                                3 => ['label' => 'Q 2.1', 'short' => '2.1', 'desc' => 'LD 2 • 3 Districts'],
                                4 => ['label' => 'Q 2.2', 'short' => '2.2', 'desc' => 'LD 2 • 4 Districts'],
                            ];
                        @endphp

                        @foreach($quadrants as $id => $q)
                        <div class="bg-white p-7 rounded-[2.5rem] shadow-xl border-l-8 border-[#c00000] flex flex-col justify-between group hover:scale-[1.01] hover:shadow-2xl transition-all duration-500 ease-out cursor-default relative overflow-hidden">
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
                                            <p class="text-[9px] font-bold text-slate-900 uppercase tracking-widest italic mt-1">{{ $q['desc'] }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <p class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-1 italic">Total Amount</p>
                                        <p class="text-2xl font-black tracking-tighter leading-none text-[#c00000]">₱{{ number_format($quadrantStats[$id]['value'] ?? 0, 2) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-1 italic">Quantity</p>
                                        <div class="flex items-baseline gap-2">
                                            <p class="text-3xl font-black text-slate-800 tracking-tighter">{{ number_format($quadrantStats[$id]['qty'] ?? 0) }}</p>
                                            <span class="text-[8px] font-black text-slate-900 italic uppercase">Units</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- 5. Bottom Table Section --}}
                <div class="bg-white rounded-[3rem] p-10 shadow-sm border border-slate-100 overflow-hidden mb-12">
                    <div class="flex justify-between items-center mb-10">
                        <div>
                            <h3 class="text-2xl font-black uppercase italic tracking-tight leading-none text-slate-900">Recent Transaction Logs</h3>
                            <p class="text-[10px] font-bold text-[#c00000] uppercase tracking-widest mt-2">Latest inventory updates across all districts</p>
                        </div>
                        <a href="{{ route('admin.logs') }}" class="text-[11px] font-black text-[#c00000] uppercase tracking-widest hover:bg-[#c00000] hover:text-white transition-all bg-red-50 px-6 py-3 rounded-2xl border border-red-100 italic shadow-sm">View All History</a>
                    </div>
                    <div class="overflow-x-auto custom-scroll">
                        <table class="w-full text-left border-separate border-spacing-0 min-w-[800px]">
                            <thead>
                                <tr class="text-[11px] font-black text-slate-900 uppercase tracking-[0.2em] border-b-2 border-slate-50">
                                    <th class="px-6 py-5 pb-8">Log ID</th>
                                    <th class="px-6 py-5 pb-8">Institutional Name</th>
                                    <th class="px-6 py-5 pb-8">Update Timestamp</th>
                                    <th class="px-6 py-5 pb-8">Quantity</th>
                                    <th class="px-6 py-5 pb-8 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm font-bold text-slate-700 divide-y divide-slate-50">
                                <template x-for="log in filteredLogs" :key="log.id">
                                    <tr class="hover:bg-slate-50 transition-colors duration-200 group cursor-default">
                                        <td class="px-6 py-7 text-slate-900 font-black italic group-hover:text-[#c00000] transition-colors" x-text="'#INV-' + log.id.toString().padStart(5, '0')"></td>
                                        <td class="px-6 py-7 font-black text-slate-900 transition-colors uppercase leading-tight" x-text="log.school"></td>
                                        <td class="px-6 py-7 text-slate-500 uppercase tracking-tighter transition-colors" x-text="log.timestamp"></td>
                                        <td class="px-6 py-7 font-black text-2xl tracking-tighter text-slate-900 transition-colors" x-text="numberFormat(log.qty)"></td>
                                        <td class="px-6 py-7 text-right">
                                            <span class="px-5 py-2.5 bg-emerald-50 text-emerald-600 rounded-xl text-[10px] font-black uppercase italic border border-emerald-100 shadow-sm group-hover:bg-emerald-500 group-hover:text-white transition-all">Verified</span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function dashboardFilter() {
            return {
                availableYears: [2026, 2025, 2024],
                monthNames: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                selectedYears: [],
                selectedMonths: [],
                cardFilter: 'Overall',
                showNotifications: false,
                
                get filterLabel() {
                    if (this.selectedYears.length === 0 && this.selectedMonths.length === 0) {
                        return "Inventory Overview";
                    }
                    return "Filtered Dashboard";
                },

                filterValues: @json($filterValues),
                origStats: {
                    total: {{ $totalAssets ?? 0 }},
                    distributed: {{ $distributedCount ?? 0 }},
                    value: {{ $totalAmount ?? 0 }},
                    serviceable: {{ $serviceableCount ?? 0 }},
                    forRepair: {{ $forRepairCount ?? 0 }},
                    unserviceable: {{ $unserviceableCount ?? 0 }}
                },

                mockLogs: @json($recentLogs),

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
                    if (this.filterValues[this.cardFilter] !== undefined) {
                        stats.value = this.filterValues[this.cardFilter];
                        
                        // If there are date filters, apply same factor to the sub-total
                        if (this.selectedYears.length > 0 || this.selectedMonths.length > 0) {
                            const factor = (this.selectedYears.length + this.selectedMonths.length) / 15;
                            stats.value = stats.value * factor;
                        }
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

        function growthChartFilter() {
            return {
                mode: 'gap',
                selectedYear: {{ date('Y') }},
                selectedGap: 5,
                loading: false,
                availableYears: @json($growthData['availableYears'] ?? []),
                
                async applyFilter() {
                    this.loading = true;
                    try {
                        const val = this.mode === 'specific' ? this.selectedYear : this.selectedGap;
                        const resp = await fetch(`/api/dashboard/growth-data?mode=${this.mode}&value=${val}`);
                        const res = await resp.json();
                        
                        updateGrowthChart(res.labels, res.data);
                    } catch (e) {
                        console.error('Filter failed', e);
                    } finally {
                        this.loading = false;
                    }
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

        // Initialize Category Distribution Chart
        let growthChart = null;

        function updateGrowthChart(labels, data) {
            if (!growthChart) return;
            growthChart.data.labels = labels;
            growthChart.data.datasets[0].data = data.buildings;
            growthChart.data.datasets[1].data = data.ppe;
            growthChart.data.datasets[2].data = data.semi_exp;
            growthChart.update();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('categoryDistributionChart').getContext('2d');
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Buildings', 'Items', 'PPE', 'Semi-Exp'],
                    datasets: [{
                        data: [@json($categoryData['buildings']), @json($categoryData['items']), @json($categoryData['ppe']), @json($categoryData['semi_exp'])],
                        backgroundColor: [
                            '#0f172a', // Buildings (Slate-900)
                            '#dc2626', // Items (Red-600)
                            '#f59e0b', // PPE (Amber-500)
                            '#10b981'  // Semi-Exp (Emerald-500)
                        ],
                        borderWidth: 0,
                        hoverOffset: 20
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#fff',
                            titleColor: '#0f172a',
                            bodyColor: '#64748b',
                            bodyFont: { weight: 'bold', family: 'Plus Jakarta Sans' },
                            padding: 12,
                            borderColor: '#f1f5f9',
                            borderWidth: 1,
                            displayColors: false
                        }
                    }
                }
            });

            // Initialize Inventory Growth Chart (Gradient Area Chart)
            const growthCtx = document.getElementById('inventoryGrowthChart').getContext('2d');
            
            growthChart = new Chart(growthCtx, {
                type: 'line',
                data: {
                    labels: @json($growthData['labels']),
                    datasets: [
                        {
                            label: 'Buildings',
                            data: @json($growthData['data']['buildings']),
                            borderColor: '#0f172a',
                            borderWidth: 2,
                            backgroundColor: 'rgba(15, 23, 42, 0.1)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0,
                            pointHoverRadius: 4
                        },
                        {
                            label: 'PPE (High-Value)',
                            data: @json($growthData['data']['ppe']),
                            borderColor: '#f59e0b',
                            borderWidth: 2,
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0,
                            pointHoverRadius: 4
                        },
                        {
                            label: 'Semi-Expendable',
                            data: @json($growthData['data']['semi_exp']),
                            borderColor: '#c00000',
                            borderWidth: 3,
                            backgroundColor: 'rgba(192, 0, 0, 0.2)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 2,
                            pointHoverRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: (context) => {
                                const popover = document.querySelector('.year-filter-popover');
                                return !popover || popover.style.display === 'none' || popover.classList.contains('hidden');
                            },
                            mode: 'index',
                            intersect: false,
                            backgroundColor: '#fff',
                            titleColor: '#0f172a',
                            bodyColor: '#c00000',
                            bodyFont: { weight: 'bold' },
                            borderColor: '#f1f5f9',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    const val = context.parsed.y;
                                    return context.dataset.label + ': ₱' + new Intl.NumberFormat('en-PH').format(val);
                                },
                                footer: function(items) {
                                    let total = 0;
                                    items.forEach(i => total += i.parsed.y);
                                    return 'Total Accumulation: ₱' + new Intl.NumberFormat('en-PH').format(total);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { weight: 'bold', family: 'Plus Jakarta Sans' }, color: '#94a3b8' }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false },
                            ticks: { 
                                font: { weight: 'bold', family: 'Plus Jakarta Sans' }, 
                                color: '#94a3b8',
                                callback: function(value) {
                                    if (value >= 1000000000) return '₱' + (value / 1000000000).toFixed(1) + 'B';
                                    if (value >= 1000000) return '₱' + (value / 1000000).toFixed(0) + 'M';
                                    if (value >= 1000) return '₱' + (value / 1000).toFixed(0) + 'K';
                                    return '₱' + value;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>