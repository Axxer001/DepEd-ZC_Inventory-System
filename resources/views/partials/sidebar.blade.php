{{-- Anti-FOUC: apply dark mode class before page renders --}}
@auth
<script>
    (function(){
        if ({{ auth()->user()->dark_mode ? 'true' : 'false' }}) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>
@endauth

<div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/40 z-40 hidden backdrop-blur-sm lg:hidden transition-opacity duration-300 opacity-0"></div>


{{-- Spacer: reserves 80px in the flex layout --}}
<div class="hidden lg:block shrink-0 transition-all duration-300" style="width: 80px;"></div>

{{-- Sidebar --}}
<aside id="sidebar"
       class="fixed inset-y-0 left-0 z-50 bg-white border-r-1 border-[#c00000] flex flex-col h-screen overflow-y-auto overflow-x-hidden -translate-x-full lg:translate-x-0 transition-all duration-300 ease-in-out group/sidebar"
       style="width: 80px;"
       onmouseenter="expandSidebar()" onmouseleave="collapseSidebar()">

    {{-- Brand --}}
    <div class="p-4 text-center relative shrink-0 border-b border-slate-100">
        <button onclick="toggleSidebar()" class="lg:hidden absolute right-3 top-3 p-2 text-slate-400 hover:text-[#c00000] transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="flex items-center gap-3 justify-center">
            <img src="{{ asset('images/deped_logo.png') }}" class="h-10 w-auto shrink-0 transition-transform duration-500 group-hover:rotate-12">
            <div class="sidebar-label hidden whitespace-nowrap overflow-hidden text-left">
                <h1 class="text-sm font-black tracking-tighter italic leading-none text-slate-900 uppercase">DepEd ZC</h1>
                <p class="text-[8px] text-[#c00000] font-bold tracking-widest uppercase mt-1">Inventory Management System</p>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-grow px-2 space-y-4 mt-4">
        {{-- Dashboard --}}
        <div class="relative group/navitem">
            @if(request()->routeIs('dashboard'))
                <a href="{{ route('dashboard') }}" class="flex items-center gap-4 px-4 py-3 bg-red-50 text-[#c00000] rounded-2xl font-bold border border-red-100 transition-all" title="Dashboard">
                   <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/navitem:scale-110">
  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
</svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">Dashboard</span>
                </a>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-[#c00000] rounded-r-full"></div>
            @else
                <a href="{{ route('dashboard') }}" class="flex items-center gap-4 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-[#c00000] rounded-2xl font-bold transition-all" title="Dashboard">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/navitem:scale-110">
  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
</svg>                    <span class="sidebar-label hidden whitespace-nowrap text-sm">Dashboard</span>
                </a>
            @endif
        </div>

        {{-- Schools Registry --}}
<div class="relative group/navitem mt-2">
    @if(request()->routeIs('admin.schools'))
        <a href="{{ route('admin.schools') }}" class="flex items-center gap-4 px-4 py-3 bg-red-50 text-[#c00000] rounded-2xl font-bold border border-red-100 transition-all" title="Schools">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A4.833 4.833 0 0012 9a4.833 4.833 0 00-7.5 1.332V21m15 0h-15" />
</svg>         
  <span class="sidebar-label hidden whitespace-nowrap text-sm">Schools</span>
        </a>
        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-[#c00000] rounded-r-full"></div>
    @else
        <a href="{{ route('admin.schools') }}" class="flex items-center gap-4 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-[#c00000] rounded-2xl font-bold transition-all" title="Schools">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A4.833 4.833 0 0012 9a4.833 4.833 0 00-7.5 1.332V21m15 0h-15" />
</svg> 
            <span class="sidebar-label hidden whitespace-nowrap text-sm">Schools</span>
        </a>
    @endif
</div>


{{-- Stakeholders Registry --}}
<div class="relative group/navitem mt-2">
    <a href="{{ route('recipients.index') }}" 
       class="flex items-center gap-4 px-4 py-3 {{ request()->routeIs('recipients.*') ? 'bg-purple-50 text-purple-700 border-purple-100' : 'text-slate-500 hover:bg-slate-50 hover:text-[#c00000]' }} rounded-2xl font-bold border border-transparent transition-all" title="Stakeholders">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
        </svg>
        <span class="sidebar-label hidden whitespace-nowrap text-sm">Stakeholders</span>
    </a>
    @if(request()->routeIs('recipients.*'))
        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-[#c00000] rounded-r-full shadow-[1px_0_10px_rgba(192,0,0,0.3)]"></div>
    @endif
</div>


{{-- View Assets --}}
<div class="relative group/navitem mt-2">
    @if(request()->routeIs('assets.view'))
        <a href="{{ route('assets.view') }}" class="flex items-center gap-4 px-4 py-3 bg-red-50 text-[#c00000] rounded-2xl font-bold border border-red-100 transition-all" title="View Assets">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/navitem:scale-110">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
            </svg>
            <span class="sidebar-label hidden whitespace-nowrap text-sm">View Assets</span>
        </a>
        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#c00000] rounded-r-full shadow-[2px_0_8px_rgba(192,0,0,0.3)]"></div>
    @else
        <a href="{{ route('assets.view') }}" class="flex items-center gap-4 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-[#c00000] rounded-2xl font-bold transition-all" title="View Assets">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/navitem:scale-110 text-slate-400 group-hover/navitem:text-[#c00000]">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
            </svg>
            {{-- Tinanggal ang lg:block dito rin --}}
            <span class="sidebar-label hidden whitespace-nowrap text-sm">View Assets</span>
        </a>
    @endif
</div>


        {{-- Configuration --}}
        <div class="pt-3 border-t border-slate-100 relative group/navitem">
            <p class="px-3 mb-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest sidebar-label hidden whitespace-nowrap">Configuration</p>
            @if(request()->routeIs('inventory.setup'))
                <a href="{{ route('inventory.setup') }}" class="flex items-center gap-4 px-4 py-3 bg-red-50 text-[#c00000] rounded-xl font-bold border border-red-100 transition-all" title="Inventory Setup">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/navitem:rotate-90">
  <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
</svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">Inventory Setup</span>
                </a>
                <div class="absolute left-0 bottom-3 w-1 h-8 bg-[#c00000] rounded-r-full"></div>
            @else
                <a href="{{ route('inventory.setup') }}" class="flex items-center gap-4 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-[#c00000] rounded-xl font-semibold transition-all group" title="Inventory Setup">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/navitem:rotate-90">
  <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
</svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">Inventory Setup</span>
                </a>
            @endif
        </div>


        {{-- CSV Import --}}
<div class="relative group/navitem mt-2">
    @if(request()->routeIs('assets.import')) {{-- Siguraduhin na ito yung name sa web.php mo --}}
        <a href="{{ route('assets.import') }}" class="flex items-center gap-4 px-4 py-3 bg-red-50 text-[#c00000] rounded-xl font-bold border border-red-100 transition-all" title="Import CSV File">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/navitem:scale-110">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v3h3" />
            </svg>
            <span class="sidebar-label hidden whitespace-nowrap text-sm">Import CSV</span>
        </a>
        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-[#c00000] rounded-r-full"></div>
    @else
        <a href="{{ route('assets.import') }}" class="flex items-center gap-4 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-[#c00000] rounded-xl font-semibold transition-all group" title="Import CSV File">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/navitem:scale-110">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v3h3" />
            </svg>
            <span class="sidebar-label hidden whitespace-nowrap text-sm">Import CSV</span>
        </a>
    @endif
</div>

        {{-- System Activity --}}
<div class="pt-3 border-t border-slate-100 relative group/navitem">
    <p class="px-3 mb-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest sidebar-label hidden whitespace-nowrap">Activity</p>
    
    @if(request()->routeIs('admin.logs'))
        <a href="{{ route('admin.logs') }}" class="flex items-center gap-4 px-4 py-3 bg-red-50 text-[#c00000] rounded-xl font-bold border border-red-100 transition-all" title="System Logs">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/navitem:scale-110">
  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
</svg>
            <span class="sidebar-label hidden whitespace-nowrap text-sm">System Logs</span>
        </a>
        <div class="absolute left-0 bottom-3 w-1 h-8 bg-[#c00000] rounded-r-full"></div>
    @else
        <a href="{{ route('admin.logs') }}" class="flex items-center gap-4 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-[#c00000] rounded-xl font-semibold transition-all group" title="System Logs">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/navitem:scale-110">
  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
</svg>
            <span class="sidebar-label hidden whitespace-nowrap text-sm">System Logs</span>
        </a>
    @endif
</div>


    </nav>

    {{-- Dark Mode Toggle --}}
    @auth
    <div class="px-3 pb-2 mt-auto">
        <button id="darkModeToggle" onclick="toggleDarkMode()"
            class="w-full flex items-center gap-4 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-[#c00000] rounded-2xl font-bold transition-all group/dm"
            title="Toggle Dark Mode">
            {{-- Moon icon (show in light mode) --}}
            <svg id="dmIconMoon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/dm:scale-110">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
            </svg>
            {{-- Sun icon (show in dark mode) --}}
            <svg id="dmIconSun" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 hidden transition-transform duration-300 group-hover/dm:scale-110">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
            </svg>
            <span class="sidebar-label hidden whitespace-nowrap text-sm" id="dmLabel">Dark Mode</span>
        </button>
    </div>
    @endauth

    {{-- User Profile --}}
    <div class="p-3 border-t border-slate-100 bg-white/80 backdrop-blur-md" id="sidebarUserProfile">
        <div class="bg-slate-50 p-3 rounded-2xl flex items-center gap-3 border border-slate-100 transition-all hover:border-red-100 hover:bg-white">
            <div class="h-10 w-10 bg-[#c00000] rounded-2xl flex items-center justify-center text-white font-bold text-sm shadow-sm shadow-red-100 italic shrink-0 transition-transform group-hover:scale-105">A</div>
            <div class="overflow-hidden leading-tight sidebar-label hidden whitespace-nowrap flex-grow">
                <p class="text-xs font-bold truncate text-slate-800">{{ auth()->user()->email }}</p>
                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest leading-none">Admin</p>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                @csrf
                <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all" title="Logout">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>


<style>
    /* Global Shadow Removal */
    #sidebar,
    .shadow-inner-red,
    #sidebar.expanded,
    #sidebar.expanded .sidebar-label a {
        box-shadow: none !important;
    }

    #sidebar {
        transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s ease-in-out;
        scrollbar-width: thin;
        scrollbar-color: rgba(192, 0, 0, 0.2) transparent;
    }

    /* Expand Width only, no shadow */
    #sidebar.expanded {
        width: 300px !important;
    }

    #sidebarOverlay { transition: opacity 0.3s ease; }

    /* Animation para sa paglitaw ng labels */
    #sidebar.expanded .sidebar-label { 
        display: block !important; 
        animation: navFadeIn 0.4s ease forwards;
    }

    @keyframes navFadeIn {
        from { opacity: 0; transform: translateX(-10px); }
        to { opacity: 1; transform: translateX(0); }
    }

    @media (max-width: 1023px) {
        #sidebar { 
            width: 300px !important; 
        }
        #sidebar .sidebar-label { display: block !important; }
    }

    /* Custom Scrollbar */
    #sidebar::-webkit-scrollbar { width: 5px; }
    #sidebar::-webkit-scrollbar-thumb { background: rgba(192, 0, 0, 0.2); border-radius: 10px; }
</style>

<script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    function expandSidebar() {
        if (window.innerWidth >= 1024) {
            sidebar.classList.add('expanded');
        }
    }

    function collapseSidebar() {
        if (window.innerWidth >= 1024) {
            sidebar.classList.remove('expanded');
        }
    }

    function toggleSidebar() {
        const isHidden = sidebar.classList.contains('-translate-x-full');

        if (isHidden) {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('expanded');
            overlay.classList.remove('hidden');
            setTimeout(() => overlay.style.opacity = "1", 10);
            document.body.classList.add('overflow-hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('expanded');
            overlay.style.opacity = "0";
            setTimeout(() => {
                overlay.classList.add('hidden');
            }, 300);
            document.body.classList.remove('overflow-hidden');
        }
    }

    // ── Dark Mode ──────────────────────────────────────────────
    const _dmPref = {{ auth()->check() ? (auth()->user()->dark_mode ? 'true' : 'false') : 'false' }};

    function applyDarkMode(isDark) {
        const html = document.documentElement;
        const moon  = document.getElementById('dmIconMoon');
        const sun   = document.getElementById('dmIconSun');
        const label = document.getElementById('dmLabel');
        const btn   = document.getElementById('darkModeToggle');

        if (isDark) {
            html.classList.add('dark');
            moon?.classList.add('hidden');
            sun?.classList.remove('hidden');
            if (label) label.textContent = 'Light Mode';
            btn?.classList.remove('text-slate-500', 'hover:bg-slate-50');
            btn?.classList.add('text-amber-400', 'hover:bg-slate-800');
        } else {
            html.classList.remove('dark');
            moon?.classList.remove('hidden');
            sun?.classList.add('hidden');
            if (label) label.textContent = 'Dark Mode';
            btn?.classList.add('text-slate-500', 'hover:bg-slate-50');
            btn?.classList.remove('text-amber-400', 'hover:bg-slate-800');
        }
    }

    // Apply on page load immediately (before paint)
    applyDarkMode(_dmPref);

    async function toggleDarkMode() {
        const isDark = document.documentElement.classList.contains('dark');
        applyDarkMode(!isDark);   // Instant UI feedback
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            await fetch('/user/dark-mode', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' }
            });
        } catch (e) { /* silent — preference already applied visually */ }
    }
</script>

{{-- ═══════════════════════════════════════════════════════════
     GLOBAL DARK MODE CSS — html.dark overrides
     Keeps all layouts intact; only colours change.
════════════════════════════════════════════════════════════ --}}
<style>
/* ── Base ── */
html.dark body                    { background-color: #0f172a; color: #e2e8f0; }

/* ── Backgrounds ── */
html.dark .bg-white               { background-color: #1e293b !important; }
html.dark .bg-slate-50            { background-color: #0f172a !important; }
html.dark .bg-slate-50\/30        { background-color: rgba(15,23,42,0.3) !important; }
html.dark .bg-slate-100           { background-color: #1e293b !important; }
html.dark .bg-white\/80           { background-color: rgba(15,23,42,0.85) !important; }
html.dark .bg-white\/40           { background-color: rgba(15,23,42,0.4) !important; }
html.dark .bg-white\/5            { background-color: rgba(255,255,255,0.05) !important; }

/* ── Text ── */
html.dark .text-slate-900         { color: #f1f5f9 !important; }
html.dark .text-slate-800         { color: #e2e8f0 !important; }
html.dark .text-slate-700         { color: #cbd5e1 !important; }
html.dark .text-slate-600         { color: #94a3b8 !important; }
html.dark .text-slate-500         { color: #64748b !important; }
html.dark .text-slate-400         { color: #475569 !important; }
html.dark .text-slate-300         { color: #334155 !important; }

/* ── Borders ── */
html.dark .border-slate-50        { border-color: #1e293b !important; }
html.dark .border-slate-100       { border-color: #334155 !important; }
html.dark .border-slate-100\/50   { border-color: rgba(51,65,85,0.5) !important; }
html.dark .border-slate-200       { border-color: #334155 !important; }
html.dark .border-white\/10       { border-color: rgba(255,255,255,0.08) !important; }

/* ── Inputs ── */
html.dark input[type="text"],
html.dark input[type="number"],
html.dark input[type="date"],
html.dark input[type="email"],
html.dark select,
html.dark textarea                { background-color: #1e293b !important; color: #e2e8f0 !important; border-color: #334155 !important; }
html.dark input::placeholder      { color: #475569 !important; }
html.dark input:focus,
html.dark select:focus,
html.dark textarea:focus          { border-color: #c00000 !important; box-shadow: 0 0 0 4px rgba(192,0,0,0.15) !important; }

/* ── Sidebar ── */
html.dark #sidebar                { background-color: #1e293b !important; border-color: #c00000 !important; }
html.dark #sidebar .border-slate-100 { border-color: #334155 !important; }
html.dark #sidebar a.text-slate-500  { color: #94a3b8 !important; }
html.dark #sidebar a:hover        { background-color: #0f172a !important; }
html.dark #sidebarUserProfile     { background-color: rgba(15,23,42,0.85) !important; border-color: #334155 !important; }
html.dark #sidebarUserProfile .bg-slate-50 { background-color: #0f172a !important; }
html.dark #sidebarUserProfile .border-slate-100 { border-color: #334155 !important; }
html.dark #sidebarUserProfile .text-slate-800 { color: #e2e8f0 !important; }

/* ── Cards & Panels ── */
html.dark .shadow-inner           { box-shadow: inset 0 2px 4px rgba(0,0,0,0.4) !important; }
html.dark .rounded-\[3rem\]       { }  /* shape kept */
html.dark .hover\:bg-white:hover  { background-color: #1e293b !important; }
html.dark .hover\:bg-slate-50:hover { background-color: #0f172a !important; }
html.dark .hover\:border-red-100:hover  { border-color: #991b1b !important; }
html.dark .hover\:border-slate-100:hover { border-color: #334155 !important; }
html.dark .hover\:border-red-200:hover  { border-color: #991b1b !important; }

/* ── Tables ── */
html.dark table                   { color: #e2e8f0; }
html.dark th                      { color: #94a3b8 !important; background-color: rgba(15,23,42,0.6) !important; border-color: #334155 !important; }
html.dark td                      { border-color: #334155 !important; }
html.dark tr:hover td             { background-color: #0f172a !important; }

/* ── Scrollbar ── */
html.dark .custom-scroll::-webkit-scrollbar-thumb { background: #334155; }

/* ── Autocomplete dropdowns ── */
html.dark .autocomplete-dropdown  { background-color: #1e293b !important; border-color: #334155 !important; }
html.dark .autocomplete-item      { color: #cbd5e1 !important; }
html.dark .autocomplete-item:hover { background-color: #0f172a !important; color: #c00000 !important; }

/* ── Right sidebar (dashboard valuation panel) ── */
html.dark aside.border-l          { background-color: #1e293b !important; border-color: #334155 !important; }

/* ── Active nav items keep red accent ── */
html.dark #sidebar a.bg-red-50    { background-color: rgba(192,0,0,0.15) !important; }
html.dark #sidebar .border-red-100 { border-color: rgba(192,0,0,0.3) !important; }

/* ── Misc ── */
html.dark .backdrop-blur-md       { backdrop-filter: blur(12px); }
html.dark .glass-red-glow         { background: radial-gradient(circle at top right, rgba(192,0,0,0.08) 0%, transparent 70%) !important; }
html.dark .bg-emerald-50          { background-color: rgba(5,46,22,0.5) !important; }
html.dark .border-emerald-100     { border-color: #14532d !important; }
html.dark .text-emerald-600       { color: #34d399 !important; }
html.dark .bg-blue-50             { background-color: rgba(23,37,84,0.5) !important; }
html.dark .text-blue-600          { color: #60a5fa !important; }
html.dark .bg-amber-50            { background-color: rgba(69,26,3,0.5) !important; }
html.dark .text-amber-600         { color: #fbbf24 !important; }
html.dark .bg-purple-50           { background-color: rgba(46,16,101,0.5) !important; }
html.dark .text-purple-700        { color: #c084fc !important; }

/* ── SweetAlert2 in dark mode ── */
html.dark .swal2-popup             { background-color: #1e293b !important; color: #e2e8f0 !important; }
html.dark .swal2-title             { color: #f1f5f9 !important; }
html.dark .swal2-html-container    { color: #cbd5e1 !important; }

/* ── Shadows — replace all white/light glows with dark palette shadows ── */
/* Generic shadow utilities */
html.dark .shadow-sm               { box-shadow: 0 1px 3px rgba(0,0,0,0.4), 0 1px 2px rgba(0,0,0,0.5) !important; }
html.dark .shadow                  { box-shadow: 0 1px 3px rgba(0,0,0,0.5), 0 1px 2px rgba(0,0,0,0.6) !important; }
html.dark .shadow-md               { box-shadow: 0 4px 6px rgba(0,0,0,0.4), 0 2px 4px rgba(0,0,0,0.5) !important; }
html.dark .shadow-lg               { box-shadow: 0 8px 20px rgba(0,0,0,0.45) !important; }
html.dark .shadow-xl               { box-shadow: 0 12px 28px rgba(0,0,0,0.5) !important; }
html.dark .shadow-2xl              { box-shadow: 0 20px 40px rgba(0,0,0,0.55) !important; }

/* Coloured shadow utilities — slate */
html.dark .shadow-slate-100        { box-shadow: 0 4px 16px rgba(0,0,0,0.4) !important; }
html.dark .shadow-slate-200        { box-shadow: 0 4px 16px rgba(0,0,0,0.4) !important; }
html.dark .shadow-slate-200\/40    { box-shadow: 0 8px 20px rgba(0,0,0,0.35) !important; }
html.dark .shadow-slate-200\/30    { box-shadow: 0 8px 24px rgba(0,0,0,0.3) !important; }

/* Coloured shadow utilities — red  (keep a subtle red tint, just much darker) */
html.dark .shadow-red-100          { box-shadow: 0 4px 16px rgba(192,0,0,0.2) !important; }
html.dark .shadow-red-100\/50      { box-shadow: 0 8px 24px rgba(192,0,0,0.18) !important; }

/* Arbitrary/inline shadows produced by Tailwind JIT */
html.dark [class*="shadow-\[0_10px"] { box-shadow: 0 10px 30px rgba(0,0,0,0.45) !important; }
html.dark [class*="shadow-\[0_20px"] { box-shadow: 0 20px 40px rgba(0,0,0,0.5) !important; }
html.dark [class*="shadow-\[-10px"]  { box-shadow: -6px 0 20px rgba(0,0,0,0.4) !important; }
html.dark [class*="shadow-\[2px"]    { box-shadow: 2px 0 10px rgba(192,0,0,0.2) !important; }
html.dark [class*="shadow-\[1px"]    { box-shadow: 1px 0 8px rgba(192,0,0,0.18) !important; }

/* Hover shadow upgrades */
html.dark .hover\:shadow-lg:hover  { box-shadow: 0 8px 24px rgba(0,0,0,0.5) !important; }
html.dark .hover\:shadow-2xl:hover { box-shadow: 0 20px 40px rgba(0,0,0,0.55) !important; }
html.dark .hover\:shadow-xl:hover  { box-shadow: 0 12px 30px rgba(0,0,0,0.5) !important; }
html.dark .hover\:shadow-2xl.hover\:shadow-red-100:hover { box-shadow: 0 20px 40px rgba(192,0,0,0.2) !important; }
</style>