<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Directory | DepEd ZC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* Entrance Animation */
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; transition: all 0.3s; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

        /* Smooth Transition for Hover */
        .table-row-transition { transition: all 0.2s ease-in-out; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex animate-fade-in text-slate-800 overflow-x-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto">
        
        <main class="p-6 lg:p-10">
            <header class="flex flex-col md:flex-row md:justify-between md:items-start mb-10 gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight text-red">School Registry</h2>
                    <p class="text-slate-500 text-sm mt-1 font-medium italic text-red">Zamboanga City Division Educational Institutions</p>
                </div>
                <button class="group bg-[#c00000] text-white px-8 py-4 rounded-[1.5rem] font-bold hover:bg-red-700 shadow-xl shadow-red-200 transition-all hover:-translate-y-1 flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 group-hover:rotate-90 transition-transform">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Register New School
                </button>
            </header>


            <section class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/40 border border-slate-50 overflow-hidden flex flex-col">
                
                <div class="p-8 border-b border-slate-50 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-3">
                        <span class="w-2 h-6 bg-[#c00000] rounded-full"></span>
                        <h3 class="font-extrabold text-slate-800 tracking-tight">Master List</h3>
                    </div>
                    
                    <div class="relative w-full md:w-80">
                        <input type="text" id="searchInput" placeholder="Search school ID or name..." class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-semibold focus:outline-none focus:ring-4 focus:ring-red-50 transition-all">
                        <span class="absolute left-4 top-4 opacity-30">🔍</span>
                    </div>
                </div>

                <div class="overflow-x-auto overflow-y-auto custom-scrollbar" style="max-height: 600px;">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead class="sticky top-0 z-10">
                            <tr class="bg-slate-50/90 backdrop-blur-md">
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">School ID</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Institutional Name</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">District Location</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center border-b border-slate-100">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="schoolTableBody" class="divide-y divide-slate-50">
                            </tbody>
                    </table>
                </div>

                <div class="p-8 bg-slate-50/30 flex items-center justify-between border-t border-slate-50">
                    <div>
                        <p id="paginationInfo" class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Showing 1 to 20 of 204 items</p>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <button onclick="prevPage()" id="prevBtn" class="p-3 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-[#c00000] hover:border-red-100 transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </button>

                        <div class="flex gap-1" id="pageNumbers">
                            </div>

                        <button onclick="nextPage()" id="nextBtn" class="p-3 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-[#c00000] hover:border-red-100 transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        let currentPage = 1;
        const itemsPerPage = 20;
        const totalItems = 204;
        const totalPages = Math.ceil(totalItems / itemsPerPage);

        function renderTable() {
            const tbody = document.getElementById('schoolTableBody');
            const start = (currentPage - 1) * itemsPerPage + 1;
            const end = Math.min(currentPage * itemsPerPage, totalItems);
            
            tbody.innerHTML = '';
            
            // Loop for current page items
            for(let i = 0; i < itemsPerPage; i++) {
                const currentIdx = ((currentPage - 1) * itemsPerPage) + i + 1;
                if (currentIdx > totalItems) break;

                const row = `
                    <tr class="group hover:bg-slate-50/80 transition-all table-row-transition cursor-default">
                        <td class="px-8 py-5">
                            <span class="font-black text-blue-600 bg-blue-50 px-4 py-2 rounded-xl text-xs tracking-tighter italic">12${1000 + currentIdx}</span>
                        </td>
                        <td class="px-8 py-5">
                            <div class="flex flex-col">
                                <span class="font-extrabold text-slate-800 group-hover:text-[#c00000] transition-colors uppercase text-sm">Example School Name ${currentIdx}</span>
                                <span class="text-[10px] font-bold text-slate-400 italic">DepEd Registered Institution</span>
                            </div>
                        </td>
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 bg-emerald-400 rounded-full shadow-[0_0_8px_rgba(52,211,153,0.6)]"></div>
                                <span class="font-bold text-slate-600 text-sm italic">District ${Math.ceil(currentIdx/10)}</span>
                            </div>
                        </td>
                        <td class="px-8 py-5">
                            <div class="flex justify-center items-center gap-2 opacity-0 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                                <button class="p-2 bg-slate-100 text-slate-500 rounded-lg hover:bg-amber-500 hover:text-white transition-all shadow-sm">✏️</button>
                                <button class="p-2 bg-slate-100 text-slate-400 rounded-lg hover:bg-red-600 hover:text-white transition-all shadow-sm">🗑️</button>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', row);
            }
            
            // Update Text
            document.getElementById('paginationInfo').innerText = `Showing ${start} to ${end} of ${totalItems} items`;
            
            // Update Buttons
            document.getElementById('prevBtn').disabled = currentPage === 1;
            document.getElementById('nextBtn').disabled = currentPage === totalPages;

            // Render Page Numbers
            renderPageNumbers();
        }

        function renderPageNumbers() {
            const container = document.getElementById('pageNumbers');
            container.innerHTML = '';
            
            // Simple logic for current, next, and dots
            const pages = [1, currentPage, totalPages];
            const uniquePages = [...new Set(pages)].sort((a, b) => a - b);
            
            uniquePages.forEach(p => {
                const isActive = p === currentPage;
                const btn = `<span class="px-4 py-2 ${isActive ? 'bg-red-50 text-[#c00000] border-red-100' : 'bg-white text-slate-400 border-slate-100'} border rounded-xl text-xs font-bold cursor-default transition-all">${p}</span>`;
                container.insertAdjacentHTML('beforeend', btn);
            });
        }

        function nextPage() { if (currentPage < totalPages) { currentPage++; renderTable(); } }
        function prevPage() { if (currentPage > 1) { currentPage--; renderTable(); } }

        // Initial Load
        renderTable();
    </script>
</body>
</html>