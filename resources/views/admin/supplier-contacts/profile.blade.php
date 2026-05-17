<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $contact->name }} | Supplier Personnel Profile</title>
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
        
        /* Dark Mode Overrides */
        html.dark body { background-color: #0f172a; color: #f8fafc; }
        html.dark .bg-white { background-color: #1e293b !important; border-color: #334155 !important; }
        html.dark .text-slate-900 { color: #f8fafc !important; }
        html.dark .text-slate-800 { color: #e2e8f0 !important; }
        html.dark .text-slate-700 { color: #cbd5e1 !important; }
        html.dark .bg-slate-50 { background-color: #0f172a !important; border-color: #1e293b !important; }
        html.dark .bg-slate-100 { background-color: #334155 !important; border-color: #475569 !important; text-color: #e2e8f0; }
        html.dark .border-slate-200 { border-color: #334155 !important; }
        html.dark .border-slate-100 { border-color: #334155 !important; }
        html.dark .divide-slate-50 { divide-color: #334155 !important; }
        html.dark .hover\:bg-slate-50:hover { background-color: #334155/30 !important; }
    </style>
</head>
<body class="flex min-h-screen text-slate-800 overflow-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll p-4 lg:p-8" x-data="{ activeTab: 'assets' }">
        
        {{-- Global Header --}}
        <header class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 sticky top-0 z-50">
            <div class="flex items-center gap-5">
                <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center border border-deped/20 shadow-sm shrink-0 dark:bg-red-950/20 dark:border-red-900/40">
                    <!-- Supplier/Provider Icon -->
                    <svg class="w-6 h-6 text-deped dark:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight leading-none uppercase italic">{{ $contact->name }}</h1>
                    <div class="flex flex-wrap items-center gap-3 mt-2">
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 rounded-md border border-slate-200 dark:border-slate-700">Org: {{ $contact->organization ?? 'N/A' }}</span>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 rounded-md border border-slate-200 dark:border-slate-700">Role: {{ $contact->position ?? 'Personnel' }}</span>
                        <span class="text-[10px] font-black text-red-700 dark:text-red-400 uppercase tracking-widest bg-red-50 dark:bg-red-950/40 px-2 py-0.5 rounded-full flex items-center gap-1.5 shadow-sm">
                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span> Certified Provider Contact
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('admin.supplier_contacts') }}" class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-black text-slate-600 dark:text-slate-300 uppercase tracking-widest hover:border-deped dark:hover:border-red-500 hover:text-deped dark:hover:text-red-500 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-sm hover:shadow-md flex items-center gap-2 group">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Back to Registry
                </a>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 flex-grow pb-10">
            
            {{-- Left Sidebar: Supplier Personnel Identity Details --}}
            <aside class="lg:col-span-3 flex flex-col gap-6 z-40 relative">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-6">
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Contact Information</p>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 bg-slate-50 dark:bg-slate-800 rounded-lg flex items-center justify-center shrink-0 border border-slate-100 dark:border-slate-705">
                                    <svg class="w-4 h-4 text-deped dark:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Phone Number</p>
                                    <p class="text-xs font-black text-slate-750 dark:text-slate-200 uppercase leading-snug truncate mt-0.5">{{ $contact->contact_number ?: 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 bg-slate-50 dark:bg-slate-800 rounded-lg flex items-center justify-center shrink-0 border border-slate-100 dark:border-slate-705">
                                    <svg class="w-4 h-4 text-deped dark:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Email Address</p>
                                    <p class="text-xs font-black text-slate-750 dark:text-slate-200 lowercase leading-snug truncate mt-0.5">{{ $contact->email ?: 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100 dark:border-slate-700 space-y-4">
                        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 rounded-xl border border-emerald-100 dark:border-emerald-900/30">
                            <p class="text-[9px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-1">Total Supplied Value</p>
                            <p class="text-xl font-black text-emerald-700 dark:text-emerald-400 leading-none">₱ {{ number_format($stats->total_value, 2) }}</p>
                            <p class="text-[10px] font-bold text-emerald-600/70 dark:text-emerald-400/70 mt-1 uppercase">{{ $stats->total_supplied }} Total Item(s)</p>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Main Content --}}
            <div class="lg:col-span-9 flex flex-col bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                {{-- Supplier Custom Tabs --}}
                <div class="flex border-b border-slate-200 bg-slate-50/50 dark:bg-slate-900/50 px-2 pt-2">
                    <button @click="activeTab = 'assets'" class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-slate-200 dark:border-slate-700 border-b-transparent text-deped dark:text-red-500 shadow-[0_-2px_4px_rgba(0,0,0,0.02)] rounded-t-xl relative top-[1px]">
                        Supplied Assets & Equipment
                    </button>
                </div>

                <div class="p-6 flex-grow overflow-y-auto custom-scroll">
                    
                    {{-- TAB: Assets --}}
                    <div x-show="activeTab === 'assets'" class="animate-fade space-y-4">
                        @if($assets->count() > 0)
                        <div class="overflow-x-auto w-full">
                            <table class="w-full text-left border-collapse" style="min-width: 800px;">
                                <thead>
                                    <tr class="border-b border-slate-100 dark:border-slate-800">
                                        <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Item details</th>
                                        <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Property & Serial</th>
                                        <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Current Location</th>
                                        <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Condition</th>
                                        <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Cost</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                                    @foreach($assets as $asset)
                                    <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                        <td class="py-4 pr-4">
                                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase leading-none">{{ $asset->item_name }}</p>
                                            <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mt-1">{{ $asset->category_name }}</p>
                                            <p class="text-[9px] text-slate-500 dark:text-slate-400 mt-1 uppercase font-semibold">Brand: {{ $asset->brand ?: 'N/A' }} | Model: {{ $asset->model ?: 'N/A' }}</p>
                                        </td>
                                        <td class="py-4">
                                            <span class="text-[10px] font-black text-slate-800 dark:text-slate-200 block uppercase">{{ $asset->property_number }}</span>
                                            <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 block mt-0.5">SN: {{ $asset->serial_number ?: 'N/A' }}</span>
                                        </td>
                                        <td class="py-4">
                                            <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300 block uppercase leading-tight truncate max-w-[200px]">{{ $asset->school_name ?: 'N/A' }}</span>
                                            <span class="text-[9px] font-semibold text-slate-400 dark:text-slate-500 block mt-0.5 uppercase truncate max-w-[200px]">{{ $asset->office_name ?: 'N/A' }}</span>
                                        </td>
                                        <td class="py-4 text-center">
                                            @php
                                                $cond = $asset->condition ?: 'Good';
                                                $theme = $cond === 'Good' || $cond === 'Serviceable' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400';
                                            @endphp
                                            <span class="px-2.5 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider {{ $theme }}">{{ $cond }}</span>
                                        </td>
                                        <td class="py-4 text-right">
                                            <p class="text-xs font-black text-emerald-600 dark:text-emerald-400 italic">₱ {{ number_format($asset->asset_cost, 2) }}</p>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="flex flex-col items-center justify-center py-12 bg-slate-50 dark:bg-slate-900/20 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest italic">No assets registered to have been supplied by this personnel</p>
                        </div>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>

</body>
</html>
