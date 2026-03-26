<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distributors Explorer | DepEd ZC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .nav-card:hover { transform: translateY(-5px); }
        [x-cloak] { display: none !important; }
        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #fed7aa; border-radius: 10px; }
        .back-btn-hover:hover { transform: translateX(-5px); border-color: #f97316; color: #f97316; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-800" x-data="distributorExplorer()">

    @include('partials.sidebar')

    <main class="flex-grow p-6 lg:p-10 h-screen overflow-y-auto custom-scroll">
        <header class="mb-10">
            <a href="{{ route('distributors.index') }}" class="back-btn-hover inline-flex items-center gap-2 px-4 py-2 mb-4 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-500 transition-all w-fit shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back to Menu
            </a>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight italic uppercase">Supply Analytics</h2>
            <p class="text-slate-500 text-sm mt-1 font-medium italic">Explore assets based on their source organization or partner</p>
        </header>

        {{-- Step 1: Distributor Selection --}}
        <section class="mb-8">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                Step 1: Select Distribution Partner
                <span class="h-[1px] flex-grow bg-slate-200"></span>
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <template x-for="(data, distName) in sources" :key="distName">
                    <button @click="selectDistributor(distName)" 
                        :class="selectedDistributor === distName ? 'border-orange-500 bg-orange-50 ring-4 ring-orange-500/10' : 'bg-white border-slate-100'"
                        class="nav-card group p-8 rounded-[2rem] border shadow-lg shadow-slate-200/50 transition-all duration-300 text-left">
                        
                        <div :class="selectedDistributor === distName ? 'bg-orange-500 text-white' : 'bg-slate-50 text-slate-400'"
                             class="w-12 h-12 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-sm">
                             <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5-1.5l-3-1m-3.145-3.145l1.5-5.454L10.5.5l-1.5 5.455 3.145 3.145Z" />
                             </svg>
                        </div>
                        
                        <h4 class="font-extrabold text-slate-800 text-lg leading-tight uppercase tracking-tighter" x-text="distName"></h4>
                        <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase" x-text="`${data.item_categories.length} Categories Provided`"></p>
                    </button>
                </template>
            </div>
        </section>

        {{-- Step 2: Category Selection --}}
        <section class="mb-10" x-show="selectedDistributor" x-transition x-cloak>
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 ml-1">Step 2: Choose Item Category</h3>
            <div class="flex flex-wrap gap-3">
                <template x-for="cat in sources[selectedDistributor]?.item_categories || []" :key="cat.name">
                    <button @click="selectCategory(cat.name)"
                        :class="selectedCategory === cat.name ? 'bg-orange-600 text-white shadow-lg' : 'bg-white text-slate-600 border-slate-200 hover:border-orange-500'"
                        class="px-6 py-3 rounded-2xl border font-bold text-sm transition-all" 
                        x-text="cat.name">
                    </button>
                </template>
            </div>
        </section>

        {{-- Results Table --}}
        <section id="resultsSection" x-show="selectedCategory" x-transition x-cloak>
            <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 overflow-hidden">
                
                <div class="p-8 border-b border-slate-50 bg-slate-50/30 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                            <span class="text-orange-600">📦</span>
                            <span x-text="`${selectedCategory} from ${selectedDistributor}`"></span>
                        </h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Specific Item Inventory</p>
                    </div>
                    
                    <div class="relative w-full md:w-64 group">
                        <input type="text" x-model="searchQuery" placeholder="Search item model..." 
                            class="w-full pl-12 pr-6 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold focus:outline-none focus:ring-4 focus:ring-orange-50 transition-all shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-orange-500 transition-colors">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Model / Asset Name</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Batch Code</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Total Delivered</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="item in filteredItems" :key="item.model">
                                <tr class="hover:bg-orange-50/30 transition-colors group">
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-2 h-2 rounded-full bg-orange-400"></div>
                                            <p class="font-bold text-slate-800 text-base" x-text="item.model"></p>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <span class="px-3 py-1 bg-slate-100 rounded-lg text-[10px] font-black text-slate-500 uppercase tracking-tighter" x-text="item.batch"></span>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <span class="font-black text-slate-800 text-lg" x-text="item.qty"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <script>
        function distributorExplorer() {
            return {
                selectedDistributor: null,
                selectedCategory: null,
                searchQuery: '',
                
                // Static Data for Frontend Prototype
                sources: {
                    "Supply Section": {
                        item_categories: [
                            { name: "Electronics", items: [{model: "Dell Latitude 3420", qty: 50, batch: "2024-A"}, {model: "Smart TV 55\"", qty: 12, batch: "2023-B"}] },
                            { name: "Furniture", items: [{model: "Monoblock Chairs", qty: 200, batch: "FURN-01"}] }
                        ]
                    },
                    "Division Office": {
                        item_categories: [
                            { name: "Office Supplies", items: [{model: "A4 Bond Paper (Box)", qty: 500, batch: "DO-MARCH"}] },
                            { name: "IT Peripherals", items: [{model: "Logitech Mouse", qty: 100, batch: "IT-2024"}] }
                        ]
                    },
                    "External Partner (NGO)": {
                        item_categories: [
                            { name: "Laboratory", items: [{model: "Microscope X100", qty: 15, batch: "NGO-SAVE"}] }
                        ]
                    }
                },

                selectDistributor(dist) {
                    this.selectedDistributor = dist;
                    this.selectedCategory = null;
                },

                selectCategory(cat) {
                    this.selectedCategory = cat;
                    setTimeout(() => {
                        document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth' });
                    }, 100);
                },

                get filteredItems() {
                    if (!this.selectedDistributor || !this.selectedCategory) return [];
                    const categoryData = this.sources[this.selectedDistributor].item_categories.find(c => c.name === this.selectedCategory);
                    const items = categoryData ? categoryData.items : [];
                    
                    if (this.searchQuery.trim() !== '') {
                        return items.filter(i => i.model.toLowerCase().includes(this.searchQuery.toLowerCase()));
                    }
                    return items;
                }
            }
        }
    </script>
</body>
</html>