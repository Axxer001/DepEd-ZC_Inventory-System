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
        /* School Card - Compact Mode */
        .school-card { 
            transition: all 0.2s ease; 
            border: 1px solid #f1f5f9; 
            padding: 0.5rem;
            border-radius: 0.75rem;
            background: #fff;
        }
        .school-card:hover { border-color: #c00000; background: #fffcfc; transform: translateY(-1px); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex animate-fade-in text-slate-800 overflow-x-hidden" x-data="assetInventory()">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scrollbar">
        <main class="p-4 lg:p-8">
            {{-- Header --}}
            <header class="flex flex-col md:flex-row md:justify-between md:items-start mb-6 gap-4">
                <div class="flex flex-col gap-3">
                    <a href="{{ route('assets.view') }}" class="back-btn-hover inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-[10px] font-black text-slate-500 transition-all w-fit shadow-sm uppercase tracking-wider">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                        Back to View Selection
                    </a>
                    <div>
                        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tighter italic uppercase leading-none">Inventory Masterlist</h2>
                        <p class="text-slate-400 text-[11px] mt-1 font-bold italic uppercase tracking-widest">Warehouse & school distribution summary</p>
                    </div>
                </div>
                <button onclick="window.print()" class="group bg-white text-slate-600 border border-slate-200 px-5 py-3 rounded-xl font-bold hover:bg-slate-50 transition-all flex items-center gap-3 shadow-sm active:scale-95 text-[11px] uppercase tracking-widest">
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
                    <h3 class="text-2xl font-black text-slate-800" x-text="totals().remaining"></h3>
                </div>
            </div>

            {{-- Filter Bar --}}
            <section class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 mb-6 flex flex-wrap gap-4">
                <div class="flex-grow grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <select x-model="filters.category" class="w-full p-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold uppercase outline-none focus:ring-2 focus:ring-red-100 transition-all">
                        <option value="all">Categories: ALL</option>
                        <option value="Furniture">Furniture</option>
                        <option value="ICT Equipment">ICT Equipment</option>
                    </select>
                    <select x-model="filters.quadrant" class="w-full p-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold uppercase outline-none focus:ring-2 focus:ring-red-100 transition-all">
                        <option value="all">Quadrants: ALL</option>
                        <option value="Q1">Quadrant 1</option>
                        <option value="Q2">Quadrant 2</option>
                    </select>
                    <select x-model="filters.sort" class="w-full p-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold uppercase outline-none text-[#c00000]">
                        <option value="none">Sort: Default</option>
                        <option value="high">High Qty first</option>
                        <option value="low">Low Qty first</option>
                    </select>
                    <div class="relative">
                        <input type="text" x-model="filters.search" placeholder="Search item/school..." class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-3 h-3 absolute left-3 top-1/2 -translate-y-1/2 text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    </div>
                </div>
            </section>

            {{-- Compact Inventory Table --}}
            <section class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-slate-50/80">
                                <th class="px-5 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Asset</th>
                                <th class="px-5 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Specs Stock</th>
                                <th class="px-5 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Deployments (Schools)</th>
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
                                        </div>
                                    </td>
                                    {{-- Compact School Cards Grid --}}
                                    <td class="px-5 py-4 min-w-[350px]">
                                        <div class="grid grid-cols-2 gap-2">
                                            <template x-for="dist in asset.distribution" :key="dist.school">
                                                <div class="school-card flex items-center gap-2">
                                                    <div class="w-6 h-6 bg-red-50 rounded-lg flex items-center justify-center text-[#c00000]">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" /></svg>
                                                    </div>
                                                    <div class="flex flex-col min-w-0">
                                                        <span class="text-[9px] font-black text-slate-700 uppercase truncate" x-text="dist.school"></span>
                                                        <span class="text-[7px] font-bold text-slate-400 uppercase italic leading-none" x-text="`${dist.district} • ${dist.quadrant}`"></span>
                                                    </div>
                                                    <span class="ml-auto text-[10px] font-black text-[#c00000]" x-text="dist.qty"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </td>
                                    {{-- Numbers --}}
                                    <td class="px-5 py-4 text-center font-black text-[13px] text-slate-900" x-text="calculateMaster(asset)"></td>
                                    <td class="px-5 py-4 text-center bg-blue-50/10 font-black text-[13px] text-blue-600" x-text="calculateDistributed(asset)"></td>
                                    <td class="px-5 py-4 text-center bg-emerald-50/10 font-black text-[13px] text-emerald-600" x-text="calculateRemaining(asset)"></td>
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
                filters: { category: 'all', quadrant: 'all', district: 'all', sort: 'none', search: '' },
                inventory: [
                    { 
                        id: 1, name: 'Armchairs', category: 'Furniture',
                        specs: [{ name: 'Plastic', qty: 300 }, { name: 'Wood', qty: 100 }],
                        distribution: [
                            { school: 'Ayala NHS', district: 'Ayala', quadrant: 'Q1', qty: 150 },
                            { school: 'Baliwasan CS', district: 'Baliwasan', quadrant: 'Q1', qty: 100 }
                        ]
                    },
                    { 
                        id: 2, name: 'Laptops', category: 'ICT Equipment',
                        specs: [{ name: 'Core i5', qty: 50 }, { name: 'Core i3', qty: 25 }],
                        distribution: [
                            { school: 'Putik Elementary', district: 'Putik', quadrant: 'Q2', qty: 40 }
                        ]
                    },
                    { 
                        id: 3, name: 'Smart TV', category: 'ICT Equipment',
                        specs: [{ name: '55" 4K', qty: 20 }],
                        distribution: [
                            { school: 'Vitali National HS', district: 'Vitali', quadrant: 'Q2', qty: 5 }
                        ]
                    }
                ],

                calculateMaster(a) { return a.specs.reduce((s, x) => s + x.qty, 0); },
                calculateDistributed(a) { return a.distribution.reduce((s, x) => s + x.qty, 0); },
                calculateRemaining(a) { return this.calculateMaster(a) - this.calculateDistributed(a); },
                totals() {
                    let m = 0, d = 0;
                    this.inventory.forEach(a => { m += this.calculateMaster(a); d += this.calculateDistributed(a); });
                    return { master: m, distributed: d, remaining: m - d };
                },
                uniqueDistricts() {
                    let dists = [];
                    this.inventory.forEach(a => a.distribution.forEach(d => dists.push(d.district)));
                    return [...new Set(dists)].sort();
                },
                filteredInventory() {
                    let filtered = this.inventory.filter(asset => {
                        const catMatch = this.filters.category === 'all' || asset.category === this.filters.category;
                        const locMatch = asset.distribution.some(d => {
                            const qMatch = this.filters.quadrant === 'all' || d.quadrant === this.filters.quadrant;
                            const dMatch = this.filters.district === 'all' || d.district === this.filters.district;
                            return qMatch && dMatch;
                        });
                        const s = this.filters.search.toLowerCase();
                        const searchMatch = asset.name.toLowerCase().includes(s) || asset.distribution.some(d => d.school.toLowerCase().includes(s));
                        return catMatch && locMatch && searchMatch;
                    });
                    if (this.filters.sort === 'high') filtered.sort((a, b) => this.calculateMaster(b) - this.calculateMaster(a));
                    if (this.filters.sort === 'low') filtered.sort((a, b) => this.calculateMaster(a) - this.calculateMaster(b));
                    return filtered;
                }
            }
        }
    </script>
</body>
</html>