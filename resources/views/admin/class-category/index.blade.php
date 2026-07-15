<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class & Category | DepEd ZC</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
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

    <div class="flex-grow flex flex-col min-w-0 h-screen lg:overflow-hidden overflow-y-auto custom-scroll p-4 lg:p-8" x-data="{ activeView: 'classifications', showAddModal: false, addType: 'classification', showEditModal: false, editType: 'classification', editId: null, editName: '', editSeeCode: '', editPpeCode: '', editClassificationId: '' }">
        
        {{-- Global Header --}}
        <header class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 sticky top-0 z-50 transition-colors">
            <div class="flex items-center gap-5">
                <div class="w-12 h-12 bg-deped_light dark:bg-deped/10 rounded-xl flex items-center justify-center border border-deped/20 shadow-sm shrink-0">
                    <svg class="w-6 h-6 text-deped" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-none uppercase italic">Class & Category</h1>
                    <p class="text-xs text-slate-500 font-medium mt-1">Manage physical classifications and logical categories for auto-generation mapping.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="/dashboard" class="px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-600 bg-white border border-slate-200 hover:text-[#c00000] hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 transition-all duration-300 flex items-center gap-2 group italic">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 group-hover:-translate-x-1 transition-transform duration-300"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path></svg>
                    Back
                </a>
                @if(auth()->user()->isSuperAdmin() && auth()->user()->isMainSystem())
                <button @click="showAddModal = true; addType = 'classification'" class="px-5 py-2.5 bg-deped text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-red-700 transition-all shadow-sm flex items-center gap-2 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    Add Class / Category
                </button>
                @endif
            </div>
        </header>

        {{-- Toggle Views & Alert System --}}
        @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-xs font-bold mb-4 animate-fade">
            {{ session('success') }}
        </div>
        @endif

        <div class="flex items-center justify-between mb-4">
            <div class="flex bg-slate-100 dark:bg-slate-800/60 p-1 rounded-xl border border-slate-200 dark:border-slate-700 shadow-inner">
                <button @click="activeView = 'classifications'" :class="activeView === 'classifications' ? 'bg-white dark:bg-slate-700 text-deped dark:text-red-400 shadow-sm font-black' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 font-bold'" class="px-5 py-2 text-xs uppercase tracking-wider rounded-lg transition-all">
                    Classifications ({{ $classifications->count() }})
                </button>
                <button @click="activeView = 'categories'" :class="activeView === 'categories' ? 'bg-white dark:bg-slate-700 text-deped dark:text-red-400 shadow-sm font-black' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 font-bold'" class="px-5 py-2 text-xs uppercase tracking-wider rounded-lg transition-all ml-1">
                    Categories ({{ $categories->count() }})
                </button>
            </div>
        </div>

        {{-- List Area --}}
        <div class="flex-grow bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col mb-6 transition-colors">
            
            {{-- VIEW: Classifications --}}
            <div x-show="activeView === 'classifications'" class="flex-grow flex flex-col overflow-hidden animate-fade">
                <div class="overflow-x-auto w-full flex-grow custom-scroll">
                    <table class="w-full text-left border-collapse" style="min-width: 600px;">
                        <thead>
                            <tr>
                                <th class="xls-th pl-6">Classification Name</th>
                                <th class="xls-th">Categories count</th>
                                <th class="xls-th text-right pr-6">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($classifications as $class)
                            <tr onclick="window.location='{{ route('admin.classifications.show', $class->id) }}'" class="xls-row group">
                                <td class="xls-td pl-6 font-bold text-slate-800 group-hover:text-deped dark:group-hover:text-red-400 transition-colors">{{ $class->name }}</td>
                                <td class="xls-td font-semibold text-slate-500">
                                    <span class="bg-slate-100 text-slate-600 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider">{{ $class->categories_count }} Category(ies)</span>
                                </td>
                                <td class="xls-td text-right pr-6">
                                    <div class="flex items-center justify-end gap-3">
                                        @if(auth()->user()->isSuperAdmin() && auth()->user()->isMainSystem())
                                        <button @click.prevent.stop="showEditModal = true; editType = 'classification'; editId = {{ $class->id }}; editName = '{{ addslashes($class->name) }}'" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg text-slate-400 hover:text-deped dark:hover:text-red-400 transition-all active:scale-95" title="Edit Classification">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        @endif
                                        <span class="text-xs font-black text-slate-400 group-hover:text-deped dark:group-hover:text-red-400 group-hover:translate-x-1 transition-all inline-block">&rarr;</span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="xls-td text-center text-xs font-black text-slate-400 uppercase tracking-widest italic bg-slate-50/20">No classifications registered yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- VIEW: Categories --}}
            <div x-show="activeView === 'categories'" class="flex-grow flex flex-col overflow-hidden animate-fade" x-cloak>
                <div class="overflow-x-auto w-full flex-grow custom-scroll">
                    <table class="w-full text-left border-collapse" style="min-width: 800px;">
                        <thead>
                            <tr>
                                <th class="xls-th pl-6">Category Name</th>
                                <th class="xls-th">Classification</th>
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
                                <td class="xls-td font-semibold text-slate-500">{{ $cat->classification->name ?? '—' }}</td>
                                <td class="xls-td font-mono font-bold text-slate-600">{{ $cat->see_category_code ?? '—' }}</td>
                                <td class="xls-td font-mono font-bold text-slate-600">{{ $cat->ppe_category_code ?? '—' }}</td>
                                <td class="xls-td font-semibold text-slate-500">
                                    <span class="bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider">{{ $cat->assets_count }} Asset(s)</span>
                                </td>
                                <td class="xls-td text-right pr-6">
                                    <div class="flex items-center justify-end gap-3">
                                        @if(auth()->user()->isSuperAdmin() && auth()->user()->isMainSystem())
                                        <button @click.prevent.stop="showEditModal = true; editType = 'category'; editId = {{ $cat->id }}; editName = '{{ addslashes($cat->name) }}'; editSeeCode = '{{ addslashes($cat->see_category_code) }}'; editPpeCode = '{{ addslashes($cat->ppe_category_code) }}'; editClassificationId = '{{ $cat->classification_id }}'" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg text-slate-400 hover:text-deped dark:hover:text-red-400 transition-all active:scale-95" title="Edit Category">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        @endif
                                        <span class="text-xs font-black text-slate-400 group-hover:text-deped dark:group-hover:text-red-400 group-hover:translate-x-1 transition-all inline-block">&rarr;</span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="xls-td text-center text-xs font-black text-slate-400 uppercase tracking-widest italic bg-slate-50/20">No categories registered yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- Super Admin Modal --}}
        @if(auth()->user()->isSuperAdmin())
        {{-- Add Modal --}}
        <div x-show="showAddModal" class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm z-[200] flex items-center justify-center p-4" x-cloak>
            <div @click.away="showAddModal = false" class="bg-white rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-lg w-full overflow-hidden p-6 animate-fade transition-colors">
                
                {{-- Tabs in modal --}}
                <div class="flex border-b border-slate-100 dark:border-slate-700 pb-3 mb-4">
                    <button @click="addType = 'classification'" :class="addType === 'classification' ? 'text-deped dark:text-red-400 font-black border-b-2 border-deped dark:border-red-400 pb-3 -mb-[14px]' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 font-bold'" class="text-xs uppercase tracking-widest mr-5">
                        Add Classification
                    </button>
                    <button @click="addType = 'category'" :class="addType === 'category' ? 'text-deped dark:text-red-400 font-black border-b-2 border-deped dark:border-red-400 pb-3 -mb-[14px]' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 font-bold'" class="text-xs uppercase tracking-widest">
                        Add Category
                    </button>
                </div>

                {{-- FORM: Add Classification --}}
                <form x-show="addType === 'classification'" action="{{ route('admin.classifications.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Classification Name</label>
                        <input type="text" name="name" required placeholder="e.g. Land, Buildings, IT Equipment" class="w-full text-xs font-semibold px-4 py-3 border rounded-xl focus:outline-none transition-all">
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="showAddModal = false" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-deped text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-red-700 transition-all shadow-sm active:scale-95">Save Classification</button>
                    </div>
                </form>

                {{-- FORM: Add Category --}}
                <form x-show="addType === 'category'" action="{{ route('admin.categories.store') }}" method="POST" class="space-y-4" x-cloak>
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Select Classification</label>
                        <select name="classification_id" required class="w-full text-xs font-semibold px-4 py-3 border rounded-xl focus:outline-none transition-all cursor-pointer">
                            <option value="" disabled selected>Select classification...</option>
                            @foreach($classifications as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Category Name</label>
                        <input type="text" name="name" required placeholder="e.g. Laptop, Printer, Armchair" class="w-full text-xs font-semibold px-4 py-3 border rounded-xl focus:outline-none transition-all">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">SEE Category Code</label>
                            <input type="text" name="see_category_code" required placeholder="e.g. 5020" class="w-full text-xs font-semibold px-4 py-3 border rounded-xl focus:outline-none transition-all font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">PPE Category Code</label>
                            <input type="text" name="ppe_category_code" required placeholder="e.g. 1060" class="w-full text-xs font-semibold px-4 py-3 border rounded-xl focus:outline-none transition-all font-mono">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="showAddModal = false" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-deped text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-red-700 transition-all shadow-sm active:scale-95">Save Category</button>
                    </div>
                </form>

            </div>
        </div>

        {{-- Edit Modal --}}
        <div x-show="showEditModal" class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm z-[200] flex items-center justify-center p-4" x-cloak>
            <div @click.away="showEditModal = false" class="bg-white rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-lg w-full overflow-hidden p-6 animate-fade transition-colors">
                
                {{-- Header --}}
                <div class="flex border-b border-slate-100 dark:border-slate-700 pb-3 mb-4">
                    <h3 class="text-sm font-black text-deped dark:text-red-400 uppercase tracking-widest" x-text="editType === 'classification' ? 'Edit Classification' : 'Edit Category'"></h3>
                </div>

                {{-- FORM --}}
                <form :action="'/admin/' + (editType === 'classification' ? 'classifications' : 'categories') + '/' + editId + '/update'" method="POST" class="space-y-4">
                    @csrf
                    
                    {{-- Classification Edit Field --}}
                    <div x-show="editType === 'classification'">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Classification Name</label>
                            <input type="text" name="name" x-model="editName" required placeholder="e.g. Land, Buildings, IT Equipment" class="w-full text-xs font-semibold px-4 py-3 border rounded-xl focus:outline-none transition-all">
                        </div>
                    </div>

                    {{-- Category Edit Fields --}}
                    <div x-show="editType === 'category'" class="space-y-4" x-cloak>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Select Classification</label>
                            <select name="classification_id" x-model="editClassificationId" required class="w-full text-xs font-semibold px-4 py-3 border rounded-xl focus:outline-none transition-all cursor-pointer">
                                @foreach($classifications as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Category Name</label>
                            <input type="text" name="name" x-model="editName" required placeholder="e.g. Laptop, Printer, Armchair" class="w-full text-xs font-semibold px-4 py-3 border rounded-xl focus:outline-none transition-all">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">SEE Category Code</label>
                                <input type="text" name="see_category_code" x-model="editSeeCode" required placeholder="e.g. 5020" class="w-full text-xs font-semibold px-4 py-3 border rounded-xl focus:outline-none transition-all font-mono">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">PPE Category Code</label>
                                <input type="text" name="ppe_category_code" x-model="editPpeCode" required placeholder="e.g. 1060" class="w-full text-xs font-semibold px-4 py-3 border rounded-xl focus:outline-none transition-all font-mono">
                            </div>
                        </div>
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
