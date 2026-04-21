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
            <div class="space-y-8 mb-6">
                {{-- TOP ROW: General Overview --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    {{-- Total System Assets --}}
                    <div class="group bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-50 transition-all duration-300 hover:-translate-y-2 cursor-default">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-4 bg-blue-50 text-blue-500 rounded-2xl group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6.75h.75m-.75 3h.75m-.75 3h.75" />
                                </svg>
                            </div>
                            <span class="bg-blue-100 text-blue-600 px-4 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest italic">Inventory</span>
                        </div>
                        <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total System Assets</h3>
                        <p class="text-5xl font-extrabold text-slate-800 tracking-tighter leading-none">{{ number_format($totalAssets) }}</p>
                    </div>

                    {{-- Total Assets Distributed --}}
                    <div class="group bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-50 transition-all duration-300 hover:-translate-y-2 cursor-default">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-4 bg-indigo-50 text-indigo-500 rounded-2xl group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25s-7.5-4.108-7.5-11.25a7.5 7.5 0 1115 0z" />
                                </svg>
                            </div>
                            <span class="bg-indigo-100 text-indigo-600 px-4 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest italic">Deployments</span>
                        </div>
                        <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total Assets Distributed</h3>
                        <p class="text-5xl font-extrabold text-slate-800 tracking-tighter leading-none">{{ number_format($distributedCount ?? 0) }}</p>
                    </div>

                    {{-- Total Asset Value --}}
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
                </div>

                {{-- BOTTOM ROW: Condition Status --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    {{-- Serviceable --}}
                    <div class="group bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-50 transition-all duration-300 hover:-translate-y-2 cursor-default border-l-[12px] border-l-emerald-500">
                        <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Serviceable Condition</h3>
                        <p class="text-5xl font-extrabold text-emerald-600 tracking-tighter leading-none">{{ number_format($serviceableCount) }}</p>
                        <span class="text-[10px] font-black text-emerald-400 uppercase mt-4 block italic tracking-wider">Operational</span>
                    </div>

                    {{-- For Repair --}}
                    <div class="group bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-50 transition-all duration-300 hover:-translate-y-2 border-l-[12px] border-l-amber-500">
                        <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">For Repair</h3>
                        <p class="text-5xl font-extrabold text-amber-500 tracking-tighter leading-none">{{ number_format($forRepairCount) }}</p>
                        <span class="text-[10px] font-black text-amber-400 uppercase mt-4 block italic tracking-wider">In Maintenance</span>
                    </div>

                    {{-- Unserviceable --}}
                    <div class="group bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-50 transition-all duration-300 hover:-translate-y-2 border-l-[12px] border-l-orange-500">
                        <h3 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Unserviceable</h3>
                        <p class="text-5xl font-extrabold text-orange-500 tracking-tighter leading-none">{{ number_format($unserviceableCount) }}</p>
                        <span class="text-[10px] font-black text-orange-400 uppercase mt-4 block italic tracking-wider">For Disposal</span>
                    </div>
                </div>
            </div>

           {{-- 2. DYNAMIC SOURCE BREAKDOWN --}}
            <div class="mb-12 relative">
                <div class="flex items-center justify-between mb-4 px-2">
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] italic">Fund Source Portfolio</h4>
                </div>
                
                <div class="flex overflow-x-auto gap-6 pb-6 custom-scroll -mx-2 px-2 snap-x">
                    @forelse($sourceBreakdown as $source)
                    <div class="group bg-white p-6 rounded-[2rem] shadow-lg shadow-slate-200/40 border border-slate-50 transition-all hover:border-red-200 min-w-[240px] flex-shrink-0 snap-start">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-1.5 h-6 bg-[#c00000] rounded-full group-hover:scale-y-125 transition-transform"></div>
                            <h4 class="text-[11px] font-black text-slate-500 uppercase tracking-wider truncate max-w-[180px]" title="{{ $source->source_name }}">
                                {{ $source->source_name ?? 'Unknown Source' }}
                            </h4>
                        </div>
                        <div class="pl-4">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 italic">Source Asset Value</p>
                            <p class="text-2xl font-black text-slate-800 tracking-tighter leading-none mb-2">
                                ₱{{ number_format($source->total_amount, 2) }}
                            </p>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded-md text-[9px] font-bold italic">{{ number_format($source->total_qty) }} Items</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="w-full py-10 text-center border-2 border-dashed border-slate-200 rounded-[2.5rem] bg-white/50">
                        <p class="text-slate-400 text-[10px] font-bold uppercase italic tracking-[0.3em]">No fund sources registered yet</p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- 3. QUICK ASSET ENTRY (SIMPLIFIED SINGLE RECIPIENT) --}}
            <section class="mb-12">
                <div class="bg-white p-10 rounded-[3rem] shadow-2xl shadow-slate-200/50 border border-slate-50 relative overflow-hidden">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                        <div>
                            <h3 class="text-2xl font-black text-slate-800 flex items-center gap-3 italic uppercase leading-none">
                                <span class="bg-[#c00000] text-white px-2 py-1 rounded-lg text-xs font-black italic shadow-md">QUICK</span>
                                Asset Entry
                            </h3>
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em] mt-2 ml-1">Direct distribution shortcut</p>
                        </div>
                    </div>

                    <div class="space-y-10">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                            {{-- Entity Type --}}
                            <div class="space-y-3">
                                <label class="text-[10px] font-black text-[#c00000] uppercase tracking-widest ml-1 italic underline underline-offset-4 decoration-2">Entity Type <span class="text-red-500">*</span></label>
                                <select id="quickEntityType" onchange="handleQuickEntityTypeChange()" class="w-full p-5 bg-slate-50 border border-slate-100 rounded-2xl font-black text-slate-700 text-sm outline-none focus:ring-4 focus:ring-red-50 transition-all cursor-pointer appearance-none">
                                    <option value="" selected disabled>-- Select Entity Type --</option>
                                    <option value="school">School (Internal)</option>
                                    <option value="external">External (Offices / Orgs)</option>
                                </select>
                            </div>
                            {{-- Recipient Dropdown --}}
                            <div class="space-y-3">
                                <label id="quickRecipientLabel" class="text-[10px] font-black text-slate-300 uppercase tracking-widest ml-1 italic">Target Recipient <span class="text-red-500">*</span></label>
                                <select id="quickRecipientSelect" disabled onchange="handleQuickRecipientChange()" class="w-full p-5 bg-slate-50 border border-slate-100 rounded-2xl font-bold text-slate-700 text-sm outline-none focus:ring-4 focus:ring-red-50 transition-all cursor-pointer disabled:opacity-50 appearance-none">
                                    <option value="">Select type first...</option>
                                </select>
                            </div>
                            {{-- Authorized Receiver --}}
                            <div class="space-y-3">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Authorized Receiver <span class="text-slate-400 text-[9px] font-medium lowercase italic">(optional)</span></label>
                                <select id="quickPersonnelSelect" disabled class="w-full p-5 bg-slate-50 border border-slate-100 rounded-2xl font-bold text-slate-700 text-sm outline-none focus:ring-4 focus:ring-red-50 transition-all cursor-pointer disabled:opacity-50 appearance-none">
                                    <option value="">No personnel found...</option>
                                </select>
                            </div>
                        </div>

                        {{-- ASSET SPECS --}}
                        <div id="quickAssetSpecs" class="hidden pt-10 border-t-2 border-dashed border-slate-100 space-y-8 animate-fade-in">
                            <div>
                                <h4 class="text-xl font-black text-[#c00000] uppercase tracking-tight italic">Distribution Specifications</h4>
                                <p class="text-slate-400 text-[10px] font-bold uppercase mt-1 tracking-widest italic">Define the items for the recipient selected above</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="space-y-2">
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 italic">Main Category</label>
                                    <select id="quickCategorySelect" class="w-full p-5 bg-slate-50 border border-slate-100 rounded-2xl font-bold text-slate-700 text-sm outline-none focus:border-[#c00000] transition-all">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 italic">Item Name</label>
                                    <select id="quickItemSelect" disabled class="w-full p-5 bg-slate-50 border border-slate-100 rounded-2xl font-bold text-slate-700 text-sm outline-none disabled:opacity-50">
                                        <option value="">Select Item</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}" data-category="{{ $item->category_id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 italic">Model / Specifications</label>
                                    <select id="quickSubItemSelect" disabled class="w-full p-5 bg-slate-50 border border-slate-100 rounded-2xl font-bold text-slate-700 text-sm outline-none disabled:opacity-50">
                                        <option value="">Select Sub-Item</option>
                                        @foreach($subItems as $sub)
                                            <option value="{{ $sub->id }}" data-item="{{ $sub->item_id }}">{{ $sub->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 italic">Quantity</label>
                                    <input type="number" id="quickQuantity" value="1" min="1" class="w-full p-5 bg-white border-2 border-slate-100 rounded-2xl font-black text-slate-800 text-center text-lg outline-none focus:border-[#c00000]">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 italic">Condition</label>
                                    <select id="quickCondition" class="w-full p-5 bg-slate-50 border border-slate-100 rounded-2xl font-bold text-slate-700 text-sm outline-none">
                                        <option value="Serviceable">Serviceable</option>
                                        <option value="Unserviceable">Unserviceable</option>
                                        <option value="For Repair">For Repair</option>
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <button type="button" onclick="submitQuickEntry()" class="w-full py-5 bg-slate-900 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-black transition-all active:scale-95 italic text-sm shadow-2xl">
                                        REGISTER TO RECIPIENT ⚡
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
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
        // Data passed from Laravel
        const stakeholders = @json($stakeholders);
        const allItems = @json($items);
        const allSubItems = @json($subItems);
        const schools = @json($schools);

        let selectedEntityType = null;

        function handleQuickEntityTypeChange() {
            const type = document.getElementById('quickEntityType').value;
            const recipientSelect = document.getElementById('quickRecipientSelect');
            const label = document.getElementById('quickRecipientLabel');
            const personnelSelect = document.getElementById('quickPersonnelSelect');
            const specsSection = document.getElementById('quickAssetSpecs');

            selectedEntityType = type;
            recipientSelect.disabled = false;
            recipientSelect.innerHTML = '<option value="" selected disabled>-- Select Recipient --</option>';
            personnelSelect.disabled = true;
            personnelSelect.innerHTML = '<option value="">No personnel found...</option>';
            specsSection.classList.add('hidden');

            label.classList.add('text-[#c00000]');
            label.classList.remove('text-slate-300');
            label.innerText = type === 'school' ? 'TARGET SCHOOL / OFFICE *' : 'EXTERNAL OFFICE / ORG *';

            let list = [];
            if (type === 'school') {
                list = schools;
            } else {
                list = stakeholders.filter(s => s.type === 'Recipient' && s.entity_type !== 'School' && s.entity_type !== 'Individual');
            }

            list.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.name;
                recipientSelect.appendChild(opt);
            });
        }

        function handleQuickRecipientChange() {
            const recipientId = document.getElementById('quickRecipientSelect').value;
            const personnelSelect = document.getElementById('quickPersonnelSelect');
            const specsSection = document.getElementById('quickAssetSpecs');

            if (!recipientId) {
                specsSection.classList.add('hidden');
                personnelSelect.disabled = true;
                return;
            }

            // Reveal Specs immediately
            specsSection.classList.remove('hidden');

            // Load Personnel
            const selectedS = stakeholders.find(s => s.id == recipientId) || schools.find(s => s.id == recipientId);
            let relatedPeople = [];

            if (selectedEntityType === 'school') {
                relatedPeople = stakeholders.filter(s => s.entity_type === 'Individual' && s.school_id == selectedS.id);
            } else {
                relatedPeople = stakeholders.filter(s => s.entity_type === 'Individual' && s.parent_id == recipientId);
            }

            if (relatedPeople.length > 0) {
                personnelSelect.disabled = false;
                personnelSelect.innerHTML = '<option value="">-- No Personnel Selected (Direct to Office) --</option>';
                relatedPeople.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = p.name + (p.position ? ` (${p.position})` : '');
                    personnelSelect.appendChild(opt);
                });
            } else {
                personnelSelect.disabled = true;
                personnelSelect.innerHTML = '<option value="">No personnel registered</option>';
            }
        }

        function submitQuickEntry() {
            const orgId = document.getElementById('quickRecipientSelect').value;
            const personnelId = document.getElementById('quickPersonnelSelect').value;
            const recipientId = personnelId || orgId;

            const itemId = document.getElementById('quickItemSelect').value;
            const subItemId = document.getElementById('quickSubItemSelect').value;
            const quantity = document.getElementById('quickQuantity').value;
            const condition = document.getElementById('quickCondition').value;

            if (!recipientId || !itemId || !subItemId || !quantity) {
                alert('Please complete all required specifications.');
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('inventory.dashboard.store') }}";
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = "{{ csrf_token() }}";
            form.appendChild(csrf);

            const fields = { recipient_id: recipientId, item_id: itemId, sub_item_id: subItemId, quantity, condition };
            for (const [key, value] of Object.entries(fields)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        }

        document.addEventListener('DOMContentLoaded', function() {
            // --- CASCADING DROPDOWNS ---
            const categorySelect = document.getElementById('quickCategorySelect');
            const itemSelect = document.getElementById('quickItemSelect');
            const subItemSelect = document.getElementById('quickSubItemSelect');

            function resetDropdown(el, txt) { el.innerHTML = `<option value="">${txt}</option>`; el.disabled = true; }

            categorySelect?.addEventListener('change', function() {
                const catId = this.value;
                resetDropdown(itemSelect, 'Select Item');
                resetDropdown(subItemSelect, 'Select Sub-Item');
                if (catId) {
                    const filtered = allItems.filter(i => i.category_id == catId);
                    filtered.forEach(i => {
                        const opt = document.createElement('option');
                        opt.value = i.id;
                        opt.textContent = `${i.name} (Avail: ${i.available_stock})`;
                        itemSelect.appendChild(opt);
                    });
                    itemSelect.disabled = (filtered.length === 0);
                }
            });

            itemSelect?.addEventListener('change', function() {
                const itemId = this.value;
                resetDropdown(subItemSelect, 'Select Sub-Item');
                if (itemId) {
                    const filtered = allSubItems.filter(s => s.item_id == itemId);
                    filtered.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.id;
                        opt.textContent = `${s.name} (Stock: ${s.quantity})`;
                        subItemSelect.appendChild(opt);
                    });
                    subItemSelect.disabled = (filtered.length === 0);
                    if (filtered.length === 0) subItemSelect.innerHTML = '<option value="">No sub-items</option>';
                }
            });
        });

        function toggleSidebar() {
            document.getElementById('sidebar')?.classList.toggle('-translate-x-full');
        }
    </script>
</body>
</html>