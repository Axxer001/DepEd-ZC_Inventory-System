<!DOCTYPE html>
<html lang="en">
<head>

<!-- cd C:\Users\Admin\DepEd-ZC_Inventory\DepEd-ZC_Inventory-System-main -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | DepEd Zamboanga City</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Custom scrollbar for the entry section */
        .custom-scroll::-webkit-scrollbar { height: 4px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex animate-fade-in text-slate-800 overflow-x-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto">
        
        <header class="lg:hidden bg-white border-b border-slate-200 p-4 sticky top-0 z-30 flex items-center gap-4">
            <button onclick="toggleSidebar()" class="p-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-100 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/deped_logo.png') }}" class="h-6 w-auto">
                <span class="font-extrabold italic text-sm">DepEd ZC</span>
            </div>
        </header>

        <main class="p-6 lg:p-10">
            <header class="flex flex-col md:flex-row md:justify-between md:items-center mb-10 gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Welcome, Admin!</h2>
                    <p class="text-slate-500 text-sm mt-1 font-medium italic">Zamboanga City Division Asset Overview</p>
                </div>
                <div class="hidden sm:block text-right bg-white px-6 py-3 rounded-2xl shadow-sm border border-slate-100">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-tight">{{ now()->format('l') }}</p>
                    <p class="text-lg font-bold text-slate-800 tracking-tight">{{ now()->format('M d, Y') }}</p>
                </div>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <div class="group bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-50 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl cursor-default">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-4 bg-blue-50 text-blue-500 rounded-2xl text-2xl group-hover:scale-110 transition-transform">🏢</div>
                        <span class="bg-blue-100 text-blue-600 px-4 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest italic">Division Wide</span>
                    </div>
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total System Assets</h3>
                    <p class="text-5xl font-extrabold text-slate-800 tracking-tighter leading-none">0</p>
                </div>

                <div class="group bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-50 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl cursor-default">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-4 bg-emerald-50 text-emerald-600 rounded-2xl text-2xl group-hover:scale-110 transition-transform">✅</div>
                        <span class="bg-emerald-100 text-emerald-600 px-4 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest italic">Serviceable</span>
                    </div>
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Good Condition</h3>
                    <p class="text-5xl font-extrabold text-emerald-600 tracking-tighter leading-none">0</p>
                </div>

                <div class="group bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl cursor-default">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-4 bg-orange-50 text-orange-500 rounded-2xl text-2xl group-hover:scale-110 transition-transform">⚠️</div>
                        <span class="bg-orange-100 text-orange-600 px-4 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest italic">Unserviceable</span>
                    </div>
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Pasira / Damaged</h3>
                    <p class="text-5xl font-extrabold text-orange-500 tracking-tighter leading-none">0</p>
                </div>
            </div>

            <section class="mb-12">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/40 border border-slate-50 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-8 opacity-5">
                        <svg class="w-32 h-32 text-slate-900" fill="currentColor" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                    </div>
                    
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                        <div>
                            <h3 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                                <span class="bg-red-100 text-[#c00000] p-2 rounded-xl text-sm italic">New</span>
                                Quick Asset Entry
                            </h3>
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em] mt-1">Directly assign assets to schools</p>
                        </div>
                        
                        <div class="relative w-full md:w-72 group" id="searchContainer">
                            <input type="text" id="schoolSearch" placeholder="Search school..." autocomplete="off" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:outline-none focus:ring-4 focus:ring-red-50 transition-all font-semibold relative z-20">
                            <span class="absolute left-4 top-3.5 text-slate-300 group-focus-within:text-[#c00000] transition-colors z-20">🔍</span>
                            
                            <!-- Search Autocomplete Results -->
                            <ul id="searchResults" class="absolute z-30 w-full bg-white border border-slate-100 rounded-2xl shadow-xl mt-2 max-h-60 overflow-y-auto hidden custom-scroll">
                            </ul>
                        </div>
                    </div>

                    <form action="#" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-6">
                        <div class="space-y-2 relative">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Assigned School</label>
                            <select id="schoolSelect" class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:outline-none focus:border-red-200 cursor-pointer transition-all relative z-10">
                                <option value="">Select a School</option>
                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Main Category</label>
                            <select class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:outline-none focus:border-red-200 cursor-pointer transition-all">
                                <option>DCP Package</option>
                                <option>Classroom Furniture</option>
                                <option>Science Kit</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Item</label>
                            <select class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:outline-none focus:border-red-200 cursor-pointer transition-all">
                                <option>Laptop</option>
                                <option>Desktop</option>
                                <option>Printer</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Sub-Item</label>
                            <select class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:outline-none focus:border-red-200 cursor-pointer transition-all">
                                <option>Dell Latitude</option>
                                <option>HP ProBook</option>
                                <option>Acer TravelMate</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-3">
                            <div class="space-y-2 flex-grow">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Quantity</label>
                                <input type="number" value="1" min="1" class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:outline-none text-center">
                            </div>
                            <button type="submit" class="p-4 bg-[#c00000] text-white rounded-2xl font-bold hover:bg-red-700 shadow-lg shadow-red-200 transition-all hover:-translate-y-1 active:scale-95">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-7 h-7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <div class="flex items-center justify-between mb-8 px-2">
                <h3 class="text-xl font-extrabold text-slate-800 tracking-tight flex items-center gap-3">
                    <span class="w-2 h-8 bg-blue-600 rounded-full"></span>
                    District Summary Portfolio
                </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 hover:border-blue-200 transition-all duration-300 hover:-translate-y-1 cursor-pointer group">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-xl font-bold group-hover:scale-110 transition-transform tracking-tighter italic">1.1</div>
                        <div>
                            <h4 class="text-xl font-extrabold text-slate-800">Quadrant 1.1</h4>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest italic leading-tight">LD 1 • 3 Districts</p>
                        </div>
                    </div>
                    <div class="bg-slate-50 p-5 rounded-3xl border border-slate-100 shadow-inner group-hover:bg-blue-50/30 transition-colors">
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Total Quadrant Assets</p>
                        <p class="text-2xl font-extrabold text-slate-800 tracking-tighter leading-none">0</p>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 hover:border-blue-200 transition-all duration-300 hover:-translate-y-1 cursor-pointer group">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-xl font-bold group-hover:scale-110 transition-transform tracking-tighter italic">1.2</div>
                        <div>
                            <h4 class="text-xl font-extrabold text-slate-800">Quadrant 1.2</h4>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest italic leading-tight">LD 1 • 2 Districts</p>
                        </div>
                    </div>
                    <div class="bg-slate-50 p-5 rounded-3xl border border-slate-100 shadow-inner group-hover:bg-blue-50/30 transition-colors">
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Total Quadrant Assets</p>
                        <p class="text-2xl font-extrabold text-slate-800 tracking-tighter leading-none">0</p>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 hover:border-emerald-200 transition-all duration-300 hover:-translate-y-1 cursor-pointer group">
                    <div class="flex items-center gap-4 mb-6 text-emerald-600">
                        <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-xl font-bold group-hover:scale-110 transition-transform tracking-tighter italic">2.1</div>
                        <div class="text-slate-800">
                            <h4 class="text-xl font-extrabold">Quadrant 2.1</h4>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest italic leading-tight">LD 2 • 3 Districts</p>
                        </div>
                    </div>
                    <div class="bg-slate-50 p-5 rounded-3xl border border-slate-100 shadow-inner group-hover:bg-emerald-50/30 transition-colors">
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Total Quadrant Assets</p>
                        <p class="text-2xl font-extrabold text-slate-800 tracking-tighter leading-none">0</p>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 hover:border-emerald-200 transition-all duration-300 hover:-translate-y-1 cursor-pointer group">
                    <div class="flex items-center gap-4 mb-6 text-emerald-600">
                        <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-xl font-bold group-hover:scale-110 transition-transform tracking-tighter italic">2.2</div>
                        <div class="text-slate-800">
                            <h4 class="text-xl font-extrabold">Quadrant 2.2</h4>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest italic leading-tight">LD 2 • 4 Districts</p>
                        </div>
                    </div>
                    <div class="bg-slate-50 p-5 rounded-3xl border border-slate-100 shadow-inner group-hover:bg-emerald-50/30 transition-colors">
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Total Quadrant Assets</p>
                        <p class="text-2xl font-extrabold text-slate-800 tracking-tighter leading-none">0</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('schoolSearch');
            const searchResults = document.getElementById('searchResults');
            const schoolSelect = document.getElementById('schoolSelect');
            
            if (!searchInput || !schoolSelect || !searchResults) return;

            // Generate an array of objects from the select options
            const schools = Array.from(schoolSelect.options)
                .filter(opt => opt.value !== "")
                .map(opt => ({
                    id: opt.value,
                    name: opt.textContent
                }));

            // Handle typing in the search bar
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                searchResults.innerHTML = '';
                
                if (query.length === 0) {
                    searchResults.classList.add('hidden');
                    return;
                }

                // Filter schools based on query
                const filtered = schools.filter(school => school.name.toLowerCase().includes(query));
                
                if (filtered.length > 0) {
                    filtered.forEach(school => {
                        const li = document.createElement('li');
                        li.className = 'px-4 py-3 hover:bg-red-50 cursor-pointer text-sm font-semibold text-slate-800 transition-colors border-b border-slate-50 last:border-0';
                        li.textContent = school.name;
                        
                        // Handle clicking a search result
                        li.addEventListener('click', function() {
                            searchInput.value = school.name; // Set text into the input
                            schoolSelect.value = school.id; // Select the matching option in the dropdown
                            searchResults.classList.add('hidden'); // Hide the dropdown
                        });
                        
                        searchResults.appendChild(li);
                    });
                } else {
                    const li = document.createElement('li');
                    li.className = 'px-4 py-3 text-sm font-semibold text-slate-400 italic pointer-events-none';
                    li.textContent = 'No matching schools found';
                    searchResults.appendChild(li);
                }
                
                searchResults.classList.remove('hidden');
            });

            // Close the search dropdown if user clicks outside of it
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.add('hidden');
                }
            });

            // If the user manually chooses a school from the standard <select>, populate the search box
            schoolSelect.addEventListener('change', function() {
                if (this.value === "") {
                    searchInput.value = "";
                } else {
                    searchInput.value = this.options[this.selectedIndex].text;
                }
            });
        });
    </script>
</body>
</html>