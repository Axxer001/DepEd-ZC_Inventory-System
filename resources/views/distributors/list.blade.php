<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distributors Masterlist | DepEd ZC</title>
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
        .back-btn-hover:hover { transform: translateX(-5px); color: #f97316; border-color: #f97316; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex animate-fade-in text-slate-800 overflow-x-hidden" x-data="distributorList()">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scrollbar">
        <main class="p-4 lg:p-8">
            {{-- Header --}}
            <header class="flex flex-col md:flex-row md:justify-between md:items-start mb-6 gap-4">
                <div class="flex flex-col gap-3">
                    <a href="{{ route('distributors.index') }}" class="back-btn-hover no-print inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-[10px] font-black text-slate-500 transition-all w-fit shadow-sm uppercase tracking-wider">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                        Back to Menu
                    </a>
                    <div>
                        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tighter italic uppercase leading-none">Distributors Directory</h2>
                        <p class="text-slate-400 text-[11px] mt-1 font-bold italic uppercase tracking-widest">Master record of all supply providers & partners</p>
                    </div>
                </div>
                <button onclick="window.print()" class="no-print group bg-white text-slate-600 border border-slate-200 px-5 py-3 rounded-xl font-bold hover:bg-slate-50 transition-all flex items-center gap-3 shadow-sm active:scale-95 text-[11px] uppercase tracking-widest">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-slate-400 group-hover:text-orange-600 transition-colors"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231a1.125 1.125 0 01-1.117-1.227L6.34 18m11.318-4.171a42.41 42.41 0 014.232.748 1.125 1.125 0 01.815 1.39l-1.077 4.195a1.125 1.125 0 01-1.392.815l-1.332-.342M17.66 18l-1.332-.342m-11.318-4.171a42.41 42.41 0 00-4.232.748 1.125 1.125 0 00-.815 1.39l1.077 4.195a1.125 1.125 0 001.392.815l1.332-.342M6.34 18l1.332-.342m0 0V5.25A2.25 2.25 0 019 3h6a2.25 2.25 0 012.25 2.25v12.75m-11.25 0h11.25" /></svg>
                    Export PDF
                </button>
            </header>

            {{-- Filter Bar --}}
            <section class="no-print bg-white p-4 rounded-2xl shadow-sm border border-slate-100 mb-6 flex flex-wrap gap-4">
                <div class="flex-grow grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {{-- Org Filter --}}
                    <select x-model="filters.org" class="w-full p-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold uppercase outline-none focus:ring-2 focus:ring-orange-100 transition-all">
                        <option value="all">Organizations: ALL</option>
                        <template x-for="org in organizations" :key="org">
                            <option :value="org" x-text="org"></option>
                        </template>
                    </select>

                    {{-- Search --}}
                    <div class="relative">
                        <input type="text" x-model="filters.search" placeholder="Search provider or personnel..." class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold outline-none focus:ring-2 focus:ring-orange-100 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-3 h-3 absolute left-3 top-1/2 -translate-y-1/2 text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    </div>

                    <div class="flex items-center justify-end">
                         <template x-if="filters.org !== 'all' || filters.search">
                            <button @click="filters.org='all'; filters.search=''" class="text-[10px] font-black text-red-500 uppercase tracking-wider hover:underline">✕ Reset</button>
                        </template>
                    </div>
                </div>
            </section>

            {{-- Table --}}
            <section class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-slate-50/80">
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Main Organization</th>
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Specific Personnel</th>
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Active Assets Provided</th>
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="dist in filteredDistributors()" :key="dist.id">
                                <tr class="group hover:bg-orange-50/30 transition-all">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center font-black text-xs" x-text="dist.org.charAt(0)"></div>
                                            <span class="font-black text-slate-800 uppercase text-[12px]" x-text="dist.org"></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="text-[11px] font-bold text-slate-600 italic" x-text="dist.personnel"></span>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-block px-3 py-1 bg-slate-100 rounded-lg font-black text-slate-700 text-[12px]" x-text="dist.total_assets"></span>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <button class="text-[9px] font-black text-orange-600 uppercase tracking-widest hover:text-orange-700">View Logs</button>
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
        function distributorList() {
            return {
                filters: { org: 'all', search: '' },
                organizations: ['Supply Section', 'Division Office', 'External Partner', 'IT Dept'],
                distributors: [
                    { id: 1, org: 'Supply Section', personnel: 'Juan Dela Cruz', total_assets: 154 },
                    { id: 2, org: 'Division Office', personnel: 'Maria Clara', total_assets: 42 },
                    { id: 3, org: 'IT Dept', personnel: 'Ricardo Dalisay', total_assets: 89 },
                    { id: 4, org: 'External Partner', personnel: 'Save the Children', total_assets: 200 }
                ],
                filteredDistributors() {
                    return this.distributors.filter(d => {
                        const matchOrg = this.filters.org === 'all' || d.org === this.filters.org;
                        const matchSearch = d.org.toLowerCase().includes(this.filters.search.toLowerCase()) || 
                                            d.personnel.toLowerCase().includes(this.filters.search.toLowerCase());
                        return matchOrg && matchSearch;
                    });
                }
            }
        }
    </script>
</body>
</html> 