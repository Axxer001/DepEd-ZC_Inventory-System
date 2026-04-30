<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipients Explorer | DepEd ZC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .nav-card:hover { transform: translateY(-5px); }
        [x-cloak] { display: none !important; }
        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e9d5ff; border-radius: 10px; }
        .back-btn-hover:hover { transform: translateX(-5px); border-color: #9333ea; color: #9333ea; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-800" x-data="recipientExplorer()">

    @include('partials.sidebar')

    <main class="flex-grow p-6 lg:p-10 h-screen overflow-y-auto custom-scroll">
        <header class="mb-10">
            <a href="{{ route('recipients.index') }}" class="back-btn-hover inline-flex items-center gap-2 px-4 py-2 mb-4 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-500 transition-all w-fit shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back to Menu
            </a>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight italic uppercase">School Asset Explorer</h2>
            <p class="text-slate-500 text-sm mt-1 font-medium italic">Track and audit assets deployed to specific schools and rooms</p>
        </header>

        {{-- Step 1: School Selection --}}
        <section class="mb-8">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                Step 1: Select Stakeholder Institution
                <span class="h-[1px] flex-grow bg-slate-200"></span>
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <template x-for="(data, schoolName) in schools" :key="schoolName">
                    <button @click="selectSchool(schoolName)" 
                        :class="selectedSchool === schoolName ? 'border-purple-500 bg-purple-50 ring-4 ring-purple-500/10' : 'bg-white border-slate-100'"
                        class="nav-card group p-8 rounded-[2rem] border shadow-lg shadow-slate-200/50 transition-all duration-300 text-left">
                        
                        <div :class="selectedSchool === schoolName ? 'bg-purple-600 text-white' : 'bg-slate-50 text-slate-400'"
                             class="w-12 h-12 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-sm">
                             <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A4.833 4.833 0 0 0 12 9a4.833 4.833 0 0 0-7.5 1.332V21m15 0h-15" />
                             </svg>
                        </div>
                        
                        <h4 class="font-extrabold text-slate-800 text-lg leading-tight uppercase tracking-tighter" x-text="schoolName"></h4>
                        <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase" x-text="`${data.rooms.length} Rooms Tracked`"></p>
                    </button>
                </template>
            </div>
        </section>

        {{-- Step 2: Room Selection --}}
        <section class="mb-10" x-show="selectedSchool" x-transition x-cloak>
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 ml-1">Step 2: Choose Room or Office</h3>
            <div class="flex flex-wrap gap-3">
                <template x-for="room in schools[selectedSchool]?.rooms || []" :key="room.name">
                    <button @click="selectRoom(room.name)"
                        :class="selectedRoom === room.name ? 'bg-purple-600 text-white shadow-lg' : 'bg-white text-slate-600 border-slate-200 hover:border-purple-500'"
                        class="px-6 py-3 rounded-2xl border font-bold text-sm transition-all" 
                        x-text="room.name">
                    </button>
                </template>
            </div>
        </section>

        {{-- Results Table --}}
        <section id="resultsSection" x-show="selectedRoom" x-transition x-cloak>
            <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 overflow-hidden">
                
                <div class="p-8 border-b border-slate-50 bg-slate-50/30 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                            <span class="text-purple-600">🏫</span>
                            <span x-text="`${selectedRoom} - ${selectedSchool}`"></span>
                        </h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">On-hand Inventory Assets</p>
                    </div>
                    
                    <div class="relative w-full md:w-64 group">
                        <input type="text" x-model="searchQuery" placeholder="Search assigned asset..." 
                            class="w-full pl-12 pr-6 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold focus:outline-none focus:ring-4 focus:ring-purple-50 transition-all shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-purple-500 transition-colors">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Asset Name / Description</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Qty on Hand</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="asset in filteredAssets" :key="asset.name">
                                <tr class="hover:bg-purple-50/30 transition-colors group">
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-2 h-2 rounded-full bg-purple-400"></div>
                                            <p class="font-bold text-slate-800 text-base" x-text="asset.name"></p>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <span :class="asset.status === 'Serviceable' ? 'text-emerald-600 bg-emerald-50 border-emerald-100' : 'text-red-600 bg-red-50 border-red-100'" 
                                              class="px-3 py-1 border rounded-lg text-[9px] font-black uppercase tracking-widest" 
                                              x-text="asset.status"></span>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <span class="font-black text-slate-800 text-lg" x-text="asset.qty"></span>
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
        function recipientExplorer() {
            return {
                selectedSchool: null,
                selectedRoom: null,
                searchQuery: '',
                
                schools: {
                    "Ayala National High School": {
                        rooms: [
                            { name: "ICT Laboratory 1", assets: [{name: "Dell Latitude 3420", qty: 25, status: "Serviceable"}, {name: "Epson Projector", qty: 1, status: "Serviceable"}] },
                            { name: "Science Lab", assets: [{name: "Microscope Kit", qty: 10, status: "Serviceable"}] },
                            { name: "Admin Office", assets: [{name: "Desktop PC", qty: 2, status: "Unserviceable"}] }
                        ]
                    },
                    "Zamboanga Central School": {
                        rooms: [
                            { name: "Library", assets: [{name: "Reading Tables", qty: 10, status: "Serviceable"}] },
                            { name: "Principal's Office", assets: [{name: "Executive Chair", qty: 1, status: "Serviceable"}] }
                        ]
                    }
                },

                selectSchool(school) {
                    this.selectedSchool = school;
                    this.selectedRoom = null;
                },

                selectRoom(room) {
                    this.selectedRoom = room;
                    setTimeout(() => {
                        document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth' });
                    }, 100);
                },

                get filteredAssets() {
                    if (!this.selectedSchool || !this.selectedRoom) return [];
                    const roomData = this.schools[this.selectedSchool].rooms.find(r => r.name === this.selectedRoom);
                    const assets = roomData ? roomData.assets : [];
                    
                    if (this.searchQuery.trim() !== '') {
                        return assets.filter(a => a.name.toLowerCase().includes(this.searchQuery.toLowerCase()));
                    }
                    return assets;
                }
            }
        }
    </script>
</body>
</html>