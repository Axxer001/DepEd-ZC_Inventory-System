<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quadrant 1.2 | DepEd ZC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .animate-fade-in { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex animate-fade-in text-slate-800">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto">
        <main class="p-6 lg:p-10">
            
            <header class="mb-8">
                <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">
                    <span>LD 1</span>
                    <span>/</span>
                    <span class="text-blue-600">Quadrant 1.2</span>
                </div>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Asset Distribution</h2>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Select District</label>
                    <select id="districtSelect" onchange="updateSchools()" class="w-full px-5 py-4 bg-white border border-slate-200 rounded-[1.5rem] text-sm font-bold focus:outline-none focus:ring-4 focus:ring-blue-50 cursor-pointer shadow-sm transition-all">
                        <option value="">-- Choose District --</option>
                        @foreach($districts as $district)
                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Select School</label>
                    <select id="schoolSelect" onchange="showItems()" disabled class="w-full px-5 py-4 bg-white border border-slate-200 rounded-[1.5rem] text-sm font-bold focus:outline-none focus:ring-4 focus:ring-blue-50 cursor-pointer shadow-sm transition-all disabled:opacity-50 disabled:bg-slate-100">
                        <option value="">-- Select District First --</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Search School</label>
                    <div class="relative" id="searchContainer">
                        <input type="text" id="searchInput" placeholder="Search school ID or name..." autocomplete="off" class="w-full pl-12 pr-4 py-4 bg-white border border-slate-200 rounded-[1.5rem] text-sm font-semibold focus:outline-none focus:ring-4 focus:ring-blue-50 shadow-sm transition-all">
                        <span class="absolute left-5 top-[1.1rem] opacity-30">🔍</span>
                        <ul id="searchResults" class="absolute z-30 w-full bg-white border border-slate-100 rounded-2xl shadow-xl mt-2 max-h-60 overflow-y-auto hidden custom-scrollbar"></ul>
                    </div>
                </div>
            </div>

            <section id="tableContainer" class="hidden animate-fade-in">
                <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/40 border border-slate-50 overflow-hidden">
                    <div class="p-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-blue-600 text-white rounded-2xl shadow-lg shadow-blue-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div>
                                <h3 id="displaySchoolName" class="font-extrabold text-slate-800 text-xl leading-tight">--</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Inventory Master List</p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50/50">
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Item Name</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Sub-Item / Description</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Category</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Quantity</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody" class="divide-y divide-slate-50">
                                </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        const schoolsByDistrict = @json($schoolsByDistrict);
        const allSchools = @json($allSchools);

        function updateSchools() {
            const districtId = document.getElementById('districtSelect').value;
            const schoolSelect = document.getElementById('schoolSelect');
            
            schoolSelect.innerHTML = '<option value="">-- Choose School --</option>';
            document.getElementById('tableContainer').classList.add('hidden');

            if (districtId && schoolsByDistrict[districtId]) {
                schoolSelect.disabled = false;
                schoolsByDistrict[districtId].forEach(school => {
                    schoolSelect.innerHTML += `<option value="${school}">${school}</option>`;
                });
            } else {
                schoolSelect.disabled = true;
            }
        }

        function showItems() {
            const school = document.getElementById('schoolSelect').value;
            const tbody = document.getElementById('itemsTableBody');
            const container = document.getElementById('tableContainer');

            if (school) {
                document.getElementById('displaySchoolName').innerText = school;
                tbody.innerHTML = '';
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }

        function selectSchoolFromSearch(districtId, schoolName) {
            const districtSelect = document.getElementById('districtSelect');
            districtSelect.value = districtId;
            updateSchools();

            const schoolSelect = document.getElementById('schoolSelect');
            schoolSelect.value = schoolName;
            showItems();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const searchResults = document.getElementById('searchResults');

            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                searchResults.innerHTML = '';

                if (query.length === 0) {
                    searchResults.classList.add('hidden');
                    return;
                }

                const filtered = allSchools.filter(s => {
                    return s.name.toLowerCase().includes(query) || (s.school_id && s.school_id.toString().includes(query));
                });

                if (filtered.length > 0) {
                    filtered.forEach(school => {
                        const li = document.createElement('li');
                        li.className = 'px-4 py-3 hover:bg-blue-50 cursor-pointer text-sm font-semibold text-slate-800 transition-colors border-b border-slate-50 last:border-0';
                        li.textContent = `${school.school_id} - ${school.name}`;
                        li.addEventListener('click', function() {
                            searchInput.value = school.name;
                            searchResults.classList.add('hidden');
                            selectSchoolFromSearch(school.district_id, school.name);
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

            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>