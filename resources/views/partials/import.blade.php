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
                            <div class="upload-area group relative border-2 border-dashed border-slate-200 rounded-[2rem] p-12 text-center hover:border-[#c00000] hover:bg-red-50/30 transition-all cursor-pointer">
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

                            <div id="file_badge" class="hidden bg-emerald-50 border border-emerald-100 p-4 rounded-2xl flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="text-xl">📄</span>
                                    <span id="file_name" class="text-sm font-bold text-emerald-700 truncate max-w-[200px]">filename.csv</span>
                                </div>
                                <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Ready to process</span>
                            </div>

                            <button type="submit" class="w-full py-5 bg-[#c00000] hover:bg-red-800 text-white rounded-3xl font-black shadow-xl shadow-red-100 transition-all hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-3">
                                <span>PROCESS ASSETS</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 italic">
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
                                <p class="text-sm text-slate-300 font-medium font-bold text-red-400 italic">Warning: Do not modify the 6-digit School ID format.</p>
                            </div>
                        </div>

                        <button class="mt-8 w-full py-4 bg-white/10 hover:bg-white/20 border border-white/20 rounded-2xl text-xs font-black uppercase tracking-widest transition-all">
                            📥 Download CSV Template
                        </button>
                    </div>
                </div>
            </div>

            {{-- Preview Table Section --}}
            @if(isset($csvRows))
            <div class="mt-12 animate-in fade-in slide-in-from-bottom-8 duration-700">
                <div class="bg-white rounded-[3rem] shadow-2xl border border-slate-50 overflow-hidden">
                    <div class="p-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
                        <div>
                            <h3 class="text-xl font-black italic text-slate-800 uppercase tracking-tight">Data Preview</h3>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Verify items before database entry</p>
                        </div>
                        <div class="px-5 py-2 bg-white border border-slate-200 rounded-full text-[10px] font-black text-slate-600 uppercase tracking-widest shadow-sm">
                            {{ count($csvRows) - 1 }} Total Assets Found
                        </div>
                    </div>

                    <div class="overflow-x-auto max-h-[500px] custom-scroll">
                        <table class="w-full text-left border-collapse">
                            <thead class="sticky top-0 bg-white z-20">
                                <tr>
                                    @foreach($csvRows[0] as $header)
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                        {{ $header }}
                                    </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach(array_slice($csvRows, 1) as $row)
                                <tr class="hover:bg-red-50/30 transition-colors group">
                                    @foreach($row as $cell)
                                    <td class="px-8 py-5 text-sm font-semibold text-slate-600 group-hover:text-slate-900 transition-colors whitespace-nowrap">
                                        {{ $cell }}
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-8 bg-slate-50 border-t border-slate-100 flex flex-col md:flex-row gap-4 items-center">
                        <p class="text-xs font-bold text-slate-400 italic flex-grow">Is everything correct? Once finalized, this cannot be undone automatically.</p>
                        <button class="w-full md:w-auto px-12 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-black shadow-xl shadow-emerald-100 transition-all hover:-translate-y-1 active:scale-95 flex items-center gap-2">
                            <span>CONFIRM & IMPORT</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                            </svg>
                        </button>
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
            if (input.files && input.files[0]) {
                name.textContent = input.files[0].name;
                badge.classList.remove('hidden');
                badge.classList.add('flex', 'animate-in', 'fade-in', 'zoom-in-95');
            }
        }
    </script>
</body>
</html>