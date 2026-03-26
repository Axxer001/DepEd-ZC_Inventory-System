<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Recipient | DepEd ZC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        
        .main-card { border-radius: 3.5rem; }
        .input-style { 
            background-color: #f8fafc; 
            border: 1px solid #f1f5f9;
            border-radius: 1.25rem;
        }
        .input-style:focus {
            background-color: white;
            border-color: #c00000;
            box-shadow: 0 0 0 4px rgba(192, 0, 0, 0.05);
        }

        .back-btn-cool {
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .back-btn-cool:hover {
            border-color: #c00000;
            color: #c00000;
            box-shadow: 0 10px 15px -3px rgba(192, 0, 0, 0.1);
            transform: translateX(-4px);
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-800 overflow-x-hidden relative">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll">
        <main class="p-6 lg:p-14">
            <header class="flex justify-between items-center mb-12 max-w-3xl mx-auto w-full px-4">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight italic uppercase">Inventory Setup</h2>
                    <p class="text-slate-500 text-sm font-medium italic">Register New Recipient</p>
                </div>
                <a href="{{ url('/inventory-setup?step=2&mode=add') }}" class="px-6 py-3 back-btn-cool rounded-2xl text-sm font-bold text-slate-600 flex items-center gap-2 shadow-sm active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Back
                </a>
            </header>

            <div class="max-w-3xl mx-auto bg-white p-12 main-card shadow-[0_20px_50px_rgba(0,0,0,0.05)] border border-slate-50 relative">
                <h1 class="text-4xl font-black text-slate-900 mb-12 italic uppercase tracking-tighter">Register Recipient</h1>

                <form action="#" method="POST" class="space-y-10">
                    @csrf
                    <div class="space-y-3">
                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.15em] ml-1">Assigned School <span class="text-[#c00000]">*</span></label>
                        <div class="relative">
                            <select name="school_id" class="w-full p-5 input-style outline-none font-bold text-slate-700 transition-all cursor-pointer appearance-none" required>
                                <option value="">Select School</option>
                                <option value="1">Ayala National High School</option>
                                <option value="2">Zamboanga Central School</option>
                            </select>
                            <div class="absolute right-5 top-5 text-slate-300 pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 pt-6 border-t border-slate-100">
                        <div class="flex justify-between items-end ml-1">
                            <div>
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.15em]">Personnel / Rooms</label>
                                <p class="text-[10px] text-slate-300 font-bold mt-1 italic">Add teachers or specific room locations</p>
                            </div>
                            <button type="button" onclick="addRecipientField()" class="px-4 py-2 bg-slate-50 text-slate-500 text-[10px] font-black uppercase rounded-xl hover:bg-red-50 hover:text-[#c00000] transition-all tracking-wider">+ Add Recipient</button>
                        </div>

                        <div id="recipientContainer" class="space-y-3 max-h-[300px] overflow-y-auto pr-2 custom-scroll">
                            <div class="flex items-center gap-3 group animate-in fade-in duration-300">
                                <input type="text" name="recipients[]" placeholder="e.g. Science Lab 1" class="flex-grow p-5 input-style outline-none font-bold text-slate-700 text-sm transition-all" required>
                                <button type="button" onclick="this.parentElement.remove()" class="w-12 h-12 flex items-center justify-center text-slate-200 hover:text-red-500 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-6 bg-[#c00000] hover:bg-[#a00000] text-white rounded-[1.5rem] font-bold text-lg shadow-xl shadow-red-100 transition-all hover:scale-[1.01] active:scale-[0.98]">
                        Register Recipient
                    </button>
                </form>
            </div>
        </main>
    </div>

    <script>
        function addRecipientField() {
            const container = document.getElementById('recipientContainer');
            const div = document.createElement('div');
            div.className = "flex items-center gap-3 group animate-in fade-in slide-in-from-top-2 duration-300";
            div.innerHTML = `
                <input type="text" name="recipients[]" placeholder="e.g. Science Lab 1" class="flex-grow p-5 input-style outline-none font-bold text-slate-700 text-sm transition-all" required>
                <button type="button" onclick="this.parentElement.remove()" class="w-12 h-12 flex items-center justify-center text-slate-200 hover:text-red-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            `;
            container.appendChild(div);
            container.scrollTop = container.scrollHeight;
        }
    </script>
</body>
</html>