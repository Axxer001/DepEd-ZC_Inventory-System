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
            <div class="sidebar-label hidden whitespace-nowrap overflow-hidden">
                <h1 class="text-xl font-extrabold tracking-tight italic leading-tight text-slate-900">DepEd ZC</h1>
                <p class="text-[9px] text-slate-400 font-bold tracking-[0.2em] uppercase">Asset Management</p>
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


{{-- LD 1 --}}
<div class="pt-3 border-t border-slate-100">
    <p class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-blue-600 sidebar-label hidden whitespace-nowrap">Legislative District 1</p>
    
    <div class="space-y-1">
        {{-- Quadrant 1.1 --}}
        <div class="relative group/navitem">
            <a href="{{ route('quadrant.1.1') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all group/subitem 
               {{ request()->routeIs('quadrant.1.1') 
                  ? 'bg-blue-50 text-blue-600 border border-blue-100' 
                  : 'text-slate-500 hover:bg-blue-50 hover:text-blue-600 border border-transparent' }}" 
               title="Quadrant 1.1">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 shrink-0 transition-transform duration-300 group-hover/subitem:-translate-y-1">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
</svg>
                <span class="sidebar-label hidden whitespace-nowrap text-sm flex-grow">Quadrant 1.1</span>
                <span class="sidebar-label hidden text-[10px] px-2 py-1 rounded-lg font-bold transition-colors
                      {{ request()->routeIs('quadrant.1.1') ? 'bg-white text-blue-600 shadow-sm' : 'bg-slate-100 text-blue-600' }}">
                    3 Dist.
                </span>
            </a>
            @if(request()->routeIs('quadrant.1.1'))
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-r-full"></div>
            @endif
        </div>

        {{-- Quadrant 1.2 --}}
        <div class="relative group/navitem">
            <a href="{{ route('quadrant.1.2') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all group/subitem 
               {{ request()->routeIs('quadrant.1.2') 
                  ? 'bg-blue-50 text-blue-600 border border-blue-100' 
                  : 'text-slate-500 hover:bg-blue-50 hover:text-blue-600 border border-transparent' }}" 
               title="Quadrant 1.2">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 shrink-0 transition-transform duration-300 group-hover/subitem:-translate-y-1">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
</svg>                <span class="sidebar-label hidden whitespace-nowrap text-sm flex-grow">Quadrant 1.2</span>
                <span class="sidebar-label hidden text-[10px] px-2 py-1 rounded-lg font-bold transition-colors
                      {{ request()->routeIs('quadrant.1.2') ? 'bg-white text-blue-600 shadow-sm' : 'bg-slate-100 text-blue-600' }}">
                    2 Dist.
                </span>
            </a>
            @if(request()->routeIs('quadrant.1.2'))
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-r-full"></div>
            @endif
        </div>
    </div>
</div>

{{-- LD 2 --}}
<div class="pt-3 border-t border-slate-100">
    <p class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-emerald-600 sidebar-label hidden whitespace-nowrap">Legislative District 2</p>
    
    <div class="space-y-1">
        {{-- Quadrant 2.1 --}}
        <div class="relative group/navitem">
            <a href="{{ route('quadrant.2.1') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all group/subitem 
               {{ request()->routeIs('quadrant.2.1') 
                  ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' 
                  : 'text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 border border-transparent' }}" 
               title="Quadrant 2.1">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 shrink-0 transition-transform duration-300 group-hover/subitem:-translate-y-1">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
</svg>
                <span class="sidebar-label hidden whitespace-nowrap text-sm flex-grow">Quadrant 2.1</span>
                <span class="sidebar-label hidden text-[10px] px-2 py-1 rounded-lg font-bold transition-colors
                      {{ request()->routeIs('quadrant.2.1') ? 'bg-white text-emerald-600 shadow-sm' : 'bg-slate-100 text-emerald-600' }}">
                    3 Dist.
                </span>
            </a>
            @if(request()->routeIs('quadrant.2.1'))
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-emerald-600 rounded-r-full"></div>
            @endif
        </div>

        {{-- Quadrant 2.2 --}}
        <div class="relative group/navitem">
            <a href="{{ route('quadrant.2.2') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all group/subitem 
               {{ request()->routeIs('quadrant.2.2') 
                  ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' 
                  : 'text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 border border-transparent' }}" 
               title="Quadrant 2.2">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 shrink-0 transition-transform duration-300 group-hover/subitem:-translate-y-1">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
</svg>
                <span class="sidebar-label hidden whitespace-nowrap text-sm flex-grow">Quadrant 2.2</span>
                <span class="sidebar-label hidden text-[10px] px-2 py-1 rounded-lg font-bold transition-colors
                      {{ request()->routeIs('quadrant.2.2') ? 'bg-white text-emerald-600 shadow-sm' : 'bg-slate-100 text-emerald-600' }}">
                    4 Dist.
                </span>
            </a>
            @if(request()->routeIs('quadrant.2.2'))
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-emerald-600 rounded-r-full"></div>
            @endif
        </div>
    </div>
</div>

    </nav>

    {{-- User Profile --}}
    <div class="p-3 border-t border-slate-100 mt-auto bg-white/80 backdrop-blur-md">
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
</script>