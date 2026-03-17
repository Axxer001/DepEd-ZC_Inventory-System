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
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">School Registry</h2>
                    <p class="text-slate-500 text-sm mt-1 font-medium italic">Zamboanga City Division Master List</p>
                </div>
                <a href="{{ route('inventory.setup') }}" class="group bg-[#c00000] text-white px-8 py-4 rounded-[1.5rem] font-bold hover:bg-red-700 shadow-xl shadow-red-200 transition-all hover:-translate-y-1 flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 group-hover:rotate-90 transition-transform">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Register New School
                </a>
            </header>

            @if(session('success'))
            <div id="flashNotification" class="fixed top-6 right-6 bg-emerald-500 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-4 z-50 animate-fade-in">
                <div class="bg-white/20 p-2 rounded-xl">✓</div>
                <p class="text-xs font-bold">{{ session('success') }}</p>
                <button onclick="this.parentElement.remove()" class="ml-4 opacity-50 hover:opacity-100">✕</button>
            </div>
            <script>setTimeout(() => document.getElementById('flashNotification')?.remove(), 5000);</script>
            @endif

            <section class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/40 border border-slate-50 overflow-hidden flex flex-col">
                
                <div class="p-8 border-b border-slate-50 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-3">
                        <span class="w-2 h-6 bg-[#c00000] rounded-full"></span>
                        <h3 class="font-extrabold text-slate-800 tracking-tight text-lg">Master List</h3>
                    </div>
                    
                    <form method="GET" action="{{ route('admin.schools') }}" class="relative w-full md:w-96 group" id="searchContainer">
                        <input type="text" id="searchInput" name="search" value="{{ $search ?? '' }}" placeholder="Search ID or school name..." autocomplete="off" class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-100 rounded-[1.5rem] text-sm font-semibold focus:outline-none focus:ring-4 focus:ring-red-50 transition-all relative z-20">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 absolute left-4 top-3.5 text-slate-300 group-focus-within:text-[#c00000] transition-colors z-20">
  <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
