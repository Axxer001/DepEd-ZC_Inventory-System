<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | DepEd Zamboanga City</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Custom scrollbar */
        .custom-scroll::-webkit-scrollbar { height: 4px; width: 4px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex animate-fade-in text-slate-800 overflow-x-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll">
        
        {{-- Mobile Header --}}
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
            {{-- PAGE HEADER --}}
            <header class="flex flex-col md:flex-row md:justify-between md:items-center mb-10 gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight italic uppercase">Welcome, Admin!</h2>
                    <p class="text-slate-500 text-sm mt-1 font-medium italic">Zamboanga City Division Asset Overview</p>
                </div>
                <div class="hidden sm:block text-right bg-white px-6 py-3 rounded-2xl shadow-sm border border-slate-100">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-tight">{{ now()->format('l') }}</p>
                    <p class="text-lg font-bold text-slate-800 tracking-tight">{{ now()->format('M d, Y') }}</p>
                </div>
            </header>

            {{-- 1. MAIN STATS GRID --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-6">
                
                {{-- Total Assets --}}
                <div class="group bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-50 transition-all duration-300 hover:-translate-y-2 cursor-default">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-4 bg-blue-50 text-blue-500 rounded-2xl group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6.75h.75m-.75 3h.75m-.75 3h.75" />
                            </svg>
                        </div>
                        <span class="bg-blue-100 text-blue-600 px-4 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest italic">Division Wide</span>
                    </div>
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total System Assets</h3>
                    <p class="text-5xl font-extrabold text-slate-800 tracking-tighter leading-none">{{ number_format($totalAssets) }}</p>
                </div>

                {{-- Serviceable --}}
                <div class="group bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-50 transition-all duration-300 hover:-translate-y-2 cursor-default border-l-[12px] border-l-emerald-500">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-4 bg-emerald-50 text-emerald-600 rounded-2xl group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21a3.745 3.745 0 01-3.129-1.593 3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.745 3.745 0 013.296-1.043A3.745 3.745 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.745 3.745 0 013.296 1.043 3.745 3.745 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                            </svg>
                        </div>
                        <span class="bg-emerald-100 text-emerald-600 px-4 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest italic">Operational</span>
                    </div>
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Serviceable Condition</h3>
                    <p class="text-5xl font-extrabold text-emerald-600 tracking-tighter leading-none">{{ number_format($serviceableCount) }}</p>
                </div>

                {{-- Valuation --}}
                <div class="group bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-50 transition-all duration-300 hover:-translate-y-2 cursor-default">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-4 bg-slate-50 text-slate-600 rounded-2xl group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.546 1.16 3.74.322 4.298-1.517.39-1.283-.34-2.63-1.732-3.46l-1.096-.653c-1.393-.83-2.122-2.177-1.732-3.46.558-1.839 2.752-2.677 4.298-1.517l.879.659M10.5 21h3m-3-18h3" />
                            </svg>
                        </div>
                        <span class="bg-slate-200 text-slate-700 px-4 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest italic">Valuation</span>
                    </div>
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total Asset Value</h3>
                    <div class="flex items-baseline gap-1">
                        <span class="text-xl font-black text-slate-400">₱</span>
                        <p class="text-5xl font-extrabold text-slate-800 tracking-tighter leading-none">{{ number_format($totalAmount ?? 0, 2) }}</p>
                    </div>
                </div>

                {{-- Unserviceable --}}
                <div class="group bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-50 transition-all duration-300 hover:-translate-y-2 border-l-[12px] border-l-orange-500">
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Unserviceable List</h3>
                    <p class="text-5xl font-extrabold text-orange-500 tracking-tighter leading-none">{{ number_format($unserviceableCount) }}</p>
                    <span class="text-[10px] font-black text-orange-400 uppercase mt-4 block italic">For Disposal</span>
                </div>

                {{-- For Repair --}}
                <div class="group bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-50 transition-all duration-300 hover:-translate-y-2 border-l-[12px] border-l-amber-500">
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">For Repair Status</h3>
                    <p class="text-5xl font-extrabold text-amber-500 tracking-tighter leading-none">{{ number_format($forRepairCount) }}</p>
                    <span class="text-[10px] font-black text-amber-400 uppercase mt-4 block italic">In Maintenance</span>
                </div>
            </div>

           {{-- 2. DYNAMIC SOURCE BREAKDOWN --}}
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-12">
    {{-- Check if variable exists and is not empty --}}
    @if(isset($sourceBreakdown) && $sourceBreakdown->count() > 0)
        @foreach($sourceBreakdown as $source)
        <div class="group bg-white p-4 rounded-3xl shadow-sm border border-slate-100 transition-all hover:border-red-200">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-1.5 h-5 bg-[#c00000] rounded-full"></div>
                <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-wider truncate">
                    {{ $source->source_name ?? 'Unknown Source' }}
                </h4>
            </div>
            <div class="pl-3">
                <p class="text-xl font-black text-slate-800 tracking-tighter leading-none">
                    {{ number_format($source->total_qty) }}
                </p>
                <p class="text-[9px] font-bold text-slate-400 mt-1 italic">
                    ₱{{ number_format($source->total_amount, 0) }}
                </p>
            </div>
        </div>
        @endforeach
    @else
        {{-- Placeholder pag wala pang data --}}
        <div class="col-span-full py-4 text-center border-2 border-dashed border-slate-200 rounded-[2rem]">
            <p class="text-slate-400 text-xs font-bold uppercase italic tracking-widest">No fund sources registered yet</p>
        </div>
    @endif
</div>
            {{-- 3. QUICK ASSET ENTRY --}}
            <section class="mb-12">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/40 border border-slate-50 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-8 opacity-5">
                        <svg class="w-32 h-32 text-slate-900" fill="currentColor" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                    </div>
                    
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                        <div>
                            <h3 class="text-xl font-extrabold text-slate-800 flex items-center gap-2 italic uppercase">
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
                            <ul id="searchResults" class="absolute z-30 w-full bg-white border border-slate-100 rounded-2xl shadow-xl mt-2 max-h-60 overflow-y-auto hidden custom-scroll"></ul>
                        </div>
                    </div>

                    <form action="{{ route('inventory.dashboard.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-6 gap-6">
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
                        <div class="space-y-2 flex-grow">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Item</label>
                            <select id="itemSelect" name="item_id" disabled class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:outline-none transition-all disabled:opacity-50">
                                <option value="">Select Item</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}" data-category="{{ $item->category_id }}" data-avail="{{ $item->available_stock }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Sub-Item</label>
                            <select name="sub_item_id" id="subItemSelect" disabled class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:outline-none transition-all disabled:opacity-50">
                                <option value="">Select Sub-Item</option>
                                @foreach($subItems as $sub)
                                    <option value="{{ $sub->id }}" data-item="{{ $sub->item_id }}">{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Condition</label>
                            <select name="condition" class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:outline-none transition-all">
                                <option value="Serviceable" selected>Serviceable</option>
                                <option value="Unserviceable">Unserviceable</option>
                                <option value="For Repair">For Repair</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-3">
                            <div class="space-y-2 flex-grow">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Quantity</label>
                                <input type="number" name="quantity" id="quantityInput" value="1" min="1" required class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold text-center">
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

            {{-- 4. RECENTLY ADDED ITEMS TABLE --}}
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
                            <tbody>
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

            {{-- 5. DISTRICT SUMMARY PORTFOLIO --}}
            <div class="flex items-center justify-between mb-8 px-2">
                <h3 class="text-xl font-extrabold text-slate-800 tracking-tight flex items-center gap-3">
                    <span class="w-2 h-8 bg-blue-600 rounded-full"></span>
                    District Summary Portfolio
                </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                @php 
                    $quadrants = [
                        1 => ['label' => '1.1', 'desc' => 'LD 1 • 3 Districts', 'color' => 'blue'],
                        2 => ['label' => '1.2', 'desc' => 'LD 1 • 2 Districts', 'color' => 'blue'],
                        3 => ['label' => '2.1', 'desc' => 'LD 2 • 3 Districts', 'color' => 'emerald'],
                        4 => ['label' => '2.2', 'desc' => 'LD 2 • 4 Districts', 'color' => 'emerald'],
                    ];
                @endphp

                @foreach($quadrants as $id => $q)
                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 hover:border-{{ $q['color'] }}-200 transition-all duration-300 hover:-translate-y-1 cursor-pointer group">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-{{ $q['color'] }}-50 text-{{ $q['color'] }}-600 rounded-2xl flex items-center justify-center text-xl font-bold group-hover:scale-110 transition-transform tracking-tighter italic">{{ $q['label'] }}</div>
                        <div>
                            <h4 class="text-xl font-extrabold text-slate-800">Quadrant {{ $q['label'] }}</h4>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest italic leading-tight">{{ $q['desc'] }}</p>
                        </div>
                    </div>
                    <div class="bg-slate-50 p-5 rounded-3xl border border-slate-100 shadow-inner group-hover:bg-{{ $q['color'] }}-50/30 transition-colors">
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Total Quadrant Assets</p>
                        <p class="text-2xl font-extrabold text-slate-800 tracking-tighter leading-none">{{ number_format($quadrantTotals[$id] ?? 0) }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </main>
    </div>

    {{-- SCRIPTS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- SCHOOL SEARCH LOGIC ---
            const searchInput = document.getElementById('schoolSearch');
            const searchResults = document.getElementById('searchResults');
            const schoolSelect = document.getElementById('schoolSelect');
            
            if (searchInput && schoolSelect && searchResults) {
                const schools = Array.from(schoolSelect.options)
                    .filter(opt => opt.value !== "")
                    .map(opt => ({ id: opt.value, name: opt.textContent }));

                searchInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase().trim();
                    searchResults.innerHTML = '';
                    if (query.length === 0) { searchResults.classList.add('hidden'); return; }

                    const filtered = schools.filter(s => s.name.toLowerCase().includes(query));
                    if (filtered.length > 0) {
                        filtered.forEach(s => {
                            const li = document.createElement('li');
                            li.className = 'px-4 py-3 hover:bg-red-50 cursor-pointer text-sm font-semibold text-slate-800 transition-colors border-b border-slate-50 last:border-0';
                            li.textContent = s.name;
                            li.addEventListener('click', () => {
                                searchInput.value = s.name;
                                schoolSelect.value = s.id;
                                searchResults.classList.add('hidden');
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

                document.addEventListener('click', (e) => {
                    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) searchResults.classList.add('hidden');
                });
            }

            // --- CASCADING DROPDOWNS ---
            const categorySelect = document.getElementById('categorySelect');
            const itemSelect = document.getElementById('itemSelect');
            const subItemSelect = document.getElementById('subItemSelect');
            
            const allItems = Array.from(itemSelect.querySelectorAll('option[data-category]'));
            const allSubItems = Array.from(subItemSelect.querySelectorAll('option[data-item]'));

            function resetDropdown(el, txt) { el.innerHTML = `<option value="">${txt}</option>`; el.disabled = true; }

            categorySelect?.addEventListener('change', function() {
                const catId = this.value;
                resetDropdown(itemSelect, 'Select Item');
                resetDropdown(subItemSelect, 'Select Sub-Item');
                if (catId) {
                    const filtered = allItems.filter(opt => opt.getAttribute('data-category') === catId);
                    filtered.forEach(opt => {
                        const clone = opt.cloneNode(true);
                        clone.textContent = `${clone.textContent} (Avail: ${clone.getAttribute('data-avail')})`;
                        itemSelect.appendChild(clone);
                    });
                    itemSelect.disabled = (filtered.length === 0);
                }
            });

            itemSelect?.addEventListener('change', function() {
                const itemId = this.value;
                resetDropdown(subItemSelect, 'Select Sub-Item');
                if (itemId) {
                    const filtered = allSubItems.filter(opt => opt.getAttribute('data-item') === itemId);
                    filtered.forEach(opt => subItemSelect.appendChild(opt.cloneNode(true)));
                    subItemSelect.disabled = (filtered.length === 0);
                    if (filtered.length === 0) subItemSelect.innerHTML = '<option value="">No sub-items</option>';
                }
            });

            // --- FORM VALIDATION ---
            document.querySelector('form[action*="dashboard.store"]')?.addEventListener('submit', function(e) {
                const qtyInput = document.getElementById('quantityInput');
                const selectedItem = itemSelect.options[itemSelect.selectedIndex];
                const avail = parseFloat(selectedItem?.getAttribute('data-avail') || 0);
                const qty = parseFloat(qtyInput.value);

                if (qty > avail) {
                    e.preventDefault();
                    alert(`Not enough stock! Available: ${avail}`);
                }
            });
        });

        function toggleSidebar() {
            document.getElementById('sidebar')?.classList.toggle('-translate-x-full');
        }
    </script>
</body>
</html>