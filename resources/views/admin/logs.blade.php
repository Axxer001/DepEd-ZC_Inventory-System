<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs | DepEd ZC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; transition: all 0.3s; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

        /* ── System Logs dark mode overrides ── */

        /* Table container card */
        html.dark #logsSection               { background-color: #1e293b !important; border-color: #334155 !important; }

        /* Section header bar */
        html.dark #logsHeaderBar             { background-color: #1e293b !important; border-color: #334155 !important; }
        html.dark #logsHeaderBar .bg-slate-900 { background-color: #f1f5f9 !important; }

        /* Sticky thead */
        html.dark #logTableHead tr           { background-color: #0f172a !important; }
        html.dark #logTableHead th           { background-color: #0f172a !important;
                                               border-color: #334155 !important;
                                               color: #64748b !important; }

        /* Table body rows */
        html.dark #logTableBody tr           { border-color: #1e293b !important; }
        html.dark #logTableBody tr:hover     { background-color: #0f172a !important; }
        html.dark #logTableBody .divide-slate-50 { border-color: #1e293b !important; }

        /* User avatar chip */
        html.dark .log-avatar                { background-color: #0f172a !important;
                                               border-color: #334155 !important;
                                               color: #94a3b8 !important; }

        /* Activity text */
        html.dark .log-activity              { color: #94a3b8 !important; }

        /* Module badge colours — keep semantic tints but darkened */
        html.dark .badge-delete              { background-color: rgba(192,0,0,0.15) !important;
                                               color: #f87171 !important;
                                               border-color: rgba(192,0,0,0.25) !important; }
        html.dark .badge-create              { background-color: rgba(5,46,22,0.5) !important;
                                               color: #34d399 !important;
                                               border-color: #14532d !important; }
        html.dark .badge-other               { background-color: rgba(23,37,84,0.5) !important;
                                               color: #60a5fa !important;
                                               border-color: #1e3a8a !important; }

        /* Pagination footer */
        html.dark #logsPagination            { background-color: #0f172a !important;
                                               border-color: #334155 !important; }
        html.dark #logsPagination .bg-slate-100  { background-color: #1e293b !important; }
        html.dark #logsPagination .bg-white      { background-color: #1e293b !important; }
        html.dark #logsPagination .border-slate-200 { border-color: #334155 !important; }
        html.dark #logsPagination .text-slate-600   { color: #94a3b8 !important; }
        html.dark #logsPagination .text-slate-400   { color: #475569 !important; }
        html.dark #logsPagination .text-slate-300   { color: #334155 !important; }
        html.dark #logsPagination .text-slate-900   { color: #e2e8f0 !important; }
        html.dark #logsPagination a:hover    { background-color: #c00000 !important;
                                               border-color: #c00000 !important;
                                               color: #ffffff !important; }

        /* Scrollbar in dark */
        html.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }

        /* Export button */
        html.dark #exportBtn                 { background-color: #1e293b !important;
                                               border: 1px solid #334155;
                                               color: #e2e8f0 !important; }
        html.dark #exportBtn:hover           { background-color: #c00000 !important;
                                               border-color: #c00000 !important; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex animate-fade-in text-slate-800 overflow-x-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto">
        
        <main class="p-6 lg:p-10">
            <header class="flex flex-col md:flex-row md:justify-between md:items-start mb-8 gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">System Logs</h2>
                    <p class="text-slate-500 text-sm mt-1 font-medium italic">Track all administrative activities (Philippine Standard Time)</p>
                </div>
                <button id="exportBtn" class="bg-slate-800 text-white px-6 py-3 rounded-2xl font-bold hover:bg-slate-900 shadow-xl shadow-slate-200 transition-all hover:-translate-y-1 flex items-center gap-3 text-sm">
                    <span>Export Logs</span>
                </button>
            </header>

            <section id="logsSection" class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/40 border border-slate-50 overflow-hidden flex flex-col">
                
                <div id="logsHeaderBar" class="p-8 border-b border-slate-50 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-3">
                        <span class="w-2 h-6 bg-slate-900 rounded-full"></span>
                        <h3 class="font-extrabold text-slate-800 tracking-tight text-lg uppercase tracking-tighter">Activity History</h3>
                    </div>
                    
                    <form method="GET" action="{{ route('admin.logs') }}" class="flex gap-2" id="filterForm">
                        <select name="action" onchange="document.getElementById('filterForm').submit()" class="px-6 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-[10px] font-black uppercase tracking-widest focus:outline-none focus:ring-4 focus:ring-slate-100 transition-all cursor-pointer shadow-sm">
                            <option value="All Actions" {{ $action == 'All Actions' ? 'selected' : '' }}>All Actions</option>
                            <optgroup label="By Action Type">
                                <option value="Create" {{ $action == 'Create' ? 'selected' : '' }}>Create</option>
                                <option value="Update" {{ $action == 'Update' ? 'selected' : '' }}>Update</option>
                                <option value="Delete" {{ $action == 'Delete' ? 'selected' : '' }}>Delete</option>
                            </optgroup>
                            <optgroup label="By Module">
                                <option value="Authentication" {{ $action == 'Authentication' ? 'selected' : '' }}>Authentication</option>
                                <option value="Schools" {{ $action == 'Schools' ? 'selected' : '' }}>Schools</option>
                                <option value="Items" {{ $action == 'Items' ? 'selected' : '' }}>Items</option>
                                <option value="Categories" {{ $action == 'Categories' ? 'selected' : '' }}>Categories</option>
                                <option value="Distribution" {{ $action == 'Distribution' ? 'selected' : '' }}>Distribution</option>
                                <option value="Accounts" {{ $action == 'Accounts' ? 'selected' : '' }}>Accounts</option>
                            </optgroup>
                        </select>
                    </form>
                </div>

                <div class="overflow-x-auto overflow-y-auto custom-scrollbar" style="max-height: 600px;">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead id="logTableHead" class="sticky top-0 z-10">
                            <tr class="bg-slate-50/95 backdrop-blur-md">
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">User</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Activity</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Module</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100 text-right">Date & Time (PST)</th>
                            </tr>
                        </thead>
                        <tbody id="logTableBody" class="divide-y divide-slate-50">
                            @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/80 transition-all cursor-default group">
                                <td class="px-8 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="log-avatar w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-[10px] font-black text-slate-500 border border-slate-200 uppercase">
                                            {{ substr($log->user ?? 'S', 0, 2) }}
                                        </div>
                                        <span class="font-bold text-slate-800 text-xs">{{ $log->user ?? 'System' }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-4">
                                    <span class="log-activity text-xs font-semibold text-slate-600 leading-relaxed italic">"{{ $log->activity }}"</span>
                                </td>
                                <td class="px-8 py-4">
                                    @php
                                        $act = strtolower($log->activity);
                                        $badgeClass = str_contains($act, 'delete')
                                            ? 'badge-delete bg-red-50 text-red-500 border-red-100'
                                            : (str_contains($act, 'add') || str_contains($act, 'create')
                                                ? 'badge-create bg-emerald-50 text-emerald-600 border-emerald-100'
                                                : 'badge-other bg-blue-50 text-blue-600 border-blue-100');
                                    @endphp
                                     <span class="{{ $badgeClass }} px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-[0.1em] border shadow-sm">
                                        {{ $log->module ?? 'System' }}
                                     </span>
                                </td>
                                <td class="px-8 py-4 text-right">
                                    <div class="flex flex-col items-end">
                                        {{-- FORCED PHILIPPINE TIMEZONE DISPLAY --}}
                                        <span class="text-[11px] font-black text-slate-800">
                                            {{ \Carbon\Carbon::parse($log->created_at)->setTimezone('Asia/Manila')->format('M d, Y') }}
                                        </span>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase">
                                            {{ \Carbon\Carbon::parse($log->created_at)->setTimezone('Asia/Manila')->format('h:i A') }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-8 py-16 text-center text-sm font-bold text-slate-400 italic">No system logs found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div id="logsPagination" class="p-8 bg-slate-50/50 border-t border-slate-50">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            Showing <span class="text-slate-900">{{ $logs->firstItem() ?? 0 }}</span> - <span class="text-slate-900">{{ $logs->lastItem() ?? 0 }}</span> of <span class="text-slate-900">{{ $logs->total() }}</span> Entries
                        </p>

                        <nav class="flex items-center gap-2">
                            {{-- First Page --}}
                            @if ($logs->onFirstPage())
                                <span class="p-3 bg-slate-100 text-slate-300 rounded-xl cursor-not-allowed">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m18.75 4.5-7.5 7.5 7.5 7.5m-6-15L5.25 12l7.5 7.5" /></svg>
                                </span>
                            @else
                                <a href="{{ $logs->url(1) }}" class="p-3 bg-white border border-slate-200 text-slate-400 rounded-xl hover:bg-slate-900 hover:text-white transition-all shadow-sm active:scale-95">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m18.75 4.5-7.5 7.5 7.5 7.5m-6-15L5.25 12l7.5 7.5" /></svg>
                                </a>
                            @endif

                            {{-- Prev Page --}}
                            @if ($logs->onFirstPage())
                                <span class="px-6 py-3 bg-slate-100 text-slate-300 rounded-2xl text-[10px] font-black uppercase cursor-not-allowed">Prev</span>
                            @else
                                <a href="{{ $logs->previousPageUrl() }}" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 rounded-2xl text-[10px] font-black uppercase hover:bg-slate-900 hover:text-white transition-all shadow-sm active:scale-95">Prev</a>
                            @endif

                            {{-- Page Count --}}
                            <div class="px-4 text-[10px] font-black text-slate-900">
                                {{ $logs->currentPage() }} <span class="text-slate-200 mx-1">/</span> {{ $logs->lastPage() }}
                            </div>

                            {{-- Next Page --}}
                            @if ($logs->hasMorePages())
                                <a href="{{ $logs->nextPageUrl() }}" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 rounded-2xl text-[10px] font-black uppercase hover:bg-slate-900 hover:text-white transition-all shadow-sm active:scale-95">Next</a>
                            @else
                                <span class="px-6 py-3 bg-slate-100 text-slate-300 rounded-2xl text-[10px] font-black uppercase cursor-not-allowed">Next</span>
                            @endif

                            {{-- Last Page --}}
                            @if ($logs->hasMorePages())
                                <a href="{{ $logs->url($logs->lastPage()) }}" class="p-3 bg-white border border-slate-200 text-slate-400 rounded-xl hover:bg-slate-900 hover:text-white transition-all shadow-sm active:scale-95">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15l7.5 7.5-7.5 7.5" /></svg>
                                </a>
                            @else
                                <span class="p-3 bg-slate-100 text-slate-300 rounded-xl cursor-not-allowed">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15l7.5 7.5-7.5 7.5" /></svg>
                                </span>
                            @endif
                        </nav>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>