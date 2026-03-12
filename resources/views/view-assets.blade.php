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
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-800" x-data="assetExplorer()">

    @include('partials.sidebar')

    <main class="flex-grow p-6 lg:p-10 h-screen overflow-y-auto">
        <header class="mb-10">
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Asset Explorer</h2>
            <p class="text-slate-500 text-sm mt-1 font-medium italic">Select a category and item to see distribution across all schools</p>
        </header>

        <section class="mb-8">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                Step 1: Select Category
                <span class="h-[1px] flex-grow bg-slate-200"></span>
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <template x-for="(data, catName) in inventory" :key="catName">
                    <button @click="selectCategory(catName)" 
                        :class="selectedCategory === catName ? 'border-blue-500 bg-blue-50 ring-4 ring-blue-500/10' : 'bg-white border-slate-100'"
                        class="category-btn group p-8 rounded-[2rem] border shadow-lg shadow-slate-200/50 transition-all duration-300 text-left">
                        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform text-xl" x-text="data.icon"></div>
                        <h4 class="font-extrabold text-slate-800 text-lg leading-tight" x-text="catName"></h4>
                        <p class="text-[10px] font-bold text-slate-400 uppercase mt-1" x-text="selectedCategory === catName ? 'Selected' : 'Click to view items'"></p>
                    </button>
                </template>
            </div>
        </section>

        <section class="mb-10" x-show="selectedCategory" x-transition x-cloak>
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 ml-1">Step 2: Choose Specific Item</h3>
            <div class="flex flex-wrap gap-3">
                <template x-for="itemName in Object.keys(inventory[selectedCategory]?.items || {})" :key="itemName">
                    <button @click="selectItem(itemName)"
                        :class="selectedItem === itemName ? 'bg-slate-900 text-white shadow-lg' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-400'"
                        class="px-6 py-3 rounded-2xl border font-bold text-sm transition-all" 
                        x-text="itemName">
                    </button>
                </template>
            </div>
        </section>

        <section id="resultsSection" x-show="selectedItem" x-transition x-cloak>
            <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 overflow-hidden">
                
                <div class="p-8 border-b border-slate-50 bg-slate-50/30 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-800 italic" x-text="`${selectedItem} - Model Inventory` "></h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Division Wide Summary</p>
                    </div>
                    
                    <div class="flex items-center gap-6">
                        <div class="text-right hidden md:block">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Overall Items Total</p>
                            <p class="text-2xl font-black text-blue-600" x-text="calculateOverallTotal()"></p>
                        </div>

                        <div class="relative w-full md:w-80">
                            <input type="text" x-model="searchQuery" placeholder="Filter models..." 
                                class="w-full pl-12 pr-6 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold focus:outline-none focus:ring-4 focus:ring-blue-50 transition-all shadow-sm">
                            <span class="absolute left-5 top-4.5 opacity-20">🔍</span>
                        </div>
                    </div>
                </div>

                <div class="p-0">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Model / Specification (Sub-Item)</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Schools Have</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Total Quantity</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(modelData, modelName) in filteredModels" :key="modelName">
                                <tbody class="border-b border-slate-100" x-data="{ expanded: false }">
                                    <tr class="hover:bg-slate-50/50 transition-colors cursor-pointer" @click="expanded = !expanded">
                                        <td class="px-8 py-6">
                                            <div class="flex items-center gap-3">
                                                <div class="w-2 h-2 rounded-full bg-blue-500" :class="expanded ? 'animate-pulse' : ''"></div>
                                                <p class="font-bold text-slate-800 text-base" x-text="modelName"></p>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 text-center">
                                            <button class="px-4 py-2 bg-blue-50 text-blue-700 rounded-xl font-black text-xs hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                                <span x-text="modelData.length"></span> Schools
                                            </button>
                                        </td>
                                        <td class="px-8 py-6 text-center">
                                            <span class="inline-block px-4 py-1 bg-slate-100 rounded-lg font-black text-slate-700 text-lg" x-text="sumModelQty(modelData)"></span>
                                        </td>
                                    </tr>

                                    <tr x-show="expanded" x-transition x-cloak class="bg-slate-50/30">
                                        <td colspan="3" class="px-8 py-6">
                                            <div class="bg-white rounded-3xl border border-slate-100 shadow-inner overflow-hidden">
                                                <table class="w-full">
                                                    <thead class="bg-slate-50/50">
                                                        <tr class="text-[9px] font-black text-slate-400 uppercase">
                                                            <th class="px-6 py-3">Recipient School</th>
                                                            <th class="px-6 py-3 text-center">Quantity</th>
                                                            <th class="px-6 py-3 text-right">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-50">
                                                        <template x-for="school in modelData" :key="school.name">
                                                            <tr class="text-sm">
                                                                <td class="px-6 py-3 font-bold text-slate-700" x-text="school.name"></td>
                                                                <td class="px-6 py-3 text-center font-black text-blue-600" x-text="school.qty"></td>
                                                                <td class="px-6 py-3 text-right">
                                                                    <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md" x-text="school.status"></span>
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
                
                // RESTRUCTURED DATA: Model -> List of Schools
                inventory: {
                    "ICT Equipment": {
                        icon: "💻",
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
                        icon: "🪑",
                        items: {
                            "Armchair": {
                                "Plastic/Steel Hybrid": [
                                    { name: "Zamboanga Central School", qty: 300, status: "Serviceable" },
                                    { name: "Tetuan Central School", qty: 200, status: "Serviceable" }
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