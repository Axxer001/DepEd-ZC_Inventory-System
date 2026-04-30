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
                    <div class="group bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-50 transition-all duration-300 hover:-translate-y-2 cursor-default relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50/50 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
                        <div class="relative z-10">
                            <div class="flex justify-between items-start mb-6">
                                <div class="p-4 bg-blue-600 text-white rounded-2xl shadow-lg shadow-blue-200 group-hover:rotate-6 transition-transform">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-7 h-7">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6.75h.75m-.75 3h.75m-.75 3h.75" />
                                    </svg>
                                </div>
                                <span class="bg-blue-50 text-blue-700 px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest italic border border-blue-100">Inventory</span>
                            </div>
                            <h3 class="text-slate-400 text-xs font-bold uppercase tracking-[0.15em] mb-2">Total System Assets</h3>
                            <p class="text-5xl font-black text-slate-900 tracking-tighter leading-none mb-6">{{ number_format($totalAssets) }}</p>
                            
                            <div class="flex items-center gap-2 pt-4 border-t border-slate-50">
                                <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                <span class="text-[10px] font-extrabold text-slate-500 uppercase italic">
                                    Latest Sync: {{ $recentOwnerships->first() ? \Carbon\Carbon::parse($recentOwnerships->first()->created_at)->format('M d, Y') : 'No Data' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Total Assets Distributed --}}
                    <div class="group bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-50 transition-all duration-300 hover:-translate-y-2 cursor-default relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50/50 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
                        <div class="relative z-10">
                            <div class="flex justify-between items-start mb-6">
                                <div class="p-4 bg-indigo-600 text-white rounded-2xl shadow-lg shadow-indigo-200 group-hover:rotate-6 transition-transform">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-7 h-7">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25s-7.5-4.108-7.5-11.25a7.5 7.5 0 1115 0z" />
                                    </svg>
                                </div>
                                <span class="bg-indigo-50 text-indigo-700 px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest italic border border-indigo-100">Deployments</span>
                            </div>
                            <h3 class="text-slate-400 text-xs font-bold uppercase tracking-[0.15em] mb-2">Total Assets Distributed</h3>
                            <p class="text-5xl font-black text-slate-900 tracking-tighter leading-none mb-6">{{ number_format($distributedCount ?? 0) }}</p>
                            
                            <div class="flex items-center gap-2 pt-4 border-t border-slate-50">
                                <div class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></div>
                                <span class="text-[10px] font-extrabold text-slate-500 uppercase italic">
                                    Last Deployment: {{ $recentOwnerships->first() ? \Carbon\Carbon::parse($recentOwnerships->first()->created_at)->format('M d, Y') : 'No Data' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Total Asset Value --}}
                    <div class="group bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-50 transition-all duration-300 hover:-translate-y-2 cursor-default relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-slate-100/50 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
                        <div class="relative z-10">
                            <div class="flex justify-between items-start mb-6">
                                <div class="p-4 bg-slate-900 text-white rounded-2xl shadow-lg shadow-slate-300 group-hover:rotate-6 transition-transform">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-7 h-7">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21V5h6a5 5 0 0 1 0 10H7m-1-7h11m-11 3h11" />
                                    </svg>
                                </div>
                                <span class="bg-slate-100 text-slate-700 px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest italic border border-slate-200">Valuation</span>
                            </div>
                            <h3 class="text-slate-400 text-xs font-bold uppercase tracking-[0.15em] mb-2">Total Asset Value</h3>
                            <div class="flex items-baseline gap-1 mb-6">
                                <span class="text-2xl font-black text-slate-400">₱</span>
                                <p class="font-black text-slate-900 tracking-tighter leading-none {{ strlen(number_format($totalAmount ?? 0, 2)) > 15 ? 'text-2xl' : (strlen(number_format($totalAmount ?? 0, 2)) > 12 ? 'text-3xl' : (strlen(number_format($totalAmount ?? 0, 2)) > 10 ? 'text-4xl' : 'text-5xl')) }}">{{ number_format($totalAmount ?? 0, 2) }}</p>
                            </div>
                            
                            <div class="flex items-center gap-2 pt-4 border-t border-slate-50">
                                <div class="w-2 h-2 rounded-full bg-slate-400 animate-pulse"></div>
                                <span class="text-[10px] font-extrabold text-slate-500 uppercase italic">
                                    Valuation Date: {{ now()->format('M d, Y') }}
                                </span>
                            </div>
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

            {{-- 3. DISTRICT SUMMARY PORTFOLIO --}}
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
        function toggleSidebar() {
            document.getElementById('sidebar')?.classList.toggle('-translate-x-full');
        }
    </script>
</body>
</html>