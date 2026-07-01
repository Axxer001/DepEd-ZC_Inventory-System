<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DepEd ZC IMS | Inventory Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        /* Bell pulse glow */
        @keyframes bellGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(192,0,0,0.5), 0 0 0 0 rgba(192,0,0,0.3); }
            50%       { box-shadow: 0 0 0 6px rgba(192,0,0,0.15), 0 0 0 12px rgba(192,0,0,0.05); }
        }
        .bell-has-unread { animation: bellGlow 1.8s ease-in-out infinite; }
        @keyframes badgePulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50%       { transform: scale(1.4); opacity: 0.7; }
        }
        .badge-pulse { animation: badgePulse 1.2s ease-in-out infinite; }
        /* Notification card states */
        .notif-unread { background: #fff7f7; border-color: #fca5a5; }
        .notif-read   { background: #f8fafc; border-color: #e2e8f0; opacity: 0.75; }
        /* Alert Edit Modal */
        .alert-modal-overlay { position:fixed;inset:0;background:rgba(15,23,42,0.55);backdrop-filter:blur(6px);z-index:200;display:flex;align-items:center;justify-content:center;padding:1.5rem; }
        .alert-modal { background:#fff;border-radius:2rem;padding:2rem;width:100%;max-width:420px;box-shadow:0 30px 80px rgba(0,0,0,0.18);border:1.5px solid #f1f5f9; }
        .alert-field { width:100%;border:1.5px solid #e2e8f0;border-radius:0.75rem;padding:10px 14px;font-size:11px;font-weight:700;color:#0f172a;outline:none;font-family:inherit;transition:border-color .15s; }
        .alert-field:focus { border-color:#c00000; }
        textarea.alert-field { resize:vertical;min-height:80px; }
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
            <div class="p-6 flex items-center justify-between border-b border-slate-50">
                <div>
                    <h3 class="text-2xl font-black tracking-tight italic uppercase leading-none text-slate-900">Notifications</h3>
                    <p class="text-[10px] font-bold text-[#c00000] uppercase tracking-widest mt-1.5">System Alerts &amp; Notices</p>
                </div>
                <div class="flex items-center gap-2">
                    <span x-show="hasUnread" x-cloak class="px-2 py-0.5 bg-red-100 text-red-600 text-[9px] font-black uppercase tracking-widest rounded-full" x-text="unreadCount + ' new'"></span>
                    <button @click="showNotifications = false" class="p-2.5 bg-slate-50 text-slate-900 rounded-xl hover:text-red-600 hover:bg-red-50 border border-slate-100 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            <div class="flex-grow overflow-y-auto custom-scroll p-6 space-y-3">
                <template x-if="notifications.length === 0 && !notifLoading">
                    <div class="flex flex-col items-center justify-center h-48 text-center text-slate-400">
                        <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                        <p class="text-xs font-bold uppercase tracking-widest">No notifications yet</p>
                    </div>
                </template>

                <template x-if="notifLoading">
                    <div class="flex items-center justify-center h-24 text-slate-400">
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                    </div>
                </template>

                <template x-for="notification in notifications" :key="notification.id">
                    <div :class="notification.read_at ? 'notif-read' : 'notif-unread'" class="p-4 rounded-2xl border group relative transition-all">
                        {{-- Unread dot --}}
                        <div x-show="!notification.read_at" class="absolute top-4 left-4 w-2 h-2 bg-red-500 rounded-full badge-pulse"></div>
                        <div class="flex items-start gap-4" :class="!notification.read_at ? 'pl-4' : ''">
                            <div class="flex-1 min-w-0 pr-6">
                                <p class="text-[11px] font-black uppercase italic" :class="notification.read_at ? 'text-slate-500' : 'text-slate-800'" x-text="notification.data.title"></p>
                                <p class="text-[10px] font-bold text-slate-500 mt-1 leading-relaxed truncate" x-text="notification.data.message"></p>
                                <div class="flex items-center justify-between mt-2">
                                    <button type="button" @click="showNotificationDetails(notification.data)" class="text-[9px] font-black text-[#c00000] uppercase tracking-widest hover:underline cursor-pointer">View Details</button>
                                    <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest" x-text="formatNotifDate(notification.created_at)"></span>
                                </div>
                            </div>
                        </div>
                        <template x-if="!notification.read_at">
                            <button @click="markAsRead(notification.id)" class="absolute top-3 right-3 p-1.5 text-slate-300 hover:text-emerald-500 hover:bg-emerald-50 rounded-lg transition-colors" title="Mark as read">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                            </button>
                        </template>
                    </div>
                </template>

                {{-- Pagination --}}
                <div x-show="notifPagination.last_page > 1" class="flex items-center justify-between pt-2">
                    <button @click="loadNotificationsFromServer(notifPage - 1)" :disabled="notifPage <= 1" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition-all">← Prev</button>
                    <span class="text-[10px] font-bold text-slate-400" x-text="'Page ' + notifPage + ' of ' + notifPagination.last_page"></span>
                    <button @click="loadNotificationsFromServer(notifPage + 1)" :disabled="notifPage >= notifPagination.last_page" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition-all">Next →</button>
                </div>
            </div>

            <div class="p-5 border-t border-slate-50 space-y-2">
                @if(auth()->user()->role === 'super_admin')
                <button @click="createCustomNotification()" class="w-full py-3.5 bg-[#c00000] text-white rounded-[1.5rem] font-black uppercase tracking-widest text-[11px] hover:bg-[#a00000] transition-all shadow-lg shadow-red-200">Create Notification</button>
                @endif
                <button @click="markAlertRead()" x-show="hasUnread" x-cloak class="w-full py-3.5 bg-slate-900 text-white rounded-[1.5rem] font-black uppercase tracking-widest text-[11px] hover:bg-slate-700 transition-all shadow-lg shadow-slate-200">Mark All as Read</button>
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

                    <button @click="openNotifications()" :class="hasUnread ? 'bell-has-unread border-red-200' : 'border-slate-200'" class="relative p-3 bg-white border text-slate-900 rounded-2xl hover:text-[#c00000] hover:border-[#c00000]/30 hover:shadow-lg hover:shadow-red-50 transition-all shadow-sm group active:scale-90">
                        <svg :class="hasUnread ? 'text-[#c00000]' : ''" class="w-5 h-5 group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                        <span x-show="hasUnread" x-cloak class="absolute -top-1 -right-1 min-w-[18px] h-[18px] bg-red-600 border-2 border-white rounded-full badge-pulse flex items-center justify-center text-white text-[8px] font-black" x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                    </button>
                </div>
            </header>

            <div class="flex-grow p-6 lg:p-10 space-y-12">
                
                {{-- 1. Top Stat Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-10">
                    {{-- Total Inventory --}}
                    <div onclick="window.location.href='{{ url('/view-assets?tab=source') }}'" class="p-8 rounded-[2.5rem] bg-white border-l-[12px] border-[#c00000] shadow-2xl flex flex-col justify-between h-56 group hover:-translate-y-2 hover:shadow-[0_30px_60px_-15px_rgba(0,0,0,0.1)] transition-all duration-500 ease-out cursor-pointer relative overflow-hidden border-r border-y border-slate-50">
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
                    <div onclick="window.location.href='{{ url('/view-assets?tab=source&condition=not_distributed') }}'" class="p-8 rounded-[2.5rem] bg-white border-l-[12px] border-[#c00000] shadow-2xl flex flex-col justify-between h-56 group hover:-translate-y-2 hover:shadow-[0_30px_60px_-15px_rgba(0,0,0,0.1)] transition-all duration-500 ease-out cursor-pointer relative overflow-hidden border-r border-y border-slate-50">
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
                {{-- Row 1: Condition Summary & Global Notice Board --}}
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-stretch">
                    {{-- Condition Summary - Enhanced Visuals --}}
                    <div class="lg:col-span-3 space-y-6 flex flex-col justify-between">
                        <div class="flex items-center gap-3 px-4">
                            <div class="w-1.5 h-4 bg-[#c00000] rounded-full animate-pulse shadow-[0_0_10px_rgba(192,0,0,0.5)]"></div>
                            <h3 class="text-xs font-black text-slate-900 uppercase tracking-[0.3em]">Asset Condition Summary</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 flex-1">
                            {{-- Serviceable --}}
                            <div onclick="window.location.href='{{ url('/view-assets?tab=source&condition=serviceable') }}'" class="bg-white p-6 rounded-[2.5rem] shadow-[0_10px_30px_rgba(0,0,0,0.03)] border-t border-slate-50 group hover:scale-[1.02] hover:shadow-emerald-50 transition-all duration-500 cursor-pointer overflow-hidden relative flex flex-col justify-between h-full">
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
                            <div onclick="window.location.href='{{ url('/view-assets?tab=source&condition=to_repair') }}'" class="bg-white p-6 rounded-[2.5rem] shadow-[0_10px_30px_rgba(0,0,0,0.03)] border-t border-slate-50 group hover:scale-[1.02] hover:shadow-amber-50 transition-all duration-500 cursor-pointer overflow-hidden relative flex flex-col justify-between h-full">
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
                            <div onclick="window.location.href='{{ url('/view-assets?tab=source&condition=unserviceable') }}'" class="bg-white p-6 rounded-[2.5rem] shadow-[0_10px_30px_rgba(0,0,0,0.03)] border-t border-slate-50 group hover:scale-[1.02] hover:shadow-red-50 transition-all duration-500 cursor-pointer overflow-hidden relative flex flex-col justify-between h-full">
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
                    </div>

                    {{-- Global Notice Board --}}
                    <div class="lg:col-span-1">
                        <div class="bg-white p-8 rounded-[2.5rem] shadow-[0_10px_30px_rgba(0,0,0,0.03)] border border-slate-100 group hover:shadow-xl hover:scale-[1.01] transition-all duration-500 h-full flex flex-col justify-between relative overflow-hidden">
                            <div class="absolute -right-16 -top-16 w-48 h-48 bg-[#c00000]/5 rounded-full blur-3xl opacity-60 group-hover:scale-150 transition-all duration-700"></div>
                            <div class="absolute -left-16 -bottom-16 w-48 h-48 bg-slate-50/50 rounded-full blur-3xl opacity-40"></div>
                            
                            <div class="relative z-10 flex flex-col justify-between h-full space-y-4 flex-1">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="px-3 py-1 bg-[#c00000]/10 text-[#c00000] border border-[#c00000]/20 rounded-full text-[8px] font-black uppercase tracking-widest italic animate-pulse">📢 Division Notice</span>
                                    @if(auth()->user()->role === 'super_admin')
                                    <button onclick="editGlobalNotice()" class="text-[8px] font-black uppercase tracking-widest text-slate-500 hover:text-[#c00000] hover:bg-slate-50 bg-slate-50 px-2.5 py-1 rounded-xl border border-slate-100/50 transition-all flex items-center gap-1">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" /></svg>
                                        Manage
                                    </button>
                                    @endif
                                </div>
                                <div class="flex-1 py-2 text-left overflow-y-auto">
                                    @if($globalNotice && !empty($globalNotice->content))
                                        <p class="text-slate-900 text-xs font-black tracking-tight leading-relaxed uppercase line-clamp-4">{{ $globalNotice->content }}</p>
                                    @else
                                        <p class="text-slate-400 text-[10px] font-bold leading-relaxed italic">No active announcements at the moment.</p>
                                    @endif
                                </div>
                                @if($globalNotice && !empty($globalNotice->content) && $globalNotice->link)
                                    <div>
                                        <a href="{{ $globalNotice->link }}" target="_blank" class="inline-flex items-center gap-1.5 text-[9px] font-black uppercase tracking-widest text-white bg-[#c00000] hover:bg-red-800 px-3.5 py-2 rounded-xl transition-all shadow-md active:scale-95 w-full justify-center">
                                            {{ $globalNotice->link_label ?: 'View Notice Details' }}
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row 2: Inventory Growth & Portfolio Analysis --}}
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 mt-8 items-stretch">
                    {{-- Total Inventory Growth - Gradient Area Chart --}}
                    <div class="lg:col-span-3">
                        <div class="bg-white p-8 rounded-[3rem] shadow-[0_10px_30px_rgba(0,0,0,0.03)] border-t border-slate-50 group hover:shadow-xl transition-all duration-500 relative z-0 h-full flex flex-col justify-between" x-data="growthChartFilter()">
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
                    <div class="lg:col-span-1">
                        <div class="bg-white p-8 rounded-[3rem] shadow-[0_20px_50px_rgba(0,0,0,0.05)] border border-slate-50 flex flex-col items-center justify-between group hover:shadow-xl transition-all duration-500 relative overflow-hidden h-full">
                            <div class="w-full relative z-10">
                                <h3 class="text-xs font-black text-slate-900 uppercase tracking-[0.3em] italic text-center mb-1">Portfolio Analysis</h3>
                                <p class="text-[8px] font-bold text-slate-900 uppercase tracking-widest text-center">Category Distribution</p>
                            </div>
                            <div class="flex-1 w-full relative z-10 py-4 flex items-center justify-center min-h-[160px]">
                                <canvas id="categoryDistributionChart"></canvas>
                                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none mt-4">
                                    <span class="text-[8px] font-black text-slate-500 uppercase tracking-tighter">Total Assets</span>
                                    <span class="text-sm font-black text-[#c00000] tracking-tighter" x-text="numberFormat(filteredStats.total)">{{ number_format($totalAssets) }}</span>
                                </div>
                            </div>
                            <div class="w-full space-y-2 relative z-10 mt-2">
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
        const ALERT_PW = 'deped@ims';
        const ALERT_DEFAULTS = {
            title: 'Quarterly Inventory Audit Coming Up',
            body:  'All institution heads are required to verify their current asset counts by the end of the month.',
            priority: 'High',
        };

        function dashboardFilter() {
            return {
                availableYears: [2026, 2025, 2024],
                monthNames: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                selectedYears: [],
                selectedMonths: [],
                cardFilter: 'Overall',
                showNotifications: false,
                hasUnread: false,
                unreadCount: 0,
                notifications: [],
                notifPage: 1,
                notifPagination: { current_page: 1, last_page: 1, total: 0 },
                notifLoading: false,

                init() {
                    this.loadNotificationsFromServer(1);
                },

                async loadNotificationsFromServer(page = 1) {
                    this.notifLoading = true;
                    this.notifPage = page;
                    try {
                        const res = await fetch(`/api/notifications?page=${page}`, { headers: { 'Accept': 'application/json' } });
                        if (!res.ok) return;
                        const data = await res.json();
                        this.notifications = data.notifications || [];
                        this.unreadCount = data.unreadCount || 0;
                        this.hasUnread = this.unreadCount > 0;
                        this.notifPagination = data.pagination || { current_page: page, last_page: 1, total: 0 };
                    } catch(e) { console.error('Failed to load notifications:', e); }
                    finally { this.notifLoading = false; }
                },

                async openNotifications() {
                    this.showNotifications = true;
                    // Refresh the current page when opening
                    await this.loadNotificationsFromServer(this.notifPage);
                },

                formatNotifDate(dateStr) {
                    if (!dateStr) return '';
                    const d = new Date(dateStr);
                    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    const h = d.getHours(), m = d.getMinutes();
                    const ampm = h >= 12 ? 'PM' : 'AM';
                    return `${months[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()} ${(h%12||12)}:${String(m).padStart(2,'0')} ${ampm}`;
                },

                showNotificationDetails(data) {
                    const details = data.detailed_message || data.message;
                    Swal.fire({
                        title: data.title,
                        html: `<p class="text-xs font-bold text-slate-600 mt-2 text-left">${details}</p>`,
                        icon: 'info',
                        customClass: {
                            popup: 'rounded-3xl p-6',
                            title: 'text-lg font-black text-slate-800 uppercase italic',
                            confirmButton: 'bg-[#c00000] text-white font-black uppercase tracking-widest text-[10px] px-8 py-3 rounded-xl hover:bg-red-800 transition-colors shadow-lg outline-none border-0'
                        },
                        buttonsStyling: false,
                        confirmButtonText: 'CLOSE'
                    });
                },

                async createCustomNotification() {
                    const { value: text } = await Swal.fire({
                        title: 'Create Announcement',
                        input: 'textarea',
                        inputPlaceholder: 'Type your message here...',
                        inputAttributes: { 'aria-label': 'Type your message here' },
                        showCancelButton: true,
                        confirmButtonText: 'SEND NOTIFICATION',
                        cancelButtonText: 'CANCEL',
                        customClass: {
                            popup: 'rounded-3xl p-6',
                            title: 'text-lg font-black text-slate-800 uppercase italic',
                            input: 'text-sm font-medium text-slate-700 bg-slate-50 border border-slate-200 rounded-xl focus:ring-[#c00000] focus:border-[#c00000] mt-4 p-4',
                            confirmButton: 'bg-[#c00000] text-white font-black uppercase tracking-widest text-[10px] px-6 py-3 rounded-xl hover:bg-red-800 transition-colors shadow-lg outline-none border-0 mt-2',
                            cancelButton: 'bg-slate-200 text-slate-700 font-black uppercase tracking-widest text-[10px] px-6 py-3 rounded-xl hover:bg-slate-300 transition-colors shadow-sm outline-none border-0 mt-2 ml-2'
                        },
                        buttonsStyling: false
                    });

                    if (text) {
                        try {
                            const res = await fetch('/api/notifications/custom', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({ message: text })
                            });
                            if (res.ok) {
                                await this.loadNotificationsFromServer(1);
                                Swal.fire({
                                    icon: 'success', title: 'Sent!', text: 'Announcement has been sent to all users.',
                                    customClass: { popup: 'rounded-3xl p-6', title: 'text-lg font-black text-slate-800 uppercase italic', confirmButton: 'bg-emerald-600 text-white font-black uppercase tracking-widest text-[10px] px-8 py-3 rounded-xl hover:bg-emerald-700 transition-colors shadow-lg outline-none border-0' },
                                    buttonsStyling: false
                                });
                            }
                        } catch (e) { console.error('Failed to send notification:', e); }
                    }
                },

                async markAlertRead() {
                    if (!this.hasUnread) return;
                    try {
                        const res = await fetch('/api/notifications/read-all', {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                        });
                        if (res.ok) {
                            // Mark all in-memory items as read (preserve history)
                            this.notifications = this.notifications.map(n => ({ ...n, read_at: n.read_at || new Date().toISOString() }));
                            this.hasUnread = false;
                            this.unreadCount = 0;
                        }
                    } catch(e) { console.error('Failed to mark all as read:', e); }
                },

                async markAsRead(id) {
                    try {
                        const res = await fetch(`/api/notifications/${id}/read`, {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                        });
                        if (res.ok) {
                            // Update in-place, keep in list
                            this.notifications = this.notifications.map(n =>
                                n.id === id ? { ...n, read_at: new Date().toISOString() } : n
                            );
                            this.unreadCount = Math.max(0, this.unreadCount - 1);
                            this.hasUnread = this.unreadCount > 0;
                        }
                    } catch(e) { console.error('Failed to mark read:', e); }
                },
                
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

        function editGlobalNotice() {
            Swal.fire({
                title: 'Manage Global Notice Board',
                html: `
                    <div class="space-y-4 text-left">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Notice Announcement Text</label>
                            <textarea id="swal-notice-content" class="w-full bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold p-3 text-slate-800 focus:ring-[#c00000] focus:border-[#c00000] min-h-[80px]" placeholder="Type division-wide announcement here...">{{ $globalNotice->content ?? '' }}</textarea>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">External Link / URL (Optional)</label>
                            <input id="swal-notice-link" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold px-3 py-2 text-slate-800 focus:ring-[#c00000] focus:border-[#c00000]" placeholder="https://example.com/pif-instructions" value="{{ $globalNotice->link ?? '' }}">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Link Label (Optional)</label>
                            <input id="swal-notice-label" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold px-3 py-2 text-slate-800 focus:ring-[#c00000] focus:border-[#c00000]" placeholder="VIEW INSTRUCTIONS" value="{{ $globalNotice->link_label ?? '' }}">
                        </div>
                        <p class="text-[9px] font-bold text-slate-400 italic">Leaving the Content field blank will clear the notice board from the dashboard.</p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'PUBLISH NOTICE',
                cancelButtonText: 'CANCEL',
                customClass: {
                    popup: 'rounded-3xl p-6 max-w-md w-full',
                    title: 'text-lg font-black text-slate-800 uppercase italic',
                    confirmButton: 'bg-[#c00000] text-white font-black uppercase tracking-widest text-[10px] px-6 py-3 rounded-xl hover:bg-red-800 transition-colors shadow-lg outline-none border-0 mt-2',
                    cancelButton: 'bg-slate-200 text-slate-700 font-black uppercase tracking-widest text-[10px] px-6 py-3 rounded-xl hover:bg-slate-300 transition-colors shadow-sm outline-none border-0 mt-2 ml-2'
                },
                buttonsStyling: false,
                preConfirm: () => {
                    return {
                        content: document.getElementById('swal-notice-content').value,
                        link: document.getElementById('swal-notice-link').value,
                        link_label: document.getElementById('swal-notice-label').value
                    }
                }
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const data = result.value;
                    try {
                        const res = await fetch('/api/global-notice', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(data)
                        });
                        
                        if (res.ok) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Notice Updated',
                                text: 'The global notice board has been updated successfully.',
                                customClass: {
                                    popup: 'rounded-3xl p-6',
                                    title: 'text-lg font-black text-slate-800 uppercase italic',
                                    confirmButton: 'bg-emerald-600 text-white font-black uppercase tracking-widest text-[10px] px-8 py-3 rounded-xl hover:bg-emerald-700 transition-colors shadow-lg outline-none border-0'
                                },
                                buttonsStyling: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error('Failed to save notice');
                        }
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Something went wrong while publishing the notice.',
                            customClass: {
                                popup: 'rounded-3xl p-6',
                                title: 'text-lg font-black text-slate-800 uppercase italic',
                                confirmButton: 'bg-red-600 text-white font-black uppercase tracking-widest text-[10px] px-8 py-3 rounded-xl hover:bg-red-700 transition-colors shadow-lg outline-none border-0'
                            },
                            buttonsStyling: false
                        });
                    }
                }
            });
        }

        // Initialize Category Distribution Chart
        let growthChart = null;

        function updateGrowthChart(labels, data) {
            if (!growthChart) return;
            growthChart.data.labels = labels;
            growthChart.data.datasets[0].data = data.ppe;
            growthChart.data.datasets[1].data = data.semi_exp;
            growthChart.update();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('categoryDistributionChart').getContext('2d');
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['PPE', 'Semi-Exp'],
                    datasets: [{
                        data: [@json($categoryData['ppe']), @json($categoryData['semi_exp'])],
                        backgroundColor: [
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