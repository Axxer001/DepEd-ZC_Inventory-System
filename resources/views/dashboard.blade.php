<!DOCTYPE html>
<html lang="en">
<head>

<!-- cd C:\Users\Admin\DepEd-ZC_Inventory\DepEd-ZC_Inventory-System-main -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | DepEd Zamboanga City</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Custom scrollbar for the entry section */
        .custom-scroll::-webkit-scrollbar { height: 4px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex animate-fade-in text-slate-800 overflow-x-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto">
        
        <header class="lg:hidden bg-white border-b border-slate-200 p-4 sticky top-0 z-30 flex items-center gap-4">
            <button onclick="toggleSidebar()" class="p-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-100 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/deped_logo.png') }}" class="h-6 w-auto">
                <span class="font-extrabold italic text-sm">DepEd ZC</span>
            </div>
        </header>

        <main class="p-6 lg:p-10">
            <header class="flex flex-col md:flex-row md:justify-between md:items-center mb-10 gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Welcome, Admin!</h2>
                    <p class="text-slate-500 text-sm mt-1 font-medium italic">Zamboanga City Division Asset Overview</p>
                </div>
                <div class="hidden sm:block text-right bg-white px-6 py-3 rounded-2xl shadow-sm border border-slate-100">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-tight">{{ now()->format('l') }}</p>
                    <p class="text-lg font-bold text-slate-800 tracking-tight">{{ now()->format('M d, Y') }}</p>
                </div>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <div class="group bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-50 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl cursor-default">
                    <div class="flex justify-between items-start mb-4">
<div class="p-4 bg-blue-50 text-blue-500 rounded-2xl group-hover:scale-110 transition-transform">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6.75h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75H21m-3.75 3.75H21" />
    </svg>
</div>
                        <span class="bg-blue-100 text-blue-600 px-4 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest italic">Division Wide</span>
                    </div>
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total System Assets</h3>
                    <p class="text-5xl font-extrabold text-slate-800 tracking-tighter leading-none">{{ number_format($totalAssets) }}</p>
                </div>

                <div class="group bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-50 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl cursor-default">
                    <div class="flex justify-between items-start mb-4">
<div class="p-4 bg-emerald-50 text-emerald-600 rounded-2xl group-hover:scale-110 transition-transform">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21a3.745 3.745 0 01-3.129-1.593 3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.745 3.745 0 013.296-1.043A3.745 3.745 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.745 3.745 0 013.296 1.043 3.745 3.745 0 011.043 3.296A3.745 3.745 0 0121 12z" />
    </svg>
</div>                        <span class="bg-emerald-100 text-emerald-600 px-4 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest italic">Serviceable</span>
                    </div>
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Good Condition</h3>
                    <p class="text-5xl font-extrabold text-emerald-600 tracking-tighter leading-none">{{ number_format($serviceableCount) }}</p>
                </div>

                <div class="group bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl cursor-default">
                    <div class="flex justify-between items-start mb-4">
<div class="p-4 bg-orange-50 text-orange-500 rounded-2xl group-hover:scale-110 transition-transform">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
    </svg>
</div>                        <span class="bg-orange-100 text-orange-600 px-4 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest italic">Unserviceable</span>
                    </div>
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Pasira / Damaged</h3>
                    <p class="text-5xl font-extrabold text-orange-500 tracking-tighter leading-none">{{ number_format($unserviceableCount) }}</p>
                </div>
            </div>

            <section class="mb-12">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/40 border border-slate-50 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-8 opacity-5">
                        <svg class="w-32 h-32 text-slate-900" fill="currentColor" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                    </div>
                    
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                        <div>
                            <h3 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                                <span class="bg-red-100 text-[#c00000] p-2 rounded-xl text-sm italic">New</span>
                                Quick Asset Entry
                            </h3>
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em] mt-1">Directly assign assets to schools</p>
                        </div>
                        
                        <div class="relative w-full md:w-72 group" id="searchContainer">
                            <input type="text" id="schoolSearch" placeholder="Search school..." autocomplete="off" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:outline-none focus:ring-4 focus:ring-red-50 transition-all font-semibold relative z-20">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 absolute left-4 top-3.5 text-slate-300 group-focus-within:text-[#c00000] transition-colors z-20">
  <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
