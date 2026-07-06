{{-- Anti-FOUC: apply dark mode class before page renders --}}
<script>
    (function(){
        let isDark = localStorage.getItem('theme') === 'dark';
        let currentUserId = '{{ auth()->check() ? auth()->id() : "guest" }}';
        
        if (!localStorage.getItem('theme') || localStorage.getItem('last_user_id') !== currentUserId) {
            isDark = {{ auth()->check() && auth()->user()->dark_mode ? 'true' : 'false' }};
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            localStorage.setItem('last_user_id', currentUserId);
        }
        
        if (isDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    })();
</script>

<div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/40 z-40 hidden backdrop-blur-sm lg:hidden transition-opacity duration-300 opacity-0"></div>


{{-- Spacer: reserves 80px in the flex layout --}}
<div class="hidden lg:block shrink-0 transition-all duration-300" style="width: 80px;"></div>

{{-- Sidebar --}}
<aside id="sidebar"
       class="fixed inset-y-0 left-0 z-[100] bg-white border-r border-[#c00000] flex flex-col h-screen overflow-x-hidden -translate-x-full lg:translate-x-0 transition-all duration-300 ease-in-out group/sidebar shadow-none"
       style="width: 80px;"
       onmouseenter="expandSidebar()" onmouseleave="collapseSidebar()">

    {{-- Brand (Fixed Header) --}}
    <div class="h-20 flex items-center px-4 relative shrink-0 border-b border-slate-100">
        <button onclick="toggleSidebar()" class="lg:hidden absolute right-3 top-1/2 -translate-y-1/2 p-2 text-slate-400 hover:text-[#c00000] transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="flex items-center gap-3 w-full" id="sidebarBrandContent">
            <img src="{{ asset('images/deped_logo.png') }}" class="h-10 w-auto shrink-0 transition-transform duration-500 group-hover:rotate-12 mx-auto lg:mx-0">
            <div class="sidebar-label hidden whitespace-nowrap overflow-hidden text-left flex-grow">
                <h1 class="text-sm font-black tracking-tighter italic leading-none text-slate-900 uppercase">DepEd ZC</h1>
                <p class="text-[8px] text-[#c00000] font-bold tracking-widest uppercase mt-1">Inventory Management System</p>
            </div>
        </div>
    </div>

    {{-- Navigation (Scrollable Area) --}}
    <nav class="flex-grow px-2 py-6 space-y-2 overflow-y-auto overflow-x-hidden custom-scroll">
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
                    </svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">Dashboard</span>
                </a>
            @endif
        </div>

        {{-- Assets --}}
        <div class="relative group/navitem">
            @if(request()->routeIs('assets.view'))
                <a href="{{ route('assets.view') }}" class="flex items-center gap-4 px-4 py-3 bg-red-50 text-[#c00000] rounded-2xl font-bold border border-red-100 transition-all" title="Assets">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/navitem:scale-110">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                    </svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">Assets</span>
                </a>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#c00000] rounded-r-full shadow-[2px_0_8px_rgba(192,0,0,0.3)]"></div>
            @else
                <a href="{{ route('assets.view') }}" class="flex items-center gap-4 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-[#c00000] rounded-2xl font-bold transition-all" title="Assets">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/navitem:scale-110">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                    </svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">Assets</span>
                </a>
            @endif
        </div>

        {{-- Employees Registry --}}
        <div class="relative group/navitem">
            @if(request()->routeIs('admin.employees'))
                <a href="{{ route('admin.employees') }}" class="flex items-center gap-4 px-4 py-3 bg-red-50 text-[#c00000] rounded-2xl font-bold border border-red-100 transition-all" title="Employees">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">Employees</span>
                </a>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-[#c00000] rounded-r-full"></div>
            @else
                <a href="{{ route('admin.employees') }}" class="flex items-center gap-4 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-[#c00000] rounded-2xl font-bold transition-all" title="Employees">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">Employees</span>
                </a>
            @endif
        </div>

        {{-- Sources Registry --}}
        <div class="relative group/navitem">
            @if(request()->routeIs('admin.sources*'))
                <a href="{{ route('admin.sources') }}" class="flex items-center gap-4 px-4 py-3 bg-red-50 text-[#c00000] rounded-2xl font-bold border border-red-100 transition-all" title="Sources">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/navitem:scale-110">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">Sources</span>
                </a>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-[#c00000] rounded-r-full"></div>
            @else
                <a href="{{ route('admin.sources') }}" class="flex items-center gap-4 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-[#c00000] rounded-2xl font-bold transition-all" title="Sources">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/navitem:scale-110">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">Sources</span>
                </a>
            @endif
        </div>


        {{-- Schools Registry --}}
        <div class="relative group/navitem">
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

        {{-- Offices Registry --}}
        <div class="relative group/navitem">
            @if(request()->routeIs('admin.offices'))
                <a href="{{ route('admin.offices') }}" class="flex items-center gap-4 px-4 py-3 bg-red-50 text-[#c00000] rounded-2xl font-bold border border-red-100 transition-all" title="Offices">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15" />
                    </svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">Offices</span>
                </a>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-[#c00000] rounded-r-full"></div>
            @else
                <a href="{{ route('admin.offices') }}" class="flex items-center gap-4 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-[#c00000] rounded-2xl font-bold transition-all" title="Offices">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15" />
                    </svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">Offices</span>
                </a>
            @endif
        </div>

        {{-- Buildings Registry --}}
        <div class="relative group/navitem">
            @if(request()->routeIs('register.building'))
                <a href="{{ route('register.building') }}" class="flex items-center gap-4 px-4 py-3 bg-red-50 text-[#c00000] rounded-2xl font-bold border border-red-100 transition-all" title="Buildings">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-10.5 3.75h.75m-.75 3h.75m3-3h.75m-.75 3h.75" />
                    </svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">Buildings</span>
                </a>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-[#c00000] rounded-r-full"></div>
            @else
                <a href="{{ route('register.building') }}" class="flex items-center gap-4 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-[#c00000] rounded-2xl font-bold transition-all" title="Buildings">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-10.5 3.75h.75m-.75 3h.75m3-3h.75m-.75 3h.75" />
                    </svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">Buildings</span>
                </a>
            @endif
        </div>

        {{-- Configuration Divider --}}
        @if(auth()->check() && auth()->user()->isAdmin())
        <div class="pt-4 border-t border-slate-100 sidebar-label hidden whitespace-nowrap text-center">
            <p class="px-3 mb-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Configuration</p>
        </div>

        {{-- Inventory Setup --}}
        <div class="relative group/navitem">
            @if(request()->routeIs('inventory.setup'))
                <a href="{{ route('inventory.setup') }}" class="flex items-center gap-4 px-4 py-3 bg-red-50 text-[#c00000] rounded-xl font-bold border border-red-100 transition-all" title="Inventory Setup">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/navitem:rotate-90">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">Inventory Setup</span>
                </a>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-[#c00000] rounded-r-full"></div>
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

        {{-- Import Assets --}}
        <div class="relative group/navitem">
            @if(request()->routeIs('buildings.import*')) 
                <a href="{{ route('buildings.import') }}" class="flex items-center gap-4 px-4 py-3 bg-red-50 text-[#c00000] rounded-xl font-bold border border-red-100 transition-all" title="Import Assets">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/navitem:-translate-y-1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">Import Assets</span>
                </a>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-[#c00000] rounded-r-full"></div>
            @else
                <a href="{{ route('buildings.import') }}" class="flex items-center gap-4 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-[#c00000] rounded-xl font-semibold transition-all group" title="Import Assets">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/navitem:-translate-y-1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">Import Assets</span>
                </a>
            @endif
        </div>
        @endif

        {{-- Download Reports --}}
        <div class="relative group/navitem">
            @if(request()->routeIs('assets.reports'))
                <a href="{{ route('assets.reports') }}" class="flex items-center gap-4 px-4 py-3 bg-red-50 text-[#c00000] rounded-xl font-bold border border-red-100 transition-all" title="Download Reports">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/navitem:scale-110">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v3h3" />
                    </svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">Download Reports</span>
                </a>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-[#c00000] rounded-r-full"></div>
            @else
                <a href="{{ route('assets.reports') }}" class="flex items-center gap-4 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-[#c00000] rounded-xl font-semibold transition-all group" title="Download Reports">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/navitem:scale-110">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v3h3" />
                    </svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">Download Reports</span>
                </a>
            @endif
        </div>

        {{-- Print QR Stickers --}}
        <div class="relative group/navitem">
            @if(request()->routeIs('assets.print_stickers'))
                <a href="{{ route('assets.print_stickers') }}" class="flex items-center gap-4 px-4 py-3 bg-red-50 text-[#c00000] rounded-xl font-bold border border-red-100 transition-all" title="Print QR Stickers">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z" />
                    </svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">Print QR Stickers</span>
                </a>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-[#c00000] rounded-r-full"></div>
            @else
                <a href="{{ route('assets.print_stickers') }}" class="flex items-center gap-4 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-[#c00000] rounded-xl font-semibold transition-all group" title="Print QR Stickers">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z" />
                    </svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">Print QR Stickers</span>
                </a>
            @endif
        </div>

        {{-- Activity Divider --}}
        <div class="pt-4 border-t border-slate-100 sidebar-label hidden whitespace-nowrap text-center">
            <p class="px-3 mb-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Activity</p>
        </div>

        {{-- System / History Logs (All Roles) --}}
        @if(auth()->check())
        <div class="relative group/navitem">
            @if(request()->routeIs('admin.logs'))
                <a href="{{ route('admin.logs') }}" class="flex items-center gap-4 px-4 py-3 bg-red-50 text-[#c00000] rounded-xl font-bold border border-red-100 transition-all" title="{{ auth()->user()->isSuperAdmin() ? 'System Logs' : 'History Logs' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/navitem:scale-110">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">{{ auth()->user()->isSuperAdmin() ? 'System Logs' : 'History Logs' }}</span>
                </a>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-[#c00000] rounded-r-full"></div>
            @else
                <a href="{{ route('admin.logs') }}" class="flex items-center gap-4 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-[#c00000] rounded-xl font-semibold transition-all group" title="{{ auth()->user()->isSuperAdmin() ? 'System Logs' : 'History Logs' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/navitem:scale-110">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">{{ auth()->user()->isSuperAdmin() ? 'System Logs' : 'History Logs' }}</span>
                </a>
            @endif
        </div>
        @endif



        {{-- User Management — Super Admin Only --}}
        @if(auth()->check() && auth()->user()->isSuperAdmin())
        <div class="relative group/navitem">
            @if(request()->routeIs('admin.user-management'))
                <a href="{{ route('admin.user-management') }}" class="flex items-center gap-4 px-4 py-3 bg-red-50 text-[#c00000] rounded-xl font-bold border border-red-100 transition-all" title="User Management">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/navitem:scale-110">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">User Management</span>
                </a>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-[#c00000] rounded-r-full"></div>
            @else
                <a href="{{ route('admin.user-management') }}" class="flex items-center gap-4 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-[#c00000] rounded-xl font-semibold transition-all group" title="User Management">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/navitem:scale-110">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    <span class="sidebar-label hidden whitespace-nowrap text-sm">User Management</span>
                </a>
            @endif
        </div>

        @endif

    </nav>

    {{-- Sticky Footer --}}
    <div class="shrink-0 bg-white border-t border-slate-100 p-2 lg:p-3 relative z-20" id="sidebarFooter">
        
        {{-- Dark Mode Toggle --}}
        @auth
        <div class="mb-2">
            <button id="darkModeToggle" onclick="toggleDarkMode()"
                class="w-full flex items-center gap-4 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-[#c00000] rounded-2xl font-bold transition-all group/dm overflow-hidden"
                title="Toggle Dark Mode">
                <svg id="dmIconMoon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 transition-transform duration-300 group-hover/dm:scale-110">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                </svg>
                <svg id="dmIconSun" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 shrink-0 hidden transition-transform duration-300 group-hover/dm:scale-110">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                </svg>
                <span class="sidebar-label hidden whitespace-nowrap text-sm" id="dmLabel">Dark Mode</span>
            </button>
        </div>
        @endauth

        {{-- User Profile Card --}}
        <div class="bg-slate-50 p-2 lg:p-3 rounded-2xl flex items-center gap-3 border border-slate-100 transition-all hover:border-red-100 hover:bg-white overflow-hidden" id="sidebarUserProfileCard">
            <div class="h-10 w-10 bg-[#c00000] rounded-2xl flex items-center justify-center text-white font-bold text-sm shadow-sm shadow-red-100 italic shrink-0 transition-transform hover:scale-105">A</div>
            <div class="overflow-hidden leading-tight sidebar-label hidden whitespace-nowrap flex-grow">
                <p class="text-xs font-bold truncate text-slate-800">{{ auth()->user()->email ?? 'guest@deped.gov.ph' }}</p>
                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest leading-none">
                    @if(auth()->check())
                        @if(auth()->user()->isSuperAdmin()) 🛡️ Super Admin
                        @elseif(auth()->user()->isAdmin()) 🛠️ Admin
                        @else 👤 User
                        @endif
                    @endif
                </p>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="shrink-0 sidebar-label hidden">
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
        scrollbar-width: none;
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

    /* Custom Scrollbar Area */
    .custom-scroll::-webkit-scrollbar { width: 4px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: rgba(192, 0, 0, 0.15); border-radius: 10px; }
    
    /* Hide scrollbar by default for sidebar */
    #sidebar::-webkit-scrollbar { width: 0px; height: 0px; }
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
    function applyDarkMode(isDark) {
        const html = document.documentElement;
        const moon  = document.getElementById('dmIconMoon');
        const sun   = document.getElementById('dmIconSun');
        const label = document.getElementById('dmLabel');
        const btn   = document.getElementById('darkModeToggle');
        const footer = document.getElementById('sidebarFooter');

        if (isDark) {
            html.classList.add('dark');
            moon?.classList.add('hidden');
            sun?.classList.remove('hidden');
            if (label) label.textContent = 'Light Mode';
            btn?.classList.remove('text-slate-500', 'hover:bg-slate-50');
            btn?.classList.add('text-amber-400', 'hover:bg-slate-800');
            if(footer) footer.style.backgroundColor = 'rgba(30,41,59,0.9)';
        } else {
            html.classList.remove('dark');
            moon?.classList.remove('hidden');
            sun?.classList.add('hidden');
            if (label) label.textContent = 'Dark Mode';
            btn?.classList.add('text-slate-500', 'hover:bg-slate-50');
            btn?.classList.remove('text-amber-400', 'hover:bg-slate-800');
            if(footer) footer.style.backgroundColor = 'rgba(255,255,255,0.9)';
        }
    }

    // Apply on page load immediately based on html class set by anti-FOUC script
    applyDarkMode(document.documentElement.classList.contains('dark'));

    async function toggleDarkMode() {
        const isDark = document.documentElement.classList.contains('dark');
        const newTheme = !isDark;
        applyDarkMode(newTheme);   // Instant UI feedback
        localStorage.setItem('theme', newTheme ? 'dark' : 'light');
        
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            await fetch('/user/dark-mode', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' }
            });
        } catch (e) { /* silent — preference already applied visually */ }
    }

    // Listen for dark mode changes in other tabs
    window.addEventListener('storage', (e) => {
        if (e.key === 'theme') {
            applyDarkMode(e.newValue === 'dark');
        }
    });
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
html.dark .bg-slate-50\/50        { background-color: rgba(15,23,42,0.5) !important; }
html.dark .bg-slate-100           { background-color: #1e293b !important; }
html.dark .bg-white\/80           { background-color: rgba(30,41,59,0.85) !important; }
html.dark .bg-white\/70           { background-color: rgba(30,41,59,0.7) !important; }
html.dark .bg-white\/40           { background-color: rgba(30,41,59,0.4) !important; }
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
html.dark .border-slate-300       { border-color: #475569 !important; }
html.dark .border-b-white         { border-bottom-color: #1e293b !important; }
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
html.dark #sidebarFooter          { background-color: #1e293b !important; border-color: #334155 !important; }
html.dark #sidebarUserProfileCard { background-color: #0f172a !important; border-color: #334155 !important; }
html.dark #sidebarUserProfileCard .text-slate-800 { color: #e2e8f0 !important; }

/* ── Cards & Panels ── */
html.dark .shadow-inner           { box-shadow: inset 0 2px 4px rgba(0,0,0,0.4) !important; }
html.dark .rounded-\[3rem\]       { }  /* shape kept */
html.dark .bg-slate-200           { background-color: #334155 !important; }
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
html.dark .bg-emerald-100         { background-color: rgba(5,46,22,0.7) !important; }
html.dark .border-emerald-100     { border-color: #14532d !important; }
html.dark .text-emerald-600       { color: #34d399 !important; }
html.dark .text-emerald-700       { color: #34d399 !important; }
html.dark .bg-blue-50             { background-color: rgba(23,37,84,0.5) !important; }
html.dark .text-blue-600          { color: #60a5fa !important; }
html.dark .bg-amber-50            { background-color: rgba(69,26,3,0.5) !important; }
html.dark .bg-amber-100           { background-color: rgba(69,26,3,0.7) !important; }
html.dark .text-amber-600         { color: #fbbf24 !important; }
html.dark .text-amber-700         { color: #fbbf24 !important; }
html.dark .bg-purple-50           { background-color: rgba(46,16,101,0.5) !important; }
html.dark .text-purple-700        { color: #c084fc !important; }
html.dark .bg-deped_light         { background-color: rgba(192,0,0,0.1) !important; }


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