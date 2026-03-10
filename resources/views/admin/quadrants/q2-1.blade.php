<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quadrant 2.1 | DepEd ZC</title>
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
                    <span>LD 2</span>
                    <span>/</span>
                    <span class="text-green-600">Quadrant 2.1</span>
                </div>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Asset Distribution</h2>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Select District</label>
                    <select id="districtSelect" onchange="updateSchools()" class="w-full px-5 py-4 bg-white border border-slate-200 rounded-[1.5rem] text-sm font-bold focus:outline-none focus:ring-4 focus:ring-blue-50 cursor-pointer shadow-sm transition-all">
                        <option value="">-- Choose District --</option>
                        <option value="ayala">Ayala District</option>
                        <option value="labuan">Labuan District</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Select School</label>
                    <select id="schoolSelect" onchange="showItems()" disabled class="w-full px-5 py-4 bg-white border border-slate-200 rounded-[1.5rem] text-sm font-bold focus:outline-none focus:ring-4 focus:ring-blue-50 cursor-pointer shadow-sm transition-all disabled:opacity-50 disabled:bg-slate-100">
                        <option value="">-- Select District First --</option>
                    </select>
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
        // Fake Data Structure
        const data = {
            "ayala": {
                "Ayala NHS": [
                    { name: "Chair", sub: "Plastic (Monoblock)", cat: "School Furniture", qty: 150 },
                    { name: "Smart TV", sub: "65-inch Crystal UHD", cat: "DCP Package", qty: 2 },
                    { name: "Laptop", sub: "Dell Latitude 3420", cat: "DCP Package", qty: 45 }
                ],
                "Ayala Central School": [
                    { name: "Table", sub: "Wooden Teachers Table", cat: "School Furniture", qty: 12 },
                    { name: "Projector", sub: "Epson EB-X06", cat: "DCP Package", qty: 5 }
                ]
            },
            "labuan": {
                "Labuan NHS": [
                    { name: "Armchair", sub: "Wood with Metal Frame", cat: "School Furniture", qty: 200 },
                    { name: "Desktop", sub: "HP ProDesk 400", cat: "DCP Package", qty: 30 }
                ]
            }
        };

        function updateSchools() {
            const district = document.getElementById('districtSelect').value;
            const schoolSelect = document.getElementById('schoolSelect');
            
            schoolSelect.innerHTML = '<option value="">-- Choose School --</option>';
            document.getElementById('tableContainer').classList.add('hidden');

            if (district && data[district]) {
                schoolSelect.disabled = false;
                Object.keys(data[district]).forEach(school => {
                    schoolSelect.innerHTML += `<option value="${school}">${school}</option>`;
                });
            } else {
                schoolSelect.disabled = true;
            }
        }

        function showItems() {
            const district = document.getElementById('districtSelect').value;
            const school = document.getElementById('schoolSelect').value;
            const tbody = document.getElementById('itemsTableBody');
            const container = document.getElementById('tableContainer');

            if (school) {
                document.getElementById('displaySchoolName').innerText = school;
                tbody.innerHTML = '';
                
                data[district][school].forEach(item => {
                    tbody.innerHTML += `
                        <tr class="hover:bg-slate-50/80 transition-all cursor-default group">
                            <td class="px-8 py-4 font-extrabold text-slate-800 text-sm uppercase">${item.name}</td>
                            <td class="px-8 py-4 text-xs font-semibold text-slate-500 italic">${item.sub}</td>
                            <td class="px-8 py-4">
                                <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter border border-blue-100">${item.cat}</span>
                            </td>
                            <td class="px-8 py-4 text-center">
                                <span class="text-lg font-black text-slate-800">${item.qty}</span>
                            </td>
                        </tr>
                    `;
                });

                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }
    </script>
</body>
</html>