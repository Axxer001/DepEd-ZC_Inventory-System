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
                <a href="{{ route('inventory.setup') }}" class="group bg-[#c00000] text-white px-8 py-4 rounded-[1.5rem] font-bold hover:bg-red-700 shadow-xl shadow-red-200 transition-all hover:-translate-y-1 flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 group-hover:rotate-90 transition-transform">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Register New School
                </a>
            </header>

            <!-- Success Flash Notification -->
            @if(session('success'))
            <div id="flashNotification" class="fixed top-6 right-6 bg-emerald-500 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-4 z-50 animate-fade-in">
                <div class="bg-white/20 p-2 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-sm tracking-tight">Success</p>
                    <p class="text-xs font-medium opacity-90">{{ session('success') }}</p>
                </div>
                <button onclick="document.getElementById('flashNotification').remove()" class="ml-4 hover:bg-white/20 p-1.5 rounded-lg transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <script>setTimeout(() => document.getElementById('flashNotification')?.remove(), 5000);</script>
            @endif

            @if(session('error'))
            <div id="flashError" class="fixed top-6 right-6 bg-red-600 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-4 z-50 animate-fade-in">
                <div class="bg-white/20 p-2 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-sm tracking-tight">Access Error</p>
                    <p class="text-xs font-medium opacity-90">{{ session('error') }}</p>
                </div>
                <button onclick="document.getElementById('flashError').remove()" class="ml-4 hover:bg-white/20 p-1.5 rounded-lg transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <script>setTimeout(() => document.getElementById('flashError')?.remove(), 7000);</script>
            @endif


            <section class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/40 border border-slate-50 overflow-hidden flex flex-col">
                
                <div class="p-8 border-b border-slate-50 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-3">
                        <span class="w-2 h-6 bg-[#c00000] rounded-full"></span>
                        <h3 class="font-extrabold text-slate-800 tracking-tight">Master List</h3>
                    </div>
                    
                    <form method="GET" action="{{ route('admin.schools') }}" class="relative w-full md:w-80 group" id="searchContainer">
                        <input type="text" id="searchInput" name="search" value="{{ $search ?? '' }}" placeholder="Search school ID or name..." autocomplete="off" class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-semibold focus:outline-none focus:ring-4 focus:ring-red-50 transition-all relative z-20">
                        <button type="submit" class="absolute left-4 top-4 opacity-30 hover:opacity-100 transition-opacity z-20">🔍</button>

                        <!-- Search Autocomplete Results -->
                        <ul id="searchResults" class="absolute z-30 w-full bg-white border border-slate-100 rounded-2xl shadow-xl mt-2 max-h-60 overflow-y-auto hidden custom-scrollbar">
                        </ul>
                    </form>
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
                            @forelse($schools as $school)
                            <tr class="group hover:bg-slate-50/80 transition-all table-row-transition cursor-default">
                                <td class="px-8 py-5">
                                    <span class="font-black text-blue-600 bg-blue-50 px-4 py-2 rounded-xl text-xs tracking-tighter italic">{{ $school->school_id }}</span>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex flex-col">
                                        <span class="font-extrabold text-slate-800 group-hover:text-[#c00000] transition-colors uppercase text-sm">{{ $school->name }}</span>
                                        <span class="text-[10px] font-bold text-slate-400 italic">DepEd Registered Institution</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 bg-emerald-400 rounded-full shadow-[0_0_8px_rgba(52,211,153,0.6)]"></div>
                                        <span class="font-bold text-slate-600 text-sm italic">{{ $school->district_name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex justify-center items-center gap-2 opacity-0 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                                        <button onclick="openDeleteModal('{{ $school->id }}', '{{ addslashes($school->name) }}')" class="p-2 bg-slate-100 text-slate-500 rounded-lg hover:bg-red-600 hover:text-white transition-all shadow-sm">
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-8 py-10 text-center text-sm font-bold text-slate-400 italic">No schools found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-8 bg-slate-50/30 flex items-center justify-between border-t border-slate-50">
                    <div class="w-full">
                        {{ $schools->links() }}
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 items-center justify-center p-4 hidden">
        <div class="bg-white rounded-[2rem] w-full max-w-md overflow-hidden shadow-2xl transform transition-all scale-95 opacity-0" id="deleteModalContent">
            
            <div class="p-8 flex flex-col items-center text-center relative">
                <button onclick="closeDeleteModal()" class="absolute top-4 right-4 p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>

                <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                
                <h3 class="text-xl font-extrabold text-slate-800 mb-2">Delete School?</h3>
                <p class="text-sm text-slate-500 font-medium px-4">
                    Are you sure you want to permanently delete <strong id="deleteSchoolName" class="text-slate-700"></strong> from the system? This action cannot be undone.
                </p>
            </div>
            
            <div class="p-4 bg-slate-50 flex gap-3 border-t border-slate-100">
                <button onclick="closeDeleteModal()" type="button" class="flex-1 py-3 px-4 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold hover:bg-slate-50 transition-colors">Cancel</button>
                <form id="deleteForm" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-3 px-4 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 shadow-md shadow-red-200 transition-all hover:-translate-y-0.5" id="confirmDeleteBtn">Delete School</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const searchResults = document.getElementById('searchResults');
            const searchForm = document.getElementById('searchContainer');
            
            if (!searchInput || !searchResults || !searchForm) return;

            // Generate an array of objects from the Laravel backend variable
            const schools = @json($allSchools);

            // Handle typing in the search bar
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                searchResults.innerHTML = '';
                
                if (query.length === 0) {
                    searchResults.classList.add('hidden');
                    return;
                }

                // Filter schools based on query (by Name OR School ID)
                const filtered = schools.filter(school => {
                    const nameMatch = school.name.toLowerCase().includes(query);
                    const idMatch = school.school_id && school.school_id.toString().includes(query);
                    return nameMatch || idMatch;
                });
                
                if (filtered.length > 0) {
                    filtered.forEach(school => {
                        const li = document.createElement('li');
                        li.className = 'px-4 py-3 hover:bg-red-50 cursor-pointer text-sm font-semibold text-slate-800 transition-colors border-b border-slate-50 last:border-0';
                        li.textContent = `${school.school_id} - ${school.name}`;
                        
                        // Handle clicking a search result
                        li.addEventListener('click', function() {
                            // Only put the exact ID in the search input so the backend exact-matches easily
                            searchInput.value = school.school_id || school.name; 
                            searchResults.classList.add('hidden'); // Hide the dropdown
                            searchForm.submit(); // Automatically fetch the results to the table layout
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

            // Close the search dropdown if user clicks outside of it
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.add('hidden');
                }
            });
        });

        // ----------------------------------------
        // MODAL DELETION LOGIC
        // ----------------------------------------
        function openDeleteModal(schoolId, schoolName) {
            const modal = document.getElementById('deleteModal');
            const content = document.getElementById('deleteModalContent');
            const nameEl = document.getElementById('deleteSchoolName');
            const form = document.getElementById('deleteForm');

            nameEl.textContent = schoolName;
            
            // Build the specific destroy route targeting that school ID
            form.action = `/admin/schools/${schoolId}`;

            // Reveal
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            // Animate In
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
            
            // Focus
            document.getElementById('confirmDeleteBtn').focus();
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            const content = document.getElementById('deleteModalContent');
            
            // Animate out
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            
            // Hide after finish
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 150);
        }
    </script>
</body>
</html>