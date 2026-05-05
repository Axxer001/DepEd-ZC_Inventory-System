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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 px-2">
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
                <div @click="selectReport('RPCSP')" class="group bg-white rounded-[3rem] border border-slate-100 shadow-xl p-10 hover:shadow-slate-200 transition-all duration-500 relative overflow-hidden flex flex-col justify-between cursor-pointer min-h-[380px]">
                    <div class="absolute -right-10 -top-10 w-48 h-48 bg-slate-50 rounded-full opacity-50 blur-3xl group-hover:bg-red-50 transition-colors"></div>
                    <div class="relative z-10">
                        <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 group-hover:-rotate-6 transition-all border border-slate-100 shadow-inner group-hover:text-deped group-hover:bg-red-50">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <h2 class="text-4xl font-black text-slate-900 uppercase tracking-tighter italic mb-2">RPCSP</h2>
                        <span class="px-4 py-1.5 bg-slate-50 text-slate-400 text-[9px] font-black uppercase tracking-[0.3em] rounded-full border border-slate-100 italic mb-6 inline-block shadow-sm group-hover:text-deped group-hover:bg-red-50 group-hover:border-red-100 transition-all">Semi-Expendable Assets</span>
                        <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] leading-relaxed mt-4 max-w-[280px]">Items valued below ₱50,000.00. Consumable Inventory Reporting.</p>
                    </div>
                    <div class="mt-auto pt-8 flex items-center gap-3 text-slate-400 font-black uppercase italic tracking-[0.3em] text-[10px] group-hover:text-deped group-hover:translate-x-2 transition-all">
                        Configure Report Options
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </div>
                </div>
            </div>
            
            {{-- STEP 1 FOOTER: CUSTOM REQUEST --}}
            <div class="mt-12 p-8 bg-white border border-slate-100 rounded-[3rem] flex flex-col md:flex-row items-center justify-between gap-8 overflow-hidden relative shadow-xl group hover:border-deped/20 transition-all duration-500">
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-red-50 rounded-full opacity-30 blur-3xl"></div>
                <div class="relative z-10 flex items-center gap-6">
                    <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-3xl flex items-center justify-center border border-slate-100 group-hover:bg-red-50 group-hover:text-deped transition-all">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div class="text-center md:text-left">
                        <h4 class="text-lg font-black text-slate-800 uppercase italic tracking-tight mb-1">Need a specialized report?</h4>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.3em]">Contact the AMU Administrator for custom data exports.</p>
                    </div>
                </div>
                <button class="relative z-10 px-10 py-4 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] hover:bg-deped transition-all shadow-lg active:scale-95 italic">Request Data Export</button>
            </div>
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
                        class="px-6 py-3 bg-white border border-slate-200 rounded-2xl text-[9px] font-black uppercase tracking-widest text-slate-500 flex items-center gap-3 shadow-sm hover:border-deped hover:text-deped transition-all active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                        </svg>
                        <span x-text="showFilters ? 'Hide Filters' : 'Filter Options'"></span>
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
                            <select x-model="filters.classification" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500 cursor-pointer">
                                <option value="">All Classifications</option>
                                <option>School Equipments</option>
                                <option>Buildings & Structures</option>
                                <option>ICT Packages</option>
                            </select>
                        </div>

                        {{-- School Type --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">School Type</label>
                            <select x-model="filters.schoolType" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500 cursor-pointer">
                                <option value="">All Types</option>
                                <option>Elementary</option>
                                <option>Secondary</option>
                                <option>Senior High</option>
                            </select>
                        </div>

                        {{-- School Name --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">School Name</label>
                            <select x-model="filters.schoolName" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500 cursor-pointer">
                                <option value="">All Schools</option>
                                <option>Zamboanga City HS</option>
                                <option>Ayala National HS</option>
                                <option>Tetuan Central School</option>
                                <option>Maria Clara Lobregat NHS</option>
                            </select>
                        </div>

                        {{-- Item Category --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Item Category</label>
                            <select x-model="filters.article" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500 cursor-pointer">
                                <option value="">All Items</option>
                                <option>Laptops & Tablets</option>
                                <option>Printers & Scanners</option>
                                <option>Desks & Chairs</option>
                                <option>Laboratory Tools</option>
                            </select>
                        </div>

                        {{-- Cost Filter --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Cost Filter</label>
                            <select x-model="filters.costOperator" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500 cursor-pointer">
                                <option value="">Any Amount</option>
                                <option value="gt">Higher than (>) </option>
                                <option value="lt">Lower than (<) </option>
                                <option value="eq">Exactly (=)</option>
                            </select>
                        </div>

                        {{-- Amount --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Amount (PHP)</label>
                            <select x-model="filters.acquisitionCost" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500 cursor-pointer">
                                <option value="">Select Amount</option>
                                <option value="5000">₱5,000.00</option>
                                <option value="10000">₱10,000.00</option>
                                <option value="50000">₱50,000.00</option>
                                <option value="100000">₱100,000.00</option>
                                <option value="500000">₱500,000.00</option>
                            </select>
                        </div>

                        {{-- Year --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Year</label>
                            <select x-model="filters.year" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500 cursor-pointer">
                                <option value="">All Years</option>
                                <option>2026</option>
                                <option>2025</option>
                                <option>2024</option>
                                <option>2023</option>
                            </select>
                        </div>

                        {{-- Month --}}
                        <div>
                            <label class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-2 block italic">Month</label>
                            <select x-model="filters.month" class="w-full bg-slate-50 border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase focus:ring-4 focus:ring-red-50 focus:border-deped transition-all text-slate-500 cursor-pointer">
                                <option value="">All Months</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-50 flex justify-end gap-3">
                        <button @click="clearFilters()" class="px-8 py-3 bg-slate-100 text-slate-500 rounded-2xl text-[9px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all active:scale-95 italic">Clear All Filters</button>
                        <button @click="showFilters = false" class="px-8 py-3 bg-slate-900 text-white rounded-2xl text-[9px] font-black uppercase tracking-widest hover:bg-deped transition-all active:scale-95 italic">Apply Configuration</button>
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

<script>
    function reportManager() {
        return {
            step: 1,
            showFilters: true,
            selectedReport: '',
            reportSubtext: '',
            filters: {
                classification: '',
                schoolType: '',
                schoolName: '',
                article: '',
                costOperator: '',
                acquisitionCost: '',
                year: '',
                month: ''
            },

            selectReport(type) {
                this.selectedReport = type;
                this.reportSubtext = (type === 'RPCPPE') ? '₱50,000.00 and Above valuation' : '₱49,999.00 and Below valuation';
                this.step = 2;
            },

            clearFilters() {
                this.filters = {
                    classification: '',
                    schoolType: '',
                    schoolName: '',
                    article: '',
                    costOperator: '',
                    acquisitionCost: '',
                    year: '',
                    month: ''
                };
            },

            download() {
                Swal.fire({
                    title: 'Downloading Report...',
                    html: 'Preparing the ' + this.selectedReport + ' Excel file for download.',
                    timer: 2000,
                    timerProgressBar: true,
                    didOpen: () => { Swal.showLoading() },
                    willClose: () => {
                        window.location.href = "{{ route('assets.reports.template') }}";
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