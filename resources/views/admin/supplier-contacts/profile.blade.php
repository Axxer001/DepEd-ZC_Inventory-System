<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $contact->name }} | Acquisition Source Profile</title>
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
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #c00000; }
        [x-cloak] { display: none !important; }

        /* Stagger fade-in animations */
        .anim-0 { animation: fadeUp 0.45s cubic-bezier(.22,.68,0,1.2) forwards; opacity: 0; }
        .anim-1 { animation: fadeUp 0.45s cubic-bezier(.22,.68,0,1.2) 0.06s forwards; opacity: 0; }
        .anim-2 { animation: fadeUp 0.45s cubic-bezier(.22,.68,0,1.2) 0.12s forwards; opacity: 0; }
        .anim-3 { animation: fadeUp 0.45s cubic-bezier(.22,.68,0,1.2) 0.18s forwards; opacity: 0; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

        /* Tab content animation */
        .tab-fade { animation: fadeIn 0.3s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }

        /* Stat card hover */
        .stat-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .stat-hover:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); }

        /* Table row hover */
        .trow { transition: background 0.12s ease; }
        .trow:hover { background: #fafafa; }

        /* Dark Mode */
        html.dark body { background-color: #0b0f19; }
        html.dark .bg-white { background-color: #1e293b !important; border-color: #334155 !important; }
        html.dark .bg-slate-50 { background-color: #0f172a !important; }
        html.dark .border-slate-100 { border-color: #334155 !important; }
        html.dark .border-slate-200 { border-color: #334155 !important; }
        html.dark .text-slate-900 { color: #f8fafc !important; }
        html.dark .text-slate-800 { color: #e2e8f0 !important; }
        html.dark .text-slate-700 { color: #cbd5e1 !important; }
        html.dark .text-slate-500 { color: #64748b !important; }
        html.dark .trow:hover { background: #334155/30 !important; }
    </style>
</head>
<body class="flex min-h-screen text-slate-800 overflow-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll p-4 lg:p-8" x-data="{ activeTab: 'assets' }">

        {{-- ===== STICKY HEADER ===== --}}
        <header class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 sticky top-0 z-50 anim-0">
            <div class="flex items-center gap-5">
                {{-- Icon --}}
                <div class="w-12 h-12 bg-deped_light rounded-xl flex items-center justify-center border border-deped/20 shadow-sm shrink-0">
                    <svg class="w-6 h-6 text-deped" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                </div>
                {{-- Name + Meta --}}
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-none uppercase italic">{{ $contact->name }}</h1>
                    <div class="flex flex-wrap items-center gap-3 mt-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-widest bg-slate-100 px-2.5 py-0.5 rounded-md border border-slate-200">{{ $contact->organization ?? 'External Provider' }}</span>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-widest bg-slate-100 px-2.5 py-0.5 rounded-md border border-slate-200">{{ $contact->position ?? 'Personnel' }}</span>
                        <span class="text-[10px] font-black text-emerald-700 uppercase tracking-widest bg-emerald-100 px-2 py-0.5 rounded-full flex items-center gap-1.5 shadow-sm">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span> Active Acquisition Source
                        </span>
                    </div>
                </div>
            </div>

            {{-- Back Button — upper right --}}
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('admin.supplier_contacts') }}" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 uppercase tracking-widest hover:border-deped hover:text-deped hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-sm hover:shadow-md flex items-center gap-2 group">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Back to Registry
                </a>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 flex-grow pb-10">

            {{-- ===== LEFT SIDEBAR ===== --}}
            <aside class="lg:col-span-3 flex flex-col gap-6 anim-1">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-6">

                    {{-- Contact Details --}}
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Contact Details</p>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 bg-slate-50 rounded-lg flex items-center justify-center shrink-0 border border-slate-100">
                                    <svg class="w-3.5 h-3.5 text-deped" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Phone</p>
                                    <p class="text-xs font-black text-slate-800 leading-snug mt-0.5">{{ $contact->contact_number ?: '—' }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 bg-slate-50 rounded-lg flex items-center justify-center shrink-0 border border-slate-100">
                                    <svg class="w-3.5 h-3.5 text-deped" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Email</p>
                                    <p class="text-xs font-black text-slate-800 lowercase leading-snug mt-0.5 truncate">{{ $contact->email ?: '—' }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 bg-slate-50 rounded-lg flex items-center justify-center shrink-0 border border-slate-100">
                                    <svg class="w-3.5 h-3.5 text-deped" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Organization</p>
                                    <p class="text-xs font-black text-slate-800 uppercase leading-snug mt-0.5">{{ $contact->organization ?: '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Stats --}}
                    <div class="pt-6 border-t border-slate-100 space-y-4">
                        <div class="stat-hover p-4 bg-deped_light rounded-xl border border-red-100 cursor-default">
                            <p class="text-[9px] font-black text-deped uppercase tracking-widest mb-1">Items Supplied</p>
                            <p class="text-xl font-black text-deped leading-none">{{ $stats->total_supplied }}</p>
                            <p class="text-[10px] font-bold text-deped/60 mt-1 uppercase">Assigned Assets in DepEd</p>
                        </div>
                        <div class="stat-hover p-4 bg-emerald-50 rounded-xl border border-emerald-100 cursor-default">
                            <p class="text-[9px] font-black text-emerald-600 uppercase tracking-widest mb-1">Total Supply Value</p>
                            <p class="text-xl font-black text-emerald-700 leading-none italic">₱ {{ number_format($stats->total_value, 2) }}</p>
                            <p class="text-[10px] font-bold text-emerald-600/70 mt-1 uppercase">Cumulative Cost</p>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- ===== MAIN CONTENT ===== --}}
            <div class="lg:col-span-9 flex flex-col bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden anim-2">
                {{-- Tabs --}}
                <div class="flex border-b border-slate-200 bg-slate-50/50 px-2 pt-2">
                    <button
                        @click="activeTab = 'assets'"
                        :class="{'bg-white border-slate-200 border-b-white text-deped shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'assets', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'assets'}"
                        class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        Supplied Equipment
                    </button>
                </div>

                <div class="p-6 flex-grow overflow-y-auto custom-scroll">

                    {{-- TAB: Supplied Equipment --}}
                    <div x-show="activeTab === 'assets'" class="tab-fade">
                        @if($assets->count() > 0)
                        <div class="overflow-x-auto w-full">
                            <table class="w-full text-left border-collapse" style="min-width: 840px;">
                                <thead>
                                    <tr class="border-b border-slate-100">
                                        <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Item / Category</th>
                                        <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Property No.</th>
                                        <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Deployed To</th>
                                        <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Condition</th>
                                        <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Cost</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach($assets as $asset)
                                    <tr class="trow group">
                                        <td class="py-4 pr-4">
                                            <p class="text-xs font-bold text-slate-800 uppercase leading-none">{{ $asset->item_name }}</p>
                                            <p class="text-[9px] font-bold text-slate-400 uppercase mt-1">{{ $asset->category_name }}</p>
                                        </td>
                                        <td class="py-4">
                                            <span class="text-[10px] font-black text-slate-500 uppercase block">{{ $asset->property_number }}</span>
                                        </td>
                                        <td class="py-4">
                                            <span class="text-[10.5px] font-bold text-slate-800 uppercase leading-tight block max-w-[180px] truncate">{{ $asset->school_name ?: '—' }}</span>
                                            @if($asset->office_name)
                                            <span class="text-[9px] font-semibold text-slate-400 uppercase block mt-0.5 max-w-[180px] truncate">{{ $asset->office_name }}</span>
                                            @endif
                                        </td>
                                        <td class="py-4 text-center">
                                            @php
                                                $cond = $asset->condition ?: 'Good';
                                                $isGood = in_array($cond, ['Good', 'Serviceable']);
                                                $theme = $isGood ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700';
                                            @endphp
                                            <span class="px-2.5 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider {{ $theme }}">{{ $cond }}</span>
                                        </td>
                                        <td class="py-4 text-right">
                                            <p class="text-xs font-black text-deped italic">₱ {{ number_format($asset->asset_cost, 2) }}</p>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="flex flex-col items-center justify-center py-12 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest italic">No assets recorded for this acquisition source yet.</p>
                        </div>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>

</body>
</html>
