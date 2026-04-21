<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stakeholder Profile | DepEd Zamboanga City</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

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

        .input-group:focus-within label { color: #c00000; }
        .input-group:focus-within .icon-container { color: #c00000; background-color: #fef2f2; }
        
        .premium-input {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .premium-input:focus {
            background: white;
            border-color: #c00000;
            box-shadow: 0 0 0 4px rgba(192, 0, 0, 0.05);
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex animate-fade-in text-slate-800 overflow-x-hidden relative">

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

        <main class="p-6 lg:p-10 max-w-5xl mx-auto w-full">
            
            {{-- PAGE HEADER --}}
            <header class="flex flex-col md:flex-row md:justify-between md:items-center mb-12 gap-6">
                <div class="flex items-center gap-5">
                    <div class="w-16 h-16 bg-[#c00000] rounded-[1.5rem] flex items-center justify-center shadow-2xl shadow-red-200">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-8 h-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-3xl font-black text-slate-900 tracking-tight italic uppercase leading-none">Stakeholder Profile</h2>
                        <p class="text-slate-500 text-sm font-medium italic mt-2">Registration & Data Management System</p>
                    </div>
                </div>
                <a href="javascript:history.back()" class="px-6 py-3 back-btn-cool rounded-2xl text-sm font-bold text-slate-600 flex items-center gap-2 shadow-sm active:scale-95 self-start md:self-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Back
                </a>
            </header>{{-- Main Form Card: Clean & Integrated --}}
            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/40 border border-slate-100 overflow-hidden animate-fade-in mb-10">
                
                {{-- Simple Header --}}
                <div class="p-8 md:p-12 pb-0">
                    <h3 class="text-slate-900 text-xl font-bold italic uppercase tracking-tight">
                        Entity Master Record
                    </h3>
                    <div class="h-1 w-20 bg-[#c00000] mt-4 rounded-full"></div> {{-- Subtle Accent Line --}}
                </div>

                {{-- Form Content --}}
                <form action="#" method="POST" class="p-8 md:p-12">
                    @csrf

                    <div class="space-y-12">
                        
            {{-- Your existing SECTION 1, SECTION 2, and Buttons go here --}}
                        {{-- SECTION 1: ROLE & CLASSIFICATION --}}
                        <div class="space-y-10">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-1.5 h-6 bg-[#c00000] rounded-full"></div>
                                <h4 class="text-sm font-black text-slate-800 uppercase italic tracking-wider">01. Role & Classification</h4>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                                {{-- Role --}}
                                <div class="space-y-4 input-group">
                                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.3em] ml-2 transition-colors">Stakeholder Role *</label>
                                    <div class="relative group">
                                        <div class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none transition-colors icon-container p-2 rounded-xl">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                                        </div>
                                        <select id="stakeholderRole" name="role" onchange="updateDynamicLabels()" class="w-full bg-slate-50 border-2 border-slate-100 rounded-[1.8rem] pl-16 pr-10 py-5 text-sm font-bold text-slate-700 outline-none premium-input appearance-none cursor-pointer">
                                            <option value="" disabled selected>Select Role</option>
                                            <option value="recipient">RECIPIENT / BENEFICIARY</option>
                                            <option value="source">ASSET SOURCE / PROVIDER</option>
                                        </select>
                                        <div class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                        </div>
                                    </div>
                                </div>

                                {{-- Type --}}
                                <div class="space-y-4 input-group">
                                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.3em] ml-2 transition-colors">Entity Type *</label>
                                    <div class="relative group">
                                        <div class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none transition-colors icon-container p-2 rounded-xl">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.651V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3.004 3.004 0 01-.621 4.72M6.75 21v-3.375a.375.375 0 01.375-.375h2.25a.375.375 0 01.375.375V21" /></svg>
                                        </div>
                                        <select id="sourceType" name="source_type" onchange="updateDynamicLabels()" class="w-full bg-slate-50 border-2 border-slate-100 rounded-[1.8rem] pl-16 pr-10 py-5 text-sm font-bold text-slate-700 outline-none premium-input appearance-none cursor-pointer">
                                            <option value="" disabled selected>Select Type</option>
                                            <option value="school">SCHOOL (DEPED)</option>
                                            <option value="external">EXTERNAL ORGANIZATION</option>
                                            <option value="individual">INDIVIDUAL</option>
                                        </select>
                                        <div class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SECTION 2: IDENTIFICATION --}}
                        <div class="space-y-10">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-1.5 h-6 bg-[#c00000] rounded-full"></div>
                                <h4 class="text-sm font-black text-slate-800 uppercase italic tracking-wider">02. Entity Identification</h4>
                            </div>

                            <div class="space-y-10">
                                {{-- Name Input --}}
                                <div class="group input-group">
                                    <label id="mainNameLabel" class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.3em] mb-4 ml-2 transition-colors">
                                        Entity Name *
                                    </label>
                                    <div class="relative">
                                        <div class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none transition-colors icon-container p-2 rounded-xl">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A4.833 4.833 0 0012 9a4.833 4.833 0 00-7.5 1.332V21m15 0h-15" /></svg>
                                        </div>
                                        <input type="text" id="mainNameInput" name="entity_name" placeholder="Enter name..." class="w-full bg-slate-50 border-2 border-slate-100 rounded-[1.8rem] pl-16 pr-10 py-6 text-sm font-bold text-slate-700 outline-none premium-input">
                                    </div>
                                </div>

                                {{-- Personnel & Position --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                                    <div class="space-y-4 input-group">
                                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.3em] ml-2 transition-colors">Authorized Personnel</label>
                                        <div class="relative group">
                                            <div class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none transition-colors icon-container p-2 rounded-xl">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            </div>
                                            <input type="text" name="authorized_person" placeholder="Full Name" class="w-full bg-slate-50 border-2 border-slate-100 rounded-[1.8rem] pl-16 pr-10 py-5 text-sm font-bold text-slate-700 outline-none premium-input">
                                        </div>
                                    </div>
                                    <div class="space-y-4 input-group">
                                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.3em] ml-2 transition-colors">Job Title / Position</label>
                                        <div class="relative group">
                                            <div class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none transition-colors icon-container p-2 rounded-xl">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 .21-.13.39-.33.47a10.703 10.703 0 01-7.92 0 .47.47 0 01-.33-.47v-4.25m-6 0V11c0-1.1.9-2 2-2h10a2 2 0 012 2v3.15m-14 0h14" /></svg>
                                            </div>
                                            <input type="text" name="job_title" placeholder="e.g. Principal, Manager" class="w-full bg-slate-50 border-2 border-slate-100 rounded-[1.8rem] pl-16 pr-10 py-5 text-sm font-bold text-slate-700 outline-none premium-input">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex flex-col md:flex-row items-center justify-end gap-6 pt-10 border-t border-slate-50">
                            <button type="reset" class="w-full md:w-auto px-10 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-all italic active:scale-95">
                                Reset Form
                            </button>
                            <button type="submit" 
    class="group w-full md:w-auto bg-[#c00000] hover:bg-red-600 text-white px-14 py-6 rounded-[2rem] text-[11px] font-black uppercase tracking-[0.2em] shadow-2xl shadow-red-200/50 transition-all duration-300 hover:shadow-red-500/60 hover:-translate-y-1.5 active:scale-95 italic flex items-center justify-center gap-3 overflow-hidden relative">
    
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>

    <span class="relative transition-all duration-300 group-hover:tracking-[0.3em] group-hover:drop-shadow-[0_0_8px_rgba(255,255,255,0.8)]">
        Register Stakeholder
    </span>
    
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" 
        class="w-4 h-4 relative transition-transform duration-300 group-hover:translate-x-2 group-hover:drop-shadow-[0_0_8px_rgba(255,255,255,0.8)]">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
    </svg>
</button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function updateDynamicLabels() {
            const role = document.getElementById('stakeholderRole').value;
            const type = document.getElementById('sourceType').value;
            const label = document.getElementById('mainNameLabel');
            const input = document.getElementById('mainNameInput');

            if (type === 'school') {
                label.innerText = "Search School Name *";
                input.placeholder = "Search DepEd Registered School Name...";
            } 
            else if (type === 'individual') {
                label.innerText = "Full Name (Individual) *";
                input.placeholder = "Enter complete legal name...";
            } 
            else if (type === 'external') {
                if (role === 'source') {
                    label.innerText = "Asset Source / Provider Name *";
                    input.placeholder = "Enter organization or company name...";
                } else {
                    label.innerText = "Recipient Organization Name *";
                    input.placeholder = "Enter beneficiary organization name...";
                }
            } 
            else {
                label.innerText = "Entity Name *";
                input.placeholder = "Select type first...";
            }
        }
    </script>
</body>
</html>
