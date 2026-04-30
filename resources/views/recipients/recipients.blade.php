<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stakeholders Menu | DepEd ZC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        .nav-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .nav-card:hover { transform: translateY(-10px); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex animate-fade-in text-slate-800 overflow-x-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen">
        
        {{-- Mobile Header --}}
        <header class="lg:hidden bg-white border-b border-slate-200 p-4 sticky top-0 z-30 flex items-center gap-4">
            <button onclick="toggleSidebar()" class="p-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-100">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
            <span class="font-extrabold italic text-sm text-purple-600">DepEd ZC Stakeholders</span>
        </header>

        <main class="flex-grow flex flex-col items-center justify-center p-6 lg:p-10">
            
            {{-- Header Text --}}
            <div class="text-center mb-12">
                <div class="flex items-center justify-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-[0.3em] mb-3">
                    <span>End-Users</span>
                    <span class="w-1.5 h-1.5 bg-purple-600 rounded-full animate-pulse"></span>
                    <span>Registry</span>
                </div>
                <h2 class="text-4xl font-black text-slate-900 tracking-tight italic uppercase">Stakeholders</h2>
                <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] mt-2">Manage and track schools and personnel receiving assets</p>
            </div>

            {{-- Big Buttons Container --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full max-w-6xl">
                
                {{-- Button 1: View All Stakeholders (Masterlist) --}}
                <a href="{{ route('recipients.list') }}" class="nav-card group bg-white p-10 rounded-[3rem] shadow-2xl shadow-slate-200/60 border-2 border-transparent hover:border-purple-600 text-center flex flex-col items-center justify-center">
                    <div class="w-20 h-20 bg-purple-50 text-purple-600 rounded-[1.8rem] flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-10 h-10">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-2.533-4.656 9.353 9.353 0 0 0-4.213-.997 9.353 9.353 0 0 0-4.213.997 4.125 4.125 0 0 0-2.533 4.656 9.337 9.337 0 0 0 4.121-.952 9.38 9.38 0 0 0 2.625-.372ZM9 7.5a3 3 0 116 0 3 3 0 01-6 0Z" />
                        </svg>
                    </div>
                    <h4 class="text-2xl font-black text-slate-800 tracking-tighter uppercase leading-none">Stakeholders List</h4>
                    <p class="text-slate-400 text-[9px] font-black uppercase mt-4 tracking-widest leading-tight opacity-70">Complete End-User Directory</p>
                </a>

                {{-- Button 2: Explore Assets by School --}}
                <a href="{{ route('recipients.explorer') }}" class="nav-card group bg-white p-10 rounded-[3rem] shadow-2xl shadow-slate-200/60 border-2 border-transparent hover:border-blue-500 text-center flex flex-col items-center justify-center">
                    <div class="w-20 h-20 bg-blue-50 text-blue-600 rounded-[1.8rem] flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-10 h-10">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v14.25m15-14.25v14.25m-15-10.5h15m-15 3.5h15m-15 3.5h15" />
                        </svg>
                    </div>
                    <h4 class="text-2xl font-black text-slate-800 tracking-tighter uppercase leading-none">Explore Assets</h4>
                    <p class="text-slate-400 text-[9px] font-black uppercase mt-4 tracking-widest leading-tight opacity-70">Search assets by school</p>
                </a>

                {{-- Button 3: Allocation History --}}
                <a href="{{ route('recipients.history') }}" class="nav-card group bg-white p-10 rounded-[3rem] shadow-2xl shadow-slate-200/60 border-2 border-transparent hover:border-emerald-600 text-center flex flex-col items-center justify-center">
                    <div class="w-20 h-20 bg-emerald-50 text-emerald-600 rounded-[1.8rem] flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.3" stroke="currentColor" class="w-10 h-10">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h4 class="text-2xl font-black text-slate-800 tracking-tighter uppercase leading-none">Receipt History</h4>
                    <p class="text-slate-400 text-[9px] font-black uppercase mt-4 tracking-widest leading-tight opacity-70">Track received allocations</p>
                </a>

            </div>

            {{-- Footer Branding --}}
            <div class="mt-16 flex items-center gap-2 opacity-30 grayscale pointer-events-none">
                <img src="{{ asset('images/deped_logo.png') }}" class="h-8 w-auto">
                <span class="text-[10px] font-bold tracking-[0.3em] uppercase italic">Division of Zamboanga City</span>
            </div>
        </main>
    </div>

</body>
</html>