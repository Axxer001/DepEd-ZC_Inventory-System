<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $classification->name }} | Classification Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
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
        /* === CSS Custom Properties for Light/Dark Theming === */
        :root {
            --bg-page:        #f8fafc;
            --bg-card:        #ffffff;
            --bg-secondary:   #f8fafc;
            --border-primary: #e2e8f0;
            --border-strong:  #cbd5e1;
            --text-primary:   #0f172a;
            --text-secondary: #1e293b;
            --text-muted:     #64748b;
            --text-faint:     #94a3b8;
            --scrollbar-thumb: #cbd5e1;
            --row-hover-bg:   rgba(192,0,0,0.03);
            --row-hover-border: #c00000;
            --row-active-bg:  rgba(192,0,0,0.08);
            --input-bg:       #f8fafc;
        }
        html.dark {
            --bg-page:        #0f172a;
            --bg-card:        #1e293b;
            --bg-secondary:   #0f172a;
            --border-primary: #334155;
            --border-strong:  #475569;
            --text-primary:   #f8fafc;
            --text-secondary: #e2e8f0;
            --text-muted:     #94a3b8;
            --text-faint:     #64748b;
            --scrollbar-thumb: #475569;
            --row-hover-bg:   rgba(192,0,0,0.07);
            --row-hover-border: #ef4444;
            --row-active-bg:  rgba(192,0,0,0.14);
            --input-bg:       #151f32;
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-page); color: var(--text-primary); transition: background-color 0.3s ease; }
        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: var(--scrollbar-thumb); border-radius: 10px; }
        [x-cloak] { display: none !important; }
        .animate-fade { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

        /* Adaptive Overrides */
        .bg-white  { background-color: var(--bg-card)      !important; }
        .bg-slate-50 { background-color: var(--bg-secondary) !important; }
        .border-slate-200 { border-color: var(--border-primary) !important; }
        .border-slate-100 { border-color: var(--border-primary) !important; }
        .text-slate-900 { color: var(--text-primary)   !important; }
        .text-slate-800 { color: var(--text-secondary) !important; }
        .text-slate-700 { color: var(--text-muted)     !important; }
        .text-slate-600 { color: var(--text-muted)     !important; }
        .text-slate-500, .text-slate-400 { color: var(--text-faint) !important; }

        /* Translucent adaptive badges in dark mode */
        html.dark .bg-slate-100 { background-color: rgba(255,255,255,0.06) !important; color: #94a3b8 !important; }
        html.dark .bg-blue-50 { background-color: rgba(59,130,246,0.1) !important; color: #60a5fa !important; }

        /* Table styles */
        .xls-th { 
            padding: 14px 16px; 
            font-size: 10px; 
            font-weight: 900; 
            text-transform: uppercase; 
            letter-spacing: 0.1em; 
            color: var(--text-muted); 
            white-space: nowrap; 
            border-bottom: 2px solid var(--border-strong); 
            background: var(--bg-secondary); 
            position: sticky; 
            top: 0; 
            z-index: 20; 
        }
        .xls-td { 
            height: 52px; 
            border-bottom: 1px solid var(--border-primary); 
            vertical-align: middle; 
            padding: 12px 16px; 
            background: var(--bg-card); 
            transition: all 0.3s ease; 
            color: var(--text-secondary); 
        }
        .xls-row { 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            cursor: pointer; 
            position: relative; 
        }
        .xls-row:hover { 
            transform: translateX(4px); 
            z-index: 10; 
        }
        .xls-row:hover .xls-td { 
            background-color: var(--row-hover-bg) !important; 
            border-bottom-color: var(--row-hover-border); 
        }
        .xls-row:hover .xls-td:first-child { 
            box-shadow: inset 4px 0 0 var(--row-hover-border); 
        }
        .xls-row:active { 
            transform: scale(0.995); 
            transition: all 0.1s; 
        }
        .xls-row:active .xls-td { 
            background-color: var(--row-active-bg) !important; 
        }

        /* Input / Select styling */
        input, select {
            color: var(--text-primary) !important;
            background-color: var(--input-bg) !important;
            border-color: var(--border-primary) !important;
        }
        input:focus, select:focus {
            background-color: var(--bg-card) !important;
            border-color: var(--row-hover-border) !important;
        }
    </style>
</head>
<body class="flex min-h-screen overflow-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen lg:overflow-hidden overflow-y-auto custom-scroll p-4 lg:p-8" x-data="{ showEditModal: false }">
        
        {{-- Global Header --}}
        <header class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 sticky top-0 z-50 transition-colors">
            <div class="flex items-center gap-5">
                <a href="{{ route('admin.class-category.index') }}" class="w-10 h-10 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl flex items-center justify-center border border-slate-200 transition-all shrink-0 active:scale-95">
                    <svg class="w-5 h-5 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"></path></svg>
                </a>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-none uppercase italic">{{ $classification->name }}</h1>
                    <p class="text-xs text-slate-500 font-medium mt-1">Classification Profile &bull; Grouping {{ $categories->count() }} Category(ies)</p>
                </div>
            </div>
            
            @if(auth()->user()->isSuperAdmin())
            <div class="flex items-center gap-3">
                <button @click="showEditModal = true" class="px-5 py-2.5 bg-deped text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-red-700 transition-all shadow-sm flex items-center gap-2 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit Classification
                </button>
            </div>
            @endif
        </header>

        {{-- Alert System --}}
        @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-xs font-bold mb-4 animate-fade">
            {{ session('success') }}
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 flex-grow overflow-hidden h-full pb-6">
            
            {{-- Sidebar Summary Card --}}
            <aside class="lg:col-span-4 space-y-6 flex flex-col shrink-0">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5 transition-colors">
                    <h3 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest border-b border-slate-100 pb-3">Classification Details</h3>
                    <div class="space-y-4">
                        <div>
                            <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Name</span>
                            <p class="text-sm font-bold text-slate-700 uppercase mt-0.5">{{ $classification->name }}</p>
                        </div>
                        <div>
                            <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Total Categories</span>
                            <p class="text-sm font-bold text-slate-700 uppercase mt-0.5">{{ $categories->count() }}</p>
                        </div>
                        <div>
                            <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Total Assets Registered</span>
                            <p class="text-sm font-bold text-slate-700 uppercase mt-0.5">{{ $categories->sum('assets_count') }}</p>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Main Content - Categories Table --}}
            <main class="lg:col-span-8 flex flex-col bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden h-full transition-colors animate-fade">
                <div class="flex items-center justify-between p-6 border-b border-slate-100 shrink-0">
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-deped"></span> Categories under this Classification
                    </h3>
                </div>

                <div class="flex-grow overflow-y-auto custom-scroll">
                    <table class="w-full text-left border-collapse" style="min-width: 600px;">
                        <thead>
                            <tr>
                                <th class="xls-th pl-6">Category Name</th>
                                <th class="xls-th">SEE Code</th>
                                <th class="xls-th">PPE Code</th>
                                <th class="xls-th">Asset Count</th>
                                <th class="xls-th text-right pr-6">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $cat)
                            <tr onclick="window.location='{{ route('admin.categories.show', $cat->id) }}'" class="xls-row group">
                                <td class="xls-td pl-6 font-bold text-slate-800 group-hover:text-deped dark:group-hover:text-red-400 transition-colors">{{ $cat->name }}</td>
                                <td class="xls-td font-mono font-bold text-slate-600">{{ $cat->see_category_code ?? '—' }}</td>
                                <td class="xls-td font-mono font-bold text-slate-600">{{ $cat->ppe_category_code ?? '—' }}</td>
                                <td class="xls-td font-semibold text-slate-500">
                                    <span class="bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider">{{ $cat->assets_count }} Asset(s)</span>
                                </td>
                                <td class="xls-td text-right pr-6">
                                    <span class="text-xs font-black text-slate-400 group-hover:text-deped dark:group-hover:text-red-400 group-hover:translate-x-1 transition-all inline-block">&rarr;</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="xls-td text-center text-xs font-black text-slate-400 uppercase tracking-widest italic bg-slate-50/20">No categories registered under this classification yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </main>

        </div>

        {{-- Edit Classification Modal --}}
        @if(auth()->user()->isSuperAdmin())
        <div x-show="showEditModal" class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm z-[200] flex items-center justify-center p-4" x-cloak>
            <div @click.away="showEditModal = false" class="bg-white rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-lg w-full overflow-hidden p-6 animate-fade transition-colors">
                <div class="flex border-b border-slate-100 dark:border-slate-700 pb-3 mb-4">
                    <h3 class="text-sm font-black text-deped dark:text-red-400 uppercase tracking-widest">Edit Classification</h3>
                </div>
                <form action="{{ route('admin.classifications.update', $classification->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Classification Name</label>
                        <input type="text" name="name" required value="{{ $classification->name }}" placeholder="e.g. Land, Buildings, IT Equipment" class="w-full text-xs font-semibold px-4 py-3 border rounded-xl focus:outline-none transition-all">
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="showEditModal = false" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-deped text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-red-700 transition-all shadow-sm active:scale-95">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        @endif

    </div>

</body>
</html>
