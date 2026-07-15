<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Profile — {{ $service->item_name }} | DepEd ZC Inventory</title>

    @if(session('error') || $errors->any())
        <div class="fixed top-6 left-1/2 -translate-x-1/2 z-[300] w-full max-w-md">
            <div class="bg-red-50 border-2 border-red-200 rounded-2xl p-4 shadow-xl flex items-start gap-4">
                <div class="w-10 h-10 bg-red-100 text-red-600 rounded-xl flex items-center justify-center shrink-0"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div>
                <div class="flex-grow pt-0.5">
                    <h4 class="text-sm font-black text-red-800 uppercase tracking-tight">Error</h4>
                    <p class="text-xs font-bold text-red-600 mt-0.5">@if(session('error')) {{ session('error') }} @endif @foreach ($errors->all() as $error) • {{ $error }}<br> @endforeach</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="fixed top-6 left-1/2 -translate-x-1/2 z-[300] w-full max-w-md">
            <div class="bg-emerald-50 border-2 border-emerald-200 rounded-2xl p-4 shadow-xl flex items-start gap-4">
                <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center shrink-0"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg></div>
                <div class="flex-grow pt-0.5">
                    <h4 class="text-sm font-black text-emerald-800 uppercase tracking-tight">Success</h4>
                    <p class="text-xs font-bold text-emerald-600 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        [x-cloak] { display: none !important; }
        .animate-fade { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.1; } }
        .blink-text { animation: blink 1.2s step-start infinite; }
    </style>
