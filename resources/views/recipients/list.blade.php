<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stakeholders Masterlist | DepEd ZC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        [x-cloak] { display: none !important; }
        .back-btn-hover:hover { transform: translateX(-5px); color: #9333ea; border-color: #9333ea; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex animate-fade-in text-slate-800 overflow-x-hidden" x-data="recipientInventory()">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scrollbar">
        <main class="p-4 lg:p-8">
            {{-- Header --}}
            <header class="flex flex-col md:flex-row md:justify-between md:items-start mb-6 gap-4">
                <div class="flex flex-col gap-3">
                    <a href="{{ route('recipients.index') }}" class="back-btn-hover no-print inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-[10px] font-black text-slate-500 transition-all w-fit shadow-sm uppercase tracking-wider">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                        Back to Menu
                    </a>
                    <div>
                        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tighter italic uppercase leading-none">End-User Masterlist</h2>
                        <p class="text-slate-400 text-[11px] mt-1 font-bold italic uppercase tracking-widest">School distribution and asset accountability records</p>
                    </div>
                </div>
                <button onclick="window.print()" class="no-print group bg-white text-slate-600 border border-slate-200 px-5 py-3 rounded-xl font-bold hover:bg-slate-50 transition-all flex items-center gap-3 shadow-sm active:scale-95 text-[11px] uppercase tracking-widest">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-slate-400 group-hover:text-purple-600 transition-colors"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231a1.125 1.125 0 01-1.117-1.227L6.34 18m11.318-4.171a42.41 42.41 0 014.232.748 1.125 1.125 0 01.815 1.39l-1.077 4.195a1.125 1.125 0 01-1.392.815l-1.332-.342M17.66 18l-1.332-.342m-11.318-4.171a42.41 42.41 0 00-4.232.748 1.125 1.125 0 00-.815 1.39l1.077 4.195a1.125 1.125 0 001.392.815l1.332-.342M6.34 18l1.332-.342m0 0V5.25A2.25 2.25 0 019 3h6a2.25 2.25 0 012.25 2.25v12.75m-11.25 0h11.25" /></svg>
                    Print Report
                </button>
            </header>

            {{-- Filter Bar --}}
            <section class="no-print bg-white p-4 rounded-2xl shadow-sm border border-slate-100 mb-6 flex flex-wrap gap-4">
                <div class="flex-grow grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- District Filter --}}
                    <select x-model="filters.district" class="w-full p-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold uppercase outline-none focus:ring-2 focus:ring-purple-100 transition-all">
                        <option value="all">Districts: ALL</option>
                        <template x-for="dist in districts" :key="dist">
                            <option :value="dist" x-text="dist"></option>
                        </template>
                    </select>

                    {{-- Condition Filter --}}
                    <select x-model="filters.condition" class="w-full p-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold uppercase outline-none focus:ring-2 focus:ring-purple-100 transition-all">
                        <option value="all">Condition: ALL</option>
                        <option value="serviceable">Serviceable</option>
                        <option value="unserviceable">Unserviceable</option>
                    </select>

                    {{-- Search --}}
                    <div class="relative lg:col-span-2">
                        <input type="text" x-model="filters.search" placeholder="Search school, room, or item..." class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold outline-none focus:ring-2 focus:ring-purple-100 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-3 h-3 absolute left-3 top-1/2 -translate-y-1/2 text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    </div>
                </div>
            </section>

            {{-- Results counter --}}
            <div class="flex items-center justify-between mb-3 px-1">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    Showing <span class="text-slate-700" x-text="filteredRecipients().length"></span> entries
                </p>
                <template x-if="filters.district !== 'all' || filters.condition !== 'all' || filters.search">
                    <button @click="filters.district='all'; filters.condition='all'; filters.search=''" class="text-[10px] font-black text-purple-600 uppercase tracking-wider hover:underline">✕ Reset Filters</button>
                </template>
            </div>

            {{-- Table --}}
            <section class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-slate-50/80">
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">School / Recipient</th>
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Room / Office</th>
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Asset Accounted</th>
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Qty</th>
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Condition</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="item in filteredRecipients()" :key="item.id">
                                <tr class="group hover:bg-purple-50/30 transition-all">
                                    <td class="px-6 py-5">
                                        <div class="flex flex-col">
                                            <span class="font-black text-slate-800 uppercase text-[12px] leading-tight" x-text="item.school"></span>
                                            <span class="text-[8px] font-black text-purple-500 uppercase mt-1 tracking-tighter" x-text="item.district"></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="text-[11px] font-bold text-slate-500 italic" x-text="item.room"></span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="text-[12px] font-bold text-slate-700" x-text="item.asset"></span>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="px-3 py-1 bg-slate-100 rounded-lg font-black text-slate-800 text-[12px]" x-text="item.qty"></span>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span :class="item.condition === 'serviceable' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-red-50 text-red-600 border-red-100'" 
                                              class="px-3 py-1 border rounded-full text-[9px] font-black uppercase tracking-wider" 
                                              x-text="item.condition">
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script>
        function recipientInventory() {
            return {
                filters: { district: 'all', condition: 'all', search: '' },
                districts: ['Central District', 'Ayala District', 'Vitali District', 'Curuan District'],
                recipients: [
                    { id: 1, school: 'Ayala National High School', district: 'Ayala District', room: 'ICT Lab 1', asset: 'Dell Latitude 3420', qty: 25, condition: 'serviceable' },
                    { id: 2, school: 'Zamboanga Central School', district: 'Central District', room: 'Admin Office', asset: 'Smart TV 55"', qty: 2, condition: 'serviceable' },
                    { id: 3, school: 'Tetuan Elementary', district: 'Central District', room: 'Grade 6 Room', asset: 'Monoblock Chairs', qty: 50, condition: 'serviceable' },
                    { id: 4, school: 'Vitali NHS', district: 'Vitali District', room: 'Science Lab', asset: 'Microscope Set', qty: 10, condition: 'unserviceable' },
                ],
                filteredRecipients() {
                    return this.recipients.filter(r => {
                        const matchDist = this.filters.district === 'all' || r.district === this.filters.district;
                        const matchCond = this.filters.condition === 'all' || r.condition === this.filters.condition;
                        const matchSearch = r.school.toLowerCase().includes(this.filters.search.toLowerCase()) || 
                                            r.asset.toLowerCase().includes(this.filters.search.toLowerCase()) ||
                                            r.room.toLowerCase().includes(this.filters.search.toLowerCase());
                        return matchDist && matchCond && matchSearch;
                    });
                }
            }
        }
    </script>
</body>
</html>