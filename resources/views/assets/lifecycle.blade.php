<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Lifecycle | DepEd ZC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        
        [x-cloak] { display: none !important; }

        .timeline-line {
            position: absolute;
            left: 0.625rem;
            top: 1.5rem;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, #c00000 0%, #fecaca 100%);
            z-index: 0;
        }

        .timeline-dot {
            position: relative;
            z-index: 10;
        }
    </style>
</head>
<body class="bg-[#f8fafc] min-h-screen flex animate-fade-in text-slate-800 overflow-x-hidden" x-data="assetLifecycle()">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll">
        <main class="p-4 lg:p-6 space-y-6 max-w-[1400px] mx-auto w-full">
            
            {{-- Header --}}
            <header class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 bg-white p-5 rounded-[1.5rem] shadow-xl shadow-slate-200/40 border border-slate-50">
                <div class="flex flex-col gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-red-50 rounded-[1rem] flex items-center justify-center border border-red-100 shadow-sm shadow-red-100/50">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-[#c00000]">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-slate-900 tracking-tight italic uppercase leading-none">Asset Lifecycle</h2>
                            <p class="text-slate-500 text-[10px] mt-1 font-bold uppercase tracking-widest">History & Transfer Tracking</p>
                        </div>
                    </div>
                </div>
                
                <div class="relative w-full md:w-[350px]">
                    <input type="text" x-model="searchQuery" placeholder="Search Property No. or Asset..." 
                           class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-[1rem] text-xs font-bold focus:outline-none focus:ring-4 focus:ring-red-50 focus:border-[#c00000] transition-all shadow-inner text-slate-800 placeholder-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                {{-- Asset List Panel --}}
                <div class="lg:col-span-4 flex flex-col bg-white rounded-[1.5rem] shadow-xl shadow-slate-200/50 border border-slate-50 overflow-hidden" style="height: calc(100vh - 180px);">
                    <div class="p-4 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-800">Select an Asset</h3>
                        <p class="text-[9px] font-bold text-slate-400 uppercase mt-0.5 tracking-wider" x-text="filteredAssets.length + ' records found'"></p>
                    </div>
                    
                    <div class="flex-grow overflow-y-auto custom-scroll p-3 space-y-2">
                        <template x-for="asset in filteredAssets" :key="asset.id">
                            <button @click="selectAsset(asset)" 
                                    :class="selectedAsset && selectedAsset.id === asset.id ? 'bg-[#c00000] shadow-md shadow-red-200 border-[#c00000] scale-[1.01]' : 'bg-white hover:bg-red-50 hover:border-red-100 border-slate-100'"
                                    class="w-full p-3 rounded-xl border text-left transition-all duration-300 group">
                                <div class="flex justify-between items-center mb-1.5">
                                    <span :class="selectedAsset && selectedAsset.id === asset.id ? 'text-white/80' : 'text-[#c00000] bg-red-50'" 
                                          class="text-[8px] font-black uppercase tracking-widest px-2 py-0.5 rounded-md" x-text="asset.property_number"></span>
                                    <span :class="selectedAsset && selectedAsset.id === asset.id ? 'text-white' : 'text-slate-400'" 
                                          class="text-[9px] font-bold uppercase tracking-wider" x-text="formatDate(asset.acquisition_date, 'YYYY')"></span>
                                </div>
                                <h4 :class="selectedAsset && selectedAsset.id === asset.id ? 'text-white' : 'text-slate-900'" 
                                    class="text-xs font-black uppercase tracking-tight italic line-clamp-1" x-text="asset.description"></h4>
                                <p :class="selectedAsset && selectedAsset.id === asset.id ? 'text-white/70' : 'text-slate-500'" 
                                   class="text-[9px] font-bold mt-1 uppercase tracking-wide truncate" x-text="'Loc: ' + asset.school_name"></p>
                            </button>
                        </template>
                        
                        <div x-show="filteredAssets.length === 0" class="flex flex-col items-center justify-center h-32 text-center px-4" x-cloak>
                            <div class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center mb-2">
                                <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <span class="text-xs font-bold text-slate-500">No assets found</span>
                        </div>
                    </div>
                </div>

                {{-- Lifecycle Timeline Panel --}}
                <div class="lg:col-span-8 flex flex-col bg-white rounded-[1.5rem] shadow-xl shadow-slate-200/50 border border-slate-50 overflow-hidden relative" style="height: calc(100vh - 180px);">
                    
                    {{-- Empty State --}}
                    <div x-show="!selectedAsset" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-50/50 p-6 z-20" x-cloak>
                        <div class="w-24 h-24 mb-4 opacity-40">
                            <img src="{{ asset('images/asset.png') }}" alt="Asset Empty" class="w-full h-full object-contain grayscale">
                        </div>
                        <h3 class="text-lg font-black text-slate-800 uppercase tracking-widest italic mb-1">Select an Asset</h3>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center max-w-sm">Choose an asset from the registry panel to view its complete historical lifecycle, transfers, and current deployment status.</p>
                    </div>

                    {{-- Timeline View --}}
                    <template x-if="selectedAsset">
                        <div class="flex flex-col h-full animate-fade-in">
                            {{-- Asset Header Info --}}
                            <div class="p-6 border-b border-slate-100 bg-gradient-to-br from-slate-900 to-slate-800 text-white relative overflow-hidden shrink-0">
                                <div class="absolute -right-10 -top-10 w-32 h-32 bg-[#c00000] rounded-full blur-3xl opacity-30 pointer-events-none"></div>
                                <div class="flex justify-between items-start relative z-10">
                                    <div>
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="px-2 py-0.5 bg-white/20 backdrop-blur-md rounded-md text-[8px] font-black uppercase tracking-widest border border-white/20" x-text="selectedAsset.category_name"></span>
                                            <span class="px-2 py-0.5 bg-[#c00000] rounded-md text-[8px] font-black uppercase tracking-widest shadow-md shadow-red-900/50" x-text="selectedAsset.property_number"></span>
                                        </div>
                                        <h2 class="text-xl font-black uppercase italic tracking-tight leading-none mb-1" x-text="selectedAsset.description"></h2>
                                        <p class="text-[10px] text-slate-300 font-bold uppercase tracking-widest" x-text="selectedAsset.item_name"></p>
                                    </div>
                                    <div class="text-right bg-white/10 p-3 rounded-xl backdrop-blur-md border border-white/10 hidden sm:block">
                                        <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Acquisition Cost</p>
                                        <p class="text-sm font-black text-emerald-400 tracking-tighter">₱ <span x-text="formatCurrency(selectedAsset.cost)"></span></p>
                                    </div>
                                </div>
                            </div>

                            {{-- Timeline Content --}}
                            <div class="flex-grow overflow-y-auto custom-scroll p-6 relative bg-slate-50/30">
                                <div class="relative max-w-xl mx-auto">
                                    
                                    <div class="timeline-line"></div>

                                    <div class="space-y-8">
                                        
                                        {{-- 1. Acquisition Event --}}
                                        <div class="relative pl-8 group">
                                            <div class="timeline-dot absolute left-[-6px] top-1 w-6 h-6 rounded-full bg-white border-2 border-[#c00000] flex items-center justify-center shadow-md shadow-red-200 group-hover:scale-110 transition-transform">
                                                <div class="w-2 h-2 bg-[#c00000] rounded-full"></div>
                                            </div>
                                            <div class="bg-white p-4 rounded-[1rem] border border-slate-200 shadow-sm group-hover:shadow-md group-hover:border-red-100 transition-all">
                                                <div class="flex justify-between items-start mb-3">
                                                    <div>
                                                        <span class="text-[8px] font-black text-[#c00000] uppercase tracking-widest bg-red-50 px-2 py-0.5 rounded">Stage 1: Acquisition</span>
                                                        <h4 class="text-sm font-black text-slate-800 uppercase italic mt-1.5">Asset Registered & Procured</h4>
                                                    </div>
                                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest" x-text="formatDate(selectedAsset.acceptance_date, 'MMM DD, YYYY')"></span>
                                                </div>
                                                <div class="grid grid-cols-2 gap-3 mt-3 bg-slate-50 p-3 rounded-lg border border-slate-100">
                                                    <div>
                                                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Source / Supplier</p>
                                                        <p class="text-[10px] font-bold text-slate-800 mt-0.5 uppercase" x-text="selectedAsset.source_name"></p>
                                                    </div>
                                                    <div>
                                                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Acquisition Mode</p>
                                                        <p class="text-[10px] font-bold text-slate-800 mt-0.5 uppercase" x-text="selectedAsset.mode_of_acquisition"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- 2. Deployment / Distribution Event --}}
                                        <div class="relative pl-8 group">
                                            <div class="timeline-dot absolute left-[-6px] top-1 w-6 h-6 rounded-full bg-white border-2 border-emerald-500 flex items-center justify-center shadow-md shadow-emerald-200 group-hover:scale-110 transition-transform">
                                                <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                                            </div>
                                            <div class="bg-white p-4 rounded-[1rem] border border-slate-200 shadow-sm group-hover:shadow-md group-hover:border-emerald-100 transition-all relative overflow-hidden">
                                                <div class="absolute right-0 top-0 w-1.5 h-full bg-emerald-500"></div>
                                                <div class="flex justify-between items-start mb-3">
                                                    <div>
                                                        <span class="text-[8px] font-black text-emerald-600 uppercase tracking-widest bg-emerald-50 px-2 py-0.5 rounded">Stage 2: Deployment</span>
                                                        <h4 class="text-sm font-black text-slate-800 uppercase italic mt-1.5">Distributed to Location</h4>
                                                    </div>
                                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest" x-text="formatDate(selectedAsset.acquisition_date, 'MMM DD, YYYY')"></span>
                                                </div>
                                                <div class="flex items-center gap-3 mt-3 bg-slate-50 p-3 rounded-lg border border-slate-100">
                                                    <div class="w-8 h-8 bg-slate-200 rounded-md flex items-center justify-center shrink-0">
                                                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Assigned Office/School</p>
                                                        <p class="text-xs font-black text-slate-800 uppercase tracking-tight line-clamp-1" x-text="selectedAsset.school_name"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- 3. Future/End of Life Indicator --}}
                                        <div class="relative pl-8 opacity-50">
                                            <div class="timeline-dot absolute left-[-2px] top-1 w-4 h-4 rounded-full bg-slate-200 border-2 border-white flex items-center justify-center">
                                            </div>
                                            <div class="py-0.5">
                                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Awaiting Future Lifecycle Events...</h4>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </main>
    </div>

    <script>
        function assetLifecycle() {
            return {
                searchQuery: '',
                assets: {!! $assetsJson !!},
                selectedAsset: null,

                get filteredAssets() {
                    const search = this.searchQuery.toLowerCase();
                    if (!search) return this.assets;
                    return this.assets.filter(a => 
                        (a.property_number && a.property_number.toLowerCase().includes(search)) ||
                        (a.description && a.description.toLowerCase().includes(search)) ||
                        (a.item_name && a.item_name.toLowerCase().includes(search))
                    );
                },

                selectAsset(asset) {
                    this.selectedAsset = asset;
                },

                formatDate(dateStr, format) {
                    if (!dateStr) return 'N/A';
                    const d = new Date(dateStr);
                    if (isNaN(d.getTime())) return dateStr;
                    
                    if (format === 'YYYY') return d.getFullYear();
                    
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    return `${months[d.getMonth()]} ${String(d.getDate()).padStart(2, '0')}, ${d.getFullYear()}`;
                },

                formatCurrency(value) {
                    return Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            }
        }
    </script>
</body>
</html>
