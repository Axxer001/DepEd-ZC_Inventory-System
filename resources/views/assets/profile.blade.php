<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $asset->property_number }} | Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        deped: '#c00000',
                        deped_light: '#fef2f2',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        [x-cloak] { display: none !important; }
        .animate-fade { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        
        .timeline-line {
            position: absolute;
            left: 11px;
            top: 24px;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
            z-index: 0;
        }
    </style>
</head>
<body class="flex min-h-screen text-slate-800 overflow-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll p-4 lg:p-8" x-data="{ activeTab: 'specs' }">
        
        {{-- Global Header (Fixed/Sticky) --}}
        <header class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 sticky top-0 z-50">
            <div class="flex items-center gap-5">
                <div class="w-12 h-12 bg-deped_light rounded-xl flex items-center justify-center border border-deped/20 shadow-sm shrink-0">
                    <svg class="w-6 h-6 text-deped" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-none uppercase italic">{{ $asset->description }}</h1>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-widest bg-slate-100 px-2.5 py-0.5 rounded-md border border-slate-200">{{ $asset->property_number }}</span>
                        {{-- Status Badge (Success placeholder) --}}
                        <span class="text-[10px] font-black text-emerald-700 uppercase tracking-widest bg-emerald-100 px-2 py-0.5 rounded-full flex items-center gap-1.5 shadow-sm">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span> Serviceable
                        </span>
                    </div>
                </div>
            </div>

            {{-- Actions Menu --}}
            <div class="flex items-center gap-3 shrink-0" x-data="{ open: false }">
                <button class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 uppercase tracking-widest hover:border-deped hover:text-deped hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-sm hover:shadow-md flex items-center gap-2 group">
                    <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    Edit
                </button>
                <div class="relative">
                    <button @click="open = !open" @click.away="open = false" class="px-5 py-2.5 bg-deped text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-red-800 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-md shadow-red-200 hover:shadow-lg hover:shadow-red-300 flex items-center gap-2">
                        Quick Actions
                        <svg class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="{'rotate-180': open}"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-2 scale-95" class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden transform origin-top-right">
                        <button class="w-full text-left px-4 py-3 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-deped hover:pl-5 transition-all flex items-center gap-2 border-b border-slate-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg> Initiate Transfer
                        </button>
                        <button class="w-full text-left px-4 py-3 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-amber-600 hover:pl-5 transition-all flex items-center gap-2 border-b border-slate-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg> Log Repair
                        </button>
                        <button class="w-full text-left px-4 py-3 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-slate-900 hover:pl-5 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg> Print QR/Tag
                        </button>
                    </div>
                </div>
                <div class="w-px h-8 bg-slate-200 mx-1"></div>
                <a href="/view-assets" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 uppercase tracking-widest hover:border-deped hover:text-deped hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-sm hover:shadow-md flex items-center gap-2 group">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Back
                </a>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 flex-grow pb-10">
            
            {{-- Left Sidebar: Asset Identity Card --}}
            <aside class="lg:col-span-3 flex flex-col gap-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                    <div class="aspect-square bg-slate-50 border-b border-slate-100 flex items-center justify-center p-6 relative group">
                        <img src="{{ asset('images/asset.png') }}" alt="Asset Placeholder" class="w-full h-full object-contain opacity-50 group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                            <button class="w-full py-2 bg-white/90 backdrop-blur-sm rounded-lg text-xs font-bold text-slate-800 hover:bg-white shadow-sm">Upload Photo</button>
                        </div>
                    </div>
                    
                    <div class="p-5 space-y-5">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Current Custodian</p>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-deped_light flex items-center justify-center text-deped font-black text-xs shrink-0">PO</div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800 uppercase leading-tight">Property Officer</p>
                                    <p class="text-[9px] font-bold text-slate-500 uppercase">Designated Custodian</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Current Location</p>
                            <a href="#" class="group flex items-start gap-2">
                                <svg class="w-4 h-4 text-deped shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <div>
                                    <p class="text-xs font-bold text-deped uppercase leading-tight group-hover:underline">{{ $asset->office_school_name }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase">{{ $asset->division }}</p>
                                </div>
                            </a>
                        </div>

                        <div class="pt-4 border-t border-slate-100">
                            <div class="flex justify-between items-end mb-1.5">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Est. Lifespan</p>
                                <p class="text-[10px] font-black text-slate-700">75%</p>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-gradient-to-r from-deped to-blue-400 h-full rounded-full" style="width: 75%"></div>
                            </div>
                            <p class="text-[8px] font-bold text-slate-400 uppercase mt-1.5 text-right">3 of 4 Years Remaining</p>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Main Content Area --}}
            <div class="lg:col-span-9 flex flex-col bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                
                {{-- Tabs Header --}}
                <div class="flex border-b border-slate-200 bg-slate-50/50 px-2 pt-2">
                    <button @click="activeTab = 'specs'" :class="{'bg-white border-slate-200 border-b-white text-deped shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'specs', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'specs'}" class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        Specifications
                    </button>
                    <button @click="activeTab = 'history'" :class="{'bg-white border-slate-200 border-b-white text-deped shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'history', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'history'}" class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        Lifecycle & History
                    </button>
                    <button @click="activeTab = 'docs'" :class="{'bg-white border-slate-200 border-b-white text-deped shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'docs', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'docs'}" class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        Documents & Media
                    </button>
                </div>

                {{-- Tab Contents --}}
                <div class="p-6 lg:p-8 flex-grow overflow-y-auto custom-scroll bg-white">
                    
                    {{-- TAB 1: Specifications --}}
                    <div x-show="activeTab === 'specs'" class="animate-fade space-y-8">
                        <div>
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-deped"></span> Technical Details
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-y-6 gap-x-8 bg-slate-50 rounded-xl p-6 border border-slate-100">
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Category</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase">{{ $asset->category_name }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Article / Item</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase">{{ $asset->item_name }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Quantity</p>
                                    <p class="text-xs font-black text-deped mt-1 uppercase">{{ $asset->quantity }} Unit(s)</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Brand / Make</p>
                                    <p class="text-xs font-bold text-slate-500 mt-1 uppercase italic">Not Specified</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Model</p>
                                    <p class="text-xs font-bold text-slate-500 mt-1 uppercase italic">Not Specified</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Serial Number</p>
                                    <p class="text-xs font-bold text-slate-500 mt-1 uppercase italic">Not Specified</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-deped"></span> Procurement Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8 bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Acquisition Date</p>
                                    <p class="text-sm font-black text-slate-800 mt-1 uppercase">{{ $asset->acquisition_date ? \Carbon\Carbon::parse($asset->acquisition_date)->format('F d, Y') : 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Unit Cost</p>
                                    <p class="text-lg font-black text-emerald-600 mt-0.5 tracking-tighter">₱ {{ number_format($asset->asset_cost, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Funding / Source</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase">{{ $asset->source_name }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Mode of Acquisition</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase">{{ $asset->mode_of_acquisition }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: Lifecycle & History --}}
                    <div x-show="activeTab === 'history'" class="animate-fade relative" x-cloak>
                        
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-deped"></span> Activity Timeline
                            </h3>
                            <div class="relative">
                                <input type="text" placeholder="Filter history..." class="pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold focus:outline-none focus:ring-2 focus:ring-deped/20 focus:border-deped transition-all">
                                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </div>

                        <div class="relative pl-3 max-w-3xl">
                            <div class="timeline-line"></div>
                            
                            <div class="space-y-6">
                                @foreach($timeline as $event)
                                <div class="relative pl-8 group">
                                    <div class="absolute left-[-2px] top-1 w-6 h-6 rounded-full bg-white border-2 {{ $event['type'] == 'Transfer' ? 'border-deped' : 'border-emerald-500' }} flex items-center justify-center shadow-sm z-10">
                                        <div class="w-2 h-2 {{ $event['type'] == 'Transfer' ? 'bg-deped' : 'bg-emerald-500' }} rounded-full"></div>
                                    </div>
                                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                                        <div class="flex justify-between items-start mb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[9px] font-black text-white uppercase tracking-widest {{ $event['type'] == 'Transfer' ? 'bg-deped' : 'bg-emerald-500' }} px-2 py-0.5 rounded">{{ $event['type'] }}</span>
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $event['date'] }}</span>
                                            </div>
                                        </div>
                                        <p class="text-sm font-bold text-slate-800 uppercase mt-2">{{ $event['description'] }}</p>
                                        <div class="mt-3 flex items-center gap-2 border-t border-slate-100 pt-2">
                                            <div class="w-4 h-4 rounded-full bg-slate-200 flex items-center justify-center">
                                                <svg class="w-2.5 h-2.5 text-slate-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                                            </div>
                                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Performed by: {{ $event['user'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach

                                {{-- Load More Button --}}
                                <div class="relative pl-8 pt-4 pb-2">
                                    <button class="text-[10px] font-black text-deped uppercase tracking-[0.2em] hover:underline bg-deped_light px-4 py-2 rounded-lg">Load More History</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 3: Documents & Media --}}
                    <div x-show="activeTab === 'docs'" class="animate-fade" x-cloak>
                        <div class="flex flex-col items-center justify-center h-64 border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50/50">
                            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-sm mb-4">
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest italic mb-1">No Documents Uploaded</h3>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest text-center">Upload manuals, warranty certs, or PTR forms.</p>
                            <button class="mt-4 px-6 py-2 bg-white border border-slate-200 rounded-lg text-[10px] font-black text-deped uppercase tracking-widest hover:border-deped transition-colors shadow-sm">Upload File</button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

</body>
</html>
