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
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex animate-fade-in text-slate-800 overflow-x-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto">
        
        <main class="p-6 lg:p-10">
            <header class="flex flex-col md:flex-row md:justify-between md:items-start mb-8 gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight text-red">System Logs</h2>
                    <p class="text-slate-500 text-sm mt-1 font-medium italic">Track all administrative activities and changes</p>
                </div>
                <button class="bg-slate-800 text-white px-6 py-3 rounded-2xl font-bold hover:bg-slate-900 shadow-xl shadow-slate-200 transition-all hover:-translate-y-1 flex items-center gap-3 text-sm">
                    <span>Export Logs</span>
                </button>
            </header>

            <section class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/40 border border-slate-50 overflow-hidden flex flex-col">
                
                <div class="p-8 border-b border-slate-50 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-3">
                        <span class="w-2 h-6 bg-slate-400 rounded-full"></span>
                        <h3 class="font-extrabold text-slate-800 tracking-tight text-lg">Activity History</h3>
                    </div>
                    
                    <form method="GET" action="{{ route('admin.logs') }}" class="flex gap-2" id="filterForm">
                        <select name="action" onchange="document.getElementById('filterForm').submit()" class="px-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-xs font-bold focus:outline-none focus:ring-4 focus:ring-red-50 transition-all cursor-pointer">
                            <option value="All Actions" {{ $action == 'All Actions' ? 'selected' : '' }}>All Actions</option>
                            <option value="Create" {{ $action == 'Create' ? 'selected' : '' }}>Create</option>
                            <option value="Update" {{ $action == 'Update' ? 'selected' : '' }}>Update</option>
                            <option value="Delete" {{ $action == 'Delete' ? 'selected' : '' }}>Delete</option>
                            <option value="Others" {{ $action == 'Others' ? 'selected' : '' }}>Others</option>
                        </select>
                    </form>
                </div>

                <div class="overflow-x-auto overflow-y-auto custom-scrollbar" style="max-height: 600px;">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead class="sticky top-0 z-10">
                            <tr class="bg-slate-50/95 backdrop-blur-md">
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">User</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Activity</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Module</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100 text-right">Date & Time</th>
                            </tr>
                        </thead>
                        <tbody id="logTableBody" class="divide-y divide-slate-50">
                            @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/80 transition-all cursor-default group">
                                <td class="px-8 py-3 w-1/4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500 uppercase border border-slate-200">
                                            {{ substr($log->user ?? 'S', 0, 2) }}
                                        </div>
                                        <span class="font-extrabold text-slate-700 text-xs">{{ $log->user ?? 'System' }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-3 w-1/3">
                                    <span class="text-xs font-bold text-slate-600">{{ $log->activity }}</span>
                                </td>
                                <td class="px-8 py-3 w-1/6">
                                     <span class="px-2.5 py-1 {{ $log->action_type == 'Delete' ? 'bg-red-50 text-red-500 border-red-100' : ($log->action_type == 'Create' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200') }} rounded-lg text-[9px] font-black uppercase tracking-widest border">{{ $log->module ?? $log->action_type }}</span>
                                </td>
                                <td class="px-8 py-3 text-right w-1/4">
                                    <span class="text-[10px] font-bold text-slate-400 italic">{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-8 py-10 text-center text-sm font-bold text-slate-400 italic">No system logs found for this filter.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-8 bg-slate-50/30 flex items-center justify-between border-t border-slate-50">
                    <div class="w-full">
                        {{ $logs->links() }}
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>