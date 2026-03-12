<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Explorer | DepEd ZC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .category-btn:hover { transform: translateY(-5px); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-800">

    @include('partials.sidebar')

    <main class="flex-grow p-6 lg:p-10 h-screen overflow-y-auto">
        <header class="mb-10">
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Asset Explorer</h2>
            <p class="text-slate-500 text-sm mt-1 font-medium italic">Filter and browse inventory by school and category</p>
        </header>

        <section class="mb-8">
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-2 block">Primary Filter: Select School</label>
                <select id="schoolFilter" class="w-full md:w-1/3 px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-lg font-bold focus:outline-none focus:ring-4 focus:ring-blue-50 transition-all cursor-pointer">
                    <option value="">-- Choose a School --</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                    @endforeach
                </select>
            </div>
        </section>

        <section id="categorySection" class="mb-10 hidden">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                Available Categories for this School
                <span class="h-[1px] flex-grow bg-slate-200"></span>
            </h3>
            <div id="categoryContainer" class="grid grid-cols-2 md:grid-cols-4 gap-6">
                </div>
        </section>

        <section id="itemsSection" class="hidden">
            <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-50 overflow-hidden">
                <div class="p-8 border-b border-slate-50 bg-slate-50/30">
                    <h3 id="selectedCategoryTitle" class="text-xl font-extrabold text-slate-800 italic">Items List</h3>
                </div>
                <div id="itemsTableContainer" class="p-0">
                    </div>
            </div>
        </section>
    </main>

   <script>
    const schoolFilter = document.getElementById('schoolFilter');
    const categorySection = document.getElementById('categorySection');
    const categoryContainer = document.getElementById('categoryContainer');
    const itemsSection = document.getElementById('itemsSection');

    // TEMPORARY MOCK DATA
    // This simulates what your database will eventually return
    const mockData = {
        "1": { // School ID 1
            name: "Zamboanga Central School",
            categories: ["DCP Package", "Furniture"],
            items: {
                "DCP Package": [
                    { name: "Laptop", model: "Dell Latitude 3420", sn: "SN-ZC-001", qty: 15, status: "SERVICEABLE" },
                    { name: "Projector", model: "Epson EB-X06", sn: "SN-ZC-002", qty: 2, status: "SERVICEABLE" }
                ],
                "Furniture": [
                    { name: "Armchair", model: "Plastic/Steel", sn: "N/A", qty: 45, status: "GOOD CONDITION" }
                ]
            }
        },
        "2": { // School ID 2
            name: "Tetuan Central School",
            categories: ["Science Kit", "DCP Package"],
            items: {
                "Science Kit": [
                    { name: "Microscope", model: "Compound Digital", sn: "SK-TET-01", qty: 5, status: "SERVICEABLE" }
                ],
                "DCP Package": [
                    { name: "Tablet", model: "Huawei MatePad", sn: "T-992-ZC", qty: 30, status: "SERVICEABLE" }
                ]
            }
        }
    };

    // 1. Handle School Selection
    schoolFilter.addEventListener('change', function() {
        const schoolId = this.value;
        categoryContainer.innerHTML = '';
        itemsSection.classList.add('hidden');

        if (schoolId && mockData[schoolId]) {
            categorySection.classList.remove('hidden');
            
            // Get categories specific to this school
            const categories = mockData[schoolId].categories;
            
            categoryContainer.innerHTML = categories.map(cat => `
                <button onclick="loadItems('${schoolId}', '${cat}')" class="category-btn group bg-white p-8 rounded-[2rem] border border-slate-100 shadow-lg shadow-slate-200/50 hover:border-blue-400 hover:bg-blue-50 transition-all duration-300 text-left">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform text-xl">📦</div>
                    <h4 class="font-extrabold text-slate-800 text-lg leading-tight">${cat}</h4>
                    <p class="text-[10px] font-bold text-slate-400 uppercase mt-1">Click to view items</p>
                </button>
            `).join('');
        } else {
            categorySection.classList.add('hidden');
        }
    });

    // 2. Handle Category Click (Load Items)
    function loadItems(schoolId, categoryName) {
        itemsSection.classList.remove('hidden');
        document.getElementById('selectedCategoryTitle').textContent = categoryName + " - " + mockData[schoolId].name;
        
        const items = mockData[schoolId].items[categoryName] || [];
        const container = document.getElementById('itemsTableContainer');
        
        container.innerHTML = `
            <table class="w-full text-left">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase">Item/Sub-Item</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase">Serial No.</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase text-center">Qty</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    ${items.map(item => `
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-8 py-5">
                                <p class="font-bold text-slate-800 text-sm">${item.name}</p>
                                <p class="text-[10px] text-slate-400 font-medium uppercase">${item.model}</p>
                            </td>
                            <td class="px-8 py-5 text-sm font-mono text-slate-500">${item.sn}</td>
                            <td class="px-8 py-5 text-center font-bold">${item.qty}</td>
                            <td class="px-8 py-5">
                                <span class="px-3 py-1 ${item.status === 'SERVICEABLE' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700'} rounded-full text-[10px] font-bold lowercase first-letter:uppercase">
                                    ${item.status}
                                </span>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
        
        // Auto-scroll to table for better UX
        itemsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
</script>
</body>
</html>