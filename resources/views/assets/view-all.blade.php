<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Inventory Masterlist | DepEd ZC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        [x-cloak] { display: none !important; }
        .table-row-transition { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .back-btn-hover:hover { transform: translateX(-5px); color: #c00000; border-color: #c00000; }
        .school-card { 
            transition: all 0.2s ease; 
            border: 1px solid #f1f5f9; 
            padding: 0.5rem;
            border-radius: 0.75rem;
            background: #fff;
        }
        .school-card:hover { border-color: #c00000; background: #fffcfc; transform: translateY(-1px); }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex animate-fade-in text-slate-800 overflow-x-hidden" x-data="assetInventory()">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scrollbar">
        <main class="p-4 lg:p-8">
            {{-- Header --}}
            <header class="flex flex-col md:flex-row md:justify-between md:items-start mb-6 gap-4">
                <div class="flex flex-col gap-3">
                    <a href="{{ route('assets.view') }}" class="back-btn-hover no-print inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-[10px] font-black text-slate-500 transition-all w-fit shadow-sm uppercase tracking-wider">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                        Back to View Selection
                    </a>
                    <div>
                        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tighter italic uppercase leading-none">Inventory Masterlist</h2>
                        <p class="text-slate-400 text-[11px] mt-1 font-bold italic uppercase tracking-widest">Warehouse & school distribution summary</p>
                    </div>
                </div>
                <button onclick="window.print()" class="no-print group bg-white text-slate-600 border border-slate-200 px-5 py-3 rounded-xl font-bold hover:bg-slate-50 transition-all flex items-center gap-3 shadow-sm active:scale-95 text-[11px] uppercase tracking-widest">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-slate-400 group-hover:text-[#c00000] transition-colors"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231a1.125 1.125 0 01-1.117-1.227L6.34 18m11.318-4.171a42.41 42.41 0 014.232.748 1.125 1.125 0 01.815 1.39l-1.077 4.195a1.125 1.125 0 01-1.392.815l-1.332-.342M17.66 18l-1.332-.342m-11.318-4.171a42.41 42.41 0 00-4.232.748 1.125 1.125 0 00-.815 1.39l1.077 4.195a1.125 1.125 0 001.392.815l1.332-.342M6.34 18l1.332-.342m0 0V5.25A2.25 2.25 0 019 3h6a2.25 2.25 0 012.25 2.25v12.75m-11.25 0h11.25" /></svg>
                    Print Summary
                </button>
            </header>

            {{-- Compact Stats --}}
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 border-l-4 border-l-slate-800">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Master Stock</p>
                    <h3 class="text-2xl font-black text-slate-800" x-text="totals().master"></h3>
                </div>
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 border-l-4 border-l-blue-600">
                    <p class="text-[9px] font-black text-blue-400 uppercase tracking-widest mb-1">Deployed Units</p>
                    <h3 class="text-2xl font-black text-slate-800" x-text="totals().distributed"></h3>
                </div>
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 border-l-4 border-l-emerald-600">
                    <p class="text-[9px] font-black text-emerald-400 uppercase tracking-widest mb-1">In Warehouse</p>
                    <h3 class="text-2xl font-black text-slate-800" x-text="totals().available"></h3>
                </div>
            </div>

            {{-- Filter Bar --}}
            <section class="no-print bg-white p-4 rounded-2xl shadow-sm border border-slate-100 mb-6 flex flex-wrap gap-4">
                <div class="flex-grow grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Category Filter --}}
                    <select x-model="filters.category" class="w-full p-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold uppercase outline-none focus:ring-2 focus:ring-red-100 transition-all">
                        <option value="all">Categories: ALL</option>
                        <template x-for="cat in categories" :key="cat">
                            <option :value="cat" x-text="cat"></option>
                        </template>
                    </select>

                    {{-- Quadrant Filter --}}
                    <select x-model="filters.quadrant" class="w-full p-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold uppercase outline-none focus:ring-2 focus:ring-red-100 transition-all">
                        <option value="all">Quadrants: ALL</option>
                        <template x-for="q in quadrants" :key="q">
                            <option :value="q" x-text="q"></option>
                        </template>
                    </select>

                    {{-- Sort Filter --}}
                    <select x-model="filters.sort" class="w-full p-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold uppercase outline-none text-[#c00000]">
                        <option value="none">Sort: Default</option>
                        <option value="high">High Qty first</option>
                        <option value="low">Low Qty first</option>
                        <option value="name_asc">Name A→Z</option>
                        <option value="name_desc">Name Z→A</option>
                    </select>

                    {{-- Search --}}
                    <div class="relative">
                        <input type="text" x-model="filters.search" placeholder="Search item/school..." class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold outline-none focus:ring-2 focus:ring-red-100 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-3 h-3 absolute left-3 top-1/2 -translate-y-1/2 text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    </div>
                </div>
            </section>

            {{-- Results count --}}
            <div class="flex items-center justify-between mb-3 px-1">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    Showing <span class="text-slate-700" x-text="filteredInventory().length"></span> of <span class="text-slate-700" x-text="inventory.length"></span> assets
                </p>
                <template x-if="filters.category !== 'all' || filters.quadrant !== 'all' || filters.search">
                    <button @click="filters.category='all'; filters.quadrant='all'; filters.sort='none'; filters.search=''" class="text-[10px] font-black text-red-500 uppercase tracking-wider hover:underline">✕ Clear Filters</button>
                </template>
            </div>

            {{-- Inventory Table --}}
            <section class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-slate-50/80">
                                <th class="px-5 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Asset</th>
                                <th class="px-5 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Specs Stock</th>
                                <th class="px-5 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Recipient Schools</th>
                                <th class="px-5 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Master</th>
                                <th class="px-5 py-4 text-[9px] font-black text-blue-500 uppercase tracking-widest border-b border-slate-100 text-center bg-blue-50/20">Sent</th>
                                <th class="px-5 py-4 text-[9px] font-black text-emerald-500 uppercase tracking-widest border-b border-slate-100 text-center bg-emerald-50/20">Rem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="asset in filteredInventory()" :key="asset.id">
                                <tr class="group hover:bg-slate-50/50 transition-all table-row-transition">
                                    {{-- Asset Identity --}}
                                    <td class="px-5 py-4 min-w-[150px]">
                                        <div class="flex flex-col">
                                            <span class="font-black text-slate-800 uppercase text-[12px] leading-tight" x-text="asset.name"></span>
                                            <span class="text-[8px] font-black text-blue-500 uppercase mt-1 tracking-tighter" x-text="asset.category"></span>
                                        </div>
                                    </td>
                                    {{-- Specs Pills --}}
                                    <td class="px-5 py-4 min-w-[150px]">
                                        <div class="flex flex-col gap-1">
                                            <template x-for="spec in asset.specs" :key="spec.name">
                                                <div class="flex items-center justify-between bg-white border border-slate-100 px-2 py-1 rounded-md">
                                                    <span class="text-[8px] font-bold text-slate-500 uppercase" x-text="spec.name"></span>
                                                    <span class="text-[9px] font-black text-slate-800" x-text="spec.qty"></span>
                                                </div>
                                            </template>
                                            <template x-if="asset.specs.length === 0">
                                                <span class="text-[8px] font-bold text-slate-300 italic">No specs</span>
                                            </template>
                                        </div>
                                    </td>
                                    {{-- Compact Recipient Count --}}
                                    <td class="px-5 py-4 min-w-[200px] text-center">
                                        <template x-if="getFilteredDistribution(asset).length > 0">
                                            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-50 text-[#c00000] border border-red-100 rounded-full font-black text-[12px]">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M10 2a.75.75 0 01.59.299l7.5 9.75a.75.75 0 01-1.18.902L10 3.864 3.09 12.951a.75.75 0 01-1.18-.902l7.5-9.75A.75.75 0 0110 2zM3 15.75a.75.75 0 01.75-.75h12.5a.75.75 0 010 1.5H3.75a.75.75 0 01-.75-.75z" clip-rule="evenodd" /></svg>
                                                <span x-text="`${getFilteredDistribution(asset).length} Schools`"></span>
                                            </div>
                                        </template>
                                        <template x-if="getFilteredDistribution(asset).length === 0">
                                            <span class="text-[10px] font-bold text-slate-300 italic">No deployments</span>
                                        </template>
                                    </td>
                                    {{-- Numbers --}}
                                    <td class="px-5 py-4 text-center font-black text-[13px] text-slate-900" x-text="asset.master_quantity"></td>
                                    <td class="px-5 py-4 text-center bg-blue-50/10 font-black text-[13px] text-blue-600" x-text="calculateDistributed(asset)"></td>
                                    <td class="px-5 py-4 text-center bg-emerald-50/10 font-black text-[13px] text-emerald-600" x-text="calculateAvailableStock(asset)"></td>
                                </tr>
                            </template>

                            {{-- Empty State --}}
                            <template x-if="filteredInventory().length === 0">
                                <tr>
                                    <td colspan="6" class="px-8 py-16 text-center">
                                        <p class="text-slate-400 font-bold text-sm">No assets match your filters</p>
                                        <p class="text-slate-300 text-xs mt-1">Try adjusting the category, quadrant, or search query</p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script>
        function assetInventory() {
            return {
                filters: { category: 'all', quadrant: 'all', sort: 'none', search: '' },

                // Dynamic data from the backend
                inventory: {!! $inventoryJson !!},
                categories: {!! $categoriesJson !!},
                quadrants: {!! $quadrantsJson !!},

                calculateDistributed(a) { return a.distribution.reduce((s, x) => s + x.qty, 0); },
                calculateAvailableStock(a) { return a.specs.reduce((s, sp) => s + sp.qty, 0); },

                totals() {
                    const filtered = this.filteredInventory();
                    let m = 0, d = 0, avail = 0;
                    filtered.forEach(a => { 
                        m += a.master_quantity; 
                        d += this.calculateDistributed(a);
                        avail += a.specs.reduce((s, sp) => s + sp.qty, 0);
                    });
                    return { master: m, distributed: d, available: avail };
                },

                getFilteredDistribution(asset) {
                    if (this.filters.quadrant === 'all') return asset.distribution;
                    return asset.distribution.filter(d => d.quadrant === this.filters.quadrant);
                },

                filteredInventory() {
                    const s = this.filters.search.toLowerCase().trim();

                    let filtered = this.inventory.filter(asset => {
                        // Category filter
                        if (this.filters.category !== 'all' && asset.category !== this.filters.category) return false;

                        // Quadrant filter: asset must have at least one distribution in the selected quadrant
                        // OR we show all assets even without distribution if quadrant is 'all'
                        if (this.filters.quadrant !== 'all') {
                            const hasQuadrant = asset.distribution.some(d => d.quadrant === this.filters.quadrant);
                            if (!hasQuadrant) return false;
                        }

                        // Search filter: match item name, category, spec names, or school names
                        if (s) {
                            const nameMatch = asset.name.toLowerCase().includes(s);
                            const catMatch = asset.category.toLowerCase().includes(s);
                            const specMatch = asset.specs.some(sp => sp.name.toLowerCase().includes(s));
                            const schoolMatch = asset.distribution.some(d => d.school.toLowerCase().includes(s));
                            if (!nameMatch && !catMatch && !specMatch && !schoolMatch) return false;
                        }

                        return true;
                    });

                    // Sorting
                    if (this.filters.sort === 'high') filtered.sort((a, b) => b.master_quantity - a.master_quantity);
                    else if (this.filters.sort === 'low') filtered.sort((a, b) => a.master_quantity - b.master_quantity);
                    else if (this.filters.sort === 'name_asc') filtered.sort((a, b) => a.name.localeCompare(b.name));
                    else if (this.filters.sort === 'name_desc') filtered.sort((a, b) => b.name.localeCompare(a.name));

                    return filtered;
                }
            }
        }
    </script>
</body>
</html>