<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Reports | DepEd Zamboanga City</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; scroll-behavior: smooth; }
        .custom-scroll::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .text-deped { color: #c00000; }
        .bg-deped { background-color: #c00000; }
        [x-cloak] { display: none !important; }
        
        .fade-enter { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>

<body class="bg-[#fcfcfd] min-h-screen flex text-slate-800 overflow-x-hidden" x-data="reportManager()">

@include('partials.sidebar')

<div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll">

    {{-- Mobile Header --}}
    <header class="lg:hidden bg-white border-b p-4 flex items-center justify-between sticky top-0 z-30">
        <div class="flex items-center gap-3">
            <button onclick="toggleSidebar()" class="p-2 rounded-xl border bg-slate-50">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-slate-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/deped_logo.png') }}" class="h-6">
                <span class="font-black italic text-sm tracking-tight uppercase">DepEd ZC</span>
            </div>
        </div>
        <div class="w-8 h-8 bg-deped rounded-lg flex items-center justify-center text-white font-bold text-xs shadow-lg shadow-red-100 italic">A</div>
    </header>

    <main class="p-6 lg:p-8 max-w-7xl mx-auto w-full">

        {{-- STEP 1: REPORT SELECTION --}}
        <div x-show="step === 1" x-transition class="fade-enter">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-12 gap-6">
                <div>
                    <h1 class="text-3xl lg:text-5xl font-black text-slate-900 tracking-tighter italic uppercase leading-none text-red-600">Report <span class="text-slate-900">Vault</span></h1>
                    <div class="flex items-center gap-3 mt-3">
                        <div class="w-8 h-1 bg-deped rounded-full"></div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.4em]">Select Classification to Download Report</p>
                    </div>
                </div>

                <button onclick="window.location.href='/dashboard'"
                    class="group px-6 py-3 bg-white border border-slate-200 rounded-2xl text-[9px] font-black uppercase tracking-widest text-slate-500 flex items-center gap-3 shadow-sm hover:border-deped hover:text-deped transition-all active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4 transition-transform group-hover:-translate-x-1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Back to System
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 px-2">
                {{-- RPCPPE Card --}}
                <div @click="selectReport('RPCPPE')" class="group bg-white rounded-[3rem] border border-slate-100 shadow-xl p-10 hover:shadow-red-50 hover:border-red-100 transition-all duration-500 relative overflow-hidden flex flex-col justify-between cursor-pointer min-h-[380px]">
                    <div class="absolute -right-10 -top-10 w-48 h-48 bg-red-50 rounded-full opacity-50 blur-3xl group-hover:bg-red-100 transition-colors"></div>
                    <div class="relative z-10">
                        <div class="w-16 h-16 bg-red-50 text-deped rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 group-hover:rotate-6 transition-all border border-red-100 shadow-inner">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <h2 class="text-4xl font-black text-slate-900 uppercase tracking-tighter italic mb-2">RPCPPE</h2>
                        <span class="px-4 py-1.5 bg-red-50 text-deped text-[9px] font-black uppercase tracking-[0.3em] rounded-full border border-red-100 italic mb-6 inline-block shadow-sm">High-Value Assets</span>
                        <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] leading-relaxed mt-4 max-w-[280px]">Items valued at ₱50,000.00 and above. Official Physical Count Reporting.</p>
                    </div>
                    <div class="mt-auto pt-8 flex items-center gap-3 text-deped font-black uppercase italic tracking-[0.3em] text-[10px] group-hover:translate-x-2 transition-all">
                        Configure Report Options
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </div>
                </div>

                {{-- RPCSP Card --}}
                <div @click="selectReport('RPCSP')" class="group bg-white rounded-[3rem] border border-slate-100 shadow-xl p-10 hover:shadow-emerald-50 hover:border-emerald-100 transition-all duration-500 relative overflow-hidden flex flex-col justify-between cursor-pointer min-h-[380px]">
                    <div class="absolute -right-10 -top-10 w-48 h-48 bg-emerald-50 rounded-full opacity-50 blur-3xl group-hover:bg-emerald-100 transition-colors"></div>
                    <div class="relative z-10">
                        <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 group-hover:-rotate-6 transition-all border border-emerald-100 shadow-inner">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <h2 class="text-4xl font-black text-slate-900 uppercase tracking-tighter italic mb-2">RPCSP</h2>
                        <span class="px-4 py-1.5 bg-emerald-50 text-emerald-600 text-[9px] font-black uppercase tracking-[0.3em] rounded-full border border-emerald-100 italic mb-6 inline-block shadow-sm group-hover:text-emerald-700 group-hover:bg-emerald-100 group-hover:border-emerald-200 transition-all">Semi-Expendable Assets</span>
                        <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] leading-relaxed mt-4 max-w-[280px]">Items valued below ₱50,000.00. Consumable Inventory Reporting.</p>
                    </div>
                    <div class="mt-auto pt-8 flex items-center gap-3 text-emerald-600 font-black uppercase italic tracking-[0.3em] text-[10px] group-hover:translate-x-2 transition-all">
                        Configure Report Options
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </div>
                </div>

                {{-- PIF Card --}}
                <div @click="selectReport('PIF')" class="group bg-white rounded-[3rem] border border-slate-100 shadow-xl p-10 hover:shadow-blue-50 hover:border-blue-100 transition-all duration-500 relative overflow-hidden flex flex-col justify-between cursor-pointer min-h-[380px]">
                    <div class="absolute -right-10 -top-10 w-48 h-48 bg-blue-50 rounded-full opacity-50 blur-3xl group-hover:bg-blue-100 transition-colors"></div>
                    <div class="relative z-10">
                        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 group-hover:rotate-6 transition-all border border-blue-100 shadow-inner">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <h2 class="text-4xl font-black text-slate-900 uppercase tracking-tighter italic mb-2">PIF</h2>
                        <span class="px-4 py-1.5 bg-blue-50 text-blue-600 text-[9px] font-black uppercase tracking-[0.3em] rounded-full border border-blue-100 italic mb-6 inline-block shadow-sm group-hover:text-blue-700 group-hover:bg-blue-100 group-hover:border-blue-200 transition-all">Full Asset Inventory</span>
                        <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] leading-relaxed mt-4 max-w-[280px]">Property Inventory Form. Combined reporting of all assets regardless of valuation (High-Value & Semi-Expendable).</p>
                    </div>
                    <div class="mt-auto pt-8 flex items-center gap-3 text-blue-600 font-black uppercase italic tracking-[0.3em] text-[10px] group-hover:translate-x-2 transition-all">
                        Configure Report Options
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </div>
                </div>
            </div>
            
            <div></div>
        </div>

        {{-- STEP 2: CONFIGURE & DOWNLOAD --}}
        <div x-show="step === 2" x-transition x-cloak class="fade-enter">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-12 gap-6">
                <div class="flex items-center gap-5">
                    <button @click="step = 1" class="w-12 h-12 bg-white border border-slate-200 rounded-2xl flex items-center justify-center text-slate-400 hover:text-deped hover:border-deped shadow-lg shadow-slate-100 hover:scale-105 transition-all active:scale-90 group">
                        <svg class="w-6 h-6 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <div>
                        <h1 class="text-2xl lg:text-4xl font-black text-slate-900 tracking-tighter italic uppercase leading-none" x-text="selectedReport"></h1>
                        <div class="flex items-center gap-3 mt-2">
                            <span class="px-2.5 py-0.5 bg-red-50 text-deped text-[8px] font-black uppercase rounded border border-red-100 italic" x-text="reportSubtext"></span>
                            <div class="w-1 h-1 bg-slate-200 rounded-full"></div>
                            <span class="text-[8px] font-black text-slate-300 uppercase tracking-widest italic">Live Configuration Mode</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button @click="showFilters = !showFilters" 
                        class="px-6 py-3 bg-white border border-slate-200 rounded-2xl text-[9px] font-black uppercase tracking-widest text-slate-500 flex items-center gap-3 shadow-sm hover:border-deped hover:text-deped transition-all active:scale-95 italic">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                        </svg>
                        <span x-text="showFilters ? 'Hide Filters' : 'Show Filters'"></span>
                    </button>

                    <button @click="download()" class="px-6 py-3 bg-deped text-white rounded-2xl text-[9px] font-black uppercase tracking-widest flex items-center gap-3 shadow-lg shadow-red-100 hover:bg-red-700 transition-all active:scale-95 italic">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Download Report
                    </button>
                </div>
            </div>

            <div x-show="showFilters" x-collapse class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 p-8 lg:p-12 overflow-hidden relative mb-12">
                <div class="absolute -left-10 -top-10 w-32 h-32 bg-red-50/50 rounded-full blur-3xl"></div>
                
                <div class="relative z-10">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        {{-- Classification --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Classification</label>
                            <select x-model="filters.classification" @change="applyFilters()" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500 cursor-pointer">
                                <option value="">All Classifications</option>
                                <template x-for="c in filterOptions.classifications" :key="c">
                                    <option :value="c" x-text="c"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Category --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Category</label>
                            <select x-model="filters.category" @change="applyFilters()" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500 cursor-pointer">
                                <option value="">All Categories</option>
                                <template x-for="cat in filterOptions.categories" :key="cat">
                                    <option :value="cat" x-text="cat"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Item --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Item</label>
                            <select x-model="filters.article" @change="applyFilters()" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500 cursor-pointer">
                                <option value="">All Items</option>
                                <template x-for="item in filterOptions.items" :key="item">
                                    <option :value="item" x-text="item"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Cost Sorting --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Cost Sorting</label>
                            <select x-model="filters.sortCost" @change="applyFilters()" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500 cursor-pointer">
                                <option value="">Default (ID)</option>
                                <option value="low_to_high">Low to High</option>
                                <option value="high_to_low">High to Low</option>
                            </select>
                        </div>

                        {{-- School Name --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">School Name</label>
                            <select x-model="filters.schoolName" @change="applyFilters()" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500 cursor-pointer">
                                <option value="">All Schools</option>
                                <template x-for="school in filterOptions.schools" :key="school">
                                    <option :value="school" x-text="school"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Office Name --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Office Name</label>
                            <select x-model="filters.officeName" @change="applyFilters()" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500 cursor-pointer">
                                <option value="">All Offices</option>
                                <template x-for="office in filterOptions.offices" :key="office">
                                    <option :value="office" x-text="office"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Source of Acquisition --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Source of Acquisition</label>
                            <select x-model="filters.source" @change="applyFilters()" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500 cursor-pointer">
                                <option value="">All Sources</option>
                                <template x-for="s in filterOptions.sources" :key="s">
                                    <option :value="s" x-text="s"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Mode of Acquisition --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Mode of Acquisition</label>
                            <select x-model="filters.mode" @change="applyFilters()" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500 cursor-pointer">
                                <option value="">All Modes</option>
                                <template x-for="m in filterOptions.modes" :key="m">
                                    <option :value="m" x-text="m"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Date Acquired --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Date Acquired (Acceptance)</label>
                            <input type="date" x-model="filters.dateAcquired" @change="applyFilters()" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500">
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-50 flex justify-end gap-3">
                        <button @click="clearFilters()" class="px-8 py-3 bg-slate-100 text-slate-500 rounded-2xl text-[9px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all active:scale-95 italic">Clear All Filters</button>
                        <button @click="applyFilters()" class="px-8 py-3 bg-slate-900 text-white rounded-2xl text-[9px] font-black uppercase tracking-widest hover:bg-deped transition-all active:scale-95 italic">Apply Configuration</button>
                    </div>
                </div>
            </div>

            {{-- LIVE PREVIEW TABLE --}}
            <div class="mb-12 bg-white rounded-[2rem] border border-slate-100 shadow-xl overflow-hidden relative fade-enter">
                <div class="p-6 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest italic" x-text="selectedReport + ' Preview'"></h3>
                        <p class="text-[10px] text-slate-400 font-bold tracking-wider mt-1 uppercase" x-text="'Showing ' + previewRows.length + ' exact matched assets'"></p>
                    </div>
                    <div x-show="loading" class="w-5 h-5 border-2 border-deped border-t-transparent rounded-full animate-spin"></div>
                </div>
                
                <div class="overflow-x-auto custom-scroll transition-all duration-300" :class="showFilters ? 'max-h-[400px]' : 'max-h-[750px]'">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead class="sticky top-0 bg-slate-50 z-10 shadow-sm">
                            <tr class="text-[9px] font-black text-slate-400 uppercase tracking-widest border-b-2 border-slate-100">
                                <th class="px-4 py-4">#</th>
                                <th class="px-4 py-4">Region</th>
                                <th class="px-4 py-4">Division</th>
                                <th class="px-4 py-4">Office/School Type</th>
                                <th class="px-4 py-4">School ID</th>
                                <th class="px-4 py-4">School Name</th>
                                <th class="px-4 py-4">Classification</th>
                                <th class="px-4 py-4">Category</th>
                                <th class="px-4 py-4 text-slate-900">Item</th>
                                <th class="px-4 py-4">Description</th>
                                <th class="px-4 py-4">Unit of Measurement</th>
                                <th class="px-4 py-4 text-right">Cost per Unit</th>
                                <th class="px-4 py-4 text-center">Quantity</th>
                                <th class="px-4 py-4 text-center">Useful Life (Yrs)</th>
                                <th class="px-4 py-4 text-[#c00000]">Property Number</th>
                                <th class="px-4 py-4">Occupancy</th>
                                <th class="px-4 py-4">Location</th>
                                <th class="px-4 py-4">Source of Acquisition</th>
                                <th class="px-4 py-4">Mode of Acquisition</th>
                                <th class="px-4 py-4">Source Personnel</th>
                                <th class="px-4 py-4">Contact Position</th>
                                <th class="px-4 py-4 text-right">Total Cost</th>
                                <th class="px-4 py-4">Acceptance Date</th>
                                <th class="px-4 py-4">Acquisition Date</th>
                                <th class="px-4 py-4">Remarks (Condition)</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs">
                            <template x-for="(row, index) in paginatedRows" :key="index">
                                <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                    <td class="px-4 py-3 text-[10px] font-black text-slate-400" x-text="(currentPage - 1) * itemsPerPage + index + 1"></td>
                                    <td class="px-4 py-3 text-slate-500" x-text="row.region"></td>
                                    <td class="px-4 py-3 text-slate-500" x-text="row.division"></td>
                                    <td class="px-4 py-3 text-slate-500" x-text="row.school_type"></td>
                                    <td class="px-4 py-3 text-slate-500" x-text="row.school_id"></td>
                                    <td class="px-4 py-3 font-bold text-slate-700" x-text="row.office_school_name"></td>
                                    <td class="px-4 py-3 text-slate-500" x-text="row.classification"></td>
                                    <td class="px-4 py-3 text-slate-500" x-text="row.category"></td>
                                    <td class="px-4 py-3 font-black text-slate-900" x-text="row.article"></td>
                                    <td class="px-4 py-3 text-slate-500 text-[10px] truncate max-w-[200px]" :title="row.description" x-text="row.description"></td>
                                    <td class="px-4 py-3 text-slate-500" x-text="row.unit_of_measurement"></td>
                                    <td class="px-4 py-3 text-right" x-text="'₱' + parseFloat(row.asset_cost || 0).toLocaleString('en-US', {minimumFractionDigits: 2})"></td>
                                    <td class="px-4 py-3 text-center" x-text="row.quantity"></td>
                                    <td class="px-4 py-3 text-center" x-text="row.estimated_useful_life"></td>
                                    <td class="px-4 py-3 font-black text-[#c00000]" x-text="row.property_number"></td>
                                    <td class="px-4 py-3 text-slate-500 text-[10px]" x-text="row.nature_of_occupancy"></td>
                                    <td class="px-4 py-3 text-slate-500" x-text="row.location"></td>
                                    <td class="px-4 py-3 text-slate-500" x-text="row.acq_source"></td>
                                    <td class="px-4 py-3 text-slate-500" x-text="row.mode_of_acquisition"></td>
                                    <td class="px-4 py-3 text-slate-500" x-text="row.source_personnel"></td>
                                    <td class="px-4 py-3 text-slate-500" x-text="row.personnel_position"></td>
                                    <td class="px-4 py-3 font-black text-right" x-text="'₱' + parseFloat(row.acquisition_cost || 0).toLocaleString('en-US', {minimumFractionDigits: 2})"></td>
                                    <td class="px-4 py-3 text-slate-500" x-text="row.acceptance_date"></td>
                                    <td class="px-4 py-3 text-slate-500" x-text="row.acquisition_date"></td>
                                    <td class="px-4 py-3 text-slate-500" x-text="row.remarks"></td>
                                </tr>
                            </template>
                            <tr x-show="previewRows.length === 0 && !loading">
                                <td colspan="25" class="px-4 py-12 text-center text-slate-400 text-xs font-bold uppercase tracking-widest italic">No matching records found. Adjust your filters.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                {{-- Pagination Controls --}}
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between" x-show="previewRows.length > 0">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                        Showing <span x-text="((currentPage - 1) * itemsPerPage) + 1"></span> to <span x-text="Math.min(currentPage * itemsPerPage, previewRows.length)"></span> of <span x-text="previewRows.length"></span>
                    </span>
                    <div class="flex items-center gap-2">
                        <button @click="prevPage()" :disabled="currentPage === 1" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-[10px] font-black uppercase tracking-widest text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm">Prev</button>
                        <span class="text-[10px] font-bold text-slate-700 px-3">Page <span x-text="currentPage"></span> / <span x-text="totalPages"></span></span>
                        <button @click="nextPage()" :disabled="currentPage === totalPages" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-[10px] font-black uppercase tracking-widest text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm">Next</button>
                    </div>
                </div>
            </div>

            <div class="p-8 bg-white border border-slate-100 rounded-[3rem] flex items-center gap-6 shadow-sm fade-enter">
                <div class="w-12 h-12 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center border border-slate-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic">System Information</p>
                    <p class="text-[10px] font-bold text-slate-500 uppercase mt-1 leading-relaxed">Generated reports are in Excel format. If you need specialized data exports, please contact the AMU Administrator.</p>
                </div>
            </div>
        </div>

    </main>
</div>

    <form id="downloadForm" method="POST" action="{{ route('assets.reports.download_rpc') }}" class="hidden">
        @csrf
        <input type="hidden" name="report_type" id="downloadReportType">
        <input type="hidden" name="filters" id="downloadFilters">
    </form>

    <script>
    function reportManager() {
        return {
            step: 1,
            showFilters: true,
            loading: false,
            selectedReport: '',
            reportSubtext: '',
            previewRows: [],
            currentPage: 1,
            itemsPerPage: 50,
            filterOptions: {
                classifications: [],
                categories: [],
                items: [],
                schools: [],
                offices: [],
                sources: [],
                modes: []
            },
            filters: {
                classification: '',
                category: '',
                article: '',
                sortCost: '',
                schoolName: '',
                officeName: '',
                source: '',
                mode: '',
                dateAcquired: ''
            },

            get totalPages() {
                return Math.max(1, Math.ceil(this.previewRows.length / this.itemsPerPage));
            },

            get paginatedRows() {
                const start = (this.currentPage - 1) * this.itemsPerPage;
                return this.previewRows.slice(start, start + this.itemsPerPage);
            },

            nextPage() {
                if (this.currentPage < this.totalPages) this.currentPage++;
            },

            prevPage() {
                if (this.currentPage > 1) this.currentPage--;
            },

            selectReport(type) {
                this.selectedReport = type;
                if (type === 'RPCPPE') {
                    this.reportSubtext = '₱50,000.00 and Above valuation';
                } else if (type === 'RPCSP') {
                    this.reportSubtext = '₱49,999.00 and Below valuation';
                } else {
                    this.reportSubtext = 'Combined Asset Valuation (All Items)';
                }
                this.step = 2;
                this.fetchFilterOptions();
                this.clearFilters();
            },

            fetchFilterOptions() {
                fetch('{{ route("api.reports.filters") }}?report_type=' + this.selectedReport)
                .then(res => res.json())
                .then(data => {
                    this.filterOptions.classifications = data.classifications || [];
                    this.filterOptions.categories = data.categories || [];
                    this.filterOptions.items = data.items || [];
                    this.filterOptions.schools = data.schools || [];
                    this.filterOptions.offices = data.offices || [];
                    this.filterOptions.sources = data.sources || [];
                    this.filterOptions.modes = data.modes || [];
                })
                .catch(err => console.error("Failed to fetch filter options", err));
            },

            clearFilters() {
                this.filters = {
                    classification: '',
                    category: '',
                    article: '',
                    sortCost: '',
                    schoolName: '',
                    officeName: '',
                    source: '',
                    mode: '',
                    dateAcquired: ''
                };
                this.applyFilters();
            },

            applyFilters() {
                this.loading = true;
                
                fetch('{{ route("api.reports.preview") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        report_type: this.selectedReport,
                        filters: this.filters
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.previewRows = data.rows || [];
                    this.currentPage = 1;
                })
                .catch(err => {
                    console.error("Preview fetch error:", err);
                    Swal.fire('Error', 'Failed to fetch preview data.', 'error');
                })
                .finally(() => {
                    this.loading = false;
                });
            },

            download() {
                if (this.previewRows.length === 0) {
                    Swal.fire('Empty Report', 'No assets match your current filters.', 'info');
                    return;
                }

                Swal.fire({
                    title: 'Generating Report...',
                    html: `Generating exact ${this.selectedReport} Template with <strong>${this.previewRows.length}</strong> assets.`,
                    timer: 2000,
                    timerProgressBar: true,
                    didOpen: () => { Swal.showLoading() },
                    willClose: () => {
                        document.getElementById('downloadReportType').value = this.selectedReport;
                        document.getElementById('downloadFilters').value = JSON.stringify(this.filters);
                        document.getElementById('downloadForm').submit();
                    }
                });
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