</svg>                        <ul id="searchResults" class="absolute z-30 w-full bg-white border border-slate-100 rounded-2xl shadow-2xl mt-2 max-h-60 overflow-y-auto hidden custom-scrollbar"></ul>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">School ID</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Institutional Name</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">District</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center border-b border-slate-100">Action</th>
                            </tr>
                        </thead>
                        <tbody id="schoolTableBody" class="divide-y divide-slate-50">
                            @forelse($schools as $school)
                            <tr class="group hover:bg-slate-50/80 transition-all table-row-transition">
                                <td class="px-8 py-5">
                                    <span class="font-black text-blue-600 bg-blue-50 px-4 py-2 rounded-xl text-xs tracking-tighter italic">{{ $school->school_id }}</span>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex flex-col">
                                        <span class="font-extrabold text-slate-800 group-hover:text-[#c00000] transition-colors uppercase text-sm leading-tight">{{ $school->name }}</span>
                                        <span class="text-[9px] font-black text-slate-300 uppercase mt-0.5">Verified DepEd School</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></div>
                                        <span class="font-bold text-slate-600 text-sm">{{ $school->district_name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex justify-center opacity-0 group-hover:opacity-100 transition-all translate-x-2 group-hover:translate-x-0">
                                        <button onclick="openDeleteModal('{{ $school->id }}', '{{ addslashes($school->name) }}')" class="p-2.5 bg-white border border-slate-100 text-slate-400 rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-8 py-12 text-center text-sm font-bold text-slate-400 italic">No schools currently found in the system.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-8 bg-slate-50/30 border-t border-slate-50">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            Showing <span class="text-slate-900">{{ $schools->firstItem() ?? 0 }}</span> - <span class="text-slate-900">{{ $schools->lastItem() ?? 0 }}</span> of <span class="text-slate-900">{{ $schools->total() }}</span> Schools
                        </p>

                        <nav class="flex items-center gap-2">
                            {{-- First Page --}}
                            @if ($schools->onFirstPage())
                                <span class="p-3 bg-slate-100 text-slate-300 rounded-xl cursor-not-allowed">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m18.75 4.5-7.5 7.5 7.5 7.5m-6-15L5.25 12l7.5 7.5" /></svg>
                                </span>
                            @else
                                <a href="{{ $schools->url(1) }}" class="p-3 bg-white border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-900 hover:text-white transition-all shadow-sm" title="First Page">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m18.75 4.5-7.5 7.5 7.5 7.5m-6-15L5.25 12l7.5 7.5" /></svg>
                                </a>
                            @endif

                            {{-- Previous --}}
                            @if ($schools->onFirstPage())
                                <span class="px-6 py-3 bg-slate-100 text-slate-300 rounded-2xl text-[10px] font-black uppercase tracking-widest cursor-not-allowed">Prev</span>
                            @else
                                <a href="{{ $schools->previousPageUrl() }}" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-[#c00000] hover:text-white hover:border-[#c00000] transition-all shadow-sm">Prev</a>
                            @endif

                            {{-- Current Page Display --}}
                            <div class="px-4 text-[10px] font-black text-slate-900">
                                <span class="text-[#c00000]">{{ $schools->currentPage() }}</span> <span class="text-slate-200 mx-1">/</span> {{ $schools->lastPage() }}
                            </div>

                            {{-- Next --}}
                            @if ($schools->hasMorePages())
                                <a href="{{ $schools->nextPageUrl() }}" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-[#c00000] hover:text-white hover:border-[#c00000] transition-all shadow-sm">Next</a>
                            @else
                                <span class="px-6 py-3 bg-slate-100 text-slate-300 rounded-2xl text-[10px] font-black uppercase tracking-widest cursor-not-allowed">Next</span>
                            @endif

                            {{-- Last Page --}}
                            @if ($schools->hasMorePages())
                                <a href="{{ $schools->url($schools->lastPage()) }}" class="p-3 bg-white border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-900 hover:text-white transition-all shadow-sm" title="Last Page">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5" /></svg>
                                </a>
                            @else
                                <span class="p-3 bg-slate-100 text-slate-300 rounded-xl cursor-not-allowed">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5" /></svg>
                                </span>
                            @endif
                        </nav>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <div id="deleteModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 items-center justify-center p-4 hidden">
        <div class="bg-white rounded-[2.5rem] w-full max-w-md overflow-hidden shadow-2xl transform transition-all scale-95 opacity-0" id="deleteModalContent">
            <div class="p-8 flex flex-col items-center text-center">
                <div class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mb-6 text-3xl animate-bounce">⚠️</div>
                <h3 class="text-2xl font-extrabold text-slate-800 mb-2">Confirm Delete</h3>
                <p class="text-sm text-slate-500 font-medium px-4 leading-relaxed">
                    Are you sure you want to delete <strong id="deleteSchoolName" class="text-slate-900"></strong>? This will remove all associated inventory records.
                </p>
            </div>
            <div class="p-6 bg-slate-50 flex gap-3 border-t border-slate-100">
                <button onclick="closeDeleteModal()" class="flex-1 py-4 bg-white border border-slate-200 text-slate-600 rounded-2xl font-bold hover:bg-slate-100 transition-all shadow-sm">Cancel</button>
                <form id="deleteForm" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-4 bg-red-600 text-white rounded-2xl font-bold hover:bg-red-700 shadow-xl shadow-red-100 transition-all transform active:scale-95">Delete School</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // --- AUTOCOMPLETE SEARCH LOGIC ---
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const searchResults = document.getElementById('searchResults');
            const schools = @json($allSchools);

            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                searchResults.innerHTML = '';
                
                if (query.length === 0) { 
                    searchResults.classList.add('hidden'); 
                    return; 
                }

                const filtered = schools.filter(s => 
                    s.name.toLowerCase().includes(query) || 
                    (s.school_id && s.school_id.toString().includes(query))
                );
                
                if (filtered.length > 0) {
                    filtered.forEach(s => {
                        const li = document.createElement('li');
                        li.className = 'px-6 py-4 hover:bg-red-50 cursor-pointer text-[11px] font-black text-slate-700 transition-all border-b border-slate-50 uppercase tracking-widest';
                        li.innerHTML = `<span class="text-blue-600 mr-2">${s.school_id}</span> - ${s.name}`;
                        li.onclick = () => { 
                            searchInput.value = s.school_id; 
                            document.getElementById('searchContainer').submit(); 
                        };
                        searchResults.appendChild(li);
                    });
                } else {
                    searchResults.innerHTML = '<li class="px-6 py-5 text-xs font-bold text-slate-400 italic">No matching institutions found</li>';
                }
                searchResults.classList.remove('hidden');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!searchInput.contains(e.target)) searchResults.classList.add('hidden');
            });
        });

        // --- DELETE MODAL ANIMATIONS ---
        function openDeleteModal(id, name) {
            document.getElementById('deleteSchoolName').textContent = name;
            document.getElementById('deleteForm').action = `/admin/schools/${id}`;
            const modal = document.getElementById('deleteModal');
            const content = document.getElementById('deleteModalContent');
            modal.classList.remove('hidden'); 
            modal.classList.add('flex');
            setTimeout(() => { 
                content.classList.remove('scale-95', 'opacity-0'); 
                content.classList.add('scale-100', 'opacity-100'); 
            }, 10);
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            const content = document.getElementById('deleteModalContent');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => { 
                modal.classList.add('hidden'); 
                modal.classList.remove('flex'); 
            }, 200);
        }
    </script>
</body>
</html>