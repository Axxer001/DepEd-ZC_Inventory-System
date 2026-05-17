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
        :root { --red: #c00000; }
        * { box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f4f5f7; margin: 0; }

        .card { background: #fff; border-radius: 1.75rem; border: 1px solid #e8eaed; box-shadow: 0 1px 6px rgba(0,0,0,0.04); }
        .label { font-size: 9px; font-weight: 900; letter-spacing: 0.16em; text-transform: uppercase; color: #94a3b8; }
        .red-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--red); display: inline-block; }

        /* ---- STAT CARDS ---- */
        .stat { padding: 1.5rem; border-radius: 1.5rem; border: 1px solid #e8eaed; background: #fff; }

        /* ---- TABLE ---- */
        .tbl { width: 100%; border-collapse: collapse; }
        .tbl thead th { padding: 10px 16px; font-size: 9px; font-weight: 900; letter-spacing: 0.14em; text-transform: uppercase; color: #94a3b8; background: #fafafa; border-bottom: 1px solid #f1f5f9; white-space: nowrap; }
        .tbl tbody tr { border-bottom: 1px solid #f8fafc; transition: background 0.12s; }
        .tbl tbody tr:last-child { border-bottom: none; }
        .tbl tbody tr:hover { background: #f8fafc; }
        .tbl tbody td { padding: 13px 16px; vertical-align: middle; }

        /* ---- SCROLL ---- */
        .scroll::-webkit-scrollbar { width: 4px; height: 4px; }
        .scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 8px; }

        /* ---- FADE IN ---- */
        .fade { animation: fade 0.4s ease forwards; opacity: 0; }
        .d1 { animation-delay: 0.04s; }
        .d2 { animation-delay: 0.10s; }
        .d3 { animation-delay: 0.16s; }
        @keyframes fade { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Dark */
        html.dark body { background: #0b0f19; }
        html.dark .card, html.dark .stat { background: #1e293b; border-color: #334155; }
        html.dark .tbl thead th { background: #0f172a; border-color: #1e293b; }
        html.dark .tbl tbody tr:hover { background: #1a2640; }
        html.dark .tbl tbody tr { border-color: #1e293b; }
    </style>
</head>
<body class="flex min-h-screen overflow-hidden text-slate-800 dark:text-slate-100">

    @include('partials.sidebar')

    <div class="flex-grow h-screen overflow-y-auto scroll" style="padding: 1.75rem; display: flex; flex-direction: column; gap: 1.25rem;">

        {{-- ===== TOP HEADER CARD ===== --}}
        <div class="card fade d1" style="padding: 1.75rem 2rem; display: flex; align-items: center; gap: 1.25rem;">

            {{-- Avatar --}}
            <div style="width:64px; height:64px; border-radius:1.25rem; background:#f4f5f7; border:1px solid #e8eaed; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <span style="font-size: 1.5rem; font-weight: 900; color: var(--red); text-transform: uppercase;">{{ substr($contact->name, 0, 1) }}</span>
            </div>

            {{-- Name + Meta --}}
            <div style="flex-grow: 1; min-width: 0;">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.4rem;">
                    <span class="label">Supplier Representative</span>
                    <span class="red-dot"></span>
                    <span class="label" style="color: var(--red);">{{ $contact->organization ?? 'External Provider' }}</span>
                </div>
                <h1 style="font-size: 1.6rem; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: -0.01em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.1;" class="dark:text-slate-100">{{ $contact->name }}</h1>
                <p style="font-size: 10.5px; font-weight: 600; color: #94a3b8; margin-top: 0.3rem; text-transform: uppercase; letter-spacing: 0.05em;">{{ $contact->position ?? 'Personnel' }}</p>
            </div>

            {{-- Contact Info + Back --}}
            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.75rem; flex-shrink: 0;">
                <a href="{{ route('admin.supplier_contacts') }}" style="display:inline-flex; align-items:center; gap:6px; padding:8px 18px; border-radius:999px; border:1.5px solid #e8eaed; font-size:10px; font-weight:800; letter-spacing:0.1em; text-transform:uppercase; color:#64748b; background:#fff; text-decoration:none; transition:all .15s;" onmouseover="this.style.borderColor='var(--red)';this.style.color='var(--red)';" onmouseout="this.style.borderColor='#e8eaed';this.style.color='#64748b';">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                    Back
                </a>
                <div style="display:flex; flex-direction:column; align-items:flex-end; gap:4px;">
                    @if($contact->contact_number)
                    <a href="tel:{{ $contact->contact_number }}" style="font-size:10.5px; font-weight:700; color:#64748b; text-decoration:none; display:flex; align-items:center; gap:6px; transition:color .12s;" onmouseover="this.style.color='var(--red)'" onmouseout="this.style.color='#64748b'">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                        {{ $contact->contact_number }}
                    </a>
                    @endif
                    @if($contact->email)
                    <a href="mailto:{{ $contact->email }}" style="font-size:10.5px; font-weight:700; color:#64748b; text-decoration:none; display:flex; align-items:center; gap:6px; transition:color .12s;" onmouseover="this.style.color='var(--red)'" onmouseout="this.style.color='#64748b'">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                        {{ $contact->email }}
                    </a>
                    @endif
                </div>
            </div>

        </div>

        {{-- ===== STATS ROW ===== --}}
        <div class="fade d2" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
            <div class="stat">
                <p class="label" style="margin-bottom:0.5rem;">Items Supplied</p>
                <p style="font-size: 2.5rem; font-weight: 900; color: #0f172a; line-height: 1;" class="dark:text-slate-100">{{ $stats->total_supplied }}</p>
                <p class="label" style="margin-top:0.5rem; color:#c0c8d0;">Assigned in DepEd</p>
            </div>
            <div class="stat" style="grid-column: span 2; display:flex; flex-direction:column; justify-content:center;">
                <p class="label" style="margin-bottom:0.5rem;">Total Inventory Value</p>
                <p style="font-size: 2.5rem; font-weight: 900; color: var(--red); line-height: 1; font-style:italic;">₱ {{ number_format($stats->total_value, 2) }}</p>
                <p class="label" style="margin-top:0.5rem; color:#c0c8d0;">Cumulative procurement cost — all time</p>
            </div>
        </div>

        {{-- ===== ASSETS TABLE ===== --}}
        <div class="card fade d3" style="overflow: hidden; flex-grow: 1;">
            <div style="padding: 1.25rem 1.75rem; border-bottom: 1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <p class="label" style="margin-bottom:0.3rem;">Equipment Records</p>
                    <h2 style="font-size:1rem; font-weight:900; color:#0f172a; text-transform:uppercase; letter-spacing:-0.01em;" class="dark:text-slate-100">Supplied Assets</h2>
                </div>
                <span style="background:#f4f5f7; color:#64748b; border:1px solid #e8eaed; font-size:9px; font-weight:900; letter-spacing:0.12em; text-transform:uppercase; padding:5px 14px; border-radius:999px;">{{ $assets->count() }} items</span>
            </div>

            @if($assets->count() > 0)
            <div class="scroll" style="overflow-x:auto;">
                <table class="tbl" style="min-width:820px;">
                    <thead>
                        <tr>
                            <th style="width:40px; text-align:center;">#</th>
                            <th>Item</th>
                            <th>Property No.</th>
                            <th>Brand / Model</th>
                            <th>Deployed To</th>
                            <th style="text-align:center;">Status</th>
                            <th style="text-align:right;">Unit Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assets as $i => $asset)
                        <tr>
                            <td style="text-align:center; font-size:10px; font-weight:900; color:#cbd5e1;">{{ $i + 1 }}</td>
                            <td>
                                <span style="display:block; font-size:11.5px; font-weight:800; color:#1e293b; text-transform:uppercase;" class="dark:text-slate-200">{{ $asset->item_name }}</span>
                                <span style="display:block; font-size:9px; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-top:2px; letter-spacing:0.06em;">{{ $asset->category_name }}</span>
                            </td>
                            <td>
                                <span style="font-size:10.5px; font-weight:800; color:#334155; text-transform:uppercase; font-family:monospace; letter-spacing:0.03em;" class="dark:text-slate-300">{{ $asset->property_number }}</span>
                            </td>
                            <td>
                                <span style="font-size:10.5px; font-weight:700; color:#475569; text-transform:uppercase;" class="dark:text-slate-400">{{ $asset->brand ?: '—' }}</span>
                                @if($asset->model)
                                <span style="font-size:9px; color:#94a3b8; margin:0 4px;">·</span>
                                <span style="font-size:10px; font-weight:600; color:#64748b; text-transform:uppercase;" class="dark:text-slate-400">{{ $asset->model }}</span>
                                @endif
                            </td>
                            <td>
                                <span style="display:block; font-size:10.5px; font-weight:700; color:#1e293b; text-transform:uppercase; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" class="dark:text-slate-300">{{ $asset->school_name ?: '—' }}</span>
                                @if($asset->office_name)
                                <span style="display:block; font-size:9px; font-weight:600; color:#94a3b8; text-transform:uppercase; margin-top:2px; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $asset->office_name }}</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                @php
                                    $cond = $asset->condition ?: 'Good';
                                    $isGood = in_array($cond, ['Good', 'Serviceable']);
                                @endphp
                                <span style="padding:3px 10px; border-radius:999px; font-size:8px; font-weight:900; text-transform:uppercase; letter-spacing:0.1em; background:{{ $isGood ? '#f0fdf4' : '#fefce8' }}; color:{{ $isGood ? '#16a34a' : '#92400e' }}; border:1px solid {{ $isGood ? '#bbf7d0' : '#fde68a' }};">{{ $cond }}</span>
                            </td>
                            <td style="text-align:right;">
                                <span style="font-size:12px; font-weight:900; color:var(--red); font-style:italic;">₱ {{ number_format($asset->asset_cost, 2) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div style="padding: 4rem 2rem; text-align:center;">
                <p class="label" style="color:#c0c8d0;">No supplied assets recorded yet.</p>
            </div>
            @endif
        </div>

        <div style="height: 1.5rem;"></div>
    </div>

</body>
</html>
