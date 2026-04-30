<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Assets | DepEd Zamboanga City</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; scroll-behavior: smooth; }
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .text-deped { color: #c00000; }
        .bg-deped { background-color: #c00000; }
    </style>
</head>

<body class="bg-slate-50 min-h-screen flex text-slate-800 overflow-x-hidden">

@include('partials.sidebar')

<div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll">

    <header class="lg:hidden bg-white border-b p-4 flex items-center gap-4 sticky top-0 z-30">
        <button onclick="toggleSidebar()" class="p-2 rounded-xl border bg-slate-50">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-slate-600">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>
        <div class="flex items-center gap-2">
            <img src="{{ asset('images/deped_logo.png') }}" class="h-6">
            <span class="font-black italic text-sm tracking-tight">DepEd ZC</span>
        </div>
    </header>

    <main class="p-6 lg:p-10 max-w-6xl mx-auto w-full">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <div>
                <h1 class="text-3xl lg:text-4xl font-black text-slate-900 tracking-tight italic uppercase leading-none">Bulk Asset Import</h1>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mt-2">Asset Management Unit • Zamboanga City</p>
            </div>

            <button onclick="window.location.href='/dashboard'"
                class="group px-6 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-600 flex items-center gap-2 shadow-sm hover:border-deped hover:text-deped transition-all active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 transition-transform group-hover:-translate-x-1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back to Dashboard
            </button>
        </div>

        {{-- EMPTY STATE / PLACEHOLDER --}}
        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl p-20 text-center flex flex-col items-center justify-center animate-fade-in-up">
            <div class="w-24 h-24 bg-red-50 text-deped rounded-3xl flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight italic mb-2">New Import System Coming Soon</h2>
            <p class="text-slate-400 font-bold uppercase tracking-widest text-xs max-w-md mx-auto leading-relaxed">
                We are currently redesigning the Bulk Asset Import module to provide a more streamlined experience.
            </p>
        </div>

    </main>
</div>

</body>
</html>
