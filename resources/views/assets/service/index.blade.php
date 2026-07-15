<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Service | DepEd ZC Inventory</title>

    @if(session('error') || $errors->any())
        <div class="fixed top-6 left-1/2 -translate-x-1/2 z-[300] w-full max-w-md animate-in slide-in-from-top duration-300">
            <div class="bg-red-50 border-2 border-red-200 rounded-2xl p-4 shadow-xl flex items-start gap-4">
                <div class="w-10 h-10 bg-red-100 text-red-600 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div class="flex-grow pt-0.5">
                    <h4 class="text-sm font-black text-red-800 uppercase tracking-tight">Error</h4>
                    <p class="text-xs font-bold text-red-600 mt-0.5 leading-relaxed">
                        @if(session('error')) {{ session('error') }} @endif
                        @foreach ($errors->all() as $error) • {{ $error }}<br> @endforeach
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="fixed top-6 left-1/2 -translate-x-1/2 z-[300] w-full max-w-md animate-in slide-in-from-top duration-300">
            <div class="bg-emerald-50 border-2 border-emerald-200 rounded-2xl p-4 shadow-xl flex items-start gap-4">
                <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div class="flex-grow pt-0.5">
                    <h4 class="text-sm font-black text-emerald-800 uppercase tracking-tight">Success</h4>
                    <p class="text-xs font-bold text-emerald-600 mt-0.5 leading-relaxed">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .custom-scroll::-webkit-scrollbar { width: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        [x-cloak] { display: none !important; }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }
        .blink { animation: blink 1s step-start infinite; }
    </style>
</head>
<body class="flex min-h-screen text-slate-800 overflow-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll p-4 lg:p-8">

        {{-- Header --}}
        <header class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
            <div class="flex items-center gap-5">
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center border border-amber-200 shadow-sm shrink-0">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-none uppercase italic">Asset Service</h1>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Assets Currently Under Repair</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-4 py-2 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl text-xs font-black uppercase tracking-widest">
                    {{ $services->count() }} {{ Str::plural('Asset', $services->count()) }} In Service
                </span>
            </div>
        </header>

        {{-- Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            @if($services->isEmpty())
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-20 h-20 bg-slate-100 rounded-2xl flex items-center justify-center mb-5">
                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path></svg>
                    </div>
                    <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest mb-1">No Assets Under Repair</h3>
                    <p class="text-xs font-bold text-slate-400">All assets are accounted for. No repairs in progress.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Asset</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Property No.</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Service Center</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Sent Date</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Expected Return</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($services as $svc)
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="px-6 py-4">
                                    <p class="text-xs font-black text-slate-800 uppercase">{{ $svc->item_name }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 mt-0.5 uppercase">{{ $svc->description ?: 'No description' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-md border border-slate-200">{{ $svc->property_number ?? 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-xs font-black text-slate-800 uppercase">{{ $svc->supplier_name }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 mt-0.5">{{ $svc->service_center }}</p>
                                </td>
                                <td class="px-6 py-4 text-xs font-bold text-slate-600">
                                    {{ \Carbon\Carbon::parse($svc->sent_date)->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 text-xs font-bold text-slate-600">
                                    {{ \Carbon\Carbon::parse($svc->expected_return_date)->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($svc->is_overdue)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-100 text-red-700 border border-red-200">
                                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full blink"></span>
                                            Overdue {{ $svc->overdue_days }}d
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>
                                            In Repair
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('asset.service.show', $svc->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-sm hover:shadow-md active:scale-95 transition-all">
                                        View Profile
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</body>
</html>
