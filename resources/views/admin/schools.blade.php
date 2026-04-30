<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Directory | DepEd ZC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Alpine.js for filtering and toggle logic --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; transition: all 0.3s; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
        .table-row-transition { transition: all 0.2s ease-in-out; }
        [x-cloak] { display: none !important; }
        .laravel-pagination-wrapper nav div:first-child { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex animate-fade-in text-slate-800 overflow-x-hidden"
      x-data="schoolFilter()">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto">
        <main class="p-6 lg:p-10">
            <header class="flex flex-col md:flex-row md:justify-between md:items-start mb-10 gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight italic">School Registry</h2>
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
                <div class="p-8 border-b border-slate-50">
                    <form id="filterForm" method="GET" action="{{ route('admin.schools') }}">
                        {{-- Hidden input for districts and quadrants array --}}
                        <input type="hidden" name="districts" :value="selectedDistricts.join(',')">
                        <input type="hidden" name="quadrants" :value="selectedQuadrants.join(',')">

                        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                            <div class="flex items-center gap-3">
                                <span class="w-2 h-6 bg-[#c00000] rounded-full"></span>
                                <h3 class="font-extrabold text-slate-800 tracking-tight text-lg italic">Master List</h3>
                            </div>
                            
                            <div class="flex items-center gap-3 w-full md:w-auto flex-grow max-w-2xl justify-end">
                                {{-- Search Bar --}}
                                <div class="relative w-full md:w-96 group" id="searchContainer">
                                    <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Search School ID or name..." autocomplete="off" class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-100 rounded-[1.5rem] text-sm font-semibold focus:outline-none focus:ring-4 focus:ring-red-50 transition-all relative z-20">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-[#c00000] transition-colors z-20">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                    </svg>
                                    <ul id="searchResults" class="absolute z-30 w-full bg-white border border-slate-100 rounded-2xl shadow-2xl mt-2 max-h-60 overflow-y-auto hidden custom-scrollbar"></ul>
                                </div>

                                {{-- Filter Toggle Button --}}
                                <button type="button" @click="showFilters = !showFilters" 
                                        :class="showFilters || selectedDistricts.length > 0 || selectedQuadrants.length > 0 ? 'bg-[#c00000] text-white border-[#c00000]' : 'bg-white text-slate-600 border-slate-200'"
                                        class="flex items-center gap-2 px-6 py-4 rounded-2xl border font-bold text-sm transition-all hover:shadow-lg active:scale-95 shrink-0 relative">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 18H7.5m9-6h2.25m-2.25 0a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 12h7.5" />
                                    </svg>
                                    <template x-if="(selectedDistricts.length + selectedQuadrants.length) > 0">
                                        <span class="bg-white text-[#c00000] text-[10px] w-5 h-5 flex items-center justify-center rounded-full font-black shadow-sm" x-text="(selectedDistricts.length + selectedQuadrants.length)"></span>
                                    </template>
                                </button>
                            </div>
                        </div>

                        {{-- Collapsible Manual Filter Section --}}
                        <div x-show="showFilters" x-cloak
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 -translate-y-4"
                             class="mt-6 p-8 bg-slate-50/50 rounded-[2rem] border border-slate-100 shadow-inner">
                            
                            <div class="flex flex-col gap-6">
                                <div class="flex justify-between items-center">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Multi-Quadrant & District Selection</span>
                                        <p class="text-xs text-slate-500 font-medium">Select one or more parameters to filter down the list.</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button type="button" x-show="selectedDistricts.length > 0 || selectedQuadrants.length > 0" 
                                                @click="selectedDistricts = []; selectedQuadrants = [];"
                                                class="text-[10px] font-black text-slate-400 uppercase hover:text-red-600 transition-colors">
                                            Clear Selection
                                        </button>
                                        <button type="submit" class="bg-slate-900 text-white px-6 py-2.5 rounded-xl font-bold text-xs shadow-lg hover:bg-slate-800 transition-all active:scale-95">
                                            Apply Filters
                                        </button>
                                    </div>
                                </div>
                                
                                {{-- Quadrant Selection Grouped by LD --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    @foreach($quadrantsByLD as $ldName => $quads)
                                    <div class="flex flex-col">
                                        <p class="text-[9px] font-black {{ str_contains($ldName, '1') ? 'text-blue-600' : 'text-emerald-600' }} uppercase tracking-widest mb-3 border-b border-slate-100 pb-1 italic">{{ $ldName }}</p>
                                        <div class="flex flex-wrap gap-2.5">
                                            @foreach($quads as $quad)
                                            <button type="button" 
                                                @click="!isQuadrantDisabled('{{ $quad->name }}') && toggleQuadrant('{{ $quad->name }}')"
                                                :disabled="isQuadrantDisabled('{{ $quad->name }}')"
                                                :class="[
                                                    selectedQuadrants.includes('{{ $quad->name }}') 
                                                        ? 'bg-[#c00000] text-white border-[#c00000] shadow-md shadow-red-100' 
                                                        : 'bg-white text-slate-500 border-slate-200 hover:border-red-200',
                                                    isQuadrantDisabled('{{ $quad->name }}') ? 'opacity-40 cursor-not-allowed bg-slate-100 hover:border-slate-200' : 'active:scale-95'
                                                ]"
                                                class="px-5 py-2.5 rounded-xl border text-[11px] font-bold uppercase transition-all flex items-center gap-2">
                                                <span>{{ $quad->name }}</span>
                                                <template x-if="selectedQuadrants.includes('{{ $quad->name }}')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                                                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                                    </svg>
                                                </template>
                                            </button>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                {{-- District Selection --}}
                                <div class="flex flex-col">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Districts</p>
                                    <div class="flex flex-wrap gap-2.5">
                                    <template x-for="dist in districts" :key="dist">
                                        <button type="button" 
                                            @click="!isDistrictDisabled(dist) && toggleDistrict(dist)"
                                            :disabled="isDistrictDisabled(dist)"
                                            :class="[
                                                selectedDistricts.includes(dist) 
                                                    ? 'bg-[#c00000] text-white border-[#c00000] shadow-md shadow-red-100' 
                                                    : 'bg-white text-slate-500 border-slate-200 hover:border-red-200',
                                                isDistrictDisabled(dist) ? 'opacity-40 cursor-not-allowed bg-slate-100 hover:border-slate-200' : 'active:scale-95'
                                            ]"
                                            class="px-5 py-2.5 rounded-xl border text-[11px] font-bold uppercase transition-all flex items-center gap-2">
                                            <span x-text="dist"></span>
                                            <template x-if="selectedDistricts.includes(dist)">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                                                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                                </svg>
                                            </template>
                                        </button>
                                    </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Table Section --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">School ID</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Institutional Name</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Quadrant</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">District</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center border-b border-slate-100">Action</th>
                            </tr>
                        </thead>
                        <tbody id="schoolTableBody" class="divide-y divide-slate-50">
                            @forelse($schools as $school)
                            <tr class="group hover:bg-slate-50/80 transition-all table-row-transition">
                                <td class="px-8 py-5">
                                    <span class="font-black text-blue-600 bg-blue-50 px-4 py-2 rounded-xl text-xs italic border border-blue-100">{{ $school->school_id }}</span>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-800 group-hover:text-[#c00000] uppercase text-sm leading-tight transition-colors">{{ $school->name }}</span>
                                        <span class="text-[9px] font-black text-slate-300 uppercase mt-0.5 tracking-tighter italic">Verified DepEd Record</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="font-bold text-slate-600 text-xs italic">{{ $school->quadrant_name ?? 'N/A' }}</span>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 bg-emerald-400 rounded-full shadow-[0_0_8px_rgba(52,211,153,0.6)]"></div>
                                        <span class="font-bold text-slate-600 text-[13px] italic">{{ $school->district_name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-center">
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
                                <td colspan="4" class="px-8 py-20 text-center font-bold text-slate-400 italic">No matching schools found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Footer --}}
                <div class="p-8 bg-slate-50/30 border-t border-slate-50">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">
                            Showing <span class="text-slate-900">{{ $schools->firstItem() ?? 0 }} - {{ $schools->lastItem() ?? 0 }}</span> of <span class="text-slate-900">{{ $schools->total() }}</span> Institutions
                        </p>
                        <div class="laravel-pagination-wrapper">
                            {{ $schools->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    {{-- Delete Modal --}}
    <div id="deleteModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] w-full max-w-md p-10 text-center shadow-2xl">
            <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6 text-2xl">⚠️</div>
            <h3 class="text-2xl font-black text-slate-800 mb-2 uppercase tracking-tight">Confirm Delete</h3>
            <p id="deleteSchoolName" class="text-sm text-slate-500 mb-8 font-medium"></p>
            <div class="flex gap-4">
                <button onclick="closeDeleteModal()" class="flex-1 py-4 bg-slate-100 rounded-2xl font-bold transition-colors hover:bg-slate-200">Cancel</button>
                <form id="deleteForm" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-4 bg-red-600 text-white rounded-2xl font-bold shadow-xl shadow-red-100 hover:bg-red-700 transition-all active:scale-95">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        {{-- Logic separated into a function to prevent HTML character issues --}}
        function schoolFilter() {
            return {
                showFilters: false,
                districts: @json($allDistricts ?? []),
                quadrants: @json($allQuadrants ?? []),
                mapping: @json($districtQuadrantMapping ?? []),
                selectedDistricts: @json(request('districts') ? explode(',', request('districts')) : []),
                selectedQuadrants: @json(request('quadrants') ? explode(',', request('quadrants')) : []),
                
                toggleDistrict(dist) {
                    if (this.selectedDistricts.includes(dist)) {
                        this.selectedDistricts = this.selectedDistricts.filter(d => d !== dist);
                    } else {
                        this.selectedDistricts.push(dist);
                    }
                },
                
                toggleQuadrant(quad) {
                    if (this.selectedQuadrants.includes(quad)) {
                        this.selectedQuadrants = this.selectedQuadrants.filter(q => q !== quad);
                    } else {
                        this.selectedQuadrants.push(quad);
                    }
                },

                isDistrictDisabled(distName) {
                    if (this.selectedQuadrants.length === 0) return false;
                    // Find the quadrant this district belongs to
                    const mapItem = this.mapping.find(m => m.district === distName);
                    if (!mapItem) return false;
                    // Disabled if its quadrant is NOT in the selected quadrants
                    return !this.selectedQuadrants.includes(mapItem.quadrant);
                },

                isQuadrantDisabled(quadName) {
                    if (this.selectedDistricts.length === 0) return false;
                    // Get all districts that belong to this specific quadrant
                    const districtsInQuad = this.mapping.filter(m => m.quadrant === quadName).map(m => m.district);
                    // Check if ANY of the currently selected districts belong to this quadrant
                    const hasSelectedDistrict = this.selectedDistricts.some(sd => districtsInQuad.includes(sd));
                    // Disabled if NONE of the selected districts belong to it
                    return !hasSelectedDistrict;
                }
            }
        }

        {{-- Autocomplete and Modal Scripts --}}
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const searchResults = document.getElementById('searchResults');
            const allSchools = @json($allSchools ?? []);

            if(searchInput) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase().trim();
                    searchResults.innerHTML = '';
                    if (query.length === 0) { searchResults.classList.add('hidden'); return; }
                    const filtered = allSchools.filter(s => (s.name && s.name.toLowerCase().includes(query)) || (s.school_id && s.school_id.toString().includes(query)));
                    if (filtered.length > 0) {
                        filtered.forEach(s => {
                            const li = document.createElement('li');
                            li.className = 'px-6 py-4 hover:bg-red-50 cursor-pointer text-[11px] font-black text-slate-700 transition-all border-b border-slate-50 uppercase tracking-widest';
                            li.innerHTML = `<span class="text-blue-600 mr-2">${s.school_id}</span> - ${s.name}`;
                            li.onclick = () => { searchInput.value = s.school_id; document.getElementById('filterForm').submit(); };
                            searchResults.appendChild(li);
                        });
                    } else {
                        searchResults.innerHTML = '<li class="px-6 py-5 text-xs font-bold text-slate-400 italic">No institutions found</li>';
                    }
                    searchResults.classList.remove('hidden');
                });
            }
            document.addEventListener('click', (e) => { if (searchInput && !searchInput.contains(e.target)) searchResults.classList.add('hidden'); });
        });

        function openDeleteModal(id, name) {
            document.getElementById('deleteSchoolName').textContent = 'Confirm deletion of ' + name + '?';
            document.getElementById('deleteForm').action = '/admin/schools/' + id;
            document.getElementById('deleteModal').classList.replace('hidden', 'flex');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.replace('flex', 'hidden');
        }
    </script>
</body>
</html>