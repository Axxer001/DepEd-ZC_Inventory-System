<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $contact->name }} | Supplier Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        deped: '#c00000',
                        deped_hover: '#9e0000',
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
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(12px); border: 1px solid rgba(226, 232, 240, 0.8); }
        .bento-glow-red { position: relative; overflow: hidden; }
        .bento-glow-red::after { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(192, 0, 0, 0.04) 0%, transparent 60%); pointer-events: none; }
        
        /* Dark Mode */
        html.dark body { background-color: #0b0f19; color: #f8fafc; }
        html.dark .glass-card { background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(255, 255, 255, 0.05); }
    </style>
</head>
<body class="flex min-h-screen text-slate-800 dark:text-slate-100 overflow-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll p-4 lg:p-8">
        
        {{-- Elegant Red & White Header Card --}}
        <header class="relative overflow-hidden bg-deped text-white rounded-3xl p-8 mb-6 shadow-xl border border-red-700/20 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            {{-- Radial Crimson Glow --}}
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(255,255,255,0.15),transparent_60%)] pointer-events-none"></div>
            
            <div class="flex items-center gap-6 relative z-10">
                <div class="w-16 h-16 bg-white/10 border border-white/20 rounded-2xl flex items-center justify-center shadow-inner shrink-0">
                    <!-- Professional Partner/Supplier Icon -->
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                </div>
                <div>
                    <span class="text-[10px] font-extrabold text-red-100 uppercase tracking-[0.2em] bg-white/10 px-2 py-0.5 rounded">Supplier Representative</span>
                    <h1 class="text-3xl font-extrabold tracking-tight mt-2 uppercase italic">{{ $contact->name }}</h1>
                    <div class="flex flex-wrap items-center gap-3 mt-3">
                        <span class="text-xs font-black bg-white text-deped border border-white px-3 py-1 rounded-xl uppercase shadow-sm">{{ $contact->organization ?? 'External Provider' }}</span>
                        <span class="text-xs font-semibold bg-red-800/40 text-red-50 border border-red-550 px-3 py-1 rounded-xl uppercase">{{ $contact->position ?? 'Personnel' }}</span>
                    </div>
                </div>
            </div>

            <div class="relative z-10 flex items-center gap-3 shrink-0">
                <a href="{{ route('admin.supplier_contacts') }}" class="px-5 py-3 bg-white hover:bg-slate-50 text-deped border border-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all duration-300 flex items-center gap-2 group shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Back to Registry
                </a>
            </div>
        </header>

        {{-- Bento Grid Dashboard Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 pb-12">
            
            {{-- BENTO ITEM 1: Profile Details Card (Span 4) --}}
            <section class="lg:col-span-4 flex flex-col gap-6">
                <div class="glass-card rounded-[2.5rem] p-6 shadow-sm border border-slate-200/60 dark:border-slate-800 space-y-6">
                    <div class="pb-4 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="text-xs font-extrabold text-red-500 uppercase tracking-widest">Representative Card</h3>
                        <p class="text-lg font-black text-slate-900 dark:text-slate-100 uppercase mt-1">Personnel Info</p>
                    </div>

                    <div class="space-y-4">
                        <div class="p-4 bg-red-50/30 dark:bg-red-950/10 rounded-2xl border border-red-100/50 dark:border-red-900/20">
                            <span class="text-[9px] font-black text-red-500 uppercase block tracking-wider">Representative Name</span>
                            <span class="text-sm font-extrabold text-slate-800 dark:text-slate-200 mt-1 block uppercase">{{ $contact->name }}</span>
                        </div>
                        <div class="p-4 bg-red-50/30 dark:bg-red-950/10 rounded-2xl border border-red-100/50 dark:border-red-900/20">
                            <span class="text-[9px] font-black text-red-500 uppercase block tracking-wider">Assigned Position</span>
                            <span class="text-sm font-extrabold text-slate-800 dark:text-slate-200 mt-1 block uppercase">{{ $contact->position ?: 'Personnel' }}</span>
                        </div>
                        <div class="p-4 bg-red-50/30 dark:bg-red-950/10 rounded-2xl border border-red-100/50 dark:border-red-900/20">
                            <span class="text-[9px] font-black text-red-500 uppercase block tracking-wider">Affiliated Supplier / Company</span>
                            <span class="text-sm font-extrabold text-slate-800 dark:text-slate-200 mt-1 block uppercase">{{ $contact->organization ?: 'N/A' }}</span>
                        </div>
                    </div>

                    {{-- Action Quick-Contacts --}}
                    <div class="pt-6 border-t border-slate-100 dark:border-slate-800 space-y-3">
                        <a href="tel:{{ $contact->contact_number }}" class="w-full py-3 px-4 bg-deped hover:bg-deped_hover text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all flex items-center justify-center gap-2 shadow-lg shadow-red-500/10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            Call: {{ $contact->contact_number ?: 'N/A' }}
                        </a>
                        <a href="mailto:{{ $contact->email }}" class="w-full py-3 px-4 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-deped dark:text-red-400 rounded-2xl text-xs font-black uppercase tracking-wider transition-all flex items-center justify-center gap-2 border border-red-200 dark:border-slate-700">
                            <svg class="w-4 h-4 text-deped dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            Email: {{ $contact->email ?: 'N/A' }}
                        </a>
                    </div>
                </div>
            </section>

            {{-- BENTO ITEM 2: Content (Span 8) --}}
            <main class="lg:col-span-8 flex flex-col gap-6">
                
                {{-- Dynamic Bento Stats Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="glass-card bento-glow-red rounded-[2rem] p-6 shadow-sm border border-slate-200/60 dark:border-slate-800 flex justify-between items-center relative overflow-hidden">
                        <div class="relative z-10">
                            <p class="text-[10px] font-black text-red-500 uppercase tracking-widest">Total Supplies Distributed</p>
                            <p class="text-4xl font-extrabold text-slate-900 dark:text-slate-100 mt-2">{{ $stats->total_supplied }}</p>
                            <p class="text-[10.5px] font-semibold text-slate-500 dark:text-slate-400 mt-2">Active assigned assets in DepEd schools</p>
                        </div>
                        <div class="w-14 h-14 bg-red-50 dark:bg-red-950/20 border border-red-100 dark:border-red-900/40 text-deped dark:text-red-400 rounded-2xl flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                        </div>
                    </div>

                    <div class="glass-card bento-glow-red rounded-[2rem] p-6 shadow-sm border border-slate-200/60 dark:border-slate-800 flex justify-between items-center relative overflow-hidden">
                        <div class="relative z-10">
                            <p class="text-[10px] font-black text-red-500 uppercase tracking-widest">Supplied Inventory Value</p>
                            <p class="text-4xl font-extrabold text-red-600 dark:text-red-400 mt-2">₱ {{ number_format($stats->total_value, 2) }}</p>
                            <p class="text-[10.5px] font-semibold text-red-650 dark:text-red-450 mt-2">Cumulative procurement cost stats</p>
                        </div>
                        <div class="w-14 h-14 bg-red-50 dark:bg-red-950/20 border border-red-100 dark:border-red-900/40 text-deped dark:text-red-400 rounded-2xl flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </div>
                    </div>
                </div>

                {{-- Supplied Assets Bento Section --}}
                <div class="glass-card rounded-[2.5rem] p-6 shadow-sm border border-slate-200/60 dark:border-slate-800 flex flex-col flex-grow">
                    <div class="pb-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-xs font-extrabold text-red-500 uppercase tracking-widest">DepEd Inventory Integration</h3>
                            <p class="text-lg font-black text-slate-900 dark:text-slate-100 uppercase mt-1">Supplied Equipment Registry</p>
                        </div>
                        <span class="px-3 py-1 bg-red-50 dark:bg-red-950 text-deped dark:text-red-400 text-[10px] font-black rounded-xl uppercase tracking-wider">{{ $assets->count() }} Items</span>
                    </div>

                    <div class="overflow-x-auto w-full flex-grow custom-scroll">
                        @if($assets->count() > 0)
                        <table class="w-full text-left border-collapse" style="min-width: 800px;">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800">
                                    <th class="pb-3 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Item details</th>
                                    <th class="pb-3 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Property & Serial</th>
                                    <th class="pb-3 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Current Location</th>
                                    <th class="pb-3 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">Condition</th>
                                    <th class="pb-3 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest text-right">Cost</th>
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
                        @else
                        <div class="flex flex-col items-center justify-center py-16 bg-slate-50 dark:bg-slate-900/20 rounded-3xl border border-dashed border-slate-200 dark:border-slate-800">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest italic">No assets registered to have been supplied by this personnel</p>
                        </div>
                        @endif
                    </div>
                </div>
            </main>

        </div>
    </div>

</body>
</html>
