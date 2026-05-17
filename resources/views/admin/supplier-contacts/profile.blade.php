<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $contact->name }} | Supplier Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,800;1,900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        :root {
            --deped: #c00000;
            --deped-dark: #900000;
            --deped-light: #ff2020;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f7f7f8; margin: 0; }

        /* =========== HERO BANNER =========== */
        .hero-banner {
            background: linear-gradient(135deg, #c00000 0%, #7a0000 60%, #3a0000 100%);
            position: relative;
            overflow: hidden;
            border-radius: 2.5rem;
            box-shadow: 0 25px 60px -15px rgba(192,0,0,0.40);
        }
        .hero-banner::before {
            content: '';
            position: absolute;
            top: -60%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255,90,90,0.20) 0%, transparent 65%);
            pointer-events: none;
        }
        .hero-banner::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 65%);
            pointer-events: none;
        }
        .hero-noise {
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
            opacity: 0.25;
            pointer-events: none;
        }
        .hero-grid-lines {
            position: absolute;
            inset: 0;
            background-image: repeating-linear-gradient(0deg, rgba(255,255,255,0.03) 0px, rgba(255,255,255,0.03) 1px, transparent 1px, transparent 40px),
                              repeating-linear-gradient(90deg, rgba(255,255,255,0.03) 0px, rgba(255,255,255,0.03) 1px, transparent 1px, transparent 40px);
            pointer-events: none;
        }
        .avatar-ring {
            background: conic-gradient(from 180deg, #ff6b6b, #c00000, #ff6b6b);
            padding: 3px;
            border-radius: 50%;
            animation: spin-slow 8s linear infinite;
        }
        @keyframes spin-slow { to { transform: rotate(360deg); } }
        .avatar-inner { background: #7a0000; border-radius: 50%; display: flex; align-items: center; justify-content: center; }

        /* =========== CARDS =========== */
        .card {
            background: #fff;
            border-radius: 2rem;
            border: 1px solid #f1f1f3;
            box-shadow: 0 2px 16px rgba(0,0,0,0.04);
            overflow: hidden;
        }
        .card-label {
            font-size: 9px;
            font-weight: 900;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--deped);
        }
        .stat-card {
            border-radius: 1.75rem;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        .stat-card-red {
            background: linear-gradient(135deg, #c00000, #820000);
            color: white;
        }
        .stat-card-white {
            background: #fff;
            border: 1px solid #f1e8e8;
            color: #1e293b;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -20%;
            width: 150px;
            height: 150px;
            background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        /* =========== TABLE =========== */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead th {
            padding: 10px 16px;
            font-size: 9px;
            font-weight: 900;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #94a3b8;
            background: #fafafa;
            border-bottom: 1px solid #f1f5f9;
            white-space: nowrap;
        }
        .data-table tbody tr {
            border-bottom: 1px solid #f8fafc;
            transition: background 0.15s;
            cursor: default;
        }
        .data-table tbody tr:hover { background: #fef2f2; }
        .data-table tbody td { padding: 14px 16px; vertical-align: middle; }

        /* =========== SCROLLBAR =========== */
        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #c00000; }

        /* =========== ANIMATION =========== */
        .animate-in { animation: slideUp 0.45s ease-out forwards; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
        .delay-1 { animation-delay: 0.05s; opacity: 0; }
        .delay-2 { animation-delay: 0.10s; opacity: 0; }
        .delay-3 { animation-delay: 0.15s; opacity: 0; }
        .delay-4 { animation-delay: 0.20s; opacity: 0; }

        /* Dark Mode */
        html.dark body { background: #0b0f19; }
        html.dark .card { background: #1e293b; border-color: #334155; }
        html.dark .stat-card-white { background: #1e293b; border-color: #334155; color: #e2e8f0; }
        html.dark .data-table thead th { background: #0f172a; color: #475569; border-color: #1e293b; }
        html.dark .data-table tbody tr { border-color: #1e293b; }
        html.dark .data-table tbody tr:hover { background: #2d1b1b; }
    </style>
</head>
<body class="flex min-h-screen text-slate-800 overflow-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll" style="padding: 1.75rem; gap: 1.5rem;">

        {{-- BACK BUTTON --}}
        <div class="animate-in">
            <a href="{{ route('admin.supplier_contacts') }}" class="inline-flex items-center gap-2 text-[11px] font-black uppercase tracking-widest text-slate-500 hover:text-deped transition-colors group">
                <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                Back to Supplier Registry
            </a>
        </div>

        {{-- HERO BANNER --}}
        <div class="hero-banner p-8 lg:p-10 animate-in delay-1">
            <div class="hero-noise"></div>
            <div class="hero-grid-lines"></div>
            <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center gap-8">

                {{-- Avatar Ring --}}
                <div class="avatar-ring w-24 h-24 shrink-0">
                    <div class="avatar-inner w-full h-full">
                        <span class="text-2xl font-black text-white uppercase">{{ substr($contact->name, 0, 1) }}</span>
                    </div>
                </div>

                {{-- Identity --}}
                <div class="flex-grow">
                    <div class="flex items-center gap-3 mb-3">
                        <span style="background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.2);" class="text-[9px] font-black text-red-100 uppercase tracking-[0.2em] px-3 py-1 rounded-full">
                            Acquisition Representative
                        </span>
                        <span style="background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.2);" class="text-[9px] font-black text-red-100 uppercase tracking-[0.2em] px-3 py-1 rounded-full flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                            Active Supplier
                        </span>
                    </div>
                    <h1 class="text-4xl font-black text-white uppercase italic tracking-tight leading-none">{{ $contact->name }}</h1>
                    <div class="flex flex-wrap items-center gap-4 mt-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15"/></svg>
                            <span class="text-sm font-semibold text-red-100 uppercase">{{ $contact->organization ?? 'External Provider' }}</span>
                        </div>
                        <div class="w-1 h-1 rounded-full bg-red-400 opacity-50"></div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                            <span class="text-sm font-semibold text-red-100 uppercase">{{ $contact->position ?? 'Personnel' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Quick Contact Badges --}}
                <div class="flex flex-col gap-3 shrink-0">
                    <a href="tel:{{ $contact->contact_number }}" style="background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.2);" class="flex items-center gap-3 px-5 py-3 rounded-2xl text-white text-xs font-bold uppercase tracking-wide hover:bg-white hover:text-deped transition-all duration-200 group">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                        {{ $contact->contact_number ?: 'No number' }}
                    </a>
                    <a href="mailto:{{ $contact->email }}" style="background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.2);" class="flex items-center gap-3 px-5 py-3 rounded-2xl text-white text-xs font-bold lowercase tracking-wide hover:bg-white hover:text-deped transition-all duration-200 group">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                        {{ $contact->email ?: 'No email' }}
                    </a>
                </div>

            </div>
        </div>

        {{-- STATS ROW --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 animate-in delay-2">
            <div class="stat-card stat-card-red">
                <p class="text-[9px] font-black uppercase tracking-[0.2em] text-red-200 mb-2">Total Items Supplied</p>
                <p class="text-5xl font-black text-white leading-none">{{ $stats->total_supplied }}</p>
                <p class="text-[10px] font-semibold text-red-200 mt-3 uppercase">Active in DepEd Inventory</p>
                <div class="absolute top-5 right-5 opacity-20">
                    <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                </div>
            </div>

            <div class="stat-card stat-card-white sm:col-span-2" style="box-shadow: 0 2px 20px rgba(192,0,0,0.06);">
                <p class="card-label mb-2">Total Supplied Inventory Value</p>
                <p class="font-black leading-none" style="font-size: 2.8rem; color: var(--deped);">₱ {{ number_format($stats->total_value, 2) }}</p>
                <p class="text-[10px] font-semibold text-slate-400 mt-3 uppercase tracking-wider">Cumulative Procurement Cost — All Time</p>
                <div class="absolute top-5 right-5 opacity-5" style="color: var(--deped);">
                    <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
            </div>
        </div>

        {{-- SUPPLIED ASSETS TABLE --}}
        <div class="card animate-in delay-3" style="padding-bottom: 2rem;">
            <div style="padding: 1.5rem 1.75rem; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <p class="card-label mb-1">DepEd Inventory Integration</p>
                    <h2 style="font-size: 1.2rem; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: -0.01em;">Supplied Equipment Registry</h2>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span style="background: #fef2f2; color: var(--deped); border: 1px solid #fecaca; font-size: 9px; font-weight: 900; letter-spacing: 0.1em; text-transform: uppercase; padding: 6px 14px; border-radius: 999px;">{{ $assets->count() }} ASSETS</span>
                </div>
            </div>

            @if($assets->count() > 0)
            <div class="custom-scroll" style="overflow-x: auto;">
                <table class="data-table" style="min-width: 860px;">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">#</th>
                            <th>Item / Category</th>
                            <th>Property & S/N</th>
                            <th>Brand · Model</th>
                            <th>Deployed To</th>
                            <th style="text-align: center;">Condition</th>
                            <th style="text-align: right;">Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assets as $i => $asset)
                        <tr>
                            <td style="text-align: center; font-size: 10px; font-weight: 900; color: #94a3b8;">{{ $i + 1 }}</td>
                            <td>
                                <span style="display: block; font-size: 11.5px; font-weight: 800; color: #1e293b; text-transform: uppercase; letter-spacing: 0.02em;">{{ $asset->item_name }}</span>
                                <span style="display: block; font-size: 9px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-top: 2px; letter-spacing: 0.08em;">{{ $asset->category_name }}</span>
                            </td>
                            <td>
                                <span style="display: block; font-size: 10.5px; font-weight: 800; color: #334155; text-transform: uppercase;">{{ $asset->property_number }}</span>
                                <span style="display: block; font-size: 9px; font-weight: 600; color: #94a3b8; margin-top: 2px;">S/N: {{ $asset->serial_number ?: '—' }}</span>
                            </td>
                            <td>
                                <span style="font-size: 10.5px; font-weight: 700; color: #475569; text-transform: uppercase;">{{ $asset->brand ?: '—' }}</span>
                                <span style="font-size: 10px; color: #94a3b8; margin: 0 4px;">·</span>
                                <span style="font-size: 10px; font-weight: 600; color: #64748b; text-transform: uppercase;">{{ $asset->model ?: '—' }}</span>
                            </td>
                            <td>
                                <span style="display: block; font-size: 10.5px; font-weight: 700; color: #1e293b; text-transform: uppercase; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $asset->school_name ?: '—' }}</span>
                                <span style="display: block; font-size: 9px; font-weight: 600; color: #94a3b8; text-transform: uppercase; margin-top: 2px; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $asset->office_name ?: '—' }}</span>
                            </td>
                            <td style="text-align: center;">
                                @php
                                    $cond = $asset->condition ?: 'Good';
                                    $isGood = in_array($cond, ['Good', 'Serviceable']);
                                @endphp
                                <span style="padding: 3px 10px; border-radius: 999px; font-size: 8px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; background: {{ $isGood ? '#dcfce7' : '#fef9c3' }}; color: {{ $isGood ? '#15803d' : '#92400e' }};">{{ $cond }}</span>
                            </td>
                            <td style="text-align: right;">
                                <span style="font-size: 11.5px; font-weight: 900; color: var(--deped); font-style: italic;">₱ {{ number_format($asset->asset_cost, 2) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div style="padding: 4rem 2rem; text-align: center; background: #fafafa; margin: 1.5rem; border-radius: 1.5rem; border: 1.5px dashed #e2e8f0;">
                <p style="font-size: 11px; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.15em;">No supplied assets recorded in the system yet.</p>
            </div>
            @endif
        </div>

        <div style="height: 2rem;"></div>
    </div>

</body>
</html>
