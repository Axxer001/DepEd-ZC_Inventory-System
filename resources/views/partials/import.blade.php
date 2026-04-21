<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Assets | DepEd Zamboanga City</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; scroll-behavior: smooth; }

        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

        .card-premium {
            background: white;
            border-radius: 2.5rem;
            border: 1px solid #f1f5f9;
            box-shadow: 0 20px 50px rgba(0,0,0,0.04);
        }

        .upload-area:hover .upload-icon { transform: scale(1.1) rotate(-5deg); }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up { animation: fadeInUp 0.5s ease-out forwards; }
        
        .text-deped { color: #c00000; }
        .bg-deped { background-color: #c00000; }
        .border-deped { border-color: #c00000; }
    </style>
</head>

<body class="bg-slate-50 min-h-screen flex text-slate-800 overflow-x-hidden">

@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
                title: 'Import Successful!',
                text: @json(session('success')),
                icon: 'success',
                confirmButtonColor: '#c00000',
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold px-6' }
            });
        });
    </script>
@endif

@include('partials.sidebar')

<div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll">

    <header class="lg:hidden bg-white border-b p-4 flex items-center gap-4 sticky top-0 z-30">
        <button onclick="toggleSidebar()" class="p-2 rounded-xl border bg-slate-50">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-slate-600">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>
        <div class="flex items-center gap-2">
            <img src="{{ asset('images/deped_logo.png') }}" class="h-6">
            <span class="font-black italic text-sm tracking-tight">DepEd ZC</span>
        </div>
    </header>

    <main class="p-6 lg:p-10 max-w-6xl mx-auto w-full">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <div>
                <h1 class="text-3xl lg:text-4xl font-black text-slate-900 tracking-tight italic uppercase leading-none">Bulk Asset Import</h1>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mt-2">Asset Management Unit • Zamboanga City</p>
            </div>

            <button onclick="window.location.href='/dashboard'"
                class="group px-6 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-600 flex items-center gap-2 shadow-sm hover:border-deped hover:text-deped transition-all active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 transition-transform group-hover:-translate-x-1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back to Dashboard
            </button>
        </div>

        @if(!isset($csvRows))
        
        {{-- 1. TOP ROW --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
            {{-- Upload --}}
            <div class="lg:col-span-7">
                <div class="card-premium p-10 relative overflow-hidden h-full flex flex-col justify-center">
                    <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight italic mb-1">Upload File</h2>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Select your CSV inventory list</p>

                    @if($errors->any())
                        <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200">
                            @foreach($errors->all() as $error)
                                <p class="text-xs font-bold text-red-600 flex items-center gap-2">
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    {{ $error }}
                                </p>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('assets.import.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div id="dropZone" class="upload-area group relative border-2 border-dashed border-slate-200 p-12 text-center rounded-[2.5rem] hover:border-deped hover:bg-red-50/30 transition-all cursor-pointer">
                            <input type="file" name="csv_file" id="csv_input" class="hidden" accept=".csv" onchange="handleFileSelect(this)">
                            <div onclick="document.getElementById('csv_input').click()">
                                <div class="upload-icon h-20 w-20 bg-red-50 rounded-3xl flex items-center justify-center mx-auto mb-4 text-deped transition-transform duration-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-10 h-10">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                    </svg>
                                </div>
                                <p class="text-lg font-black text-slate-700">Click to browse</p>
                                <p class="text-xs font-bold text-slate-400 mt-1 uppercase tracking-wider">or drag and drop your .csv here</p>
                            </div>
                        </div>

                        <div id="file_badge" class="hidden mt-6 bg-emerald-50 border border-emerald-100 p-4 rounded-2xl flex justify-between items-center animate-fade-in-up">
                            <div class="flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-emerald-600">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                <span id="file_name" class="text-sm font-black text-emerald-800 truncate max-w-[200px]"></span>
                            </div>
                            <span class="text-[10px] font-black bg-emerald-500 text-white px-3 py-1 rounded-full uppercase tracking-widest">Ready</span>
                        </div>

                        <button id="processBtn" disabled class="mt-8 w-full py-5 bg-slate-200 text-slate-400 rounded-3xl font-black shadow-lg transition-all group-enabled:bg-deped group-enabled:text-white group-enabled:hover:shadow-red-200 active:scale-95">
                            PROCESS ASSETS
                        </button>
                    </form>
                </div>
            </div>

           {{-- RIGHT: DATA ENTRY RULES --}}
<div class="lg:col-span-5">
    <div class="bg-slate-900 p-8 rounded-[3rem] text-white shadow-2xl relative overflow-hidden h-full flex flex-col">
        <div class="absolute -right-4 -top-4 h-32 w-32 bg-white/5 rounded-full blur-3xl"></div>

        <h4 class="text-xl font-black italic uppercase tracking-tight mb-8 flex items-center gap-2 relative z-10">
            <span class="text-[#c00000]">|</span> Import Guidelines 
        </h4>
        
        <div class="space-y-7 overflow-y-auto pr-2 custom-scroll max-h-[420px] relative z-10">
            {{-- 01: Headers --}}
            <div class="flex gap-4">
                <div class="h-8 w-8 rounded-xl bg-white/10 flex items-center justify-center shrink-0 font-black text-[#c00000] border border-white/10 text-xs">01</div>
                <div>
                    <p class="text-sm font-bold text-white uppercase tracking-wide">Keep Headers Intact</p>
                    <p class="text-[11px] text-slate-400 mt-1 leading-relaxed">
                        Do not rename or move the <span class="text-white italic underline">top row</span>. The system identifies columns by these specific names.
                    </p>
                </div>
            </div>

            {{-- 02: Currency --}}
            <div class="flex gap-4">
                <div class="h-8 w-8 rounded-xl bg-white/10 flex items-center justify-center shrink-0 font-black text-[#c00000] border border-white/10 text-xs">02</div>
                <div>
                    <p class="text-sm font-bold text-white uppercase tracking-wide">Currency Format</p>
                    <p class="text-[11px] text-slate-400 mt-1 leading-relaxed">
                        Numbers only. <span class="text-red-400 font-bold uppercase">No ₱ sign and no commas.</span>
                    </p>
                    <div class="mt-2 inline-flex items-center gap-2 px-3 py-1 bg-emerald-500/10 border border-emerald-500/20 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3 text-emerald-500">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4.13-5.689z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-[10px] text-emerald-400 font-mono font-bold tracking-wider">Correct: 15000.00</span>
                    </div>
                </div>
            </div>

            {{-- 03: Quantity --}}
            <div class="flex gap-4">
                <div class="h-8 w-8 rounded-xl bg-white/10 flex items-center justify-center shrink-0 font-black text-[#c00000] border border-white/10 text-xs">03</div>
                <div>
                    <p class="text-sm font-bold text-white uppercase tracking-wide">Plain Quantity Numbers</p>
                    <p class="text-[11px] text-slate-400 mt-1 leading-relaxed">
                        Input whole numbers only. <span class="text-amber-400 font-bold uppercase">Do not add units</span> like "pcs" or "boxes" inside the column.
                    </p>
                </div>
            </div>

            {{-- 04: Serialization --}}
            <div class="flex gap-4">
                <div class="h-8 w-8 rounded-xl bg-white/10 flex items-center justify-center shrink-0 font-black text-[#c00000] border border-white/10 text-xs">04</div>
                <div>
                    <p class="text-sm font-bold text-white uppercase tracking-wide">Serialization Logic</p>
                    <p class="text-[11px] text-slate-400 mt-1 leading-relaxed">
                        Type <span class="text-blue-400 font-bold uppercase">"yes"</span> for unique assets (Laptops/Printers) or <span class="text-slate-300 font-bold uppercase">"no"</span> for bulk/consumable items.
                    </p>
                </div>
            </div>

            {{-- 05: Source Type --}}
            <div class="flex gap-4">
                <div class="h-8 w-8 rounded-xl bg-white/10 flex items-center justify-center shrink-0 font-black text-[#c00000] border border-white/10 text-xs">05</div>
                <div>
                    <p class="text-sm font-bold text-white uppercase tracking-wide">Source Type</p>
                    <p class="text-[11px] text-slate-400 mt-1 leading-relaxed">
                        The <span class="text-white font-bold">source_type</span> column must be one of exactly three values:
                        <span class="text-amber-400 font-bold">School</span>,
                        <span class="text-amber-400 font-bold">External</span>, or
                        <span class="text-amber-400 font-bold">Individual</span>.
                        Any other value will reject the entire import.
                    </p>
                </div>
            </div>
        </div>

        {{-- Scroll-to-Builder CTA --}}
        <div class="mt-auto pt-6 border-t border-white/10 relative z-10">
            <button onclick="scrollToBuilder()" class="w-full bg-[#c00000]/10 border border-[#c00000]/20 p-4 rounded-2xl flex items-center justify-center gap-3 group hover:bg-[#c00000]/20 transition-all active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-white animate-bounce">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <p class="text-[11px] text-white font-bold uppercase italic leading-tight">Download the CSV template below</p>
            </button>
        </div>
    </div>
</div>
        </div>

        {{-- 2. COLUMN REFERENCE --}}
        <div class="card-premium p-10 mb-8">
            <h5 class="text-xs font-black text-slate-400 uppercase mb-8 tracking-[0.3em] flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5-3.75h16.5m-16.5 7.5h16.5" />
                </svg>
                CSV Column Reference
            </h5>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-12 gap-y-4 text-sm">
                @php
                    $cols = [
                        'category' => 'Main inventory group', 'item_name' => 'General item title',
                        'sub_item_name' => 'Specific model/specs', 'quantity' => 'Total units (Integer)',
                        'unit_price' => 'Cost (Numbers only)', 'condition' => 'Serviceable / Repair',
                        'source' => 'Distributor / origin name', 'source_type' => 'School / External / Individual',
                        'is_serialized' => 'yes / no', 'serial_number' => 'Required if yes',
                        'date_acquired' => 'YYYY-MM-DD'
                    ];
                @endphp
                @foreach($cols as $name => $desc)
                <div class="flex justify-between items-center border-b border-slate-50 pb-2">
                    <span class="font-bold text-slate-700">{{ $name }}</span>
                    <span class="text-slate-400 text-xs italic">{{ $desc }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- 3. CSV BUILDER --}}
        <div id="csv-builder-section" class="card-premium p-10">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h4 class="text-2xl font-black text-slate-800 italic uppercase leading-none">CSV Builder</h4>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-2">Manual Template Generator &mdash; <span class="text-emerald-600">date_acquired</span> is auto-set to today on download</p>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="openBulkAddModal()"
                        class="bg-emerald-600 text-white px-6 py-3 rounded-2xl font-black uppercase text-xs shadow-lg shadow-emerald-100 hover:scale-105 transition-all flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Bulk add
                    </button>
                    <button type="button" onclick="addCsvRow()"
                        class="bg-deped text-white px-6 py-3 rounded-2xl font-black uppercase text-xs shadow-lg shadow-red-100 hover:scale-105 transition-all flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Add Row
                    </button>
                </div>
            </div>

            {{-- Column header labels --}}
            <div id="csvColLabels" class="hidden mb-1 text-[9px] font-black text-slate-400 uppercase tracking-widest pl-3" style="display:none">
                <div class="flex gap-2">
                    <div style="width:140px">Category</div>
                    <div style="width:152px">Item</div>
                    <div style="width:152px">Sub-item</div>
                    <div style="width:140px">Source</div>
                    <div style="width:130px">Source Type</div>
                    <div style="width:120px">Condition</div>
                    <div style="width:100px">Serialized</div>
                    <div style="width:72px">Qty</div>
                </div>
            </div>

            <form action="{{ route('assets.import.template') }}" method="POST">
                @csrf
                <div id="csvRowsContainer" class="space-y-2 mb-8 overflow-x-auto pb-2">
                    <div id="csvEmptyMsg" class="text-center py-10 text-slate-400 text-sm font-semibold italic">
                        No rows yet &mdash; click <strong class="text-slate-600">"+ Add Row"</strong> to start building your template.
                    </div>
                </div>
                <div class="flex justify-center border-t border-slate-50 pt-8">
                    <button class="bg-slate-800 hover:bg-slate-900 text-white px-10 py-4 rounded-2xl text-xs font-black uppercase tracking-widest transition shadow-xl flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Download Custom CSV Template
                    </button>
                </div>
            </form>
        </div>

        @else
        
        {{-- PREVIEW MODE --}}
        <form action="{{ route('assets.import.store') }}" method="POST">
            @csrf
            <div class="card-premium overflow-hidden">
                <div class="bg-slate-900 p-8 flex justify-between items-center text-white">
                    <div>
                        <h4 class="text-2xl font-black italic uppercase tracking-tight">Import Preview</h4>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Review asset data before final system entry</p>
                    </div>
                    <div class="flex gap-4">
                        <button type="button" onclick="window.location.reload()" class="px-6 py-3 bg-white/10 hover:bg-white/20 rounded-xl font-black uppercase text-[10px] tracking-widest transition-all">Cancel</button>
                        <button class="px-8 py-3 bg-deped hover:bg-red-700 rounded-xl font-black uppercase text-[10px] tracking-widest shadow-2xl shadow-red-500/20 transition-all">Confirm Import</button>
                    </div>
                </div>

                <div class="overflow-x-auto custom-scroll">
                    <table class="w-full text-left border-collapse min-w-[1200px]">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                                @foreach($headers as $h)
                                <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ str_replace('_', ' ', $h) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($csvRows as $index => $map)
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="p-6">
                                        <button type="button" onclick="dropPreviewRow(this)" class="p-2 text-slate-300 hover:text-red-500 transition-colors">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </td>
                                    @foreach($headers as $hVal)
                                    @php 
                                        $fieldCls = "w-full bg-transparent border-0 focus:ring-0 p-0 text-xs font-bold text-slate-700 placeholder:text-slate-300";
                                        $idxStr = $index."_".$hVal;
                                    @endphp
                                    <td class="p-6">
                                        @if($hVal === 'category')
                                        <input type="text" name="rows[{{ $index }}][category]" value="{{ $map['category'] ?? '' }}" class="{{ $fieldCls }}">
                                        
                                        @elseif($hVal === 'item_name')
                                        <input type="text" name="rows[{{ $index }}][item_name]" value="{{ $map['item_name'] ?? '' }}" class="{{ $fieldCls }}">
                                        
                                        @elseif($hVal === 'sub_item_name')
                                        <input type="text" name="rows[{{ $index }}][sub_item_name]" value="{{ $map['sub_item_name'] ?? '' }}" class="{{ $fieldCls }}">
                                        
                                        @elseif($hVal === 'quantity')
                                        <input type="number" name="rows[{{ $index }}][quantity]" value="{{ $map['quantity'] ?? '' }}" class="{{ $fieldCls }}">
                                        
                                        @elseif($hVal === 'source')
                                        <input type="text" name="rows[{{ $index }}][source]" value="{{ $map['source'] ?? '' }}" class="{{ $fieldCls }}">
                                        
                                        @elseif($hVal === 'condition')
                                        <select name="rows[{{ $index }}][condition]" class="{{ $fieldCls }}">
                                            <option value="Serviceable" {{ strtolower($map['condition'] ?? '') === 'serviceable' ? 'selected' : '' }}>Serviceable</option>
                                            <option value="Unserviceable" {{ strtolower($map['condition'] ?? '') === 'unserviceable' ? 'selected' : '' }}>Unserviceable</option>
                                            <option value="For Repair" {{ strtolower($map['condition'] ?? '') === 'for repair' ? 'selected' : '' }}>For Repair</option>
                                        </select>
                                        
                                        @elseif($hVal === 'source_type')
                                        <select name="rows[{{ $index }}][source_type]" id="stype_{{ $idxStr }}" class="{{ $fieldCls }}">
                                            <option value="School" {{ strtolower($map['source_type'] ?? '') === 'school' ? 'selected' : '' }}>School</option>
                                            <option value="External" {{ strtolower($map['source_type'] ?? '') === 'external' ? 'selected' : '' }}>External</option>
                                            <option value="Individual" {{ strtolower($map['source_type'] ?? '') === 'individual' ? 'selected' : '' }}>Individual</option>
                                        </select>
                                        
                                        @elseif($hVal === 'unit_price')
                                        <input type="number" step="0.01" name="rows[{{ $index }}][unit_price]" value="{{ $map['unit_price'] ?? '' }}" class="{{ $fieldCls }}">
                                        
                                        @elseif($hVal === 'date_acquired')
                                        <input type="date" name="rows[{{ $index }}][date_acquired]" value="{{ $map['date_acquired'] ?? now()->toDateString() }}" class="{{ $fieldCls }}">
                                        
                                        @elseif($hVal === 'is_serialized')
                                        <select name="rows[{{ $index }}][is_serialized]" class="{{ $fieldCls }}">
                                            <option value="no" {{ strtolower($map['is_serialized'] ?? 'no') === 'no' ? 'selected' : '' }}>No</option>
                                            <option value="yes" {{ strtolower($map['is_serialized'] ?? 'no') === 'yes' ? 'selected' : '' }}>Yes</option>
                                        </select>
                                        
                                        @elseif($hVal === 'property_number')
                                        <input type="text" name="rows[{{ $index }}][property_number]" value="{{ $map['property_number'] ?? '' }}" class="{{ $fieldCls }}">
                                        
                                        @elseif($hVal === 'serial_number')
                                        <input type="text" name="rows[{{ $index }}][serial_number]" value="{{ $map['serial_number'] ?? '' }}" class="{{ $fieldCls }}">
                                        
                                        @else
                                        {{-- Fallback for unregistered columns --}}
                                        <input type="text" name="rows[{{ $index }}][{{ $hVal }}]" value="{{ $map[$hVal] ?? '' }}" class="{{ $fieldCls }}">
                                        @endif
                                    </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </form>

        @endif
    </main>
</div>

<!-- Bulk Add Modal -->
<div id="bulkAddModal" class="fixed inset-0 z-[100] hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeBulkAddModal()"></div>
        
        <div class="relative bg-white rounded-[3rem] shadow-2xl w-full max-w-4xl overflow-hidden animate-fade-in-up">
            <div class="bg-slate-900 p-8 flex justify-between items-center text-white">
                <div>
                    <h3 class="text-2xl font-black uppercase italic tracking-tight">Bulk Add Window</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Configure global values for multiple rows</p>
                </div>
                <button onclick="closeBulkAddModal()" class="p-2 hover:bg-white/10 rounded-xl transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-10 space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Category -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Global Category</label>
                        <select id="bulk_category" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl font-bold text-sm focus:outline-none focus:border-red-400 transition-all">
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $cat)
                                <option value="{{ is_string($cat) ? $cat : $cat->name }}">{{ is_string($cat) ? $cat : $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Item -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Global Item Name</label>
                        <input type="text" id="bulk_item" placeholder="Type item name..." class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl font-bold text-sm focus:outline-none focus:border-red-400 transition-all">
                    </div>

                    <!-- Source -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Global Source</label>
                        <input type="text" id="bulk_source" placeholder="Type source name..." class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl font-bold text-sm focus:outline-none focus:border-red-400 transition-all">
                    </div>

                    <!-- Source Type -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Global Source Type</label>
                        <select id="bulk_source_type" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl font-bold text-sm focus:outline-none focus:border-red-400 transition-all">
                            <option value="">-- Select Type --</option>
                            <option value="School">School</option>
                            <option value="External">External</option>
                            <option value="Individual">Individual</option>
                        </select>
                    </div>

                    <!-- Condition -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Global Condition</label>
                        <select id="bulk_condition" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl font-bold text-sm focus:outline-none focus:border-red-400 transition-all">
                            <option value="Serviceable" selected>Serviceable</option>
                            <option value="For Repair">For Repair</option>
                            <option value="Unserviceable">Unserviceable</option>
                        </select>
                    </div>

                    <!-- Serialized -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Is Serialized?</label>
                        <select id="bulk_serialized" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl font-bold text-sm focus:outline-none focus:border-red-400 transition-all">
                            <option value="no" selected>No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>

                    <!-- Quantity per Row -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Quantity per Row</label>
                        <input type="number" id="bulk_row_qty" value="1" min="1" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl font-bold text-sm focus:outline-none focus:border-red-400 transition-all">
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-100 flex items-center justify-between gap-8">
                    <div class="flex-grow max-w-xs">
                        <label class="text-[10px] font-black text-[#c00000] uppercase tracking-widest ml-1 italic">Number of Rows to Generate</label>
                        <div class="flex items-center gap-3 mt-2">
                            <input type="number" id="bulk_qty" value="5" min="1" max="100" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl font-black text-center focus:outline-none focus:border-red-400 transition-all">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Sets</span>
                        </div>
                    </div>
                    
                    <button type="button" onclick="confirmBulkAdd()" class="bg-[#c00000] text-white px-12 py-5 rounded-[2rem] font-black uppercase tracking-[0.2em] shadow-2xl shadow-red-200 hover:-translate-y-1 transition-all active:scale-95 flex items-center justify-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Apply Bulk Add
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ── Data from PHP ─────────────────────────────────────────────────────────────
const rawCategories = @json($categories->values() ?? []);
const rawItemsMap   = @json($itemsMap ?? []);    // { catName: [itemName, …] }
const rawSubMap     = @json($subItemsMap ?? []); // { itemName: [subName, …] }
const rawSources    = @json($sources->values() ?? []);

let rowIndex = 0;

// ── Drop Preview Row ─────────────────────────────────────────────────────────
function dropPreviewRow(btn) {
    Swal.fire({
        title: 'Drop this asset?',
        text: "This asset will be excluded from the bulk import. You cannot undo this action.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Yes, drop it!',
        cancelButtonText: 'Keep asset',
        customClass: {
            popup: 'rounded-3xl shadow-2xl',
            confirmButton: 'rounded-xl tracking-widest font-bold uppercase',
            cancelButton: 'rounded-xl tracking-widest font-bold uppercase text-slate-500'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            btn.closest('tr').remove();
            let counter = document.getElementById('previewRowCount');
            if(counter) {
                let current = parseInt(counter.innerText) || 0;
                if(current > 0) counter.innerText = (current - 1);
            }
        }
    });
}

// ── Bulk Add Logic ────────────────────────────────────────────────────────────
function openBulkAddModal() {
    document.getElementById('bulkAddModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeBulkAddModal() {
    document.getElementById('bulkAddModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function confirmBulkAdd() {
    const qty = parseInt(document.getElementById('bulk_qty').value) || 0;
    if (qty <= 0) {
        Swal.fire('Error', 'Please enter a valid number of rows.', 'error');
        return;
    }

    Swal.fire({
        title: 'Confirm Bulk Add?',
        text: `You are about to generate ${qty} asset rows in the builder.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#c00000',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Yes, add them!',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'rounded-[3rem] shadow-2xl',
            confirmButton: 'rounded-xl tracking-widest font-bold uppercase py-3 px-6',
            cancelButton: 'rounded-xl tracking-widest font-bold uppercase py-3 px-6'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            executeBulkAdd();
        }
    });
}

function executeBulkAdd() {
    const qty = parseInt(document.getElementById('bulk_qty').value) || 0;
    const cat = document.getElementById('bulk_category').value;
    const item = document.getElementById('bulk_item').value;
    const src = document.getElementById('bulk_source').value;
    const srcType = document.getElementById('bulk_source_type').value;
    const cond = document.getElementById('bulk_condition').value;
    const ser = document.getElementById('bulk_serialized').value;
    const rowQty = document.getElementById('bulk_row_qty').value;

    for (let i = 0; i < qty; i++) {
        addCsvRow({
            category: cat,
            item: item,
            source: src,
            source_type: srcType,
            condition: cond,
            serialized: ser,
            quantity: rowQty
        });
    }

    closeBulkAddModal();
    scrollToBuilder();

    Swal.fire({
        title: 'Rows Generated!',
        text: `Successfully added ${qty} rows to your template builder.`,
        icon: 'success',
        timer: 2000,
        showConfirmButton: false,
        customClass: { popup: 'rounded-[3.5rem] shadow-2xl border-0' }
    });
}

// ── Add / Remove row ─────────────────────────────────────────────────────────
function addCsvRow(initialData = null) {
    const idx = rowIndex++;
    const container = document.getElementById('csvRowsContainer');
    const emptyMsg = document.getElementById('csvEmptyMsg');
    const lbl = document.getElementById('csvColLabels');
    
    if (emptyMsg) emptyMsg.style.display = 'none';
    if (lbl) lbl.style.display = 'block';

    const fieldCls = 'w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold outline-none focus:border-red-400 transition';
    const disabledCls = ' opacity-40 cursor-not-allowed';

    const row = document.createElement('div');
    row.className = 'csv-custom-row flex items-end gap-2 p-3 bg-slate-50 rounded-2xl border border-slate-100';
    row.style.width = 'max-content';
    row.innerHTML = `
        <!-- Category -->
        <div class="flex-shrink-0" style="width:140px">
            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Category</label>
            <div class="relative">
                <input type="text" id="cat_${idx}" name="rows[${idx}][category]"
                    placeholder="Browse / type new..."
                    class="${fieldCls}"
                    value="${initialData?.category || ''}"
                    oninput="csvFilterCat(${idx})" onfocus="csvFilterCat(${idx})" autocomplete="off">
                <div id="catDrop_${idx}" class="hidden absolute z-40 mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-52 overflow-y-auto custom-scroll" style="min-width:180px"></div>
            </div>
        </div>
        <!-- Item -->
        <div class="flex-shrink-0" style="width:152px">
            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Item</label>
            <div class="relative">
                <input type="text" id="item_${idx}" name="rows[${idx}][item_name]"
                    placeholder="${initialData?.item ? 'Type item name...' : 'Pick category first...'}"
                    class="${fieldCls}${(!initialData?.item && !initialData?.category) ? disabledCls : ''}" ${(!initialData?.item && !initialData?.category) ? 'disabled' : ''}
                    value="${initialData?.item || ''}"
                    oninput="csvFilterItem(${idx})" onfocus="csvFilterItem(${idx})" autocomplete="off">
                <div id="itemDrop_${idx}" class="hidden absolute z-40 mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-52 overflow-y-auto custom-scroll" style="min-width:190px"></div>
            </div>
        </div>
        <!-- Sub-item -->
        <div class="flex-shrink-0" style="width:152px">
            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Sub-item</label>
            <div class="relative">
                <input type="text" id="sub_${idx}" name="rows[${idx}][sub_item_name]"
                    placeholder="Pick item first..."
                    class="${fieldCls}${!initialData?.item ? disabledCls : ''}" ${!initialData?.item ? 'disabled' : ''}
                    oninput="csvFilterSub(${idx})" onfocus="csvFilterSub(${idx})" autocomplete="off">
                <div id="subDrop_${idx}" class="hidden absolute z-40 mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-52 overflow-y-auto custom-scroll" style="min-width:190px"></div>
            </div>
        </div>
        <!-- Source -->
        <div class="flex-shrink-0" style="width:140px">
            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Source</label>
            <div class="relative">
                <input type="text" id="src_${idx}" name="rows[${idx}][source]"
                    placeholder="Browse / type new..."
                    class="${fieldCls}"
                    value="${initialData?.source || ''}"
                    oninput="csvFilterSrc(${idx})" onfocus="csvFilterSrc(${idx})" autocomplete="off">
                <div id="srcDrop_${idx}" class="hidden absolute z-40 mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-52 overflow-y-auto custom-scroll" style="min-width:180px"></div>
            </div>
        </div>
        <!-- Source Type -->
        <div class="flex-shrink-0" style="width:130px">
            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Source Type</label>
            <select id="srcType_${idx}" name="rows[${idx}][source_type]" class="${fieldCls}">
                <option value="School" ${initialData?.source_type === 'School' ? 'selected' : ''}>School</option>
                <option value="External" ${initialData?.source_type === 'External' ? 'selected' : ''}>External</option>
                <option value="Individual" ${initialData?.source_type === 'Individual' ? 'selected' : ''}>Individual</option>
            </select>
        </div>
        <!-- Condition -->
        <div class="flex-shrink-0" style="width:120px">
            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Condition</label>
            <select name="rows[${idx}][condition]" class="${fieldCls}">
                <option value="Serviceable" ${initialData?.condition === 'Serviceable' ? 'selected' : ''}>Serviceable</option>
                <option value="For Repair" ${initialData?.condition === 'For Repair' ? 'selected' : ''}>For Repair</option>
                <option value="Unserviceable" ${initialData?.condition === 'Unserviceable' ? 'selected' : ''}>Unserviceable</option>
            </select>
        </div>
        <!-- Is Serialized -->
        <div class="flex-shrink-0" style="width:100px">
            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Serialized</label>
            <select name="rows[${idx}][is_serialized]" class="${fieldCls}">
                <option value="no" ${initialData?.serialized === 'no' ? 'selected' : ''}>No</option>
                <option value="yes" ${initialData?.serialized === 'yes' ? 'selected' : ''}>Yes</option>
            </select>
        </div>
        <!-- Qty -->
        <div class="flex-shrink-0" style="width:72px">
            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Qty</label>
            <input type="number" name="rows[${idx}][quantity]" value="${initialData?.quantity || 1}" min="1" class="${fieldCls}">
        </div>
        <!-- Delete -->
        <button type="button" onclick="removeCsvRow(this)"
            class="h-8 w-8 rounded-full bg-white border border-slate-200 text-slate-400 hover:text-red-500 hover:border-red-200 flex items-center justify-center shadow-sm transition-all shrink-0 mb-0.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;
    document.getElementById('csvRowsContainer').appendChild(row);
}

function removeCsvRow(btn) {
    btn.closest('.csv-custom-row')?.remove();
    if (!document.querySelectorAll('.csv-custom-row').length) {
        document.getElementById('csvEmptyMsg').style.display = '';
        document.getElementById('csvColLabels').style.display = 'none';
    }
}

// ── Shared helpers ────────────────────────────────────────────────────────────
function csvShow(dropId, html) {
    const el = document.getElementById(dropId);
    if (!el) return;
    el.innerHTML = html || `<div class="px-4 py-3 text-xs text-slate-400 italic">No options</div>`;
    
    // Use fixed positioning to prevent clipping by overflow-x-auto containers
    const parts = dropId.split('Drop_');
    if (parts.length === 2) {
        const input = document.getElementById(`${parts[0]}_${parts[1]}`);
        if (input) {
            const rect = input.getBoundingClientRect();
            el.style.position = 'fixed';
            el.style.zIndex = '99999';
            el.style.width = Math.max(180, input.offsetWidth) + 'px';
            el.style.left = rect.left + 'px';
            
            // Pop upwards if space is constrained
            if (rect.bottom + 220 > window.innerHeight) {
                el.style.top = 'auto';
                el.style.bottom = (window.innerHeight - rect.top) + 'px';
                el.style.marginTop = '0';
                el.style.marginBottom = '4px';
            } else {
                el.style.bottom = 'auto';
                el.style.top = rect.bottom + 'px';
                el.style.marginTop = '4px';
                el.style.marginBottom = '0';
            }
        }
    }
    
    el.classList.remove('hidden');
}
function csvHide(dropId) { document.getElementById(dropId)?.classList.add('hidden'); }

// ── Category ──────────────────────────────────────────────────────────────────
function csvFilterCat(idx) {
    const input = document.getElementById(`cat_${idx}`);
    if (!input) return;
    const val = input.value.toLowerCase().trim();
    const results = val ? rawCategories.filter(c => c.name.toLowerCase().includes(val)) : rawCategories;

    let html = results.length
        ? `<div class="px-3 py-1.5 text-[9px] font-black text-slate-400 uppercase tracking-widest sticky top-0 bg-white border-b border-slate-50">Categories</div>` +
          results.slice(0, 30).map(c =>
            `<div onmousedown="csvSelectCat(${idx},'${c.name.replace(/'/g,"\\'")}')"
                class="px-4 py-2 text-xs font-bold hover:bg-red-50 hover:text-red-600 cursor-pointer border-b border-slate-50 last:border-0">${c.name}</div>`
          ).join('')
        : '';

    if (val && !rawCategories.find(c => c.name.toLowerCase() === val))
        html += `<div onmousedown="csvSelectCat(${idx},'${input.value.replace(/'/g,"\\'")}')"
            class="px-4 py-2 text-xs font-bold text-emerald-600 hover:bg-emerald-50 cursor-pointer border-t border-slate-100 flex items-center justify-between">
            <span>+ Create New: <strong>${input.value}</strong></span>
            <span class="text-[8px] bg-emerald-100 text-emerald-600 px-1.5 py-0.5 rounded-full uppercase font-black ml-2">NEW</span>
        </div>`;

    if (!html) html = `<div class="px-4 py-3 text-xs text-slate-400 italic">No categories found</div>`;
    csvShow(`catDrop_${idx}`, html);
}

function csvSelectCat(idx, name) {
    const input   = document.getElementById(`cat_${idx}`);
    const itemInp = document.getElementById(`item_${idx}`);
    const subInp  = document.getElementById(`sub_${idx}`);
    if (input) input.value = name;
    csvHide(`catDrop_${idx}`);
    if (itemInp) {
        itemInp.disabled = false;
        itemInp.classList.remove('opacity-40','cursor-not-allowed');
        itemInp.value = '';
        itemInp.placeholder = (rawItemsMap[name]?.length ? 'Browse / type new...' : 'Type item name...');
    }
    if (subInp) {
        subInp.disabled = true;
        subInp.classList.add('opacity-40','cursor-not-allowed');
        subInp.value = '';
        subInp.placeholder = 'Pick item first...';
    }
    csvHide(`itemDrop_${idx}`); csvHide(`subDrop_${idx}`);
}

// ── Item ──────────────────────────────────────────────────────────────────────
function csvFilterItem(idx) {
    const catInput = document.getElementById(`cat_${idx}`);
    const input    = document.getElementById(`item_${idx}`);
    if (!input || input.disabled) return;
    const catName = catInput?.value || '';
    const val     = input.value.toLowerCase().trim();
    const pool    = rawItemsMap[catName] || [];
    const results = val ? pool.filter(i => i.toLowerCase().includes(val)) : pool;

    let html = results.length
        ? `<div class="px-3 py-1.5 text-[9px] font-black text-slate-400 uppercase tracking-widest sticky top-0 bg-white border-b border-slate-50">Items under ${catName || 'category'}</div>` +
          results.slice(0, 30).map(i =>
            `<div onmousedown="csvSelectItem(${idx},'${i.replace(/'/g,"\\'")}')"
                class="px-4 py-2 text-xs font-bold hover:bg-red-50 hover:text-red-600 cursor-pointer border-b border-slate-50 last:border-0">${i}</div>`
          ).join('')
        : '';

    if (val && !pool.find(i => i.toLowerCase() === val))
        html += `<div onmousedown="csvSelectItem(${idx},'${input.value.replace(/'/g,"\\'")}')"
            class="px-4 py-2 text-xs font-bold text-emerald-600 hover:bg-emerald-50 cursor-pointer border-t border-slate-100 flex items-center justify-between">
            <span>+ Create New: <strong>${input.value}</strong></span>
            <span class="text-[8px] bg-emerald-100 text-emerald-600 px-1.5 py-0.5 rounded-full uppercase font-black ml-2">NEW</span>
        </div>`;

    if (!html) html = `<div class="px-4 py-3 text-xs text-slate-400 italic">${catName ? 'No items under '+catName : 'Select a category first'}</div>`;
    csvShow(`itemDrop_${idx}`, html);
}

function csvSelectItem(idx, name) {
    const input  = document.getElementById(`item_${idx}`);
    const subInp = document.getElementById(`sub_${idx}`);
    if (input) input.value = name;
    csvHide(`itemDrop_${idx}`);
    if (subInp) {
        subInp.disabled = false;
        subInp.classList.remove('opacity-40','cursor-not-allowed');
        subInp.value = '';
        subInp.placeholder = (rawSubMap[name]?.length ? 'Browse / type new...' : 'Type sub-item name...');
    }
    csvHide(`subDrop_${idx}`);
}

// ── Sub-item ──────────────────────────────────────────────────────────────────
function csvFilterSub(idx) {
    const itemInput = document.getElementById(`item_${idx}`);
    const input     = document.getElementById(`sub_${idx}`);
    if (!input || input.disabled) return;
    const itemName = itemInput?.value || '';
    const val      = input.value.toLowerCase().trim();
    const pool     = rawSubMap[itemName] || [];
    const results  = val ? pool.filter(s => s.toLowerCase().includes(val)) : pool;

    let html = results.length
        ? `<div class="px-3 py-1.5 text-[9px] font-black text-slate-400 uppercase tracking-widest sticky top-0 bg-white border-b border-slate-50">Sub-items under ${itemName || 'item'}</div>` +
          results.slice(0, 30).map(s =>
            `<div onmousedown="csvSelectSub(${idx},'${s.replace(/'/g,"\\'")}')"
                class="px-4 py-2 text-xs font-bold hover:bg-red-50 hover:text-red-600 cursor-pointer border-b border-slate-50 last:border-0">${s}</div>`
          ).join('')
        : '';

    if (val && !pool.find(s => s.toLowerCase() === val))
        html += `<div onmousedown="csvSelectSub(${idx},'${input.value.replace(/'/g,"\\'")}')"
            class="px-4 py-2 text-xs font-bold text-emerald-600 hover:bg-emerald-50 cursor-pointer border-t border-slate-100 flex items-center justify-between">
            <span>+ Create New: <strong>${input.value}</strong></span>
            <span class="text-[8px] bg-emerald-100 text-emerald-600 px-1.5 py-0.5 rounded-full uppercase font-black ml-2">NEW</span>
        </div>`;

    if (!html) html = `<div class="px-4 py-3 text-xs text-slate-400 italic">${itemName ? 'No sub-items under '+itemName : 'Select an item first'}</div>`;
    csvShow(`subDrop_${idx}`, html);
}

function csvSelectSub(idx, name) {
    const input = document.getElementById(`sub_${idx}`);
    if (input) input.value = name;
    csvHide(`subDrop_${idx}`);
}

// ── Source ────────────────────────────────────────────────────────────────────
const srcTypeBadgeColors = {
    'School':     'bg-blue-100 text-blue-600',
    'External':   'bg-purple-100 text-purple-600',
    'Individual': 'bg-orange-100 text-orange-600',
};

function csvFilterSrc(idx) {
    const input = document.getElementById(`src_${idx}`);
    if (!input) return;

    // If user edits the field after an existing source was locked, unlock first
    csvUnlockSrcType(idx);

    const val = input.value.toLowerCase().trim();
    const results = val
        ? rawSources.filter(s => s.name.toLowerCase().includes(val))
        : rawSources;

    let html = results.length
        ? `<div class="px-3 py-1.5 text-[9px] font-black text-slate-400 uppercase tracking-widest sticky top-0 bg-white border-b border-slate-50">Existing Sources</div>` +
          results.slice(0, 30).map(s => {
              const typeLabel = s.entity_type || '';
              const badgeCls  = srcTypeBadgeColors[typeLabel] || 'bg-slate-100 text-slate-500';
              const typeBadge = typeLabel
                  ? `<span class="text-[8px] ${badgeCls} px-1.5 py-0.5 rounded-full uppercase font-black ml-1.5 shrink-0">${typeLabel}</span>`
                  : '';
              return `<div onmousedown="csvSelectSrc(${idx},'${s.name.replace(/'/g,"\\'")}','${typeLabel}')"
                  class="px-4 py-2 text-xs font-bold hover:bg-red-50 hover:text-red-600 cursor-pointer border-b border-slate-50 last:border-0 flex items-center justify-between">
                  <span class="truncate">${s.name}</span>
                  <div class="flex items-center gap-1 shrink-0 ml-2">
                      ${typeBadge}
                      <span class="text-[8px] bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded-full uppercase font-black">EXISTS</span>
                  </div>
              </div>`;
          }).join('')
        : '';

    if (val && !rawSources.find(s => s.name.toLowerCase() === val))
        html += `<div onmousedown="csvSelectSrc(${idx},'${input.value.replace(/'/g,"\\'")}')"
            class="px-4 py-2 text-xs font-bold text-emerald-600 hover:bg-emerald-50 cursor-pointer border-t border-slate-100 flex items-center justify-between">
            <span>+ Register as New: <strong>${input.value}</strong></span>
            <span class="text-[8px] bg-emerald-100 text-emerald-600 px-1.5 py-0.5 rounded-full uppercase font-black ml-2">NEW</span>
        </div>`;

    if (!html) html = `<div class="px-4 py-3 text-xs text-slate-400 italic">No existing sources — type to register new</div>`;
    csvShow(`srcDrop_${idx}`, html);
}

function csvSelectSrc(idx, name, entityType = null) {
    const input = document.getElementById(`src_${idx}`);
    if (input) input.value = name;
    csvHide(`srcDrop_${idx}`);

    if (entityType) {
        csvLockSrcType(idx, entityType);
    } else {
        csvUnlockSrcType(idx);
    }
}

function csvLockSrcType(idx, entityType) {
    const sel = document.getElementById(`srcType_${idx}`);
    if (!sel) return;
    sel.value    = entityType;
    sel.disabled = true;
    sel.classList.add('opacity-60', 'cursor-not-allowed', 'bg-slate-100');
    sel.title    = `Locked — this source is registered as "${entityType}" in the system`;
}

function csvUnlockSrcType(idx) {
    const sel = document.getElementById(`srcType_${idx}`);
    if (!sel) return;
    sel.disabled = false;
    sel.classList.remove('opacity-60', 'cursor-not-allowed', 'bg-slate-100');
    sel.title    = '';
}


// ── Close all dropdowns on outside click or scroll ────────────────────────────
document.addEventListener('click', function(e) {
    ['catDrop_','itemDrop_','subDrop_','srcDrop_'].forEach(prefix => {
        document.querySelectorAll(`[id^="${prefix}"]`).forEach(drop => {
            if (!drop.closest('.relative')?.contains(e.target)) drop.classList.add('hidden');
        });
    });
});

document.addEventListener('scroll', function(e) {
    // Ignore internal scrolling inside the dropdowns themselves
    if (e.target && e.target.id && e.target.id.includes('Drop_')) return;
    
    ['catDrop_','itemDrop_','subDrop_','srcDrop_'].forEach(prefix => {
        document.querySelectorAll(`[id^="${prefix}"]`).forEach(drop => drop.classList.add('hidden'));
    });
}, true);

// ── Scroll to builder ─────────────────────────────────────────────────────────
function scrollToBuilder() {
    const el = document.getElementById('csv-builder-section');
    el.scrollIntoView({ behavior: 'smooth' });
    el.classList.add('ring-4', 'ring-deped/20');
    setTimeout(() => el.classList.remove('ring-4', 'ring-deped/20'), 2000);
}

// ── File select handler ───────────────────────────────────────────────────────
function handleFileSelect(input) {
    const badge = document.getElementById('file_badge');
    const name  = document.getElementById('file_name');
    const btn   = document.getElementById('processBtn');
    if (input.files[0]) {
        name.textContent = input.files[0].name;
        badge.classList.remove('hidden'); badge.classList.add('flex');
        btn.disabled = false;
        btn.classList.remove('bg-slate-200','text-slate-400','cursor-not-allowed');
        btn.classList.add('bg-deped','text-white');
    }
}

// ── Drag & Drop ───────────────────────────────────────────────────────────────
const dropZone = document.getElementById('dropZone');
if (dropZone) {
    ['dragenter','dragover'].forEach(ev => dropZone.addEventListener(ev, e => {
        e.preventDefault(); dropZone.classList.add('border-deped','bg-red-50/30');
    }));
    ['dragleave','drop'].forEach(ev => dropZone.addEventListener(ev, e => {
        e.preventDefault(); dropZone.classList.remove('border-deped','bg-red-50/30');
    }));
    dropZone.addEventListener('drop', e => {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            document.getElementById('csv_input').files = files;
            handleFileSelect(document.getElementById('csv_input'));
        }
    });
}
</script>

</body>
</html>