</head>
<body class="flex min-h-screen text-slate-800 overflow-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll p-4 lg:p-8"
         x-data="{ activeTab: 'specs', showReturnCustodianModal: false, showReturnAmuModal: false, showImageFullscreen: false }">

        {{-- Header --}}
        <header class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 sticky top-0 z-50">
            <div class="flex items-center gap-5">
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center border border-amber-200 shadow-sm shrink-0">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-none uppercase italic">{{ $service->item_name }}</h1>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-widest bg-slate-100 px-2.5 py-0.5 rounded-md border border-slate-200">{{ $service->property_number ?? 'No Prop. No.' }}</span>
                        @if($isOverdue)
                            <span class="text-[10px] font-black text-red-700 uppercase tracking-widest bg-red-100 px-2 py-0.5 rounded-full flex items-center gap-1.5 border border-red-200 blink-text">
                                <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span> Overdue
                            </span>
                        @else
                            <span class="text-[10px] font-black text-amber-700 uppercase tracking-widest bg-amber-100 px-2 py-0.5 rounded-full flex items-center gap-1.5 border border-amber-200">
                                <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span> Under Repair
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                @if(auth()->check() && auth()->user()->isAdmin())
                <button @click="showReturnCustodianModal = true"
                    {{ !$service->previous_custodian_id ? 'disabled' : '' }}
                    class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-md shadow-blue-600/20 hover:shadow-lg transition-all active:scale-95 flex items-center gap-2"
                    title="{{ !$service->previous_custodian_id ? 'No previous custodian saved' : 'Return to original custodian' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Return to Custodian
                </button>
                <button @click="showReturnAmuModal = true" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-md shadow-emerald-600/20 hover:shadow-lg transition-all active:scale-95 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                    Return to AMU
                </button>
                @endif
                <a href="{{ route('asset.service.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 uppercase tracking-widest hover:border-deped hover:text-deped transition-all shadow-sm flex items-center gap-2 group">
                    <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path></svg>
                    Back
                </a>
            </div>
        </header>

        {{-- Main Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 flex-grow pb-10">

            {{-- Left Sidebar --}}
            <aside class="lg:col-span-3 flex flex-col gap-6 z-40 relative">

                {{-- Asset Photo --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="aspect-square bg-slate-50 border-b border-slate-100 flex items-center justify-center p-6 relative group rounded-t-2xl overflow-hidden cursor-pointer"
                         @click="{{ $service->photo_path ? 'showImageFullscreen = true' : '' }}">
                        <img src="{{ $service->photo_path ? asset('storage/' . $service->photo_path) : asset('images/asset.png') }}"
                             alt="Asset Photo"
                             class="w-full h-full object-contain transition-transform duration-500 {{ $service->photo_path ? 'opacity-100 scale-100 group-hover:scale-110' : 'opacity-50 group-hover:scale-105' }}"
                             onerror="this.src='{{ asset('images/asset.png') }}'">
                        @if($service->photo_path)
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4 pointer-events-none">
                                <span class="text-white text-[10px] font-black uppercase tracking-widest">Click to Expand</span>
                            </div>
                        @endif
                    </div>

                    <div class="p-5 space-y-5">
                        {{-- Expected Return Date --}}
                        <div class="bg-amber-50 border border-amber-200 p-4 rounded-2xl relative overflow-hidden">
                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-amber-500"></div>
                            <p class="text-[9px] font-black text-amber-600 uppercase tracking-widest mb-2 flex items-center gap-1.5 pl-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                Expected Return
                            </p>
                            <p class="text-sm font-black text-amber-800 pl-1">{{ \Carbon\Carbon::parse($service->expected_return_date)->format('F d, Y') }}</p>
                            @if($isOverdue)
                                <p class="text-[10px] font-black text-red-600 mt-1 pl-1 blink-text">⚠ {{ $overdueDays }} working {{ Str::plural('day', $overdueDays) }} overdue</p>
                            @else
                                @php
                                    $secondsLeft = max(0, (int) \Carbon\Carbon::now()->diffInSeconds(\Carbon\Carbon::parse($service->expected_return_date), false));
                                    $dLeft  = intdiv($secondsLeft, 86400);
                                    $hLeft  = intdiv($secondsLeft % 86400, 3600);
                                    $mLeft  = intdiv($secondsLeft % 3600, 60);
                                    $remainParts = [];
                                    if ($dLeft > 0) $remainParts[] = "{$dLeft}d";
                                    if ($hLeft > 0) $remainParts[] = "{$hLeft}h";
                                    if ($mLeft > 0 || empty($remainParts)) $remainParts[] = "{$mLeft}m";
                                    $remainStr = implode(' ', $remainParts);
                                @endphp
                                <p class="text-[10px] font-bold text-amber-600 mt-1 pl-1">
                                    {{ $remainStr }} remaining
                                </p>
                            @endif
                        </div>

                        {{-- Lifespan & Warranty --}}
                        @php
                            $usefulLifeYears = $service->estimated_useful_life ?? 0;
                            $startDate = $service->acceptance_date ? \Carbon\Carbon::parse($service->acceptance_date) : null;
                            $percentRemaining = 0;
                            $progressClass = 'from-slate-400 to-slate-300';
                            $statusText = 'Lifespan Data Unavailable';
                            if ($usefulLifeYears > 0 && $startDate) {
                                $endDate = $startDate->copy()->addYears($usefulLifeYears);
                                $now2 = \Carbon\Carbon::now();
                                $totalDays = $startDate->diffInDays($endDate);
                                $daysElapsed = $startDate->diffInDays($now2, false);
                                if ($daysElapsed < 0) {
                                    $percentRemaining = 100;
                                    $statusText = "{$usefulLifeYears} of {$usefulLifeYears} Years Remaining";
                                } elseif ($daysElapsed >= $totalDays) {
                                    $percentRemaining = 0;
                                    $statusText = "0 of {$usefulLifeYears} Years Remaining (Depleted)";
                                } else {
                                    $daysRemaining = $totalDays - $daysElapsed;
                                    $percentRemaining = round(($daysRemaining / $totalDays) * 100);
                                    $yearsRemainingFloat = round($daysRemaining / 365.25, 1);
                                    $yearsStr = (floor($yearsRemainingFloat) == $yearsRemainingFloat) ? (int)$yearsRemainingFloat : $yearsRemainingFloat;
                                    $statusText = "{$yearsStr} of {$usefulLifeYears} Years Remaining";
                                    $progressClass = $percentRemaining > 60 ? 'from-emerald-500 to-teal-400' : ($percentRemaining > 30 ? 'from-amber-500 to-yellow-400' : 'from-red-500 to-rose-400');
                                }
                            }
                            $warrantyMonths = $service->warranty ?? 0;
                            $warrantyText = 'No Warranty';
                            $warrantyClass = 'text-slate-400';
                            $warrantyPill = 'bg-slate-100 text-slate-500 border-slate-200';
                            if ($warrantyMonths > 0 && $startDate) {
                                $wEnd = $startDate->copy()->addMonths($warrantyMonths);
                                $warrantyText = $wEnd->format('M d, Y');
                                if (\Carbon\Carbon::now()->greaterThanOrEqualTo($wEnd)) {
                                    $warrantyClass = 'text-red-600';
                                    $warrantyPill = 'bg-red-100 text-red-700 border-red-200';
                                } else {
                                    $warrantyClass = 'text-emerald-600';
                                    $warrantyPill = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                                }
                            }
                        @endphp

                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Estimated Lifespan</p>
                            <div class="h-2 bg-slate-100 rounded-full overflow-hidden mb-1">
                                <div class="h-full bg-gradient-to-r {{ $progressClass }} rounded-full transition-all duration-700" style="width: {{ $percentRemaining }}%"></div>
                            </div>
                            <p class="text-[10px] font-bold text-slate-500">{{ $statusText }}</p>
                        </div>

                        <div class="flex items-center justify-between">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Warranty</p>
                            <span class="text-[10px] font-black px-2.5 py-1 rounded-full border {{ $warrantyPill }}">
                                {{ $warrantyMonths > 0 ? $warrantyMonths . ' Months' : 'No Warranty' }}
                            </span>
                        </div>
                        @if($warrantyMonths > 0)
                        <p class="text-[9px] font-bold {{ $warrantyClass }} -mt-3">Expires: {{ $warrantyText }}</p>
                        @endif

                        {{-- Previous Custodian --}}
                        @if($service->previous_custodian_id)
                        <div class="bg-slate-50 border border-slate-100 p-4 rounded-2xl">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Previous Custodian</p>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-blue-100 border border-blue-200 flex items-center justify-center text-blue-700 font-black text-xs shrink-0">
                                    {{ strtoupper(substr($prevCustodianFullName, 0, 2)) ?: 'NA' }}
                                </div>
                                <div>
                                    <p class="text-xs font-black text-slate-700 uppercase leading-tight">{{ $prevCustodianFullName ?: 'N/A' }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase mt-0.5">{{ $service->prev_position ?? '' }}</p>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="bg-slate-50 border border-slate-100 p-4 rounded-2xl">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Previous Custodian</p>
                            <p class="text-xs font-bold text-slate-400 italic">None (was in warehouse)</p>
                        </div>
                        @endif
                    </div>
                </div>
            </aside>

            {{-- Right Content Panel --}}
            <div class="lg:col-span-9 flex flex-col bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

                {{-- Tabs --}}
                <div class="flex border-b border-slate-200 px-6 pt-6 gap-1 bg-slate-50/50">
                    <button @click="activeTab = 'specs'" :class="{'bg-white border-slate-200 border-b-white text-amber-600 shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'specs', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'specs'}" class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        Specifications
                    </button>
                    <button @click="activeTab = 'progress'" :class="{'bg-white border-slate-200 border-b-white text-amber-600 shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'progress', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'progress'}" class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        Service Progression
                    </button>
                </div>

                <div class="p-6 lg:p-8 flex-grow overflow-y-auto custom-scroll bg-white">

                    {{-- TAB 1: Specifications --}}
                    <div x-show="activeTab === 'specs'" class="animate-fade space-y-8">

                        {{-- Technical Details --}}
                        <div>
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Technical Details
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-y-6 gap-x-8 bg-slate-50 rounded-xl p-6 border border-slate-100">
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Classification</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $service->classification_name }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Category</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $service->category_name }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Article / Item</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $service->item_name }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Description</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $service->description ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Unit Cost</p>
                                    <p class="text-xs font-bold text-emerald-600 mt-1 uppercase px-1">₱ {{ number_format($service->asset_cost, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Current Condition</p>
                                    <p class="text-xs font-bold text-amber-600 mt-1 uppercase px-1">{{ $service->condition }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Procurement Information --}}
                        <div>
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Procurement Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8 bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Acceptance Date</p>
                                    <p class="text-sm font-black text-slate-800 mt-1 uppercase">{{ $service->acceptance_date ? \Carbon\Carbon::parse($service->acceptance_date)->format('F d, Y') : 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Funding / Source</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase">{{ $service->source_name }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Mode of Acquisition</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase">{{ $service->mode_of_acquisition ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Supplier</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase">{{ $service->supplier_name }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Service Center</p>
                                    <p class="text-xs font-bold text-amber-700 mt-1 uppercase">{{ $service->service_center }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Supplier Contact</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1">{{ $service->supplier_contact ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: Service Progression --}}
                    <div x-show="activeTab === 'progress'" class="animate-fade space-y-8" x-cloak
                         x-data="serviceProgression({{ $progress }}, {{ $isOverdue ? 'true' : 'false' }}, {{ $overdueDays }})">

                        {{-- Progress Header --}}
                        <div>
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Repair Progress
                            </h3>

                            {{-- Timeline Details --}}
                            <div class="grid grid-cols-3 gap-4 mb-6">
                                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 text-center">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Sent for Repair</p>
                                    <p class="text-xs font-black text-slate-700">{{ \Carbon\Carbon::parse($service->sent_date)->format('M d, Y') }}</p>
                                </div>
                                <div class="bg-amber-50 rounded-xl p-4 border border-amber-200 text-center">
                                    <p class="text-[9px] font-black text-amber-600 uppercase tracking-widest mb-1">Expected Return</p>
                                    <p class="text-xs font-black text-amber-800">{{ \Carbon\Carbon::parse($service->expected_return_date)->format('M d, Y') }}</p>
                                </div>
                                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 text-center">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Today</p>
                                    <p class="text-xs font-black text-slate-700">{{ \Carbon\Carbon::now()->format('M d, Y') }}</p>
                                </div>
                            </div>

                            {{-- Progress Bar --}}
                            <div class="mb-3 flex items-center justify-between">
                                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Time Remaining</p>
                                <p class="text-[10px] font-black" :class="isOverdue ? 'text-red-600' : 'text-amber-600'" x-text="isOverdue ? 'OVERDUE' : progress + '%'"></p>
                            </div>
                            <div class="h-4 bg-slate-100 rounded-full overflow-hidden shadow-inner mb-2">
                                <div class="h-full rounded-full transition-all duration-1000"
                                     :class="isOverdue ? 'bg-red-500' : (progress > 60 ? 'bg-gradient-to-r from-emerald-500 to-teal-400' : (progress > 25 ? 'bg-gradient-to-r from-amber-500 to-yellow-400' : 'bg-gradient-to-r from-red-500 to-rose-400'))"
                                     :style="'width: ' + progress + '%'"></div>
                            </div>

                            {{-- Overdue Counter --}}
                            <div x-show="isOverdue" x-cloak class="mt-4 p-4 bg-red-50 rounded-xl border border-red-200 text-center">
                                <p class="text-xs font-black text-red-700 uppercase tracking-widest blink-text flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    ⚠ Overdue by <span x-text="overdueDays"></span> PH Working {{ Str::plural('Day', $overdueDays) }}
                                </p>
                                <p class="text-[10px] font-bold text-red-500 mt-1">This asset has exceeded its expected return date.</p>
                            </div>
                        </div>

                        {{-- Resolution Buttons --}}
                        @if(auth()->check() && auth()->user()->isAdmin())
                        <div>
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Complete Repair
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Return to Custodian --}}
                                @if($service->previous_custodian_id)
                                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 flex flex-col gap-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-black text-blue-800 uppercase">Return to Custodian</p>
                                            <p class="text-[10px] font-bold text-blue-600 mt-0.5">Reassign to <strong>{{ $prevCustodianFullName }}</strong> and mark as Good Condition.</p>
                                        </div>
                                    </div>
                                    <button @click="showReturnCustodianModal = true" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-md shadow-blue-600/20 transition-all active:scale-95">
                                        Initiate Return to Custodian
                                    </button>
                                </div>
                                @else
                                <div class="bg-slate-100 border border-slate-200 rounded-2xl p-5 opacity-60 flex flex-col gap-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 bg-slate-200 text-slate-400 rounded-xl flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-black text-slate-500 uppercase">Return to Custodian</p>
                                            <p class="text-[10px] font-bold text-slate-400 mt-0.5">No previous custodian was saved. Asset was in the warehouse prior to repair.</p>
                                        </div>
                                    </div>
                                    <button disabled class="w-full py-2.5 bg-slate-300 text-slate-500 rounded-xl text-xs font-black uppercase tracking-widest cursor-not-allowed">Unavailable</button>
                                </div>
                                @endif

                                {{-- Return to AMU --}}
                                <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 flex flex-col gap-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-black text-emerald-800 uppercase">Return to AMU</p>
                                            <p class="text-[10px] font-bold text-emerald-600 mt-0.5">Return asset to the warehouse (unassigned) and mark as Good Condition.</p>
                                        </div>
                                    </div>
                                    <button @click="showReturnAmuModal = true" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-md shadow-emerald-600/20 transition-all active:scale-95">
                                        Initiate Return to AMU
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>

        {{-- Return to Custodian Confirmation Modal --}}
        <div x-show="showReturnCustodianModal" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center">
            <div x-show="showReturnCustodianModal" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showReturnCustodianModal = false"></div>
            <div x-show="showReturnCustodianModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" class="bg-white rounded-3xl shadow-2xl p-8 max-w-sm w-full mx-4 relative z-10 border border-slate-100 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-5 ring-8 ring-blue-50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight mb-2">Return to Custodian?</h3>
                <p class="text-xs font-bold text-slate-500 mb-2 leading-relaxed">This will reassign the asset to <strong class="text-blue-700">{{ $prevCustodianFullName ?: 'N/A' }}</strong>, set its condition to <strong>Good Condition</strong>, record the repair history, and remove this repair record.</p>
                <p class="text-[10px] font-bold text-slate-400 mb-8">This action cannot be undone.</p>
                <div class="flex items-center gap-3 w-full">
                    <button type="button" @click="showReturnCustodianModal = false" class="flex-1 py-3.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-black uppercase tracking-widest transition-colors">Cancel</button>
                    <form action="{{ route('asset.service.return-custodian', $service->id) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full py-3.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-blue-600/30 transition-all active:scale-95">Confirm</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Return to AMU Confirmation Modal --}}
        <div x-show="showReturnAmuModal" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center">
            <div x-show="showReturnAmuModal" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showReturnAmuModal = false"></div>
            <div x-show="showReturnAmuModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" class="bg-white rounded-3xl shadow-2xl p-8 max-w-sm w-full mx-4 relative z-10 border border-slate-100 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mb-5 ring-8 ring-emerald-50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                </div>
                <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight mb-2">Return to AMU?</h3>
                <p class="text-xs font-bold text-slate-500 mb-2 leading-relaxed">This will place the asset back in the <strong class="text-emerald-700">warehouse (unassigned)</strong>, set its condition to <strong>Good Condition</strong>, and record the complete repair history.</p>
                <p class="text-[10px] font-bold text-slate-400 mb-8">This action cannot be undone.</p>
                <div class="flex items-center gap-3 w-full">
                    <button type="button" @click="showReturnAmuModal = false" class="flex-1 py-3.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-black uppercase tracking-widest transition-colors">Cancel</button>
                    <form action="{{ route('asset.service.return-amu', $service->id) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-emerald-600/30 transition-all active:scale-95">Confirm</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Fullscreen Image Modal --}}
        <div x-show="showImageFullscreen" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/95 backdrop-blur-md">
            <button @click="showImageFullscreen = false" class="absolute top-6 right-6 text-white/50 hover:text-white transition-colors p-2 rounded-full hover:bg-white/10 active:scale-95">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <img src="{{ $service->photo_path ? asset('storage/' . $service->photo_path) : '' }}" class="max-w-[90vw] max-h-[90vh] object-contain rounded-xl shadow-2xl" @click.away="showImageFullscreen = false">
        </div>

    </div>

    <script>
        function serviceProgression(initialProgress, initialIsOverdue, initialOverdueDays) {
            return {
                progress: initialProgress,
                isOverdue: initialIsOverdue,
                overdueDays: initialOverdueDays,
            };
        }
    </script>

</body>
</html>
