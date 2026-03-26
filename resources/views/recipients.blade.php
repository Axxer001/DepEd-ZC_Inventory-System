<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipients | DepEd ZC</title>
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
<body class="bg-slate-50 min-h-screen flex animate-fade-in text-slate-800 overflow-x-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto">
        <main class="p-6 lg:p-10">
            
            <header class="mb-8 flex justify-between items-end">
                <div>
                    <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">
                        <span>End-Users</span>
                        <span>/</span>
                        <span class="text-purple-600">Recipients Masterlist</span>
                    </div>
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Recipient Schools & Staff</h2>
                </div>
                <a href="{{ route('inventory.setup') }}" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-400 hover:text-purple-600 hover:border-purple-200 transition-all shadow-sm">
                    « Back to Setup
                </a>
            </header>

            {{-- Filter Section --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                {{-- District Dropdown --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Select District</label>
                    <select id="distSelect" onchange="updateSchools()" class="w-full px-5 py-4 bg-white border border-slate-200 rounded-[1.5rem] text-sm font-bold focus:outline-none focus:ring-4 focus:ring-purple-50 cursor-pointer shadow-sm transition-all">
                        <option value="">-- Choose District --</option>
                        <option value="central">Central District</option>
                        <option value="ayala">Ayala District</option>
                        <option value="vitali">Vitali District</option>
                    </select>
                </div>

                {{-- School Dropdown --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Specific School</label>
                    <select id="schoolSelect" onchange="dummyLoadTable()" disabled class="w-full px-5 py-4 bg-white border border-slate-200 rounded-[1.5rem] text-sm font-bold focus:outline-none focus:ring-4 focus:ring-purple-50 cursor-pointer shadow-sm transition-all disabled:opacity-50 disabled:bg-slate-50">
                        <option value="">-- Select District First --</option>
                    </select>
                </div>

                {{-- Search Bar --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Search Assets</label>
                    <div class="relative">
                        <input type="text" id="recipientSearch" onkeyup="filterRecipientTable()" placeholder="Search items or specifications..." class="w-full pl-12 pr-4 py-4 bg-white border border-slate-200 rounded-[1.5rem] text-sm font-semibold focus:outline-none focus:ring-4 focus:ring-purple-50 shadow-sm transition-all">
                        <span class="absolute left-5 top-[1.1rem] opacity-30">🔍</span>
                    </div>
                </div>
            </div>

            {{-- Table Section --}}
            <section id="tableContainer" class="hidden animate-fade-in">
                <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-50 overflow-hidden">
                    <div class="p-8 border-b border-slate-50 flex items-center gap-3 bg-purple-50/20">
                        <div class="p-3 bg-purple-600 text-white rounded-2xl shadow-lg shadow-purple-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        </div>
                        <div>
                            <h3 id="displayName" class="font-extrabold text-slate-800 text-xl leading-tight">--</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Inventory Received Records</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50/50">
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Received Date</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Item / Specification</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Condition</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Qty</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody" class="divide-y divide-slate-50">
                                {{-- Loaded via JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        const dummySchools = {
            "central": ["Zamboanga Central School", "Tetuan Elementary", "Sta. Maria CS"],
            "ayala": ["Ayala National High School", "Tulungatung ES", "Labuan NHS"],
            "vitali": ["Vitali NHS", "Tictapul ES"]
        };

        function updateSchools() {
            const dist = document.getElementById('distSelect').value;
            const schoolSelect = document.getElementById('schoolSelect');
            schoolSelect.innerHTML = '<option value="">-- All Schools --</option>';
            
            if (dist) {
                schoolSelect.disabled = false;
                dummySchools[dist].forEach(s => schoolSelect.innerHTML += `<option value="${s}">${s}</option>`);
                dummyLoadTable();
            } else {
                schoolSelect.disabled = true;
                document.getElementById('tableContainer').classList.add('hidden');
            }
        }

        function dummyLoadTable() {
            const container = document.getElementById('tableContainer');
            const tbody = document.getElementById('tableBody');
            const school = document.getElementById('schoolSelect').value;
            const distText = document.getElementById('distSelect').options[document.getElementById('distSelect').selectedIndex].text;
            
            container.classList.remove('hidden');
            document.getElementById('displayName').innerText = school || distText;
            
            tbody.innerHTML = `
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-8 py-4 text-xs font-bold text-slate-500">2026-03-24</td>
                    <td class="px-8 py-4">
                        <span class="block text-sm font-bold text-slate-700">Monoblock Chairs (White)</span>
                        <span class="text-[10px] text-purple-600 font-bold uppercase">Furniture / Fixtures</span>
                    </td>
                    <td class="px-8 py-4">
                        <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-black uppercase">Serviceable</span>
                    </td>
                    <td class="px-8 py-4 text-sm font-black text-center text-slate-800">50</td>
                </tr>
            `;
        }

        function filterRecipientTable() {
            const input = document.getElementById("recipientSearch");
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll("#tableBody tr");

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? "" : "none";
            });
        }
    </script>
</body>
</html>