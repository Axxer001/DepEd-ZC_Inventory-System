<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs | DepEd ZC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; transition: all 0.3s; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex animate-fade-in text-slate-800 overflow-x-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto">
        
        <main class="p-6 lg:p-10">
            <header class="flex flex-col md:flex-row md:justify-between md:items-start mb-8 gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight text-red">System Logs</h2>
                    <p class="text-slate-500 text-sm mt-1 font-medium italic">Track all administrative activities and changes</p>
                </div>
                <button class="bg-slate-800 text-white px-6 py-3 rounded-2xl font-bold hover:bg-slate-900 shadow-xl shadow-slate-200 transition-all hover:-translate-y-1 flex items-center gap-3 text-sm">
                    <span>Export Logs</span>
                </button>
            </header>

            <section class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/40 border border-slate-50 overflow-hidden flex flex-col">
                
                <div class="p-8 border-b border-slate-50 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-3">
                        <span class="w-2 h-6 bg-slate-400 rounded-full"></span>
                        <h3 class="font-extrabold text-slate-800 tracking-tight text-lg">Activity History</h3>
                    </div>
                    
                    <div class="flex gap-2">
                        <select class="px-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-red-50 transition-all cursor-pointer">
                            <option>All Actions</option>
                            <option>Create</option>
                            <option>Update</option>
                            <option>Delete</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto overflow-y-auto custom-scrollbar" style="max-height: 600px;">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead class="sticky top-0 z-10">
                            <tr class="bg-slate-50/95 backdrop-blur-md">
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">User</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Activity</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Module</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100 text-right">Date & Time</th>
                            </tr>
                        </thead>
                        <tbody id="logTableBody" class="divide-y divide-slate-50">
                            </tbody>
                    </table>
                </div>

                <div class="p-8 bg-slate-50/30 flex items-center justify-between border-t border-slate-50">
                    <p id="paginationInfo" class="text-[10px] font-black text-slate-400 uppercase tracking-widest text-xs">Page 1 of 5</p>
                    <div class="flex items-center gap-3">
                        <button onclick="prevPage()" id="prevBtn" class="p-3 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-slate-800 disabled:opacity-30">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </button>
                        <button onclick="nextPage()" id="nextBtn" class="p-3 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-slate-800 disabled:opacity-30">
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
        const totalItems = 100;
        const totalPages = Math.ceil(totalItems / itemsPerPage);

        const actions = ["Added new asset", "Updated school info", "Deleted record", "Assigned DCP Package"];
        const modules = ["Inventory", "Schools", "Settings"];

        function renderTable() {
            const tbody = document.getElementById('logTableBody');
            tbody.innerHTML = '';
            
            for(let i = 0; i < itemsPerPage; i++) {
                const currentIdx = ((currentPage - 1) * itemsPerPage) + i + 1;
                if (currentIdx > totalItems) break;

                const row = `
                    <tr class="hover:bg-slate-50/80 transition-all cursor-default group">
                        <td class="px-8 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500 uppercase border border-slate-200">AD</div>
                                <span class="font-extrabold text-slate-700 text-xs">Admin_User</span>
                            </div>
                        </td>
                        <td class="px-8 py-3">
                            <span class="text-xs font-bold text-slate-600">${actions[Math.floor(Math.random() * actions.length)]}</span>
                        </td>
                        <td class="px-8 py-3">
                             <span class="px-2.5 py-1 bg-slate-100 text-slate-500 rounded-lg text-[9px] font-black uppercase tracking-widest border border-slate-200">${modules[Math.floor(Math.random() * modules.length)]}</span>
                        </td>
                        <td class="px-8 py-3 text-right">
                            <span class="text-[10px] font-bold text-slate-400 italic">2026-03-10 14:30:22</span>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', row);
            }
            document.getElementById('paginationInfo').innerText = `Page ${currentPage} of ${totalPages}`;
            document.getElementById('prevBtn').disabled = currentPage === 1;
            document.getElementById('nextBtn').disabled = currentPage === totalPages;
        }

        function nextPage() { if (currentPage < totalPages) { currentPage++; renderTable(); } }
        function prevPage() { if (currentPage > 1) { currentPage--; renderTable(); } }
        renderTable();
    </script>
</body>
</html>