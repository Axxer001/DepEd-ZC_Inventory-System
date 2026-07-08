<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Profile | DepEd ZC Inventory</title>

    {{-- Error/Success Alerts --}}
    @if(session('error') || $errors->any())
        <div class="fixed top-6 left-1/2 -translate-x-1/2 z-[300] w-full max-w-md animate-in slide-in-from-top duration-300">
            <div class="bg-red-50 border-2 border-red-200 rounded-2xl p-4 shadow-xl flex items-start gap-4">
                <div class="w-10 h-10 bg-red-100 text-red-600 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div class="flex-grow pt-0.5">
                    <h4 class="text-sm font-black text-red-800 uppercase tracking-tight">Updating Failed</h4>
                    <p class="text-xs font-bold text-red-600 mt-0.5 leading-relaxed">
                        @if(session('error')) {{ session('error') }} @endif
                        @foreach ($errors->all() as $error)
                            • {{ $error }}<br>
                        @endforeach
                    </p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-red-400 hover:text-red-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
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

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll p-4 lg:p-8" x-data="{ activeTab: 'specs', showEditModal: false, showTransferModal: false, showReturnAmuModal: false, showReturnSourceModal: false, showImageFullscreen: false, showRemoveConfirmModal: false, isSaving: false, historyLimit: 5 }">
        
        {{-- Global Header (Fixed/Sticky) --}}
        <header class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 sticky top-0 z-50">
            <div class="flex items-center gap-5">
                <div class="w-12 h-12 bg-deped_light rounded-xl flex items-center justify-center border border-deped/20 shadow-sm shrink-0">
                    <svg class="w-6 h-6 text-deped" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-none uppercase italic">{{ $asset->item_name }}</h1>
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
                @if(auth()->check() && auth()->user()->isAdmin())
                <button @click="showEditModal = true" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 uppercase tracking-widest hover:border-deped hover:text-deped hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-sm hover:shadow-md flex items-center gap-2 group">
                    <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    Edit Asset
                </button>
                <div class="relative">
                    <button @click="open = !open" @click.away="open = false" class="px-5 py-2.5 bg-deped text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-red-800 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-md shadow-red-200 hover:shadow-lg hover:shadow-red-300 flex items-center gap-2">
                        Quick Actions
                        <svg class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="{'rotate-180': open}"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-2 scale-95" class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden transform origin-top-right">
                        <button @click="showTransferModal = true; open = false" class="w-full text-left px-4 py-3 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-blue-600 hover:pl-5 transition-all flex items-center gap-2 border-b border-slate-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg> Initiate Transfer
                        </button>
                        @if($asset->employee_id || ($asset->is_in_source ?? false))
                        <button @click="showReturnAmuModal = true; open = false" class="w-full text-left px-4 py-3 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-emerald-600 hover:pl-5 transition-all flex items-center gap-2 border-b border-slate-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg> Return to AMU
                        </button>
                        @else
                        <button disabled class="w-full text-left px-4 py-3 text-xs font-bold text-slate-400 cursor-not-allowed flex items-center gap-2 border-b border-slate-100" title="Asset is currently unassigned or already in AMU">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg> Return to AMU
                        </button>
                        @endif

                        @if(!($asset->is_in_source ?? false))
                        <button @click="showReturnSourceModal = true; open = false" class="w-full text-left px-4 py-3 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-orange-600 hover:pl-5 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-6a4 4 0 00-4-4H4m0 0l4-4m-4 4l4 4"></path></svg> Return to Supplier
                        </button>
                        @else
                        <button disabled class="w-full text-left px-4 py-3 text-xs font-bold text-slate-400 cursor-not-allowed flex items-center gap-2" title="Asset is already at the acquisition source/supplier">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-6a4 4 0 00-4-4H4m0 0l4-4m-4 4l4 4"></path></svg> Return to Supplier
                        </button>
                        @endif
                    </div>
                </div>
                <div class="w-px h-8 bg-slate-200 mx-1"></div>
                @endif
                <a href="/view-assets" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 uppercase tracking-widest hover:border-deped hover:text-deped hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 shadow-sm hover:shadow-md flex items-center gap-2 group">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Back
                </a>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 flex-grow pb-10">
            
            {{-- Left Sidebar: Asset Identity Card --}}
            <aside class="lg:col-span-3 flex flex-col gap-6 z-40 relative">
                <form action="{{ route('assets.photo.upload', $asset->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-visible flex flex-col relative" x-data="{ photoPreview: null, showPhotoConfirmModal: false, isHoveringImage: false }">
                    @csrf
                    <div class="aspect-square bg-slate-50 border-b border-slate-100 flex items-center justify-center p-6 relative group rounded-t-2xl overflow-hidden" @mouseenter="isHoveringImage = true" @mouseleave="isHoveringImage = false">
                        <input type="file" name="photo" id="photo-upload" class="hidden" accept="image/*" capture="environment" @change="
                            const file = $event.target.files[0]; 
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = (e) => photoPreview = e.target.result;
                                reader.readAsDataURL(file);
                            }
                        ">
                        <img :src="photoPreview || '{{ $asset->photo_path ? asset('storage/' . $asset->photo_path) : asset('images/asset.png') }}'" alt="Asset Photo" class="w-full h-full object-contain transition-transform duration-500 cursor-pointer" :class="(photoPreview || '{{ $asset->photo_path }}') ? 'opacity-100 scale-100 group-hover:scale-110' : 'opacity-50 group-hover:scale-105'" @click="if(!photoPreview && '{{ $asset->photo_path }}') showImageFullscreen = true">
                        
                        {{-- Hover Preview Popout (E-commerce Style) --}}
                        <div x-show="isHoveringImage && !photoPreview && '{{ $asset->photo_path }}'" 
                             x-transition:enter="transition ease-out duration-300 delay-150" 
                             x-transition:enter-start="opacity-0 scale-95 -translate-x-4" 
                             x-transition:enter-end="opacity-100 scale-100 translate-x-0" 
                             x-transition:leave="transition ease-in duration-150" 
                             x-transition:leave-start="opacity-100 scale-100" 
                             x-transition:leave-end="opacity-0 scale-95" 
                             class="absolute top-0 -right-[420px] w-[400px] h-[400px] bg-white rounded-2xl shadow-2xl border border-slate-200 z-[150] pointer-events-none flex items-center justify-center p-4 hidden lg:flex" x-cloak>
                            <img src="{{ $asset->photo_path ? asset('storage/' . $asset->photo_path) : '' }}" class="w-full h-full object-contain rounded-xl drop-shadow-md">
                        </div>
                        
                        {{-- Controls for View/Remove --}}
                        @if($asset->photo_path)
                        <div x-show="!photoPreview" class="absolute top-4 right-4 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10">
                            <button type="button" @click="showImageFullscreen = true" class="w-8 h-8 bg-white/90 backdrop-blur-sm rounded-full text-slate-700 hover:text-blue-600 shadow-sm flex items-center justify-center hover:scale-110 active:scale-95 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
                            </button>
                            @if(auth()->check() && auth()->user()->isAdmin())
                            <button type="button" @click="showRemoveConfirmModal = true" class="w-8 h-8 bg-white/90 backdrop-blur-sm rounded-full text-slate-700 hover:text-red-600 shadow-sm flex items-center justify-center hover:scale-110 active:scale-95 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                            @endif
                        </div>
                        @endif

                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4 pointer-events-none">
                            
                            {{-- State: No new photo selected --}}
                            @if(auth()->check() && auth()->user()->isAdmin())
                            <label x-show="!photoPreview" for="photo-upload" class="w-full py-2.5 bg-white/90 backdrop-blur-md rounded-lg text-xs font-black uppercase tracking-widest text-slate-800 hover:bg-white shadow-lg text-center cursor-pointer transition-all hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2 pointer-events-auto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span>{{ $asset->photo_path ? 'Change Photo' : 'Upload / Take Photo' }}</span>
                            </label>

                            {{-- State: New photo selected --}}
                            <div x-show="photoPreview" x-cloak class="w-full flex gap-2 pointer-events-auto">
                                <button type="button" @click="photoPreview = null; document.getElementById('photo-upload').value = ''" class="flex-1 py-2.5 bg-white/90 backdrop-blur-md text-slate-700 hover:bg-white rounded-lg text-[10px] font-black uppercase tracking-widest shadow-sm transition-all active:scale-95">Cancel</button>
                                <button type="button" @click="showPhotoConfirmModal = true" class="flex-[2] py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-600/30 transition-all active:scale-95">Save Photo</button>
                            </div>
                            @endif

                        </div>
                    </div>

                    {{-- Photo Confirm Modal (Internal to the form so it submits correctly) --}}
                    <div x-show="showPhotoConfirmModal" x-cloak class="fixed inset-0 z-[110] flex items-center justify-center">
                        <div x-show="showPhotoConfirmModal" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showPhotoConfirmModal = false"></div>
                        <div x-show="showPhotoConfirmModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" class="bg-white rounded-3xl shadow-2xl p-8 max-w-sm w-full mx-4 relative z-10 border border-slate-100 flex flex-col items-center text-center">
                            <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-5 ring-8 ring-blue-50">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            </div>
                            <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight mb-2">Save New Photo?</h3>
                            <p class="text-xs font-bold text-slate-500 mb-8 leading-relaxed">Are you sure you want to permanently update the photo for this asset?</p>
                            <div class="flex items-center gap-3 w-full">
                                <button type="button" @click="showPhotoConfirmModal = false" class="flex-1 py-3.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-black uppercase tracking-widest transition-colors">Cancel</button>
                                <button type="submit" class="flex-1 py-3.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-blue-600/30 transition-all active:scale-95">Yes, Save</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-5 space-y-5">
                        <div class="bg-slate-50 border border-slate-100 p-4 rounded-2xl shadow-sm relative overflow-hidden group">
                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-deped"></div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                                <svg class="w-3 h-3 text-deped" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                Current Custodian
                            </p>
                            @php
                                $custodianFullName = trim(($asset->custodian_first ?? '') . ' ' . ($asset->custodian_middle ? $asset->custodian_middle . ' ' : '') . ($asset->custodian_last ?? ''));
                                $custodianDisplay = $custodianFullName ?: ($asset->office_school_name ?? 'Warehouse');
                                $custodianInitials = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $custodianDisplay), 0, 2)) ?: 'NA';
                            @endphp
                            <div class="flex items-center gap-3 pl-1">
                                <div class="w-10 h-10 rounded-full bg-slate-200 border border-slate-300 flex items-center justify-center text-slate-600 font-black text-xs shrink-0 shadow-sm group-hover:scale-110 group-hover:bg-deped group-hover:border-deped group-hover:text-white transition-all">
                                    {{ $custodianInitials }}
                                </div>
                                <div>
                                    <p class="text-xs font-black text-slate-700 uppercase leading-tight group-hover:text-deped transition-colors">{{ $custodianDisplay }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase mt-0.5">{{ $custodianFullName ? $asset->office_school_name : 'Unassigned / Warehouse' }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Current Location</p>
                            <a href="#" class="group flex items-start gap-2">
                                <svg class="w-4 h-4 text-deped shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <div>
                                    <p class="text-xs font-bold text-deped uppercase leading-tight group-hover:underline">{{ $asset->office_school_name }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase">{{ $asset->division ?? 'Division of Zamboanga City' }}</p>
                                </div>
                            </a>
                        </div>

                        @php
                            $usefulLifeYears = $asset->estimated_useful_life ?? 0;
                            $startDate = $asset->acceptance_date ? \Carbon\Carbon::parse($asset->acceptance_date) : null;
                            
                            $percentRemaining = 0;
                            $progressClass = 'from-slate-400 to-slate-300';
                            $statusText = "Lifespan Data Unavailable";

                            if ($usefulLifeYears > 0 && $startDate) {
                                $endDate = $startDate->copy()->addYears($usefulLifeYears);
                                $now = \Carbon\Carbon::now();
                                
                                $totalDays = $startDate->diffInDays($endDate);
                                $daysElapsed = $startDate->diffInDays($now, false);
                                
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
                                    
                                    // Strip trailing .0 if integer
                                    $yearsStr = (floor($yearsRemainingFloat) == $yearsRemainingFloat) 
                                        ? floor($yearsRemainingFloat) 
                                        : $yearsRemainingFloat;
                                        
                                    $statusText = "{$yearsStr} of {$usefulLifeYears} Years Remaining";
                                }
                                
                                if ($percentRemaining > 50) {
                                    $progressClass = 'from-emerald-500 to-green-400';
                                } elseif ($percentRemaining > 25) {
                                    $progressClass = 'from-amber-500 to-amber-400';
                                } else {
                                    $progressClass = 'from-red-600 to-red-400';
                                }
                            }
                        @endphp
                        <div class="pt-4 border-t border-slate-100">
                            <div class="flex justify-between items-end mb-1.5">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Est. Lifespan</p>
                                <p class="text-[10px] font-black text-slate-700">{{ $percentRemaining }}%</p>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-gradient-to-r {{ $progressClass }} h-full rounded-full transition-all duration-1000" style="width: {{ $percentRemaining }}%"></div>
                            </div>
                            <p class="text-[8px] font-bold text-slate-400 uppercase mt-1.5 text-right">{{ $statusText }}</p>
                        </div>

                        @php
                            $warrantyMonths = $asset->warranty ?? 0;
                            $warrantyPercentRemaining = 0;
                            $warrantyProgressClass = 'from-slate-400 to-slate-300';
                            $warrantyStatusText = "Warranty Data Unavailable";

                            if ($warrantyMonths > 0 && $startDate) {
                                $warrantyEndDate = $startDate->copy()->addMonths($warrantyMonths);
                                $now = \Carbon\Carbon::now();
                                
                                $totalWarrantyDays = $startDate->diffInDays($warrantyEndDate);
                                $daysWarrantyElapsed = $startDate->diffInDays($now, false);
                                
                                if ($daysWarrantyElapsed < 0) {
                                    $warrantyPercentRemaining = 100;
                                    $warrantyStatusText = "{$warrantyMonths} of {$warrantyMonths} Months Remaining";
                                } elseif ($daysWarrantyElapsed >= $totalWarrantyDays) {
                                    $warrantyPercentRemaining = 0;
                                    $warrantyStatusText = "0 of {$warrantyMonths} Months Remaining (Expired)";
                                } else {
                                    $daysWarrantyRemaining = $totalWarrantyDays - $daysWarrantyElapsed;
                                    $warrantyPercentRemaining = round(($daysWarrantyRemaining / $totalWarrantyDays) * 100);
                                    $monthsRemainingFloat = round($daysWarrantyRemaining / 30.417, 1);
                                    
                                    $monthsStr = (floor($monthsRemainingFloat) == $monthsRemainingFloat) 
                                        ? floor($monthsRemainingFloat) 
                                        : $monthsRemainingFloat;
                                        
                                    $warrantyStatusText = "{$monthsStr} of {$warrantyMonths} Months Remaining";
                                }
                                
                                if ($warrantyPercentRemaining > 50) {
                                    $warrantyProgressClass = 'from-emerald-500 to-green-400';
                                } elseif ($warrantyPercentRemaining > 25) {
                                    $warrantyProgressClass = 'from-amber-500 to-amber-400';
                                } else {
                                    $warrantyProgressClass = 'from-red-600 to-red-400';
                                }
                            }
                        @endphp
                        <div class="pt-4 mt-4 border-t border-slate-100">
                            <div class="flex justify-between items-end mb-1.5">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Est. Warranty</p>
                                <p class="text-[10px] font-black text-slate-700">{{ $warrantyPercentRemaining }}%</p>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-gradient-to-r {{ $warrantyProgressClass }} h-full rounded-full transition-all duration-1000" style="width: {{ $warrantyPercentRemaining }}%"></div>
                            </div>
                            <p class="text-[8px] font-bold text-slate-400 uppercase mt-1.5 text-right">{{ $warrantyStatusText }}</p>
                        </div>
                    </div>
                </form>
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
                                <span class="w-1.5 h-1.5 rounded-full bg-deped"></span> Custodian Details
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-8 bg-slate-50 rounded-xl p-6 border border-slate-100 mb-8">
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Region</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $asset->region ?? 'Region IX - Zamboanga Peninsula' }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Division</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $asset->division ?? 'Division of Zamboanga City' }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Office / School Name</p>
                                    @php
                                        $officeSchoolLabel = $asset->school_name ?? $asset->office_name ?? null;
                                    @endphp
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">
                                        {{ $officeSchoolLabel ?: ($asset->office_school_name !== 'Warehouse' ? $asset->office_school_name : 'Unassigned') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Custodian</p>
                                    @php
                                        $custodianName = trim(($asset->custodian_first ?? '') . ' ' . ($asset->custodian_middle ? $asset->custodian_middle . ' ' : '') . ($asset->custodian_last ?? ''));
                                    @endphp
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $custodianName ?: 'No Employee Assigned' }}</p>
                                    @if($asset->employee_id_code)
                                        <p class="text-[9px] font-bold text-slate-400 mt-0.5 px-1">ID: {{ $asset->employee_id_code }}</p>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Position</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $asset->custodian_position ?: 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Contact No.</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $asset->custodian_contact ?: 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-deped"></span> Technical Details
                            </h3>
                             <div class="grid grid-cols-1 md:grid-cols-3 gap-y-6 gap-x-8 bg-slate-50 rounded-xl p-6 border border-slate-100">
                                {{-- Classification --}}
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Classification</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $asset->classification_name }}</p>
                                </div>

                                {{-- Category --}}
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Category</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $asset->category_name }}</p>
                                </div>

                                 {{-- Item Name --}}
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Article / Item</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $asset->item_name }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Description</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase px-1">{{ $asset->description }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Unit Cost</p>
                                    <p class="text-xs font-bold text-emerald-600 mt-1 uppercase px-1">₱ {{ number_format($asset->asset_cost, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Quantity</p>
                                    <p class="text-xs font-black text-deped mt-1 uppercase px-1">{{ $asset->quantity }} Unit(s)</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-deped"></span> Procurement Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8 bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Property Number</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase">{{ $asset->property_number ?: 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Serial Number</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase">{{ $asset->serial_number ?: 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Acquisition Date</p>
                                    <p class="text-sm font-black text-slate-800 mt-1 uppercase">{{ $asset->acquisition_date ? \Carbon\Carbon::parse($asset->acquisition_date)->format('F d, Y') : 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Funding / Source</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase">{{ $asset->source_name }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Mode of Acquisition</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase">{{ $asset->mode_of_acquisition }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Supplier</p>
                                    <p class="text-xs font-bold text-slate-800 mt-1 uppercase">{{ $asset->supplier_name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: Lifecycle & History --}}
                    <div x-show="activeTab === 'history'" class="animate-fade h-full flex flex-col" x-cloak>
                        
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-deped"></span> Activity Timeline
                            </h3>
                            <div class="relative">
                                <input type="text" placeholder="Filter history..." class="pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold focus:outline-none focus:ring-2 focus:ring-deped/20 focus:border-deped transition-all">
                                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </div>

                        <div class="relative pl-3 max-w-3xl flex-1 min-h-0 overflow-y-auto custom-scroll pr-4">
                            <div class="timeline-line"></div>
                            
                            <div class="space-y-6">
                                @foreach($timeline as $event)
                                @php
                                    $colors = match($event['type']) {
                                        'Transfer' => ['border' => 'border-deped', 'bg' => 'bg-deped'],
                                        'Return' => ['border' => 'border-amber-500', 'bg' => 'bg-amber-500'],
                                        'Return to Supplier' => ['border' => 'border-orange-500', 'bg' => 'bg-orange-500'],
                                        'Temporary Borrow' => ['border' => 'border-blue-500', 'bg' => 'bg-blue-500'],
                                        default => ['border' => 'border-emerald-500', 'bg' => 'bg-emerald-500'],
                                    };
                                @endphp
                                <div class="relative pl-8 group">
                                    <div class="absolute left-[-2px] top-1 w-6 h-6 rounded-full bg-white border-2 {{ $colors['border'] }} flex items-center justify-center shadow-sm z-10">
                                        <div class="w-2 h-2 {{ $colors['bg'] }} rounded-full"></div>
                                    </div>
                                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                                        <div class="flex justify-between items-start mb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[9px] font-black text-white uppercase tracking-widest {{ $colors['bg'] }} px-2 py-0.5 rounded">{{ $event['type'] }}</span>
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $event['date'] }}</span>
                                            </div>
                                        </div>
                                        <p class="text-sm font-bold text-slate-800 mt-2">{{ $event['description'] }}</p>
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
                    <div x-show="activeTab === 'docs'" class="animate-fade space-y-6" x-cloak>
                        
                        @if(auth()->check() && auth()->user()->isAdmin())
                        {{-- Upload Form --}}
                        <form action="{{ route('assets.document.upload', $asset->id) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-2xl border-2 border-dashed border-slate-200 hover:border-blue-500 transition-colors group relative" x-data="{ docName: null }">
                            @csrf
                            <input type="file" name="document" id="doc-upload" class="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx,image/*" @change="docName = $event.target.files[0]?.name; document.getElementById('camera-upload').value = ''">
                            <input type="file" name="document_camera" id="camera-upload" class="hidden" accept="image/*" capture="environment" @change="docName = $event.target.files[0]?.name; document.getElementById('doc-upload').value = ''">
                            
                            <div class="flex flex-col items-center justify-center h-32" x-show="!docName">
                                <div class="flex gap-4 mb-3">
                                    <label for="doc-upload" class="w-14 h-14 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center shadow-sm cursor-pointer hover:scale-110 hover:bg-blue-100 transition-all" title="Browse Files">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                    </label>
                                    <label for="camera-upload" class="w-14 h-14 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center shadow-sm cursor-pointer hover:scale-110 hover:bg-emerald-100 transition-all" title="Take a Picture">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </label>
                                </div>
                                <h3 class="text-xs font-black text-slate-700 uppercase tracking-widest mb-1">Upload or Capture</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">PDF, DOCX, or Image (Max 10MB)</p>
                            </div>

                            <div x-show="docName" x-cloak class="flex flex-col items-center justify-center h-32">
                                <div class="w-14 h-14 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center shadow-sm mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <h3 class="text-xs font-black text-slate-700 uppercase tracking-widest mb-1 truncate max-w-[250px]" x-text="docName"></h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Ready to upload</p>
                            </div>
                            
                            <div x-show="docName" x-cloak class="mt-4 flex justify-center gap-3">
                                <button type="button" @click="docName = null; document.getElementById('doc-upload').value = ''; document.getElementById('camera-upload').value = ''" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-[10px] font-black uppercase tracking-widest transition-colors">Cancel</button>
                                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-600/30 transition-all active:scale-95">Upload Document</button>
                            </div>
                        </form>
                        @endif

                        {{-- Document List --}}
                        @if($documents->count() > 0)
                            <div class="space-y-3">
                                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-2 mb-3">Uploaded Files ({{ $documents->count() }})</h3>
                                @foreach($documents as $doc)
                                    <div class="flex items-center justify-between p-4 bg-white rounded-2xl shadow-sm border border-slate-100 hover:border-slate-300 transition-all group">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 border border-slate-100">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            </div>
                                            <div>
                                                <p class="text-xs font-black text-slate-700 truncate max-w-[200px] lg:max-w-[300px]">{{ $doc->file_name }}</p>
                                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ number_format($doc->file_size / 1024, 1) }} KB &bull; {{ \Carbon\Carbon::parse($doc->created_at)->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 hover:text-blue-600 hover:bg-blue-50 flex items-center justify-center transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            </a>
                                            @if(auth()->check() && auth()->user()->isAdmin())
                                            <form action="{{ route('assets.document.remove', $doc->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this document?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 hover:text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-8 border border-slate-100 rounded-2xl bg-slate-50/50">
                                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic mb-1">No Documents Uploaded</h3>
                            </div>
                        @endif

                    </div>

                </div>
            </div>

        </div>
        


        {{-- Transfer Asset Modal --}}
        <div x-show="showTransferModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center">
            <div x-show="showTransferModal" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showTransferModal = false"></div>
            <div x-show="showTransferModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-8 scale-95" class="bg-white rounded-3xl shadow-2xl w-full max-w-xl mx-4 relative z-10 flex flex-col overflow-hidden border border-slate-100 max-h-[90vh]">
                
                {{-- Modal Header --}}
                <div class="bg-slate-50 border-b border-slate-100 px-6 py-5 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center shadow-inner">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-[0.1em]">Transfer Asset</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase mt-0.5">Reassign to a new custodian</p>
                        </div>
                    </div>
                    <button @click="showTransferModal = false" class="text-slate-400 hover:text-slate-600 hover:bg-slate-200/50 p-2.5 rounded-full transition-colors active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                {{-- Form Body --}}
                <form action="{{ route('assets.transfer', $asset->id) }}" method="POST" class="flex flex-col min-h-0">
                    @csrf
                    <div class="p-6 space-y-5 overflow-y-auto custom-scroll" x-data="{
                        employees: @js($employees),
                        schools: @js($schools),
                        offices: @js($offices),
                        recipientType: 'employee',
                        searchQuery: '',
                        schoolSearchQuery: '',
                        officeSearchQuery: '',
                        showDropdown: false,
                        showSchoolDropdown: false,
                        showOfficeDropdown: false,
                        selectedEmployee: null,
                        selectedSchool: null,
                        selectedOffice: null,
                        transferType: 'Permanent Reassignment',

                        get filteredEmployees() {
                            let q = this.searchQuery.trim().toLowerCase();
                            if (q === '') return this.employees.slice(0, 50);
                            return this.employees.filter(e => 
                                (e.full_name && e.full_name.toLowerCase().includes(q)) || 
                                (e.employee_id && String(e.employee_id).toLowerCase().includes(q))
                            ).slice(0, 50);
                        },

                        get filteredSchools() {
                            let q = this.schoolSearchQuery.trim().toLowerCase();
                            if (q === '') return this.schools.slice(0, 50);
                            return this.schools.filter(s => 
                                (s.name && s.name.toLowerCase().includes(q)) || 
                                (s.school_id && String(s.school_id).toLowerCase().includes(q))
                            ).slice(0, 50);
                        },

                        get filteredOffices() {
                            let q = this.officeSearchQuery.trim().toLowerCase();
                            if (q === '') return this.offices.slice(0, 50);
                            return this.offices.filter(o => 
                                (o.name && o.name.toLowerCase().includes(q)) || 
                                (o.office_id && String(o.office_id).toLowerCase().includes(q))
                            ).slice(0, 50);
                        },

                        selectEmployee(emp) {
                            this.selectedEmployee = emp;
                            this.searchQuery = emp.full_name;
                            this.showDropdown = false;
                        },

                        selectSchool(sch) {
                            this.selectedSchool = sch;
                            this.schoolSearchQuery = sch.name;
                            this.showSchoolDropdown = false;
                        },

                        selectOffice(off) {
                            this.selectedOffice = off;
                            this.officeSearchQuery = off.name;
                            this.showOfficeDropdown = false;
                        }
                    }">
                        
                        {{-- Current Info Header remains for context --}}
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex justify-between items-center relative overflow-hidden group mb-4">
                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500"></div>
                            <div class="pl-2">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Current Location</p>
                                <p class="text-xs font-black text-slate-700 uppercase">{{ $asset->office_school_name }}</p>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm border border-slate-200">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                            </div>
                        </div>

                        {{-- Recipient Type Selection --}}
                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Transfer Recipient Type</label>
                            <div class="grid grid-cols-3 gap-2 bg-slate-100 p-1 rounded-xl">
                                <button type="button" @click="recipientType = 'employee'"
                                    :class="recipientType === 'employee' ? 'bg-[#c00000] text-white shadow-sm font-black' : 'text-slate-600 hover:text-slate-900 font-bold'"
                                    class="py-2 text-[10px] uppercase tracking-wider rounded-lg transition-all text-center">
                                    Employee
                                </button>
                                <button type="button" @click="recipientType = 'school'"
                                    :class="recipientType === 'school' ? 'bg-[#c00000] text-white shadow-sm font-black' : 'text-slate-600 hover:text-slate-900 font-bold'"
                                    class="py-2 text-[10px] uppercase tracking-wider rounded-lg transition-all text-center">
                                    School Direct
                                </button>
                                <button type="button" @click="recipientType = 'office'"
                                    :class="recipientType === 'office' ? 'bg-[#c00000] text-white shadow-sm font-black' : 'text-slate-600 hover:text-slate-900 font-bold'"
                                    class="py-2 text-[10px] uppercase tracking-wider rounded-lg transition-all text-center">
                                    Office Direct
                                </button>
                            </div>
                        </div>

                        {{-- Employee Recipient Form --}}
                        <div x-show="recipientType === 'employee'" class="space-y-5">
                            {{-- Employee Search Field --}}
                            <div class="relative z-50">
                                <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Search Employee <span class="text-deped">*</span></label>
                                <div class="relative group" @click.away="showDropdown = false">
                                    <input type="text" x-model="searchQuery" @focus="showDropdown = true" @input="showDropdown = true" :required="recipientType === 'employee'" autocomplete="off"
                                        class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm hover:border-slate-300" placeholder="Type name or ID to search...">
                                    <svg x-show="!selectedEmployee" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-hover:text-blue-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                    <button type="button" x-show="selectedEmployee" @click="selectedEmployee = null; searchQuery = ''; showDropdown = true" class="absolute right-4 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-md transition-all cursor-pointer" x-cloak>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                    
                                    <div x-show="showDropdown && filteredEmployees.length > 0" x-cloak 
                                        class="absolute left-0 right-0 mt-2 bg-white border-2 border-slate-200 rounded-xl shadow-xl max-h-48 overflow-y-auto custom-scroll p-1 z-50">
                                        <template x-for="e in filteredEmployees" :key="e.id">
                                            <div @click="selectEmployee(e)" 
                                                class="px-4 py-2.5 text-[10px] font-black text-slate-600 uppercase hover:bg-slate-100 hover:text-blue-600 rounded-lg cursor-pointer transition-colors flex justify-between items-center">
                                                <span x-text="e.full_name"></span>
                                                <span class="text-slate-400 font-bold ml-2" x-text="e.employee_id ? '[' + e.employee_id + ']' : ''"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Employee Autofilled details --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Employee ID</label>
                                    <input type="text" readonly :value="selectedEmployee ? selectedEmployee.employee_id : ''"
                                        class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-400 uppercase outline-none shadow-sm cursor-not-allowed" placeholder="AUTO-FILLED">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Position</label>
                                    <input type="text" readonly :value="selectedEmployee ? (selectedEmployee.position || 'N/A') : ''"
                                        class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-400 uppercase outline-none shadow-sm cursor-not-allowed" placeholder="AUTO-FILLED">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Office / School Location</label>
                                <input type="text" readonly :value="selectedEmployee ? (selectedEmployee.location_name || 'N/A') : ''"
                                    class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-400 uppercase outline-none shadow-sm cursor-not-allowed" placeholder="AUTO-FILLED">
                            </div>
                        </div>

                        {{-- School Recipient Form --}}
                        <div x-show="recipientType === 'school'" class="space-y-5" x-cloak>
                            <div class="relative z-50">
                                <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Search School <span class="text-deped">*</span></label>
                                <div class="relative group" @click.away="showSchoolDropdown = false">
                                    <input type="text" x-model="schoolSearchQuery" @focus="showSchoolDropdown = true" @input="showSchoolDropdown = true" :required="recipientType === 'school'" autocomplete="off"
                                        class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm hover:border-slate-300" placeholder="Type school name to search...">
                                    <svg x-show="!selectedSchool" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-hover:text-blue-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                    <button type="button" x-show="selectedSchool" @click="selectedSchool = null; schoolSearchQuery = ''; showSchoolDropdown = true" class="absolute right-4 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-md transition-all cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                    
                                    <div x-show="showSchoolDropdown && filteredSchools.length > 0" x-cloak 
                                        class="absolute left-0 right-0 mt-2 bg-white border-2 border-slate-200 rounded-xl shadow-xl max-h-48 overflow-y-auto custom-scroll p-1 z-50">
                                        <template x-for="s in filteredSchools" :key="s.id">
                                            <div @click="selectSchool(s)" 
                                                class="px-4 py-2.5 text-[10px] font-black text-slate-600 uppercase hover:bg-slate-100 hover:text-blue-600 rounded-lg cursor-pointer transition-colors flex justify-between items-center">
                                                <span x-text="s.name"></span>
                                                <span class="text-slate-400 font-bold ml-2" x-text="s.school_id ? '[' + s.school_id + ']' : ''"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">School ID</label>
                                    <input type="text" readonly :value="selectedSchool ? selectedSchool.school_id : ''"
                                        class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-400 uppercase outline-none shadow-sm cursor-not-allowed" placeholder="AUTO-FILLED">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Type</label>
                                    <input type="text" readonly :value="selectedSchool ? (selectedSchool.type || 'N/A') : ''"
                                        class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-400 uppercase outline-none shadow-sm cursor-not-allowed" placeholder="AUTO-FILLED">
                                </div>
                            </div>
                        </div>

                        {{-- Office Recipient Form --}}
                        <div x-show="recipientType === 'office'" class="space-y-5" x-cloak>
                            <div class="relative z-50">
                                <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Search Office <span class="text-deped">*</span></label>
                                <div class="relative group" @click.away="showOfficeDropdown = false">
                                    <input type="text" x-model="officeSearchQuery" @focus="showOfficeDropdown = true" @input="showOfficeDropdown = true" :required="recipientType === 'office'" autocomplete="off"
                                        class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm hover:border-slate-300" placeholder="Type office name to search...">
                                    <svg x-show="!selectedOffice" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-hover:text-blue-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                    <button type="button" x-show="selectedOffice" @click="selectedOffice = null; officeSearchQuery = ''; showOfficeDropdown = true" class="absolute right-4 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-md transition-all cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                    
                                    <div x-show="showOfficeDropdown && filteredOffices.length > 0" x-cloak 
                                        class="absolute left-0 right-0 mt-2 bg-white border-2 border-slate-200 rounded-xl shadow-xl max-h-48 overflow-y-auto custom-scroll p-1 z-50">
                                        <template x-for="o in filteredOffices" :key="o.id">
                                            <div @click="selectOffice(o)" 
                                                class="px-4 py-2.5 text-[10px] font-black text-slate-600 uppercase hover:bg-slate-100 hover:text-blue-600 rounded-lg cursor-pointer transition-colors flex justify-between items-center">
                                                <span x-text="o.name"></span>
                                                <span class="text-slate-400 font-bold ml-2" x-text="o.office_id ? '[' + o.office_id + ']' : ''"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Office ID</label>
                                    <input type="text" readonly :value="selectedOffice ? selectedOffice.office_id : ''"
                                        class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-400 uppercase outline-none shadow-sm cursor-not-allowed" placeholder="AUTO-FILLED">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Type</label>
                                    <input type="text" readonly :value="selectedOffice ? (selectedOffice.type || 'N/A') : ''"
                                        class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-400 uppercase outline-none shadow-sm cursor-not-allowed" placeholder="AUTO-FILLED">
                                </div>
                            </div>
                        </div>

                        {{-- Hidden inputs --}}
                        <input type="hidden" name="employee_id" :value="recipientType === 'employee' && selectedEmployee ? selectedEmployee.id : ''">
                        <input type="hidden" name="school_db_id" :value="recipientType === 'school' && selectedSchool ? selectedSchool.id : (recipientType === 'office' && selectedOffice ? selectedOffice.id : '')">
                        <input type="hidden" name="is_office" :value="recipientType === 'office' ? '1' : ''">

                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Date of Transfer</label>
                                <input type="date" name="transfer_date" value="{{ date('Y-m-d') }}" class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm hover:border-slate-300 cursor-pointer">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Transfer Type</label>
                                <div class="relative group">
                                    <select name="transfer_type" x-model="transferType" class="w-full appearance-none bg-white border-2 border-slate-200 rounded-xl pl-4 pr-10 py-3.5 text-xs font-black text-slate-700 uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm hover:border-slate-300 cursor-pointer">
                                        <option value="Permanent Reassignment">Permanent Reassignment</option>
                                        <option value="Temporary Borrow">Temporary Borrow</option>
                                    </select>
                                    <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-hover:text-blue-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>

                        <div x-show="transferType === 'Temporary Borrow'" x-cloak>
                            <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Borrowed Until</label>
                            <input type="date" name="return_date" class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm hover:border-slate-300 cursor-pointer">
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Asset Condition <span class="text-deped">*</span></label>
                            <div class="relative group">
                                <select name="condition" required class="w-full appearance-none bg-white border-2 border-slate-200 rounded-xl pl-4 pr-10 py-3.5 text-xs font-black text-slate-700 uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm hover:border-slate-300 cursor-pointer">
                                    <option value="" disabled selected>Select condition...</option>
                                    <option value="Good Condition">Good Condition</option>
                                    <option value="Needs Repair">Needs Repair</option>
                                    <option value="Unserviceable">Unserviceable</option>
                                </select>
                                <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-hover:text-blue-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Reason / Remarks</label>
                            <textarea name="remarks" rows="2" class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-bold text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm resize-none hover:border-slate-300" placeholder="State reason for transfer..."></textarea>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="bg-slate-50 border-t border-slate-100 p-6 flex items-center justify-end gap-3">
                        <button type="button" @click="showTransferModal = false" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl text-xs font-black uppercase tracking-widest transition-colors shadow-sm active:scale-95">Cancel</button>
                        <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-blue-600/30 transition-all active:scale-95 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            Confirm Transfer
                        </button>
                    </div>
                </form>

            </div>
        </div>

        {{-- Return to AMU Modal --}}
        <div x-show="showReturnAmuModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center">
            <div x-show="showReturnAmuModal" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showReturnAmuModal = false"></div>
            <form action="{{ route('assets.return', $asset->id) }}" method="POST" x-show="showReturnAmuModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-8 scale-95" class="bg-white rounded-3xl shadow-2xl w-full max-w-xl mx-4 relative z-10 flex flex-col overflow-hidden border border-slate-100">
                @csrf
                {{-- Modal Header --}}
                <div class="bg-slate-50 border-b border-slate-100 px-6 py-5 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center shadow-inner">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-[0.1em]">Return to AMU</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase mt-0.5">Surrender asset back to division</p>
                        </div>
                    </div>
                    <button type="button" @click="showReturnAmuModal = false" class="text-slate-400 hover:text-slate-600 hover:bg-slate-200/50 p-2.5 rounded-full transition-colors active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 space-y-6">
                    
                    {{-- Current Info --}}
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex justify-between items-center relative overflow-hidden group">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-500"></div>
                        <div class="pl-2">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Returning From</p>
                            <p class="text-xs font-black text-slate-700 uppercase">{{ $asset->office_school_name }}</p>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm border border-slate-200">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                        </div>
                    </div>

                    {{-- Form Fields --}}
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Date of Return <span class="text-deped">*</span></label>
                            <input type="date" name="return_date" value="{{ date('Y-m-d') }}" required class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all shadow-sm hover:border-slate-300 cursor-pointer">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Asset Condition <span class="text-deped">*</span></label>
                            <div class="relative group">
                                <select name="condition" required class="w-full appearance-none bg-white border-2 border-slate-200 rounded-xl pl-4 pr-10 py-3 text-xs font-black text-slate-700 uppercase focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all shadow-sm hover:border-slate-300 cursor-pointer">
                                    <option value="" disabled selected>Select condition...</option>
                                    <option value="Good Condition">Good Condition</option>
                                    <option value="Needs Repair">Needs Repair</option>
                                    <option value="Unserviceable">Unserviceable</option>
                                </select>
                                <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-hover:text-emerald-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Reason for Return</label>
                        <textarea name="remarks" rows="3" class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-bold text-slate-700 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all shadow-sm resize-none hover:border-slate-300" placeholder="State reason why the asset is being surrendered..."></textarea>
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div class="bg-slate-50 border-t border-slate-100 p-6 flex items-center justify-end gap-3">
                    <button type="button" @click="showReturnAmuModal = false" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl text-xs font-black uppercase tracking-widest transition-colors shadow-sm active:scale-95">Cancel</button>
                    <button type="submit" class="px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-emerald-600/30 transition-all active:scale-95 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        Confirm Return
                    </button>
                </div>

            </form>
        </div>

        {{-- Return to Supplier Modal --}}
        <div x-show="showReturnSourceModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center">
            <div x-show="showReturnSourceModal" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showReturnSourceModal = false"></div>
            <form action="{{ route('assets.return_source', $asset->id) }}" method="POST"
                  x-data="{ returnCondition: '', supplierHasServiceCenter: {{ $supplierHasServiceCenter ? 'true' : 'false' }} }"
                  x-show="showReturnSourceModal"
                  x-transition:enter="transition ease-out duration-300"
                  x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                  x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                  x-transition:leave="transition ease-in duration-200"
                  x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                  x-transition:leave-end="opacity-0 translate-y-8 scale-95"
                  class="bg-white rounded-3xl shadow-2xl w-full max-w-xl mx-4 relative z-10 flex flex-col overflow-hidden border border-slate-100">
                @csrf
                {{-- Modal Header --}}
                <div class="bg-slate-50 border-b border-slate-100 px-6 py-5 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center shadow-inner">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 15v-6a4 4 0 00-4-4H4m0 0l4-4m-4 4l4 4"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-[0.1em]">Return to Supplier</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase mt-0.5">Send asset back to supplier</p>
                        </div>
                    </div>
                    <button type="button" @click="showReturnSourceModal = false" class="text-slate-400 hover:text-slate-600 hover:bg-slate-200/50 p-2.5 rounded-full transition-colors active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 space-y-5">

                    {{-- Current Info --}}
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex justify-between items-center relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-orange-500"></div>
                        <div class="pl-2">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Returning From</p>
                            <p class="text-xs font-black text-slate-700 uppercase">{{ $asset->office_school_name }}</p>
                        </div>
                        <div class="text-right pr-2">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Returning To</p>
                            <p class="text-xs font-black text-slate-700 uppercase">{{ $asset->supplier_name ?? $asset->source_name ?? 'Supplier' }}</p>
                        </div>
                    </div>

                    {{-- Form Fields --}}
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Date of Return <span class="text-deped">*</span></label>
                            <input type="date" name="return_date" value="{{ date('Y-m-d') }}" required class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 outline-none transition-all shadow-sm hover:border-slate-300 cursor-pointer">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Asset Condition <span class="text-deped">*</span></label>
                            <div class="relative group">
                                <select name="condition" required x-model="returnCondition" class="w-full appearance-none bg-white border-2 border-slate-200 rounded-xl pl-4 pr-10 py-3 text-xs font-black text-slate-700 uppercase focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 outline-none transition-all shadow-sm hover:border-slate-300 cursor-pointer">
                                    <option value="" disabled selected>Select condition...</option>
                                    <option value="Good Condition">Good Condition</option>
                                    <option value="Needs Repair">Needs Repair</option>
                                    <option value="Unserviceable">Unserviceable</option>
                                </select>
                                <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-hover:text-orange-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Expected Return Date (shown only when condition = Needs Repair AND supplier has service center) --}}
                    <div x-show="returnCondition === 'Needs Repair' && supplierHasServiceCenter"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-cloak>
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                            <label class="block text-[10px] font-black text-amber-700 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                Expected Return Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="expected_return_date"
                                   :required="returnCondition === 'Needs Repair' && supplierHasServiceCenter"
                                   class="w-full bg-white border-2 border-amber-300 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 outline-none transition-all shadow-sm cursor-pointer">
                            <p class="text-[10px] font-bold text-amber-600 mt-2">
                                This asset will be tracked under <strong>Asset Service</strong> for repair monitoring.
                            </p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Reason for Return</label>
                        <textarea name="remarks" rows="3" class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-bold text-slate-700 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 outline-none transition-all shadow-sm resize-none hover:border-slate-300" placeholder="State reason why the asset is being returned to supplier..."></textarea>
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div class="bg-slate-50 border-t border-slate-100 p-6 flex items-center justify-end gap-3">
                    <button type="button" @click="showReturnSourceModal = false" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl text-xs font-black uppercase tracking-widest transition-colors shadow-sm active:scale-95">Cancel</button>
                    <button type="submit" class="px-8 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-orange-600/30 transition-all active:scale-95 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        Confirm Return
                    </button>
                </div>

            </form>
        </div>

        {{-- Hidden Form for Photo Removal --}}
        <form id="remove-photo-form" action="{{ route('assets.photo.remove', $asset->id) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>

        {{-- Remove Photo Confirm Modal --}}
        <div x-show="showRemoveConfirmModal" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center">
            <div x-show="showRemoveConfirmModal" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showRemoveConfirmModal = false"></div>
            <div x-show="showRemoveConfirmModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" class="bg-white rounded-3xl shadow-2xl p-8 max-w-sm w-full mx-4 relative z-10 border border-slate-100 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-5 ring-8 ring-red-50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </div>
                <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight mb-2">Remove Photo?</h3>
                <p class="text-xs font-bold text-slate-500 mb-8 leading-relaxed">Are you sure you want to delete this asset's photo permanently? This action cannot be undone.</p>
                <div class="flex items-center gap-3 w-full">
                    <button type="button" @click="showRemoveConfirmModal = false" class="flex-1 py-3.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-black uppercase tracking-widest transition-colors">Cancel</button>
                    <button type="button" @click="document.getElementById('remove-photo-form').submit()" class="flex-1 py-3.5 px-4 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-red-600/30 transition-all active:scale-95">Yes, Remove</button>
                </div>
            </div>
        </div>

        {{-- Edit Asset Modal --}}
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center">
            <div x-show="showEditModal" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showEditModal = false"></div>
            <form id="update-asset-form" action="{{ route('assets.update', $asset->id) }}" method="POST" x-show="showEditModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" class="bg-white rounded-3xl shadow-2xl w-full max-w-xl mx-4 relative z-10 flex flex-col overflow-hidden border border-slate-100">
                @csrf
                {{-- Modal Header --}}
                <div class="bg-slate-50 border-b border-slate-100 px-6 py-5 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-deped/10 text-deped rounded-2xl flex items-center justify-center shadow-inner">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-[0.1em]">Edit Asset</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase mt-0.5">Update specifications</p>
                        </div>
                    </div>
                    <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 hover:bg-slate-200/50 p-2.5 rounded-full transition-colors active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                {{-- Modal Body --}}
                {{-- Modal Body --}}
                <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto custom-scroll bg-slate-50/50">
                    
                    {{-- Always Editable --}}
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                        <h4 class="text-[10px] font-black text-slate-800 uppercase tracking-widest flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Always Editable</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Article / Item <span class="text-deped">*</span></label>
                                <input type="text" name="item_name" value="{{ $asset->item_name }}" placeholder="Item Name" required class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-300">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Description</label>
                                <input type="text" name="description" value="{{ $asset->description }}" placeholder="Description" class="w-full bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-300">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Condition <span class="text-deped">*</span></label>
                                <div class="relative group">
                                    <select name="condition" required class="w-full appearance-none bg-white border-2 border-slate-200 rounded-xl px-4 py-3 text-xs font-black text-slate-700 uppercase focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-300 cursor-pointer">
                                        <option value="Good Condition" {{ $asset->condition === 'Good Condition' ? 'selected' : '' }}>Good Condition</option>
                                        <option value="Needs Repair" {{ $asset->condition === 'Needs Repair' ? 'selected' : '' }}>Needs Repair</option>
                                        <option value="Unserviceable" {{ $asset->condition === 'Unserviceable' ? 'selected' : '' }}>Unserviceable</option>
                                    </select>
                                    <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-hover:text-deped transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Conditionally Editable --}}
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                        <h4 class="text-[10px] font-black text-slate-800 uppercase tracking-widest flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Editable if Empty</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Property Number</label>
                                <input type="text" name="property_number" value="{{ $asset->property_number }}" placeholder="N/A" {{ $asset->property_number ? "readonly class='w-full bg-slate-50 border-2 border-slate-100 text-slate-400 rounded-xl px-4 py-3 text-xs font-black uppercase cursor-not-allowed outline-none shadow-inner'" : "class='w-full bg-white border-2 border-slate-200 text-slate-700 rounded-xl px-4 py-3 text-xs font-black uppercase focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-300'" }}>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Acquisition Date</label>
                                <input type="date" name="acquisition_date" value="{{ $asset->acquisition_date }}" {{ $asset->acquisition_date ? "readonly class='w-full bg-slate-50 border-2 border-slate-100 text-slate-400 rounded-xl px-4 py-3 text-xs font-black uppercase cursor-not-allowed outline-none shadow-inner'" : "class='w-full bg-white border-2 border-slate-200 text-slate-700 rounded-xl px-4 py-3 text-xs font-black uppercase focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-300 cursor-pointer'" }}>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Unit Cost</label>
                                <input type="number" step="0.01" name="asset_cost" value="{{ $asset->asset_cost }}" placeholder="0.00" {{ $asset->asset_cost ? "readonly class='w-full bg-slate-50 border-2 border-slate-100 text-slate-400 rounded-xl px-4 py-3 text-xs font-black uppercase cursor-not-allowed outline-none shadow-inner'" : "class='w-full bg-white border-2 border-slate-200 text-slate-700 rounded-xl px-4 py-3 text-xs font-black uppercase focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-300'" }}>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2 ml-1">Quantity</label>
                                <input type="number" name="quantity" value="{{ $asset->quantity }}" placeholder="0" {{ $asset->quantity ? "readonly class='w-full bg-slate-50 border-2 border-slate-100 text-slate-400 rounded-xl px-4 py-3 text-xs font-black uppercase cursor-not-allowed outline-none shadow-inner'" : "class='w-full bg-white border-2 border-slate-200 text-slate-700 rounded-xl px-4 py-3 text-xs font-black uppercase focus:border-deped focus:ring-4 focus:ring-deped/10 outline-none transition-all shadow-sm hover:border-slate-300'" }}>
                            </div>
                        </div>
                    </div>

                    {{-- System Managed (Read-Only) --}}
                    <div class="bg-slate-100 p-5 rounded-2xl border border-slate-200 shadow-inner space-y-4">
                        <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> System Managed (Untypable)</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Classification</label>
                                <input type="text" value="{{ $asset->classification_name ?? 'N/A' }}" readonly class="w-full bg-slate-50 border-2 border-slate-200/50 text-slate-400 rounded-xl px-4 py-3 text-xs font-black uppercase cursor-not-allowed outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Category</label>
                                <input type="text" value="{{ $asset->category_name ?? 'N/A' }}" readonly class="w-full bg-slate-50 border-2 border-slate-200/50 text-slate-400 rounded-xl px-4 py-3 text-xs font-black uppercase cursor-not-allowed outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Funding / Source</label>
                                <input type="text" value="{{ $asset->source_name ?? 'N/A' }}" readonly class="w-full bg-slate-50 border-2 border-slate-200/50 text-slate-400 rounded-xl px-4 py-3 text-xs font-black uppercase cursor-not-allowed outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Mode of Acquisition</label>
                                <input type="text" value="{{ $asset->mode_of_acquisition ?? 'N/A' }}" readonly class="w-full bg-slate-50 border-2 border-slate-200/50 text-slate-400 rounded-xl px-4 py-3 text-xs font-black uppercase cursor-not-allowed outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Supplier</label>
                                <input type="text" value="{{ $asset->supplier_name ?? 'N/A' }}" readonly class="w-full bg-slate-50 border-2 border-slate-200/50 text-slate-400 rounded-xl px-4 py-3 text-xs font-black uppercase cursor-not-allowed outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Custodian</label>
                                <input type="text" value="{{ trim(($asset->custodian_first ?? '') . ' ' . ($asset->custodian_middle ? $asset->custodian_middle . ' ' : '') . ($asset->custodian_last ?? '')) ?: 'N/A' }}" readonly class="w-full bg-slate-50 border-2 border-slate-200/50 text-slate-400 rounded-xl px-4 py-3 text-xs font-black uppercase cursor-not-allowed outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Position</label>
                                <input type="text" value="{{ $asset->custodian_position ?? 'N/A' }}" readonly class="w-full bg-slate-50 border-2 border-slate-200/50 text-slate-400 rounded-xl px-4 py-3 text-xs font-black uppercase cursor-not-allowed outline-none">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Office / School Name</label>
                                <input type="text" value="{{ $asset->school_name ?? $asset->office_name ?? $asset->office_school_name ?? 'N/A' }}" readonly class="w-full bg-slate-50 border-2 border-slate-200/50 text-slate-400 rounded-xl px-4 py-3 text-xs font-black uppercase cursor-not-allowed outline-none">
                            </div>
                        </div>
                    </div>

                </div>
                
                {{-- Modal Footer --}}
                <div class="bg-slate-50 border-t border-slate-100 p-6 flex items-center justify-end gap-3">
                    <button type="button" @click="showEditModal = false" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl text-xs font-black uppercase tracking-widest transition-colors shadow-sm active:scale-95">Cancel</button>
                    <button type="submit" @click="isSaving = true" :disabled="isSaving" class="px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-emerald-600/30 transition-all active:scale-95 flex items-center justify-center gap-2">
                        <template x-if="!isSaving">
                            <span>Save Changes</span>
                        </template>
                        <template x-if="isSaving">
                            <div class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>Saving...</span>
                            </div>
                        </template>
                    </button>
                </div>
            </form>
        </div>

        {{-- Fullscreen Image Modal --}}
        <div x-show="showImageFullscreen" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/95 backdrop-blur-md">
            <button @click="showImageFullscreen = false" class="absolute top-6 right-6 text-white/50 hover:text-white transition-colors p-2 rounded-full hover:bg-white/10 active:scale-95">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <img src="{{ $asset->photo_path ? asset('storage/' . $asset->photo_path) : '' }}" class="max-w-[90vw] max-h-[90vh] object-contain rounded-xl shadow-2xl" @click.away="showImageFullscreen = false" x-show="showImageFullscreen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        </div>

        @include('partials.documentation-modal')

    </div>

</body>
</html>