</svg>                            
                            <!-- Search Autocomplete Results -->
                            <ul id="searchResults" class="absolute z-30 w-full bg-white border border-slate-100 rounded-2xl shadow-xl mt-2 max-h-60 overflow-y-auto hidden custom-scroll">
                            </ul>
                        </div>
                    </div>

                    <form action="{{ route('inventory.dashboard.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-6">
                        @csrf
                        <div class="space-y-2 relative">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Assigned School</label>
                            <select name="school_id" id="schoolSelect" required class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:outline-none focus:border-red-200 cursor-pointer transition-all relative z-10">
                                <option value="">Select a School</option>
                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Main Category</label>
                            <select name="category_id" id="categorySelect" required class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:outline-none focus:border-red-200 cursor-pointer transition-all">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Item</label>
                            <select name="item_id" id="itemSelect" required disabled class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:outline-none focus:border-red-200 cursor-pointer transition-all disabled:opacity-50">
                                <option value="">Select Item</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}" data-category="{{ $item->category_id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Sub-Item</label>
                            <select name="sub_item_id" id="subItemSelect" disabled class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:outline-none focus:border-red-200 cursor-pointer transition-all disabled:opacity-50">
                                <option value="">Select Sub-Item</option>
                                @foreach($subItems as $sub)
                                    <option value="{{ $sub->id }}" data-item="{{ $sub->item_id }}">{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-end gap-3">
                            <div class="space-y-2 flex-grow">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Quantity</label>
                                <input type="number" name="quantity" id="quantityInput" value="1" min="1" required class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:outline-none text-center">
                            </div>
                            <button type="submit" class="p-4 bg-[#c00000] text-white rounded-2xl font-bold hover:bg-red-700 shadow-lg shadow-red-200 transition-all hover:-translate-y-1 active:scale-95 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </section>


            <section class="mb-12">
    <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/40 border border-slate-50 overflow-hidden">
        <div class="p-8 border-b border-slate-50 flex justify-between items-center">
            <div>
                <h3 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                    <span class="bg-blue-100 text-blue-600 p-2 rounded-xl text-sm italic">Latest</span>
                    Recently Added Items
                </h3>
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em] mt-1">Review the most recent inventory updates</p>
            </div>
            <a href="#" class="text-[#c00000] text-xs font-bold uppercase tracking-widest hover:underline">View All Assets </a>
        </div>
        
        <div class="overflow-x-auto custom-scroll">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">School</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Category</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Item / Model</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Qty</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Added By</th>
                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</th>
                    </tr>
                </thead>
                    @forelse($recentOwnerships as $item)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-8 py-5">
                            <p class="font-bold text-slate-800 text-sm">{{ $item->school_name }}</p>
                            <p class="text-[10px] text-slate-400 font-medium italic">{{ $item->district_name }}</p>
                        </td>
                        <td class="px-8 py-5">
                            <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-bold uppercase">{{ $item->category_name }}</span>
                        </td>
                        <td class="px-8 py-5">
                            <p class="font-bold text-slate-700 text-sm leading-tight">{{ $item->item_name }}</p>
                            @if($item->sub_item_name)
                                <p class="text-[10px] text-slate-400 font-medium uppercase tracking-tighter">{{ $item->sub_item_name }}</p>
                            @endif
                        </td>
                        <td class="px-8 py-5 text-center">
                            <span class="font-black text-slate-800">{{ $item->quantity }}</span>
                        </td>
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500">
                                    {{ strtoupper(substr($item->added_by ?? 'Sys', 0, 1)) }}
                                </div>
                                <p class="text-sm font-bold text-slate-600">{{ $item->added_by ?? 'System' }}</p>
                            </div>
                        </td>
                        <td class="px-8 py-5 text-sm text-slate-500 font-medium">
                            {{ \Carbon\Carbon::parse($item->created_at)->timezone('Asia/Manila')->format('M d, Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-8 py-10 text-center text-slate-500 text-sm font-medium">
                            No recently added items found.
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
            </table>
        </div>
    </div>
</section>

            <div class="flex items-center justify-between mb-8 px-2">
                <h3 class="text-xl font-extrabold text-slate-800 tracking-tight flex items-center gap-3">
                    <span class="w-2 h-8 bg-blue-600 rounded-full"></span>
                    District Summary Portfolio
                </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 hover:border-blue-200 transition-all duration-300 hover:-translate-y-1 cursor-pointer group">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-xl font-bold group-hover:scale-110 transition-transform tracking-tighter italic">1.1</div>
                        <div>
                            <h4 class="text-xl font-extrabold text-slate-800">Quadrant 1.1</h4>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest italic leading-tight">LD 1 • 3 Districts</p>
                        </div>
                    </div>
                    <div class="bg-slate-50 p-5 rounded-3xl border border-slate-100 shadow-inner group-hover:bg-blue-50/30 transition-colors">
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Total Quadrant Assets</p>
                        <p class="text-2xl font-extrabold text-slate-800 tracking-tighter leading-none">{{ number_format($quadrantTotals[1] ?? 0) }}</p>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 hover:border-blue-200 transition-all duration-300 hover:-translate-y-1 cursor-pointer group">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-xl font-bold group-hover:scale-110 transition-transform tracking-tighter italic">1.2</div>
                        <div>
                            <h4 class="text-xl font-extrabold text-slate-800">Quadrant 1.2</h4>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest italic leading-tight">LD 1 • 2 Districts</p>
                        </div>
                    </div>
                    <div class="bg-slate-50 p-5 rounded-3xl border border-slate-100 shadow-inner group-hover:bg-blue-50/30 transition-colors">
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Total Quadrant Assets</p>
                        <p class="text-2xl font-extrabold text-slate-800 tracking-tighter leading-none">{{ number_format($quadrantTotals[2] ?? 0) }}</p>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 hover:border-emerald-200 transition-all duration-300 hover:-translate-y-1 cursor-pointer group">
                    <div class="flex items-center gap-4 mb-6 text-emerald-600">
                        <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-xl font-bold group-hover:scale-110 transition-transform tracking-tighter italic">2.1</div>
                        <div class="text-slate-800">
                            <h4 class="text-xl font-extrabold">Quadrant 2.1</h4>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest italic leading-tight">LD 2 • 3 Districts</p>
                        </div>
                    </div>
                    <div class="bg-slate-50 p-5 rounded-3xl border border-slate-100 shadow-inner group-hover:bg-emerald-50/30 transition-colors">
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Total Quadrant Assets</p>
                        <p class="text-2xl font-extrabold text-slate-800 tracking-tighter leading-none">{{ number_format($quadrantTotals[3] ?? 0) }}</p>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 hover:border-emerald-200 transition-all duration-300 hover:-translate-y-1 cursor-pointer group">
                    <div class="flex items-center gap-4 mb-6 text-emerald-600">
                        <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-xl font-bold group-hover:scale-110 transition-transform tracking-tighter italic">2.2</div>
                        <div class="text-slate-800">
                            <h4 class="text-xl font-extrabold">Quadrant 2.2</h4>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest italic leading-tight">LD 2 • 4 Districts</p>
                        </div>
                    </div>
                    <div class="bg-slate-50 p-5 rounded-3xl border border-slate-100 shadow-inner group-hover:bg-emerald-50/30 transition-colors">
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Total Quadrant Assets</p>
                        <p class="text-2xl font-extrabold text-slate-800 tracking-tighter leading-none">{{ number_format($quadrantTotals[4] ?? 0) }}</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('schoolSearch');
            const searchResults = document.getElementById('searchResults');
            const schoolSelect = document.getElementById('schoolSelect');
            
            if (!searchInput || !schoolSelect || !searchResults) return;

            // Generate an array of objects from the select options
            const schools = Array.from(schoolSelect.options)
                .filter(opt => opt.value !== "")
                .map(opt => ({
                    id: opt.value,
                    name: opt.textContent
                }));

            // Handle typing in the search bar
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                searchResults.innerHTML = '';
                
                if (query.length === 0) {
                    searchResults.classList.add('hidden');
                    return;
                }

                // Filter schools based on query
                const filtered = schools.filter(school => school.name.toLowerCase().includes(query));
                
                if (filtered.length > 0) {
                    filtered.forEach(school => {
                        const li = document.createElement('li');
                        li.className = 'px-4 py-3 hover:bg-red-50 cursor-pointer text-sm font-semibold text-slate-800 transition-colors border-b border-slate-50 last:border-0';
                        li.textContent = school.name;
                        
                        // Handle clicking a search result
                        li.addEventListener('click', function() {
                            searchInput.value = school.name; // Set text into the input
                            schoolSelect.value = school.id; // Select the matching option in the dropdown
                            searchResults.classList.add('hidden'); // Hide the dropdown
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

            // If the user manually chooses a school from the standard <select>, populate the search box
            schoolSelect.addEventListener('change', function() {
                if (this.value === "") {
                    searchInput.value = "";
                } else {
                    searchInput.value = this.options[this.selectedIndex].text;
                }
            });

            // Cascading Dropdowns Logic
            const categorySelect = document.getElementById('categorySelect');
            const itemSelect = document.getElementById('itemSelect');
            const subItemSelect = document.getElementById('subItemSelect');
            
            // Store original options
            const allItems = Array.from(itemSelect.querySelectorAll('option[data-category]'));
            const allSubItems = Array.from(subItemSelect.querySelectorAll('option[data-item]'));

            // Function to reset and disable a select
            function resetDropdown(selectElement, defaultText) {
                selectElement.innerHTML = `<option value="">${defaultText}</option>`;
                selectElement.disabled = true;
            }

            // Category Change
            categorySelect.addEventListener('change', function() {
                const catId = this.value;
                resetDropdown(itemSelect, 'Select Item');
                resetDropdown(subItemSelect, 'Select Sub-Item');

                if (catId) {
                    const filteredItems = allItems.filter(opt => opt.getAttribute('data-category') === catId);
                    if (filteredItems.length > 0) {
                        filteredItems.forEach(opt => itemSelect.appendChild(opt.cloneNode(true)));
                        itemSelect.disabled = false;
                    }
                }
            });

            // Item Change
            itemSelect.addEventListener('change', function() {
                const itemId = this.value;
                resetDropdown(subItemSelect, 'Select Sub-Item');

                if (itemId) {
                    const filteredSubItems = allSubItems.filter(opt => opt.getAttribute('data-item') === itemId);
                    if (filteredSubItems.length > 0) {
                        filteredSubItems.forEach(opt => subItemSelect.appendChild(opt.cloneNode(true)));
                        subItemSelect.disabled = false;
                    } else {
                        // Keep disabled if no sub-items exist for this item
                        subItemSelect.innerHTML = `<option value="">No sub-items</option>`;
                    }
                }
            });
        });
    </script>
</body>
</html>