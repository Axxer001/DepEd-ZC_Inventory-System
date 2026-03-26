<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt History | DepEd ZC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e9d5ff; border-radius: 10px; }
        [x-cloak] { display: none !important; }
        .table-row-transition { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .back-btn-hover:hover { transform: translateX(-5px); color: #9333ea; border-color: #9333ea; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex animate-fade-in text-slate-800 overflow-x-hidden" x-data="recipientHistory()">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scrollbar">
        <main class="p-6 lg:p-10">
            {{-- Header --}}
            <header class="flex flex-col md:flex-row md:justify-between md:items-start mb-12 gap-4">
                <div class="flex flex-col gap-4">
                    <a href="{{ route('recipients.index') }}" class="back-btn-hover inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-500 transition-all w-fit shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Back to Menu
                    </a>
                    <div>
                        <h2 class="text-4xl font-extrabold text-slate-900 tracking-tight italic uppercase leading-none">Receipt History</h2>
                        <p class="text-slate-500 text-sm mt-2 font-medium italic uppercase tracking-wider">Historical Logs of Assets Received by Schools</p>
                    </div>
                </div>
                <div class="flex gap-3 items-end h-full pt-10 md:pt-0">
                    <button onclick="window.print()" class="group bg-white text-slate-600 border border-slate-200 px-6 py-4 rounded-[1.5rem] font-bold hover:bg-slate-50 transition-all flex items-center gap-3 shadow-sm active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-slate-400 group-hover:text-purple-600 transition-colors">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231a1.125 1.125 0 01-1.117-1.227L6.34 18m11.318-4.171a42.41 42.41 0 014.232.748 1.125 1.125 0 01.815 1.39l-1.077 4.195a1.125 1.125 0 01-1.392.815l-1.332-.342M17.66 18l-1.332-.342m-11.318-4.171a42.41 42.41 0 00-4.232.748 1.125 1.125 0 00-.815 1.39l1.077 4.195a1.125 1.125 0 001.392.815l1.332-.342M6.34 18l1.332-.342m0 0V5.25A2.25 2.25 0 019 3h6a2.25 2.25 0 012.25 2.25v12.75m-11.25 0h11.25" />
                        </svg>
                        Print History
                    </button>
                </div>
            </header>

            {{-- Table Container --}}
            <section class="bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200/50 border border-slate-50 overflow-hidden flex flex-col">
                
                {{-- Search & Title Bar --}}
                <div class="p-8 border-b border-slate-50 flex flex-col md:flex-row justify-between items-center gap-6 bg-white/50 backdrop-blur-md">
                    <div class="flex items-center gap-4">
                        <div class="w-2.5 h-10 bg-purple-600 rounded-full shadow-lg shadow-purple-100"></div>
                        <h3 class="font-black text-slate-800 tracking-tight text-2xl uppercase italic">Allocation Index</h3>
                    </div>
                    
                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <div class="relative w-full md:w-96 group">
                            <input type="text" x-model="searchQuery" placeholder="Search school, item, or personnel..." class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-100 rounded-[1.5rem] text-sm font-semibold focus:outline-none focus:ring-4 focus:ring-purple-50 transition-all shadow-inner">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-purple-600 transition-colors">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <button @click="showFilters = !showFilters" 
                                :class="showFilters ? 'bg-slate-900 text-white shadow-lg' : 'bg-white text-slate-500 border border-slate-200'"
                                class="p-4 rounded-2xl transition-all active:scale-95 group hover:border-purple-600">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 group-hover:text-purple-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 18H7.5m9-6h2.25m-2.25 0a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 12h7.5" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Collapsible Dynamic Filters --}}
                <div x-show="showFilters" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-4" class="p-8 bg-slate-50 border-b border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-8 shadow-inner">
                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Receipt Year</label>
                        <div class="flex flex-wrap gap-2">
                            <button @click="selectedYear = 'all'" :class="selectedYear === 'all' ? 'bg-purple-600 text-white shadow-lg' : 'bg-white text-slate-500 border-slate-200'" class="px-5 py-2.5 rounded-xl text-xs font-bold transition-all uppercase">All Time</button>
                            <template x-for="year in getUniqueYears()" :key="year">
                                <button @click="selectedYear = year" :class="selectedYear == year ? 'bg-purple-600 text-white shadow-lg' : 'bg-white text-slate-500 border-slate-200'" class="px-5 py-2.5 rounded-xl text-xs font-bold transition-all" x-text="year"></button>
                            </template>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">District Cluster</label>
                        <select x-model="selectedDistrict" class="w-full p-4 bg-white border border-slate-200 rounded-2xl text-xs font-bold outline-none focus:ring-4 focus:ring-purple-50 cursor-pointer">
                            <option value="all">All Districts</option>
                            <template x-for="dist in getUniqueDistricts()" :key="dist">
                                <option :value="dist" x-text="dist"></option>
                            </template>
                        </select>
                    </div>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Asset Received</th>
                                <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100 text-center">Qty</th>
                                <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Recipient School</th>
                                <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100 text-center">Status</th>
                                <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100 text-center">Received Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <template x-for="item in filteredItems()" :key="item.id">
                                <tr class="group hover:bg-purple-50/30 transition-all table-row-transition">
                                    <td class="px-8 py-6">
                                        <div class="flex flex-col">
                                            <span class="font-extrabold text-slate-800 group-hover:text-purple-600 uppercase text-[13px] leading-tight transition-colors" x-text="item.item_name"></span>
                                            <span class="text-[9px] font-black text-slate-400 uppercase mt-1 tracking-widest" x-text="item.category"></span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <div class="inline-flex items-center justify-center bg-slate-900 text-white w-10 h-10 rounded-2xl font-black text-xs shadow-lg" x-text="item.qty"></div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center text-purple-600 font-black text-xs" x-text="item.school.charAt(0)"></div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-700 text-xs uppercase tracking-tight" x-text="item.school"></span>
                                                <span class="text-[9px] font-bold text-slate-400 uppercase italic" x-text="item.district"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <span class="px-3 py-1 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-full text-[9px] font-black uppercase tracking-wider" x-text="item.status"></span>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="text-xs font-black text-slate-700 uppercase" x-text="formatDate(item.received_at, 'MMM DD')"></span>
                                            <span class="text-[10px] font-bold text-purple-500 uppercase" x-text="formatDate(item.received_at, 'YYYY')"></span>
                                        </div>
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
        function recipientHistory() {
            return {
                showFilters: false,
                searchQuery: '',
                selectedYear: 'all',
                selectedDistrict: 'all',
                // Prototype Data
                items: [
                    { id: 1, item_name: 'Dell Latitude 3420', category: 'Electronics', school: 'Ayala National High School', district: 'Ayala District', qty: 25, status: 'Serviceable', received_at: '2024-03-20' },
                    { id: 2, item_name: 'Smart TV 55"', category: 'Multimedia', school: 'Zamboanga Central School', district: 'Central District', qty: 2, status: 'Serviceable', received_at: '2023-11-15' },
                    { id: 3, item_name: 'Monoblock Chairs', category: 'Furniture', school: 'Tetuan Elementary', district: 'Central District', qty: 50, status: 'Serviceable', received_at: '2024-02-10' }
                ],

                getUniqueYears() {
                    const years = this.items.map(i => new Date(i.received_at).getFullYear());
                    return [...new Set(years)].sort((a, b) => b - a);
                },

                getUniqueDistricts() {
                    const districts = this.items.map(i => i.district);
                    return [...new Set(districts)].sort();
                },

                filteredItems() {
                    return this.items.filter(item => {
                        const date = new Date(item.received_at);
                        const yearMatch = this.selectedYear === 'all' || date.getFullYear() == this.selectedYear;
                        const distMatch = this.selectedDistrict === 'all' || item.district == this.selectedDistrict;
                        const search = this.searchQuery.toLowerCase();
                        const keywordMatch = !search || 
                                           item.item_name.toLowerCase().includes(search) || 
                                           item.school.toLowerCase().includes(search) ||
                                           item.district.toLowerCase().includes(search);
                        return yearMatch && distMatch && keywordMatch;
                    }).sort((a, b) => new Date(b.received_at) - new Date(a.received_at));
                },

                formatDate(dateStr, format) {
                    const d = new Date(dateStr);
                    return format === 'MMM DD' ? d.toLocaleDateString('en-US', { month: 'short', day: '2-digit' }) : d.getFullYear();
                }
            }
        }
    </script>
</body>
</html>