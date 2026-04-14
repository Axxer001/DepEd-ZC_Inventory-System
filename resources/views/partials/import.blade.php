<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Assets | DepEd Zamboanga City</title>

    <script src="https://cdn.tailwindcss.com"></script>
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
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-8">Select your CSV inventory list</p>

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
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h4 class="text-2xl font-black text-slate-800 italic uppercase leading-none">CSV Builder</h4>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-2">Manual Template Generator</p>
                </div>
                <button type="button" onclick="addCustomRow()"
                    class="bg-deped text-white px-6 py-3 rounded-2xl font-black uppercase text-xs shadow-lg shadow-red-100 hover:scale-105 transition-all flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Row
                </button>
            </div>

            <form action="{{ route('assets.import.template') }}" method="POST">
                @csrf
                <div id="customRowsContainer" class="grid grid-cols-1 md:grid-cols-2 gap-6 empty:hidden mb-10"></div>
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

        @endif
    </main>
</div>

{{-- JS ROW TEMPLATE --}}
<template id="rowTemplate">
    <div class="custom-row bg-slate-50 p-6 rounded-[2.5rem] border border-slate-100 animate-fade-in-up relative group">
        <button type="button" onclick="this.closest('.custom-row').remove()" class="absolute -top-2 -right-2 bg-white shadow-md text-slate-400 hover:text-red-500 w-8 h-8 rounded-full border transition-all z-10 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Item Name</label>
                <input type="text" name="rows[__INDEX__][item_name]" placeholder="Ex: Laptop" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-xs font-bold outline-none focus:border-deped transition">
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Category</label>
                <input type="text" name="rows[__INDEX__][category]" placeholder="Category" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-xs font-bold outline-none focus:border-deped">
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Qty</label>
                <input type="number" name="rows[__INDEX__][quantity]" placeholder="1" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-xs font-bold outline-none focus:border-deped">
            </div>
        </div>
    </div>
</template>

<script>
let rowIndex = 0;

function scrollToBuilder() {
    const builderSection = document.getElementById('csv-builder-section');
    builderSection.scrollIntoView({ behavior: 'smooth' });
    // Flash effect to show user where they landed
    builderSection.classList.add('ring-4', 'ring-deped/20');
    setTimeout(() => builderSection.classList.remove('ring-4', 'ring-deped/20'), 2000);
}

function handleFileSelect(input){
    const badge = document.getElementById('file_badge');
    const name = document.getElementById('file_name');
    const btn = document.getElementById('processBtn');
    if(input.files[0]){
        name.textContent = input.files[0].name;
        badge.classList.remove('hidden'); badge.classList.add('flex');
        btn.disabled = false;
        btn.classList.remove('bg-slate-200','text-slate-400','cursor-not-allowed');
        btn.classList.add('bg-deped','text-white');
    }
}

function addCustomRow() {
    const container = document.getElementById('customRowsContainer');
    const template = document.getElementById('rowTemplate').innerHTML;
    const rendered = template.replace(/__INDEX__/g, rowIndex++);
    container.insertAdjacentHTML('beforeend', rendered);
}

const dropZone = document.getElementById('dropZone');
if (dropZone) {
    ['dragenter', 'dragover'].forEach(e => dropZone.addEventListener(e, (evt) => {
        evt.preventDefault(); dropZone.classList.add('border-deped', 'bg-red-50/30');
    }));
    ['dragleave', 'drop'].forEach(e => dropZone.addEventListener(e, (evt) => {
        evt.preventDefault(); dropZone.classList.remove('border-deped', 'bg-red-50/30');
    }));
    dropZone.addEventListener('drop', (e) => {
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