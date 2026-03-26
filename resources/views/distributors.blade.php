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
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight italic uppercase">Distributors & Suppliers</h2>
                </div>
                <a href="{{ route('inventory.setup') }}" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-400 hover:text-orange-600 hover:border-orange-200 transition-all shadow-sm flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Back to Setup
                </a>
            </header>

            {{-- Filter Section --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                {{-- Dropdown 1 --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Select Organization</label>
                    <select id="orgSelect" onchange="updatePersonnel()" class="w-full px-5 py-4 bg-white border border-slate-200 rounded-[1.5rem] text-sm font-bold focus:outline-none focus:ring-4 focus:ring-orange-50 cursor-pointer shadow-sm transition-all appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222.5%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpath%20d%3D%22M6%209l6%206%206-6%22%3E%3C%2Fpath%3E%3C%2Fsvg%3E')] bg-[length:1.25rem] bg-[right_1.25rem_center] bg-no-repeat">
                        <option value="">-- Choose Organization --</option>
                        <option value="office">Division Office</option>
                        <option value="supply">Supply Section</option>
                        <option value="ngo">External Partners (NGO)</option>
                    </select>
                </div>

                {{-- Dropdown 2 --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Specific Personnel</label>
                    <select id="personSelect" onchange="dummyLoadTable()" disabled class="w-full px-5 py-4 bg-white border border-slate-200 rounded-[1.5rem] text-sm font-bold focus:outline-none focus:ring-4 focus:ring-orange-50 cursor-pointer shadow-sm transition-all disabled:opacity-50 disabled:bg-slate-50 appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%20fill%3D%22none%22%20stroke%3D%22%23cbd5e1%22%20stroke-width%3D%222.5%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpath%20d%3D%22M6%209l6%206%206-6%22%3E%3C%2Fpath%3E%3C%2Fsvg%3E')] bg-[length:1.25rem] bg-[right_1.25rem_center] bg-no-repeat">
                        <option value="">-- Select Org First --</option>
                    </select>
                </div>

                {{-- Search Bar --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Search Records</label>
                    <div class="relative">
                        <input type="text" id="tableSearch" onkeyup="searchTable()" placeholder="Search item, date, or school..." class="w-full pl-12 pr-4 py-4 bg-white border border-slate-200 rounded-[1.5rem] text-sm font-semibold focus:outline-none focus:ring-4 focus:ring-orange-50 shadow-sm transition-all">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Table Section --}}
            <section id="tableContainer" class="hidden animate-fade-in">
                <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-50 overflow-hidden">
                    <div class="p-8 border-b border-slate-50 flex items-center gap-4 bg-orange-50/20">
                        <div class="p-3.5 bg-orange-600 text-white rounded-2xl shadow-lg shadow-orange-200">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.129-1.125V11.25c0-4.446-3.604-8.1-8.1-8.1H9a8.1 8.1 0 0 0-8.1 8.1v3.375c0 .621.504 1.125 1.125 1.125H3.375M9 15h3.375M9 15V3.375M9 15h3.375M9 15h3.375" />
                            </svg>
                        </div>
                        <div>
                            <h3 id="displayName" class="font-extrabold text-slate-800 text-xl leading-tight tracking-tight uppercase italic">--</h3>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Inventory Distribution Logs</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50">
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.1em]">Date Provided</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.1em]">Item / Description</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.1em]">Recipient School</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.1em] text-center">Qty</th>
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
            
            tbody.innerHTML = `
                <tr class="hover:bg-slate-50/50 transition-colors group">
                    <td class="px-8 py-5 text-xs font-bold text-slate-500">Mar 25, 2026</td>
                    <td class="px-8 py-5">
                        <span class="block text-sm font-bold text-slate-700 group-hover:text-orange-600 transition-colors">Dell Latitude 3420</span>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wide">Laptops / Electronics</span>
                    </td>
                    <td class="px-8 py-5 text-sm font-semibold text-slate-600 italic">Ayala National High School</td>
                    <td class="px-8 py-5 text-center">
                        <span class="px-4 py-1.5 bg-slate-100 rounded-lg text-sm font-black text-slate-800">10</span>
                    </td>
                </tr>
                <tr class="hover:bg-slate-50/50 transition-colors group">
                    <td class="px-8 py-5 text-xs font-bold text-slate-500">Mar 20, 2026</td>
                    <td class="px-8 py-5">
                        <span class="block text-sm font-bold text-slate-700 group-hover:text-orange-600 transition-colors">Smart TV 55"</span>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wide">Multimedia</span>
                    </td>
                    <td class="px-8 py-5 text-sm font-semibold text-slate-600 italic">Zamboanga Central School</td>
                    <td class="px-8 py-5 text-center">
                        <span class="px-4 py-1.5 bg-slate-100 rounded-lg text-sm font-black text-slate-800">5</span>
                    </td>
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