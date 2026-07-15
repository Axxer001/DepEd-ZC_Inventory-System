<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $building->property_number }} | Building Profile</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        /* === CSS Custom Properties for Light/Dark Theming === */
        :root {
            --bg-page:        #f8fafc;
            --bg-card:        #ffffff;
            --bg-secondary:   #f8fafc;
            --border-primary: #e2e8f0;
            --border-subtle:  #f1f5f9;
            --text-primary:   #0f172a;
            --text-secondary: #1e293b;
            --text-muted:     #64748b;
            --text-faint:     #94a3b8;
            --scrollbar-thumb: #cbd5e1;
            --timeline-line:  #e2e8f0;
        }
        html.dark {
            --bg-page:        #0f172a;
            --bg-card:        #1e293b;
            --bg-secondary:   #0f172a;
            --border-primary: #334155;
            --border-subtle:  #334155;
            --text-primary:   #f8fafc;
            --text-secondary: #e2e8f0;
            --text-muted:     #94a3b8;
            --text-faint:     #64748b;
            --scrollbar-thumb: #475569;
            --timeline-line:  #334155;
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-page); color: var(--text-primary); }

        /* Adaptive Tailwind overrides */
        .bg-white     { background-color: var(--bg-card)      !important; }
        .bg-slate-50  { background-color: var(--bg-secondary) !important; }
        .bg-slate-100 { background-color: color-mix(in srgb, var(--bg-card) 70%, var(--border-primary)) !important; }
        .border-slate-200 { border-color: var(--border-primary) !important; }
        .border-slate-100 { border-color: var(--border-subtle)  !important; }
        .text-slate-900 { color: var(--text-primary)   !important; }
        .text-slate-800 { color: var(--text-secondary) !important; }
        .text-slate-700 { color: var(--text-muted)     !important; }
        .text-slate-600 { color: var(--text-muted)     !important; }
        .text-slate-500, .text-slate-400 { color: var(--text-faint) !important; }

        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: var(--scrollbar-thumb); border-radius: 10px; }
        [x-cloak] { display: none !important; }
        .animate-fade { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

        .timeline-line {
            position: absolute;
            left: 11px;
            top: 24px;
            bottom: 0;
            width: 2px;
            background: var(--timeline-line);
            z-index: 0;
        }
    </style>
</head>
<body class="flex min-h-screen text-slate-800 overflow-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll p-4 lg:p-8" x-data="{ activeTab: 'specs', isEditing: false, showConfirmModal: false, showTransferModal: false, showReturnAmuModal: false, showImageFullscreen: false, showRemoveConfirmModal: false }">
        
        {{-- Global Header (Fixed/Sticky) --}}
        <header class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 sticky top-0 z-50">
            <div class="flex items-center gap-5">
                <div class="w-12 h-12 bg-deped_light rounded-xl flex items-center justify-center border border-deped/20 shadow-sm shrink-0">
                    <svg class="w-6 h-6 text-deped" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-none uppercase italic">{{ $building->spec_description ?: $building->type_name }}</h1>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-widest bg-slate-100 px-2.5 py-0.5 rounded-md border border-slate-200">{{ $building->property_number }}</span>
                        <span class="text-[10px] font-black text-emerald-700 uppercase tracking-widest bg-emerald-100 px-2 py-0.5 rounded-full flex items-center gap-1.5 shadow-sm">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span> Active / Occupied
                        </span>
                    </div>
                </div>
            </div>

            {{-- Actions Menu --}}
            <div class="flex items-center gap-3 shrink-0" x-data="{ open: false }">
                @php
                    $canDelete = false;
                    $user = auth()->user();
                    if ($user && $user->isAdmin()) {
                        if ($user->isMainSystem()) {
                            $canDelete = true;
                        } else {
                            $createdToday = $building->created_at ? \Carbon\Carbon::parse($building->created_at)->isToday() : false;
                            $isSelfRegistered = ($building->origin_system_type === 'school' && $building->registered_by_school_id === $user->school_id);
                            $canDelete = $createdToday && $isSelfRegistered;
                        }
                    }
                @endphp
                @if($canDelete)
                <form action="{{ route('buildings.destroy', $building->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to archive/delete this building? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-5 py-2.5 bg-red-50 border border-red-200 rounded-xl text-xs font-black text-red-600 uppercase tracking-widest hover:bg-red-600 hover:text-white hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-sm hover:shadow-md flex items-center gap-2 group">
                        <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Archive Building
                    </button>
                </form>
                @endif
                <button @click="isEditing = true" x-show="!isEditing" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 uppercase tracking-widest hover:border-deped hover:text-deped hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-sm hover:shadow-md flex items-center gap-2 group">
                    <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    Edit
                </button>
                <button @click="isEditing = false" x-show="isEditing" x-cloak class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-500 uppercase tracking-widest hover:border-slate-300 hover:text-slate-700 transition-all duration-300 shadow-sm flex items-center gap-2">
                    Cancel
                </button>
                <button @click="showConfirmModal = true" x-show="isEditing" x-cloak class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-emerald-700 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-sm shadow-emerald-600/30 hover:shadow-md flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    Save Changes
                </button>

                <a href="{{ route('register.building') }}" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 uppercase tracking-widest hover:border-deped hover:text-deped hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-sm hover:shadow-md flex items-center gap-2 group">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Back
                </a>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 flex-grow pb-10">
            
            {{-- Left Sidebar: Building Identity Card --}}
            <aside class="lg:col-span-3 flex flex-col gap-6 z-40 relative">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col relative" x-data="{ isHoveringImage: false }">
                    <div class="aspect-square bg-slate-50 border-b border-slate-100 flex items-center justify-center p-6 relative group overflow-hidden" @mouseenter="isHoveringImage = true" @mouseleave="isHoveringImage = false">
                        <img src="{{ asset('images/building_placeholder.png') }}" alt="Building Photo" class="w-full h-full object-contain transition-transform duration-500 opacity-50 group-hover:scale-110">
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4 pointer-events-none">
                            <button class="w-full py-2.5 bg-white/90 backdrop-blur-md rounded-lg text-xs font-black uppercase tracking-widest text-slate-800 hover:bg-white shadow-lg text-center cursor-pointer transition-all hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2 pointer-events-auto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span>Upload Photo</span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-5 space-y-5">
                        <a href="{{ route('schools.profile', $building->school_id) }}" class="block bg-transparent border border-red-100 p-4 rounded-2xl shadow-sm relative overflow-hidden group hover:border-deped hover:shadow-md transition-all">
                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-500"></div>
                            <p class="text-[9px] font-black text-red-500 dark:text-red-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                School Assignment
                            </p>
                            <div class="flex items-center gap-3 pl-1">
                                <div class="w-10 h-10 rounded-full bg-white dark:bg-slate-800 border border-red-100 dark:border-red-900/50 flex items-center justify-center text-red-600 dark:text-red-400 font-black text-xs shrink-0 shadow-sm group-hover:scale-110 transition-transform">
                                    {{ strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $building->school_name), 0, 2)) ?: 'SC' }}
                                </div>
                                <div>
                                    <p class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase leading-tight group-hover:text-red-700 dark:group-hover:text-red-400 transition-colors">{{ $building->school_name }}</p>
                                    <p class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mt-0.5">School ID: {{ $building->school_identifier }}</p>
                                </div>
                            </div>
                        </a>

                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">District</p>
                            <div class="group flex items-start gap-2">
                                <svg class="w-4 h-4 text-deped shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <div>
                                    <p class="text-xs font-bold text-deped uppercase leading-tight">{{ $building->district_name ?? 'N/A' }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase">DepEd Zamboanga City</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100">
                            <div class="flex justify-between items-end mb-1.5">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Building Lifespan</p>
                                <p class="text-[10px] font-black text-slate-700">60%</p>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-gradient-to-r from-red-600 to-amber-400 h-full rounded-full" style="width: 60%"></div>
                            </div>
                            <p class="text-[8px] font-bold text-slate-400 uppercase mt-1.5 text-right">Approx. 15 of 25 Years Remaining</p>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Main Content Area --}}
            <div class="lg:col-span-9 flex flex-col bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                
                {{-- Tabs Header --}}
                <div class="flex border-b border-slate-200 bg-slate-50/50 px-2 pt-2">
                    <button @click="activeTab = 'specs'" :class="{'bg-white border-slate-200 border-b-white text-deped shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'specs', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'specs'}" class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        Infrastructure Details
                    </button>
                    <button @click="activeTab = 'history'" :class="{'bg-white border-slate-200 border-b-white text-deped shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'history', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'history'}" class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        Lifecycle & History
                    </button>
                    <button @click="activeTab = 'docs'" :class="{'bg-white border-slate-200 border-b-white text-deped shadow-[0_-2px_4px_rgba(0,0,0,0.02)]': activeTab === 'docs', 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100': activeTab !== 'docs'}" class="px-6 py-3.5 text-xs font-black uppercase tracking-widest border border-b-0 rounded-t-xl transition-all relative top-[1px]">
                        Blueprint & Documents
                    </button>
                </div>

                {{-- Tab Contents --}}
                <div class="p-6 lg:p-8 flex-grow overflow-y-auto custom-scroll bg-white">
                    
                    {{-- TAB 1: Infrastructure Details --}}
                    <form id="update-building-form" action="{{ route('buildings.update', $building->id) }}" method="POST" x-show="activeTab === 'specs'" class="animate-fade space-y-8">
                        @csrf
                        <div>
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-deped"></span> Technical Specifications
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-y-6 gap-x-8 bg-slate-50 rounded-xl p-6 border border-slate-100">
                                {{-- Searchable Classification --}}
                                <div x-data="{ open: false, search: '{{ $building->classification_name }}', options: @js($classifications) }" class="relative" @click.away="open = false">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Classification</p>
                                    <p x-show="!isEditing" class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $building->classification_name }}</p>
                                    <div x-show="isEditing" class="relative group" x-cloak>
                                        <input type="text" x-model="search" name="classification" @focus="open = true" @input="open = true" placeholder="Search or type new..." class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 uppercase focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                        <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                        <div x-show="open" x-cloak class="absolute z-50 w-full mt-2 bg-slate-800 border-2 border-slate-700 rounded-xl shadow-2xl max-h-60 overflow-y-auto custom-scroll p-1">
                                            <template x-for="opt in options.filter(o => o.name.toLowerCase().includes(search.toLowerCase()))" :key="opt.id">
                                                <div @click="search = opt.name; open = false;" class="px-4 py-2.5 text-[10px] font-black text-slate-300 uppercase hover:bg-slate-700 hover:text-white rounded-lg cursor-pointer transition-colors" x-text="opt.name"></div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                {{-- Searchable Type --}}
                                <div x-data="{ open: false, search: '{{ $building->type_name }}', options: @js($types) }" class="relative" @click.away="open = false">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Type / Structure</p>
                                    <p x-show="!isEditing" class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $building->type_name }}</p>
                                    <div x-show="isEditing" class="relative group" x-cloak>
                                        <input type="text" x-model="search" name="type_name" @focus="open = true" @input="open = true" placeholder="Search or type new..." class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 uppercase focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                        <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                        <div x-show="open" x-cloak class="absolute z-50 w-full mt-2 bg-slate-800 border-2 border-slate-700 rounded-xl shadow-2xl max-h-60 overflow-y-auto custom-scroll p-1">
                                            <template x-for="opt in options.filter(o => o.name.toLowerCase().includes(search.toLowerCase()))" :key="opt.id">
                                                <div @click="search = opt.name; open = false;" class="px-4 py-2.5 text-[10px] font-black text-slate-300 uppercase hover:bg-slate-700 hover:text-white rounded-lg cursor-pointer transition-colors" x-text="opt.name"></div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                {{-- Occupancy Nature Select --}}
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Occupancy Nature</p>
                                    <p x-show="!isEditing" class="text-xs font-black text-deped mt-1 uppercase px-1">{{ $building->occupancy_nature ?: 'NOT SPECIFIED' }}</p>
                                    <div x-show="isEditing" class="relative group" x-cloak>
                                        <select name="occupancy_nature" class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 uppercase focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                            <option value="OWNED" {{ $building->occupancy_nature === 'OWNED' ? 'selected' : '' }}>OWNED</option>
                                            <option value="DONATED" {{ $building->occupancy_nature === 'DONATED' ? 'selected' : '' }}>DONATED</option>
                                            <option value="USUFRUCT" {{ $building->occupancy_nature === 'USUFRUCT' ? 'selected' : '' }}>USUFRUCT</option>
                                            <option value="LEASED" {{ $building->occupancy_nature === 'LEASED' ? 'selected' : '' }}>LEASED</option>
                                            <option value="NOT SPECIFIED" {{ !$building->occupancy_nature ? 'selected' : '' }}>NOT SPECIFIED</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Storeys --}}
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Number of Storeys</p>
                                    <p x-show="!isEditing" class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $building->storeys }} Storey(s)</p>
                                    <div x-show="isEditing" class="relative group" x-cloak>
                                        <input type="number" name="storeys" min="1" value="{{ $building->storeys }}" class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                    </div>
                                </div>

                                {{-- Classrooms --}}
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Total Classrooms</p>
                                    <p x-show="!isEditing" class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $building->classrooms }} Classroom(s)</p>
                                    <div x-show="isEditing" class="relative group" x-cloak>
                                        <input type="number" name="classrooms" min="0" value="{{ $building->classrooms }}" class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                    </div>
                                </div>

                                {{-- Property Number --}}
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Property Number</p>
                                    <p x-show="!isEditing" class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $building->property_number }}</p>
                                    <div x-show="isEditing" class="relative group" x-cloak>
                                        <input type="text" name="property_number" value="{{ $building->property_number }}" class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 uppercase focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-deped"></span> Procurement & Construction
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8 bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                                {{-- Date Constructed --}}
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Date Constructed</p>
                                    <p x-show="!isEditing" class="text-sm font-black text-slate-800 mt-1 uppercase px-1">{{ $building->date_constructed ? \Carbon\Carbon::parse($building->date_constructed)->format('F d, Y') : 'N/A' }}</p>
                                    <div x-show="isEditing" class="relative group" x-cloak>
                                        <input type="date" name="date_constructed" value="{{ $building->date_constructed ? \Carbon\Carbon::parse($building->date_constructed)->format('Y-m-d') : '' }}" class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                    </div>
                                </div>

                                {{-- Acquisition Cost --}}
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Acquisition Cost</p>
                                    <p x-show="!isEditing" class="text-lg font-black text-emerald-600 mt-0.5 tracking-tighter px-1">₱ {{ number_format($building->acquisition_cost, 2) }}</p>
                                    <div x-show="isEditing" class="relative group" x-cloak>
                                        <input type="number" name="acquisition_cost" step="0.01" min="0" value="{{ $building->acquisition_cost }}" class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                    </div>
                                </div>

                                {{-- Useful Life --}}
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Estimated Useful Life</p>
                                    <p x-show="!isEditing" class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $building->estimated_useful_life }} Year(s)</p>
                                    <div x-show="isEditing" class="relative group" x-cloak>
                                        <input type="number" name="estimated_useful_life" min="0" value="{{ $building->estimated_useful_life }}" class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                    </div>
                                </div>

                                {{-- Appraised Value --}}
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Appraised Value</p>
                                    <p x-show="!isEditing" class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">₱ {{ number_format($building->appraised_value, 2) }}</p>
                                    <div x-show="isEditing" class="relative group" x-cloak>
                                        <input type="number" name="appraised_value" step="0.01" min="0" value="{{ $building->appraised_value }}" class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-deped"></span> Remarks / Notes
                            </h3>
                            <div class="bg-slate-50 rounded-xl p-6 border border-slate-100">
                                <p x-show="!isEditing" class="text-xs font-medium text-slate-600 leading-relaxed italic px-1">"{{ $building->remarks ?: 'No remarks recorded.' }}"</p>
                                <div x-show="isEditing" class="relative group" x-cloak>
                                    <textarea name="remarks" rows="3" class="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-xs font-black text-slate-100 focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-600">{{ $building->remarks }}</textarea>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- TAB 2: Lifecycle & History --}}
                    <div x-show="activeTab === 'history'" class="animate-fade relative" x-cloak>
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-deped"></span> Facility Timeline
                            </h3>
                        </div>

                        <div class="relative pl-3 max-w-3xl">
                            <div class="timeline-line"></div>
                            <div class="space-y-6">
                                @foreach($timeline as $event)
                                <div class="relative pl-8 group">
                                    <div class="absolute left-[-2px] top-1 w-6 h-6 rounded-full bg-white border-2 border-slate-300 flex items-center justify-center shadow-sm z-10">
                                        <div class="w-2 h-2 bg-slate-400 rounded-full"></div>
                                    </div>
                                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                                        <div class="flex justify-between items-start mb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[9px] font-black text-white uppercase tracking-widest bg-slate-500 px-2 py-0.5 rounded">{{ $event['type'] }}</span>
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $event['date'] }}</span>
                                            </div>
                                        </div>
                                        <p class="text-sm font-bold text-slate-800 uppercase mt-2">{{ $event['description'] }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- TAB 3: Blueprint & Documents --}}
                    <div x-show="activeTab === 'docs'" class="animate-fade space-y-6" x-cloak>
                        <div class="flex flex-col items-center justify-center py-12 border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50/50">
                            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center text-slate-300 shadow-sm mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic mb-1">No blueprints or warranty certs uploaded</h3>
                            <button class="mt-4 px-6 py-2 bg-white border border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-600 hover:text-deped hover:border-deped transition-all">Upload Document</button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
        
        {{-- Confirmation Modal --}}
        <div x-show="showConfirmModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center">
            <div x-show="showConfirmModal" x-transition.opacity class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showConfirmModal = false"></div>
            <div x-show="showConfirmModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" class="bg-white rounded-3xl shadow-2xl p-8 max-w-sm w-full mx-4 relative z-10 border border-slate-100 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-amber-100 text-amber-500 rounded-full flex items-center justify-center mb-5 ring-8 ring-amber-50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight mb-2">Save Changes?</h3>
                <p class="text-xs font-bold text-slate-500 mb-8 leading-relaxed">Confirm to update the building records. This action will be logged.</p>
                <div class="flex items-center gap-3 w-full">
                    <button @click="showConfirmModal = false" class="flex-1 py-3.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-black uppercase tracking-widest transition-colors">Cancel</button>
                    <button @click="document.getElementById('update-building-form').submit();" class="flex-1 py-3.5 px-4 bg-deped hover:bg-red-800 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-deped/30 transition-all active:scale-95">Yes, Save</button>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
