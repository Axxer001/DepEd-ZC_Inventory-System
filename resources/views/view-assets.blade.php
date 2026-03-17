<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Explorer | DepEd ZC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .category-btn:hover { transform: translateY(-5px); }
        [x-cloak] { display: none !important; }
        
        /* Custom scrollbar */
        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-800" x-data="assetExplorer()">

    @include('partials.sidebar')

    <main class="flex-grow p-6 lg:p-10 h-screen overflow-y-auto custom-scroll">
        <header class="mb-10">
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Asset Explorer</h2>
            <p class="text-slate-500 text-sm mt-1 font-medium italic">Select a category and item to see distribution across all schools</p>
        </header>

        {{-- Step 1: Category Selection --}}
        <section class="mb-8">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                Step 1: Select Category
                <span class="h-[1px] flex-grow bg-slate-200"></span>
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <template x-for="(data, catName) in inventory" :key="catName">
                    <button @click="selectCategory(catName)" 
                        :class="selectedCategory === catName ? 'border-[#c00000] bg-red-50 ring-4 ring-red-500/10' : 'bg-white border-slate-100'"
                        class="category-btn group p-8 rounded-[2rem] border shadow-lg shadow-slate-200/50 transition-all duration-300 text-left">
                        
                        <div :class="selectedCategory === catName ? 'bg-white text-[#c00000]' : 'bg-slate-50 text-slate-400'"
                             class="w-12 h-12 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-sm" 
                             x-html="data.icon">
                        </div>
                        
                        <h4 class="font-extrabold text-slate-800 text-lg leading-tight" x-text="catName"></h4>
                        <p class="text-[10px] font-bold uppercase mt-1" 
                           :class="selectedCategory === catName ? 'text-[#c00000]' : 'text-slate-400'"
                           x-text="selectedCategory === catName ? 'Active Selection' : 'View Items'">
                        </p>
                    </button>
                </template>
            </div>
        </section>

        {{-- Step 2: Item Selection --}}
        <section class="mb-10" x-show="selectedCategory" x-transition x-cloak>
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 ml-1">Step 2: Choose Specific Item</h3>
            <div class="flex flex-wrap gap-3">
                <template x-for="itemName in Object.keys(inventory[selectedCategory]?.items || {})" :key="itemName">
                    <button @click="selectItem(itemName)"
                        :class="selectedItem === itemName ? 'bg-slate-900 text-white shadow-lg' : 'bg-white text-slate-600 border-slate-200 hover:border-[#c00000]'"
                        class="px-6 py-3 rounded-2xl border font-bold text-sm transition-all" 
                        x-text="itemName">
                    </button>
                </template>
            </div>
        </section>

        {{-- Results Table --}}
        <section id="resultsSection" x-show="selectedItem" x-transition x-cloak>
            <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 overflow-hidden">
                
                <div class="p-8 border-b border-slate-50 bg-slate-50/30 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                            <span class="text-[#c00000]" x-html="inventory[selectedCategory]?.icon"></span>
                            <span x-text="`${selectedItem} Inventory` "></span>
                        </h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Division Wide Summary</p>
                    </div>
                    
                    <div class="flex items-center gap-6">
                        <div class="text-right hidden md:block border-r border-slate-200 pr-6">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Quantity</p>
                            <p class="text-2xl font-black text-[#c00000]" x-text="calculateOverallTotal()"></p>
                        </div>

                        <div class="relative w-full md:w-80 group">
                            <input type="text" x-model="searchQuery" placeholder="Search model or spec..." 
                                class="w-full pl-12 pr-6 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold focus:outline-none focus:ring-4 focus:ring-red-50 transition-all shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-[#c00000] transition-colors">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Model / Specification</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">School Count</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Total Qty</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(modelData, modelName) in filteredModels" :key="modelName">
                                <tbody class="border-b border-slate-100" x-data="{ expanded: false }">
                                    <tr class="hover:bg-slate-50/50 transition-colors cursor-pointer group" @click="expanded = !expanded">
                                        <td class="px-8 py-6">
                                            <div class="flex items-center gap-3">
                                                <div class="p-2 bg-slate-100 rounded-lg group-hover:bg-[#c00000] group-hover:text-white transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4" :class="expanded ? 'rotate-180' : ''">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                    </svg>
                                                </div>
                                                <p class="font-bold text-slate-800 text-base" x-text="modelName"></p>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 text-center">
                                            <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-black uppercase">
                                                <span x-text="modelData.length"></span> Recipient Schools
                                            </span>
                                        </td>
                                        <td class="px-8 py-6 text-center">
                                            <span class="font-black text-slate-800 text-lg" x-text="sumModelQty(modelData)"></span>
                                        </td>
                                    </tr>

                                    {{-- Expanded School List --}}
                                    <tr x-show="expanded" x-transition x-cloak class="bg-slate-50/30">
                                        <td colspan="3" class="px-8 py-6">
                                            <div class="bg-white rounded-3xl border border-slate-200 shadow-inner overflow-hidden">
                                                <table class="w-full">
                                                    <thead class="bg-slate-50/50 border-b border-slate-100">
                                                        <tr class="text-[9px] font-black text-slate-400 uppercase">
                                                            <th class="px-6 py-3 text-left">School Name</th>
                                                            <th class="px-6 py-3 text-center">Quantity</th>
                                                            <th class="px-6 py-3 text-right">Unit Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-50">
                                                        <template x-for="school in modelData" :key="school.name">
                                                            <tr class="text-sm hover:bg-slate-50/30 transition-colors">
                                                                <td class="px-6 py-3 font-bold text-slate-700" x-text="school.name"></td>
                                                                <td class="px-6 py-3 text-center font-black text-slate-900" x-text="school.qty"></td>
                                                                <td class="px-6 py-3 text-right">
                                                                    <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md border border-emerald-100">
                                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                                        <span x-text="school.status"></span>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        </template>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <script>
        function assetExplorer() {
            return {
                selectedCategory: null,
                selectedItem: null,
                searchQuery: '',
                
                inventory: {
                    "ICT Equipment": {
                        icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" /></svg>`,
                        items: {
                            "Laptop": {
                                "Dell Latitude 3420": [
                                    { name: "Zamboanga Central School", qty: 25, status: "Serviceable" },
                                    { name: "Tetuan Central School", qty: 20, status: "Serviceable" }
                                ],
                                "HP ProBook 440": [
                                    { name: "Ayala National HS", qty: 15, status: "Serviceable" },
                                    { name: "Don Pablo Lorenzo", qty: 15, status: "Serviceable" }
                                ]
                            },
                            "Projector": {
                                "Epson EB-X06": [
                                    { name: "Zamboanga Central School", qty: 5, status: "Serviceable" },
                                    { name: "Sta. Maria CS", qty: 5, status: "Serviceable" }
                                ]
                            }
                        }
                    },
                    "Furniture": {
                        icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>`,
                        items: {
                            "Armchair": {
                                "Plastic/Steel Hybrid": [
                                    { name: "Zamboanga Central School", qty: 300, status: "Serviceable" },
                                    { name: "Tetuan Central School", qty: 200, status: "Serviceable" }
                                ]
                            }
                        }
                    },
                    "Science Kits": {
                        icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v1.244c0 .892-.506 1.707-1.3 2.11L3.571 9.094c-.803.414-1.321 1.24-1.321 2.138v5.127c0 .937.545 1.79 1.403 2.179l5.42 2.454a2.25 2.25 0 001.754 0l5.42-2.454c.858-.389 1.403-1.242 1.403-2.179v-5.127c0-.898-.518-1.724-1.321-2.138L11.3 6.458a2.25 2.25 0 01-1.3-2.11V3.104c0-.422.355-.758.75-.758h.5c.395 0 .75.336.75.758v1.244c0 .892.506 1.707 1.3 2.11l4.879 2.54c.803.414 1.321 1.24 1.321 2.138v5.127c0 .937-.545 1.79-1.403 2.179l-5.42 2.454a2.25 2.25 0 01-1.754 0l-5.42-2.454c-.858-.389-1.403-1.242-1.403-2.179v-5.127c0-.898.518-1.724 1.321-2.138l4.879-2.54a2.25 2.25 0 001.3-2.11V3.104c0-.422-.355-.758-.75-.758h-.5c-.395 0-.75.336-.75.758z" /></svg>`,
                        items: {
                            "Microscope": {
                                "Digital Compound": [
                                    { name: "Sta. Maria National HS", qty: 10, status: "Serviceable" }
                                ]
                            }
                        }
                    },
                    "Sports Equip.": {
                        icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" /></svg>`,
                        items: {
                            "Volleyball": {
                                "Mikasa MVA200": [
                                    { name: "Ayala Central School", qty: 50, status: "Serviceable" }
                                ]
                            }
                        }
                    }
                },

                selectCategory(cat) {
                    this.selectedCategory = cat;
                    this.selectedItem = null;
                    this.searchQuery = '';
                },

                selectItem(item) {
                    this.selectedItem = item;
                    this.searchQuery = '';
                    setTimeout(() => {
                        document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth' });
                    }, 100);
                },

                sumModelQty(modelData) {
                    return modelData.reduce((sum, school) => sum + school.qty, 0);
                },

                calculateOverallTotal() {
                    if (!this.selectedItem) return 0;
                    let models = this.inventory[this.selectedCategory].items[this.selectedItem];
                    let total = 0;
                    Object.values(models).forEach(schoolList => {
                        total += schoolList.reduce((sum, s) => sum + s.qty, 0);
                    });
                    return total;
                },

                get filteredModels() {
                    if (!this.selectedCategory || !this.selectedItem) return {};
                    let models = this.inventory[this.selectedCategory].items[this.selectedItem];
                    
                    if (this.searchQuery.trim() !== '') {
                        let filtered = {};
                        Object.keys(models).forEach(key => {
                            if (key.toLowerCase().includes(this.searchQuery.toLowerCase())) {
                                filtered[key] = models[key];
                            }
                        });
                        return filtered;
                    }
                    return models;
                }
            }
        }
    </script>
</body>
</html>