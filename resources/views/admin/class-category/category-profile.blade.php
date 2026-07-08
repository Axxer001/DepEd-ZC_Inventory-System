<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $category->name }} | Category Profile</title>
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
        html.dark .bg-emerald-50 { background-color: rgba(16,185,129,0.1) !important; color: #34d399 !important; border-color: rgba(16,185,129,0.2) !important; }
        html.dark .bg-amber-50 { background-color: rgba(245,158,11,0.1) !important; color: #fbbf24 !important; border-color: rgba(245,158,11,0.2) !important; }
        html.dark .bg-rose-50 { background-color: rgba(239,68,68,0.1) !important; color: #f87171 !important; border-color: rgba(239,68,68,0.2) !important; }

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
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-none uppercase italic">{{ $category->name }}</h1>
                    <p class="text-xs text-slate-500 font-medium mt-1">Category Profile &bull; Code: {{ $category->category_code ?? '—' }}</p>
                </div>
            </div>
            
            @if(auth()->user()->isSuperAdmin())
            <div class="flex items-center gap-3">
                <button @click="showEditModal = true" class="px-5 py-2.5 bg-deped text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-red-700 transition-all shadow-sm flex items-center gap-2 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit Category
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
            
            {{-- Sidebar Details Card --}}
            <aside class="lg:col-span-4 space-y-6 flex flex-col shrink-0">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5 transition-colors">
                    <h3 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest border-b border-slate-100 pb-3">Category Details</h3>
                    <div class="space-y-4">
                        <div>
                            <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Name</span>
                            <p class="text-sm font-bold text-slate-700 uppercase mt-0.5">{{ $category->name }}</p>
                        </div>
                        <div>
                            <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Classification</span>
                            <p class="text-sm font-bold text-slate-700 uppercase mt-0.5">
                                @if($category->classification)
                                <a href="{{ route('admin.classifications.show', $category->classification->id) }}" class="text-deped hover:underline">{{ $category->classification->name }}</a>
                                @else
                                —
                                @endif
                            </p>
                        </div>
                        <div>
                            <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Category Code</span>
                            <p class="text-xs font-mono font-bold text-slate-600 mt-0.5">{{ $category->category_code ?? '—' }}</p>
                        </div>
                        <div>
                            <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Short Code</span>
                            <p class="text-xs font-mono font-bold text-slate-600 mt-0.5">{{ $category->short_category_code ?? '—' }}</p>
                        </div>
                        <div>
                            <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Total Registered Assets</span>
                            <p class="text-sm font-bold text-slate-700 mt-0.5">{{ $assets->total() }}</p>
                        </div>
                        <div>
                            <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Total Value</span>
                            <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">₱{{ number_format($assets->sum('asset_cost'), 2) }}</p>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Main Content - Assets Table --}}
            <main class="lg:col-span-8 flex flex-col bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden h-full transition-colors animate-fade">
                <div class="flex items-center justify-between p-6 border-b border-slate-100 shrink-0">
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-deped"></span> Registered Assets under this Category
                    </h3>
                </div>

                <div class="flex-grow overflow-y-auto custom-scroll">
                    <table class="w-full text-left border-collapse" style="min-width: 800px;">
                        <thead>
                            <tr>
                                <th class="xls-th pl-6">Item Name</th>
                                <th class="xls-th">Property Number</th>
                                <th class="xls-th">Serial Number</th>
                                <th class="xls-th">Custodian</th>
                                <th class="xls-th">Location</th>
                                <th class="xls-th">Value</th>
                                <th class="xls-th">Condition</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assets as $asset)
                            <tr onclick="window.location='{{ route('assets.profile', $asset->id) }}'" class="xls-row group">
                                <td class="xls-td pl-6 font-bold text-slate-800 group-hover:text-deped dark:group-hover:text-red-400 transition-colors">{{ $asset->item_name }}</td>
                                <td class="xls-td font-mono font-bold text-slate-600">{{ $asset->property_number ?? '—' }}</td>
                                <td class="xls-td font-mono font-bold text-slate-600">{{ $asset->serial_number ?? '—' }}</td>
                                <td class="xls-td font-semibold text-slate-700">{{ $asset->custodian_name ?: '—' }}</td>
                                <td class="xls-td font-semibold text-slate-500">{{ $asset->location_name ?: '—' }}</td>
                                <td class="xls-td font-bold text-slate-700">₱{{ number_format($asset->asset_cost, 2) }}</td>
                                <td class="xls-td">
                                    @php
                                        $cond = strtolower($asset->condition ?? 'good');
                                        $condColor = $cond === 'good' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($cond === 'fair' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-rose-50 text-rose-700 border-rose-200');
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full border text-[9px] font-black uppercase tracking-wider {{ $condColor }}">{{ $asset->condition ?? 'Good' }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="xls-td text-center text-xs font-black text-slate-400 uppercase tracking-widest italic bg-slate-50/20">No assets registered under this category yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($assets->hasPages())
                <div class="p-4 border-t border-slate-100 shrink-0">
                    {{ $assets->links() }}
                </div>
                @endif
            </main>

        </div>

        {{-- Edit Category Modal --}}
        @if(auth()->user()->isSuperAdmin())
        <div x-show="showEditModal" class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm z-[200] flex items-center justify-center p-4" x-cloak>
            <div @click.away="showEditModal = false" class="bg-white rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-lg w-full overflow-hidden p-6 animate-fade transition-colors">
                <div class="flex border-b border-slate-100 dark:border-slate-700 pb-3 mb-4">
                    <h3 class="text-sm font-black text-deped dark:text-red-400 uppercase tracking-widest">Edit Category</h3>
                </div>
                <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Select Classification</label>
                        <select name="classification_id" required class="w-full text-xs font-semibold px-4 py-3 border rounded-xl focus:outline-none transition-all cursor-pointer">
                            @foreach(\App\Models\Classification::orderBy('name')->get() as $c)
                            <option value="{{ $c->id }}" {{ $c->id == $category->classification_id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Category Name</label>
                        <input type="text" name="name" required value="{{ $category->name }}" placeholder="e.g. Laptop, Printer, Armchair" class="w-full text-xs font-semibold px-4 py-3 border rounded-xl focus:outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Category Code</label>
                        <input type="text" name="category_code" required value="{{ $category->category_code }}" placeholder="e.g. 5020321000" class="w-full text-xs font-semibold px-4 py-3 border rounded-xl focus:outline-none transition-all font-mono">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Short Category Code (Shortcut)</label>
                        <input type="text" name="short_category_code" required value="{{ $category->short_category_code }}" placeholder="e.g. LAPTOP, PRNTR, CHAIR" class="w-full text-xs font-semibold px-4 py-3 border rounded-xl focus:outline-none transition-all font-mono">
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
