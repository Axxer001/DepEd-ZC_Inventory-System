<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distributors | DepEd ZC</title>
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
                        <span>Logistics</span>
                        <span>/</span>
                        <span class="text-orange-600">Distributors Masterlist</span>
                    </div>
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Distributors & Suppliers</h2>
                </div>
                <a href="{{ route('inventory.setup') }}" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-400 hover:text-orange-600 hover:border-orange-200 transition-all shadow-sm">
                    « Back to Setup
                </a>
            </header>

            {{-- Filter Section --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                {{-- Dropdown 1 --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Select Organization</label>
                    <select id="orgSelect" onchange="updatePersonnel()" class="w-full px-5 py-4 bg-white border border-slate-200 rounded-[1.5rem] text-sm font-bold focus:outline-none focus:ring-4 focus:ring-orange-50 cursor-pointer shadow-sm transition-all">
                        <option value="">-- Choose Organization --</option>
                        <option value="office">Division Office</option>
                        <option value="supply">Supply Section</option>
                        <option value="ngo">External Partners (NGO)</option>
                    </select>
                </div>

                {{-- Dropdown 2 --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Specific Personnel</label>
                    <select id="personSelect" onchange="dummyLoadTable()" disabled class="w-full px-5 py-4 bg-white border border-slate-200 rounded-[1.5rem] text-sm font-bold focus:outline-none focus:ring-4 focus:ring-orange-50 cursor-pointer shadow-sm transition-all disabled:opacity-50 disabled:bg-slate-50">
                        <option value="">-- Select Org First --</option>
                    </select>
                </div>

                {{-- Search Bar --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Search Records</label>
                    <div class="relative">
                        <input type="text" id="tableSearch" onkeyup="searchTable()" placeholder="Search item, date, or school..." class="w-full pl-12 pr-4 py-4 bg-white border border-slate-200 rounded-[1.5rem] text-sm font-semibold focus:outline-none focus:ring-4 focus:ring-orange-50 shadow-sm transition-all">
                        <span class="absolute left-5 top-[1.1rem] opacity-30">🔍</span>
                    </div>
                </div>
            </div>

            {{-- Table Section --}}
            <section id="tableContainer" class="hidden animate-fade-in">
                <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-50 overflow-hidden">
                    <div class="p-8 border-b border-slate-50 flex items-center gap-3 bg-orange-50/20">
                        <div class="p-3 bg-orange-600 text-white rounded-2xl shadow-lg shadow-orange-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <div>
                            <h3 id="displayName" class="font-extrabold text-slate-800 text-xl leading-tight">--</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Inventory Provided Records</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50/50">
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date Provided</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Item / Description</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Recipient School</th>
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
        const dummyPersonnel = {
            "office": ["Juan Dela Cruz", "Maria Clara"],
            "supply": ["Pedro Penduko", "Gabriela Silang"],
            "ngo": ["Save the Children Admin", "Red Cross Logistics"]
        };

        function updatePersonnel() {
            const org = document.getElementById('orgSelect').value;
            const personSelect = document.getElementById('personSelect');
            personSelect.innerHTML = '<option value="">-- All Personnel --</option>';
            
            if (org) {
                personSelect.disabled = false;
                dummyPersonnel[org].forEach(p => personSelect.innerHTML += `<option value="${p}">${p}</option>`);
                dummyLoadTable();
            } else {
                personSelect.disabled = true;
                document.getElementById('tableContainer').classList.add('hidden');
            }
        }

        function dummyLoadTable() {
            const container = document.getElementById('tableContainer');
            const tbody = document.getElementById('tableBody');
            const person = document.getElementById('personSelect').value;
            const orgText = document.getElementById('orgSelect').options[document.getElementById('orgSelect').selectedIndex].text;
            
            container.classList.remove('hidden');
            document.getElementById('displayName').innerText = person || orgText;
            
            // Dummy Data Rows
            tbody.innerHTML = `
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-8 py-4 text-xs font-bold text-slate-500">2026-03-25</td>
                    <td class="px-8 py-4">
                        <span class="block text-sm font-bold text-slate-700">Dell Latitude 3420</span>
                        <span class="text-[10px] text-orange-600 font-bold uppercase">Laptops / Electronics</span>
                    </td>
                    <td class="px-8 py-4 text-sm font-semibold text-slate-600">Ayala National High School</td>
                    <td class="px-8 py-4 text-sm font-black text-center text-slate-800">10</td>
                </tr>
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-8 py-4 text-xs font-bold text-slate-500">2026-03-20</td>
                    <td class="px-8 py-4">
                        <span class="block text-sm font-bold text-slate-700">Smart TV 55"</span>
                        <span class="text-[10px] text-orange-600 font-bold uppercase">Multimedia</span>
                    </td>
                    <td class="px-8 py-4 text-sm font-semibold text-slate-600">Zamboanga Central School</td>
                    <td class="px-8 py-4 text-sm font-black text-center text-slate-800">5</td>
                </tr>
            `;
        }

        function searchTable() {
            const input = document.getElementById("tableSearch");
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