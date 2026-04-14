<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Assets | DepEd Zamboanga City</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        
        .upload-area:hover .upload-icon { transform: scale(1.1) rotate(-5deg); }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up { animation: fadeInUp 0.5s ease-out forwards; }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            50% { box-shadow: 0 0 0 12px rgba(16, 185, 129, 0); }
        }
        .pulse-glow { animation: pulse-glow 2s infinite; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-800 overflow-x-hidden relative">
    
    {{-- This uses your actual sidebar partial --}}
    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll">
        {{-- Mobile Header --}}
        <header class="lg:hidden bg-white border-b border-slate-200 p-4 sticky top-0 z-30 flex items-center gap-4">
            <button onclick="toggleSidebar()" class="p-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-100 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/deped_logo.png') }}" class="h-6 w-auto">
                <span class="font-extrabold italic text-sm">DepEd ZC</span>
            </div>
        </header>

        <main class="p-6 lg:p-10 max-w-6xl mx-auto w-full">
            {{-- Page Header --}}
            <header class="flex justify-between items-center mb-12">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight italic">Bulk Asset Import</h2>
                    <p class="text-slate-500 text-sm font-medium italic">Zamboanga City Division Asset Management</p>
                </div>
                <button onclick="window.location.href='/dashboard'" class="px-6 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-600 flex items-center gap-2 shadow-sm hover:border-[#c00000] hover:text-[#c00000] transition-all active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Back to Home
                </button>
            </header>

            {{-- Success / Error Messages --}}
            @if(session('success'))
            <div class="mb-8 bg-emerald-50 border border-emerald-200 p-5 rounded-2xl flex items-center gap-4 animate-fade-in-up">
                <div class="h-10 w-10 bg-emerald-100 rounded-xl flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-emerald-600">
                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-emerald-800">{{ session('success') }}</p>
                </div>
            </div>
            @endif

            @if($errors->any())
            <div class="mb-8 bg-red-50 border border-red-200 p-5 rounded-2xl flex items-center gap-4 animate-fade-in-up">
                <div class="h-10 w-10 bg-red-100 rounded-xl flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-red-600">
                        <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    @foreach($errors->all() as $error)
                    <p class="text-sm font-bold text-red-700">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Upload + Guidelines Grid (shown when no preview data) --}}
            @if(!isset($csvRows))
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                {{-- Left: Upload Form --}}
                <div class="lg:col-span-7 space-y-6">
                    <div class="bg-white p-10 rounded-[3rem] shadow-2xl border border-slate-50 overflow-hidden relative">
                        <div class="absolute top-0 right-0 p-8 opacity-10">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-24 h-24 text-[#c00000]">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                        </div>

                        <h4 class="text-2xl font-black text-slate-800 mb-2 uppercase tracking-tight italic">Upload File</h4>
                        <p class="text-slate-400 text-xs font-bold mb-8 uppercase tracking-widest">Select your CSV inventory list</p>

                        <form action="{{ route('assets.import.process') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            <div class="upload-area group relative border-2 border-dashed border-slate-200 rounded-[2rem] p-12 text-center hover:border-[#c00000] hover:bg-red-50/30 transition-all cursor-pointer" id="dropZone">
                                <input type="file" name="csv_file" id="csv_input" class="hidden" accept=".csv" onchange="handleFileSelect(this)">
                                
                                <div onclick="document.getElementById('csv_input').click()">
                                    <div class="upload-icon h-16 w-16 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-[#c00000] transition-transform duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                    </div>
                                    <h5 class="text-lg font-bold text-slate-700">Click to browse</h5>
                                    <p class="text-xs text-slate-400 font-semibold mt-1">or drag and drop your .csv file here</p>
                                </div>
                            </div>

                            <div id="file_badge" class="hidden bg-emerald-50 border border-emerald-100 p-4 rounded-2xl items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="text-xl">📄</span>
                                    <span id="file_name" class="text-sm font-bold text-emerald-700 truncate max-w-[200px]">filename.csv</span>
                                </div>
                                <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Ready to process</span>
                            </div>

                            <button type="submit" id="processBtn" class="w-full py-5 bg-slate-200 text-slate-400 rounded-3xl font-black shadow-xl transition-all cursor-not-allowed flex items-center justify-center gap-3" disabled>
                                <span>PROCESS ASSETS</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Right: Guidelines --}}
                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-slate-900 p-8 rounded-[3rem] text-white shadow-2xl relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 h-32 w-32 bg-white/5 rounded-full blur-3xl"></div>
                        
                        <h4 class="text-xl font-black italic uppercase tracking-tight mb-6">Import Guidelines</h4>
                        
                        <div class="space-y-6">
                            <div class="flex gap-4">
                                <div class="h-8 w-8 rounded-xl bg-white/10 flex items-center justify-center shrink-0 font-black italic text-[#c00000]">01</div>
                                <p class="text-sm text-slate-300 font-medium">Use the <span class="text-white font-bold underline">Standard CSV Template</span> to ensure column headers match our system.</p>
                            </div>
                            <div class="flex gap-4">
                                <div class="h-8 w-8 rounded-xl bg-white/10 flex items-center justify-center shrink-0 font-black italic text-[#c00000]">02</div>
                                <p class="text-sm text-slate-300 font-medium">The system automatically generates <span class="text-white font-bold">QR Codes</span> for each item based on the Asset ID.</p>
                            </div>
                            <div class="flex gap-4">
                                <div class="h-8 w-8 rounded-xl bg-white/10 flex items-center justify-center shrink-0 font-black italic text-[#c00000]">03</div>
                                <p class="text-sm text-slate-300 font-medium">New <span class="text-white font-bold">Categories</span> and <span class="text-white font-bold">Sources</span> in the CSV are automatically registered.</p>
                            </div>
                            <div class="flex gap-4">
                                <div class="h-8 w-8 rounded-xl bg-white/10 flex items-center justify-center shrink-0 font-black italic text-amber-400">04</div>
                                <p class="text-sm text-amber-300/80 font-bold italic">Warning: Set is_serialized to "yes" for unique assets (laptops, vehicles). Leave "no" for bulk items.</p>
                            </div>
                        </div>

                        <!-- Dynamic CSV Builder Form -->
                        <div class="mt-8 space-y-4">
                            <hr class="border-white/10">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h5 class="text-white font-bold tracking-tight">Customize CSV Template</h5>
                                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-widest">Optional: Pre-fill up to 10 asset rows</p>
                                </div>
                                <button type="button" onclick="addCustomRow()" id="addRowBtn" class="px-3 py-1.5 bg-white/10 hover:bg-white/20 text-white rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all flex items-center gap-1 border border-white/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                    Add Row
                                </button>
                            </div>

                            <form id="templateBuilderForm" action="{{ route('assets.import.template') }}" method="POST">
                                @csrf
                                <div id="customRowsContainer" class="space-y-2 max-h-[300px] overflow-y-auto custom-scroll pr-2 empty:hidden">
                                    <!-- Rows will be injected here via JS -->
                                </div>

                                <button type="submit" class="mt-4 w-full py-4 bg-slate-800 hover:bg-slate-700 border border-white/20 rounded-2xl text-xs text-white font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 shadow-lg shadow-black/20">
                                    📥 Download CSV Template
                                </button>
                            </form>
                        </div>
                        
                        <!-- Hidden Row Template for JS Cloning -->
                        <template id="rowTemplate">
                            <div class="custom-row bg-slate-900/50 p-2.5 border border-white/10 rounded-xl relative group flex flex-wrap gap-2 items-end">
                                <button type="button" onclick="this.closest('.custom-row').remove(); checkRowCount();" class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity shadow-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                                
                                <div class="w-32 flex-grow">
                                    <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Category</label>
                                    <select name="rows[__INDEX__][category]" onchange="handleCategoryChange(this)" class="row-category-select w-full bg-slate-800 text-slate-200 text-[10px] rounded-lg border border-white/10 outline-none p-1.5 focus:border-[#c00000]">
                                        <option value="">(Select Category)</option>
                                        @foreach($categories as $cat) <option value="{{ $cat }}">{{ $cat }}</option> @endforeach
                                    </select>
                                </div>
                                <div class="w-32 flex-grow">
                                    <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Item Name</label>
                                    <select name="rows[__INDEX__][item_name]" onchange="handleItemChange(this)" class="row-item-select w-full bg-slate-800 text-slate-200 text-[10px] rounded-lg border border-white/10 outline-none p-1.5 focus:border-[#c00000]" disabled>
                                        <option value="">(Select Category First)</option>
                                    </select>
                                </div>
                                <div class="w-32 flex-grow">
                                    <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Sub Item</label>
                                    <select name="rows[__INDEX__][sub_item_name]" class="row-subitem-select w-full bg-slate-800 text-slate-200 text-[10px] rounded-lg border border-white/10 outline-none p-1.5 focus:border-[#c00000]" disabled>
                                        <option value="">(Select Item First)</option>
                                    </select>
                                </div>
                                <div class="w-24">
                                    <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Source</label>
                                    <select name="rows[__INDEX__][source]" class="w-full bg-slate-800 text-slate-200 text-[10px] rounded-lg border border-white/10 outline-none p-1.5 focus:border-[#c00000]">
                                        <option value="">(Blank)</option>
                                        @foreach($sources as $src) <option value="{{ $src }}">{{ $src }}</option> @endforeach
                                    </select>
                                </div>
                                <div class="w-20">
                                    <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Condition</label>
                                    <select name="rows[__INDEX__][condition]" class="w-full bg-slate-800 text-slate-200 text-[10px] rounded-lg border border-white/10 outline-none p-1.5 focus:border-[#c00000]">
                                        <option value="Serviceable">Serviceable</option>
                                        <option value="Unserviceable">Unserviceable</option>
                                        <option value="For Repair">For Repair</option>
                                    </select>
                                </div>
                                <div class="w-16">
                                    <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Serialized?</label>
                                    <select name="rows[__INDEX__][is_serialized]" class="w-full bg-slate-800 text-slate-200 text-[10px] rounded-lg border border-white/10 outline-none p-1.5 focus:border-[#c00000]">
                                        <option value="no">no</option>
                                        <option value="yes">yes</option>
                                    </select>
                                </div>
                            </div>
                        </template>

                    {{-- Column Reference Card --}}
                    <div class="bg-white p-6 rounded-[2rem] shadow-xl border border-slate-100">
                        <h5 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">CSV Column Reference</h5>
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between py-1.5 border-b border-slate-50"><span class="font-bold text-slate-600">category</span><span class="text-slate-400">Main category name</span></div>
                            <div class="flex justify-between py-1.5 border-b border-slate-50"><span class="font-bold text-slate-600">item_name</span><span class="text-slate-400">Item/product name</span></div>
                            <div class="flex justify-between py-1.5 border-b border-slate-50"><span class="font-bold text-slate-600">sub_item_name</span><span class="text-slate-400">Variant/specification</span></div>
                            <div class="flex justify-between py-1.5 border-b border-slate-50"><span class="font-bold text-slate-600">quantity</span><span class="text-slate-400">Number of units</span></div>
                            <div class="flex justify-between py-1.5 border-b border-slate-50"><span class="font-bold text-slate-600">condition</span><span class="text-slate-400">Serviceable / For Repair</span></div>
                            <div class="flex justify-between py-1.5 border-b border-slate-50"><span class="font-bold text-slate-600">source</span><span class="text-slate-400">Distributor/provider name</span></div>
                            <div class="flex justify-between py-1.5 border-b border-slate-50"><span class="font-bold text-slate-600">unit_price</span><span class="text-slate-400">Cost per unit (₱)</span></div>
                            <div class="flex justify-between py-1.5 border-b border-slate-50"><span class="font-bold text-slate-600">date_acquired</span><span class="text-slate-400">YYYY-MM-DD format</span></div>
                            <div class="flex justify-between py-1.5 border-b border-slate-50"><span class="font-bold text-slate-600">is_serialized</span><span class="text-slate-400">yes / no</span></div>
                            <div class="flex justify-between py-1.5 border-b border-slate-50"><span class="font-bold text-slate-600">property_number</span><span class="text-slate-400">Optional tracking ID</span></div>
                            <div class="flex justify-between py-1.5"><span class="font-bold text-slate-600">serial_number</span><span class="text-slate-400">For serialized items</span></div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Preview Table Section (shown after CSV is parsed) --}}
            @if(isset($csvRows))
            <div class="animate-fade-in-up">
                <div class="bg-white rounded-[3rem] shadow-2xl border border-slate-50 overflow-hidden">
                    <div class="p-8 border-b border-slate-50 flex flex-col md:flex-row items-center justify-between bg-slate-50/50 gap-4">
                        <div>
                            <h3 class="text-xl font-black italic text-slate-800 uppercase tracking-tight">Data Preview</h3>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Verify items before database entry</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="px-5 py-2 bg-white border border-slate-200 rounded-full text-[10px] font-black text-slate-600 uppercase tracking-widest shadow-sm">
                                {{ count($csvRows) - 1 }} Total Assets Found
                            </div>
                            <a href="{{ route('assets.import') }}" class="px-5 py-2 bg-white border border-slate-200 rounded-full text-[10px] font-black text-slate-500 uppercase tracking-widest shadow-sm hover:border-red-300 hover:text-red-600 transition-all">
                                ✕ Cancel
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto max-h-[500px] custom-scroll">
                        <table class="w-full text-left border-collapse">
                            <thead class="sticky top-0 bg-white z-20">
                                <tr>
                                    <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">#</th>
                                    @foreach($csvRows[0] as $header)
                                    <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                        {{ $header }}
                                    </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach(array_slice($csvRows, 1) as $rowIdx => $row)
                                <tr class="hover:bg-red-50/30 transition-colors group">
                                    <td class="px-6 py-4 text-xs font-black text-slate-300">{{ $rowIdx + 1 }}</td>
                                    @foreach($row as $cellIdx => $cell)
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-600 group-hover:text-slate-900 transition-colors whitespace-nowrap">
                                        @if(strtolower(trim($csvRows[0][$cellIdx] ?? '')) === 'unit_price' && is_numeric($cell))
                                            ₱{{ number_format((float)$cell, 2) }}
                                        @elseif(strtolower(trim($csvRows[0][$cellIdx] ?? '')) === 'is_serialized')
                                            @if(in_array(strtolower(trim($cell)), ['yes', '1', 'true']))
                                                <span class="px-3 py-1 bg-amber-50 text-amber-700 rounded-full text-[10px] font-black uppercase">Serialized</span>
                                            @else
                                                <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-black uppercase">Bulk</span>
                                            @endif
                                        @elseif(strtolower(trim($csvRows[0][$cellIdx] ?? '')) === 'condition')
                                            <span class="px-3 py-1 {{ strtolower(trim($cell)) === 'serviceable' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }} rounded-full text-[10px] font-black uppercase">{{ $cell }}</span>
                                        @else
                                            {{ $cell }}
                                        @endif
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-8 bg-slate-50 border-t border-slate-100 flex flex-col md:flex-row gap-4 items-center">
                        <p class="text-xs font-bold text-slate-400 italic flex-grow">Is everything correct? Once finalized, this cannot be undone automatically.</p>
                        <form action="{{ route('assets.import.confirm') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full md:w-auto px-12 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-black shadow-xl shadow-emerald-100 transition-all hover:-translate-y-1 active:scale-95 flex items-center gap-2 pulse-glow">
                                <span>CONFIRM & IMPORT</span>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </main>
    </div>

    <script>
        function handleFileSelect(input) {
            const badge = document.getElementById('file_badge');
            const name = document.getElementById('file_name');
            const btn = document.getElementById('processBtn');
            if (input.files && input.files[0]) {
                name.textContent = input.files[0].name;
                badge.classList.remove('hidden');
                badge.classList.add('flex', 'animate-fade-in-up');

                // Enable the submit button
                btn.disabled = false;
                btn.classList.remove('bg-slate-200', 'text-slate-400', 'cursor-not-allowed');
                btn.classList.add('bg-[#c00000]', 'hover:bg-red-800', 'text-white', 'shadow-red-100', 'hover:-translate-y-1', 'active:scale-95');
            }
        }

        // Drag and drop support
        const dropZone = document.getElementById('dropZone');
        if (dropZone) {
            ['dragenter', 'dragover'].forEach(evt => {
                dropZone.addEventListener(evt, (e) => {
                    e.preventDefault();
                    dropZone.classList.add('border-[#c00000]', 'bg-red-50/30');
                });
            });
            ['dragleave', 'drop'].forEach(evt => {
                dropZone.addEventListener(evt, (e) => {
                    e.preventDefault();
                    dropZone.classList.remove('border-[#c00000]', 'bg-red-50/30');
                });
            });
            dropZone.addEventListener('drop', (e) => {
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    const csvInput = document.getElementById('csv_input');
                    csvInput.files = files;
                    handleFileSelect(csvInput);
                }
            });
        }

        // Dynamic Template Builder Logic
        const ITEMS_MAP = @json($itemsMap);
        const SUBITEMS_MAP = @json($subItemsMap);
        let customRowIndex = 0;

        function handleCategoryChange(select) {
            const row = select.closest('.custom-row');
            const itemSelect = row.querySelector('.row-item-select');
            const subitemSelect = row.querySelector('.row-subitem-select');
            const catName = select.value;

            // Reset SubItem
            subitemSelect.innerHTML = '<option value="">(Select Item First)</option>';
            subitemSelect.disabled = true;

            if (!catName || !ITEMS_MAP[catName] || ITEMS_MAP[catName].length === 0) {
                itemSelect.innerHTML = '<option value="">(No Items Found)</option>';
                itemSelect.disabled = true;
                return;
            }

            itemSelect.disabled = false;
            itemSelect.innerHTML = '<option value="">(Select Item)</option>';
            ITEMS_MAP[catName].forEach(item => {
                const opt = document.createElement('option');
                opt.value = item;
                opt.textContent = item;
                itemSelect.appendChild(opt);
            });
        }

        function handleItemChange(select) {
            const row = select.closest('.custom-row');
            const subitemSelect = row.querySelector('.row-subitem-select');
            const itemName = select.value;

            if (!itemName || !SUBITEMS_MAP[itemName] || SUBITEMS_MAP[itemName].length === 0) {
                subitemSelect.innerHTML = '<option value="">(No Sub-items Found)</option>';
                subitemSelect.disabled = true;
                return;
            }

            subitemSelect.disabled = false;
            subitemSelect.innerHTML = '<option value="">(Choose Sub-Item or leave blank)</option>';
            SUBITEMS_MAP[itemName].forEach(sub => {
                const opt = document.createElement('option');
                opt.value = sub;
                opt.textContent = sub;
                subitemSelect.appendChild(opt);
            });
        }

        function addCustomRow() {
            const container = document.getElementById('customRowsContainer');
            const template = document.getElementById('rowTemplate').innerHTML;
            const rowCount = container.querySelectorAll('.custom-row').length;

            if (rowCount >= 10) {
                alert("You can only pre-fill up to 10 rows at a time.");
                return;
            }

            // Replace placeholder index
            const newRowHtml = template.replace(/__INDEX__/g, customRowIndex++);
            container.insertAdjacentHTML('beforeend', newRowHtml);
            checkRowCount();
        }

        function checkRowCount() {
            const container = document.getElementById('customRowsContainer');
            const rowCount = container.querySelectorAll('.custom-row').length;
            const btn = document.getElementById('addRowBtn');
            if (rowCount >= 10) {
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                btn.disabled = true;
            } else {
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>