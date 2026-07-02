<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Print QR Stickers | DepEd ZC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!-- qrcode.js for client-side QR generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* Custom Scrollbar Styles for modern premium feel */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* ── Checkbox custom ── */
        .asset-checkbox {
            appearance: none;
            -webkit-appearance: none;
            width: 16px;
            height: 16px;
            border: 2px solid #cbd5e1;
            border-radius: 4px;
            outline: none;
            background-color: #fff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        .asset-checkbox:checked {
            border-color: #c00000;
            background-color: #c00000;
        }
        .asset-checkbox:checked::after {
            content: '';
            width: 4px;
            height: 8px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
            margin-bottom: 2px;
        }

        /* ── Sticker card (95mm × 75mm, landscape waybill) ── */
        .sticker-card {
            width: 95mm;
            height: 75mm;
            border: 2px solid #c00000;
            padding: 0;
            box-sizing: border-box;
            page-break-inside: avoid;
            background: #fff;
            position: relative;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ── Long bond paper container (216mm × 279mm - Letter, 2 columns of 2×4") ── */
        .bond-paper {
            width: 216mm;
            height: 279mm;
            margin: 0 auto;
            background: white;
            padding: 5mm 4mm;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.15);
            display: grid;
            grid-template-columns: repeat(2, 101.6mm);
            grid-template-rows: repeat(5, 50.8mm);
            gap: 0.8mm 1mm;
            box-sizing: border-box;
        }

        /* ── Print media (default: bond paper) ── */
        @media print {
            @page { size: 216mm 279mm; margin: 0; }
            .no-print { display: none !important; }
            body { background: white; margin: 0; padding: 0; }
            .bond-paper { box-shadow: none; margin: 0; }
            .print-area { padding: 0; }
        }

        /* ── Selection panel ── */
        .asset-row {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border-left: 4px solid transparent;
            animation: slideUp 0.4s ease-out forwards;
        }
        .asset-row:hover {
            transform: translateY(-1.5px) scale-[1.002];
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.03);
            background: rgba(248, 250, 252, 0.8) !important;
        }
        .asset-row.selected {
            background: linear-gradient(to right, rgba(255, 245, 245, 0.85), rgba(255, 255, 255, 0.5)) !important;
            border-left: 4px solid #c00000;
        }
        .selected-badge { animation: pop 0.25s cubic-bezier(0.34, 1.56, 0.64, 1); }
        @keyframes pop { 0%,100% { transform: scale(1); } 50% { transform: scale(1.2); } }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-800 overflow-x-hidden">

@include('partials.sidebar')

<div class="flex-grow flex flex-col min-w-0 h-screen overflow-hidden bg-slate-50">

    {{-- ── Fixed Top Bar ── --}}
    <div class="no-print sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-slate-200/60 shadow-[0_4px_30px_rgba(0,0,0,0.02)] px-8 py-5 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="h-8 w-1 bg-gradient-to-b from-[#c00000] to-rose-600 rounded-full"></div>
            <div>
                <h1 class="text-base font-black tracking-tight text-slate-900 uppercase italic leading-none">Print QR Stickers</h1>
                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-[0.15em] mt-1.5">Select assets <span class="text-slate-300">→</span> Generate Stickers</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span id="selectedCount" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-rose-50/50 text-[#c00000] border border-rose-100/60 rounded-full text-[10px] font-black uppercase tracking-wider shadow-sm transition-all duration-300">
                <span id="countNum" class="text-xs">0</span> Selected
            </span>
            <button onclick="showPreview('sheet')" id="previewBtn"
                class="px-6 py-3 bg-gradient-to-r from-red-600 to-rose-700 hover:from-red-500 hover:to-rose-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-md hover:shadow-lg hover:shadow-red-500/15 transition-all duration-300 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed disabled:pointer-events-none flex items-center gap-2"
                disabled>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.227v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM13.5 16.5a.75.75 0 01.75-.75h.75a.75.75 0 01.75.75v.75a.75.75 0 01-.75.75h-.75a.75.75 0 01-.75-.75v-.75zM13.5 13.5a.75.75 0 01.75-.75h.75a.75.75 0 01.75.75v.75a.75.75 0 01-.75.75h-.75a.75.75 0 01-.75-.75v-.75zM16.5 13.5a.75.75 0 01.75-.75h.75a.75.75 0 01.75.75v.75a.75.75 0 01-.75.75h-.75a.75.75 0 01-.75-.75v-.75zM16.5 16.5a.75.75 0 01.75-.75h.75a.75.75 0 01.75.75v.75a.75.75 0 01-.75.75h-.75a.75.75 0 01-.75-.75v-.75zM19.5 13.5a.75.75 0 01.75-.75h.75a.75.75 0 01.75.75v.75a.75.75 0 01-.75.75h-.75a.75.75 0 01-.75-.75v-.75zM19.5 16.5a.75.75 0 01.75-.75h.75a.75.75 0 01.75.75v.75a.75.75 0 01-.75.75h-.75a.75.75 0 01-.75-.75v-.75z"/></svg>
                Print Sheets (Bond Paper)
            </button>
            <button onclick="showPreview('waybill')" id="waybillBtn"
                class="px-6 py-3 bg-gradient-to-r from-slate-800 to-zinc-900 hover:from-slate-700 hover:to-zinc-800 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-md hover:shadow-lg hover:shadow-slate-800/15 transition-all duration-300 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed disabled:pointer-events-none flex items-center gap-2"
                disabled>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Waybill Sticker Printing
            </button>
        </div>
    </div>

    {{-- ── Main Content ── --}}
    <div class="flex w-full gap-0 h-[calc(100vh-73px)] overflow-hidden">

        {{-- ── LEFT: Asset Selector ── --}}
        <div id="selectorPanel" class="no-print w-full p-8 border-r border-slate-200/60 overflow-y-auto h-full transition-all duration-300">

            {{-- Search & Filters --}}
            <div class="flex gap-3 mb-8">
                <div class="relative flex-1 group">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[#c00000] transition-colors duration-300"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input id="searchInput" type="text" placeholder="Search property number, item, serial..." class="w-full pl-11 pr-4 py-3 bg-slate-50/50 hover:bg-slate-50 border border-slate-200 rounded-2xl text-[11px] font-bold text-slate-700 outline-none focus:bg-white focus:border-[#c00000] focus:ring-4 focus:ring-red-50 transition-all duration-300 shadow-inner">
                </div>
                <button onclick="selectAll()" class="px-6 py-3 bg-slate-50 hover:bg-slate-100 hover:border-slate-300 text-slate-700 rounded-2xl text-[10px] font-black uppercase tracking-wider transition-all duration-300 shadow-sm border border-slate-200 active:scale-95">Select All</button>
                <button onclick="clearAll()" class="px-6 py-3 bg-slate-50 hover:bg-red-50/50 hover:text-red-600 hover:border-red-100 text-slate-500 rounded-2xl text-[10px] font-black uppercase tracking-wider transition-all duration-300 shadow-sm border border-slate-200 active:scale-95">Clear</button>
            </div>

            {{-- Dummy loadingState to prevent JS errors --}}
            <div id="loadingState" class="hidden"></div>

            {{-- Asset Table --}}
            <div id="assetTableWrap" class="bg-white rounded-3xl border border-slate-150 shadow-[0_10px_30px_rgba(0,0,0,0.02)] overflow-hidden flex flex-col transition-all duration-300">
                <div class="px-6 py-4 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Asset Records</p>
                    <span id="tableCount" class="text-[10px] font-black text-[#c00000] bg-red-50 px-3 py-1 rounded-full border border-red-100/50"></span>
                </div>
                <div class="overflow-x-auto overflow-y-auto max-h-[65vh]">
                    <table class="w-full text-left">
                        <thead class="sticky top-0 bg-slate-50/90 backdrop-blur-md z-10 border-b border-slate-100">
                            <tr>
                                <th class="px-5 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest w-8">
                                    <input type="checkbox" id="checkAll" class="asset-checkbox" onchange="toggleAll(this)">
                                </th>
                                <th class="px-5 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Property No.</th>
                                <th class="px-5 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Item / Description</th>
                                <th class="px-5 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Serial No.</th>
                                <th class="px-5 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Location</th>
                                <th class="px-5 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Condition</th>
                            </tr>
                        </thead>
                        <tbody id="assetTableBody" class="divide-y divide-slate-50"></tbody>
                    </table>
                </div>
            </div>

            {{-- Empty State --}}
            <div id="emptyState" class="hidden flex flex-col items-center justify-center py-20 gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-slate-200"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                <p class="text-sm font-bold text-slate-400">No assets found</p>
            </div>
        </div>

        {{-- ── RIGHT: Preview Panel (modern light workspace layout) ── --}}
        <div id="previewPanel" class="hidden w-2/5 flex-col bg-slate-50 p-8 overflow-y-auto h-full border-l border-slate-200/80 transition-all duration-300 relative">
            <div class="flex items-center justify-between mb-8 border-b border-slate-200/60 pb-5">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
                    <p id="previewPanelTitle" class="text-[10px] font-black text-slate-800 uppercase tracking-widest italic leading-none">Bond Paper Preview</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="closePreview()" class="px-4 py-2 border border-slate-250 bg-white hover:bg-slate-50 text-slate-700 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all shadow-sm active:scale-[0.98]">
                        Cancel
                    </button>
                    <button id="printActionBtn" onclick="window.print()" class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-red-600 to-rose-700 hover:from-red-500 hover:to-rose-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-md hover:shadow-lg hover:shadow-red-500/15 active:scale-[0.98]">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0v2.796c0 1.176.836 2.19 2.013 2.342a40.52 40.52 0 006.474 0 2.25 2.25 0 002.013-2.342V9.034z"/></svg>
                        <span id="printBtnText">Print Sheets</span>
                    </button>
                </div>
            </div>
            <div id="bondPaperPreview" class="scale-[0.55] origin-top-left" style="width:182%;">
                {{-- Bond paper pages rendered here by JS --}}
            </div>
        </div>
    </div>
</div>

{{-- ── PRINT AREA (hidden on screen, shown when printing) ── --}}
<div id="printArea" class="print-area print-only">
    {{-- Bond paper pages generated by JS --}}
</div>

<script>
/* ═══════════════════════════════════════
   DATA & STATE
═══════════════════════════════════════ */
let allAssets = @json($assets);
let selectedIds = new Set();

/* ═══════════════════════════════════════
   BOOTSTRAP
═══════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    // loadAssets preloaded
    document.getElementById('searchInput').addEventListener('input', renderTable);
    renderTable();
});

async function loadAssets(isRetry = false) {
    // Delay by 1.5s on first load so the initial page render completes
    // before firing the AJAX — prevents single-threaded server lock on php artisan serve
    if (!isRetry) {
        await new Promise(r => setTimeout(r, 1500));
    }

    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 20000); // 20s timeout

    try {
        const res = await fetch('/api/assets/print-list', {
            signal: controller.signal,
            headers: { 'Accept': 'application/json' }
        });
        clearTimeout(timeout);

        if (!res.ok) throw new Error(`Server error: ${res.status} ${res.statusText}`);

        const data = await res.json();
        allAssets = data.assets || [];
        renderTable();
    } catch (e) {
        clearTimeout(timeout);
        const msg = e.name === 'AbortError' ? 'Request timed out. Server may be busy.' : (e.message || 'Unknown error');
        document.getElementById('loadingState').innerHTML = `
            <div class="flex flex-col items-center gap-2 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-red-300">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
                <p class="text-[11px] font-black text-red-500 uppercase tracking-widest">Failed to load assets</p>
                <p class="text-[10px] text-slate-400 font-semibold max-w-xs">${msg}</p>
                <button onclick="loadAssets(true)" class="mt-1 px-5 py-2 bg-[#c00000] text-white rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-red-800 transition-all">Retry</button>
            </div>
        `;
    }
}

/* ═══════════════════════════════════════
   RENDER TABLE
═══════════════════════════════════════ */
function renderTable() {
    const q = document.getElementById('searchInput').value.toLowerCase().trim();
    const filtered = allAssets.filter(a => {
        if (!q) return true;
        return (a.property_number||'').toLowerCase().includes(q) ||
               (a.item_name||'').toLowerCase().includes(q) ||
               (a.description||'').toLowerCase().includes(q) ||
               (a.serial_number||'').toLowerCase().includes(q) ||
               (a.location||'').toLowerCase().includes(q);
    });

    document.getElementById('loadingState').classList.add('hidden');

    if (filtered.length === 0) {
        document.getElementById('assetTableWrap').classList.add('hidden');
        document.getElementById('emptyState').classList.remove('hidden');
        return;
    }

    document.getElementById('emptyState').classList.add('hidden');
    document.getElementById('assetTableWrap').classList.remove('hidden');
    document.getElementById('tableCount').textContent = `${filtered.length} records`;

    const tbody = document.getElementById('assetTableBody');
    tbody.innerHTML = filtered.map(a => {
        const checked = selectedIds.has(a.id);
        return `<tr class="asset-row border-b border-slate-100/50 hover:bg-slate-50/80 ${checked ? 'selected' : ''}" onclick="toggleRow(${a.id})" data-id="${a.id}">
            <td class="px-5 py-4">
                <input type="checkbox" class="asset-checkbox w-4 h-4 rounded" ${checked ? 'checked' : ''} onclick="event.stopPropagation(); toggleRow(${a.id})">
            </td>
            <td class="px-5 py-4">
                <span class="font-black text-[11.5px] text-slate-800 tracking-tight">${a.property_number || '<span class="text-slate-300 italic">No # yet</span>'}</span>
            </td>
            <td class="px-5 py-4">
                <p class="font-black text-[11.5px] text-slate-800">${a.item_name || '—'}</p>
                <p class="text-[10px] text-slate-400 font-medium leading-relaxed mt-0.5">${a.description || ''}</p>
            </td>
            <td class="px-5 py-4 font-mono text-[10px] font-bold text-slate-500">${a.serial_number || '—'}</td>
            <td class="px-5 py-4 text-[10px] font-bold text-slate-500">${a.location || '—'}</td>
            <td class="px-5 py-4">
                <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-wider border ${conditionClass(a.condition)}">${a.condition || 'N/A'}</span>
            </td>
        </tr>`;
    }).join('');
}

function conditionClass(c) {
    if (!c) return 'bg-slate-50 text-slate-500 border-slate-200/50';
    const l = c.toLowerCase();
    if (l.includes('service')) return 'bg-emerald-50/70 text-emerald-700 border-emerald-100';
    if (l.includes('repair')) return 'bg-amber-50/70 text-amber-700 border-amber-100';
    if (l.includes('condemn') || l.includes('dispose')) return 'bg-rose-50/70 text-rose-700 border-rose-100';
    return 'bg-slate-50 text-slate-500 border-slate-200/50';
}

/* ═══════════════════════════════════════
   SELECTION
═══════════════════════════════════════ */
function toggleRow(id) {
    const row = document.querySelector(`.asset-row[data-id="${id}"]`);
    if (!row) return;
    
    const checkbox = row.querySelector('.asset-checkbox');
    const isSelected = selectedIds.has(id);
    
    if (isSelected) {
        selectedIds.delete(id);
        row.classList.remove('selected');
        if (checkbox) checkbox.checked = false;
    } else {
        selectedIds.add(id);
        row.classList.add('selected');
        if (checkbox) checkbox.checked = true;
    }
    
    // Update checkAll header checkbox
    const q = document.getElementById('searchInput').value.toLowerCase().trim();
    const filtered = allAssets.filter(a => {
        if (!q) return true;
        return (a.property_number||'').toLowerCase().includes(q) ||
               (a.item_name||'').toLowerCase().includes(q) ||
               (a.description||'').toLowerCase().includes(q) ||
               (a.serial_number||'').toLowerCase().includes(q) ||
               (a.location||'').toLowerCase().includes(q);
    });
    const allFilteredSelected = filtered.every(a => selectedIds.has(a.id));
    const checkAll = document.getElementById('checkAll');
    if (checkAll) {
        checkAll.checked = filtered.length > 0 && allFilteredSelected;
    }

    updateCount();
}

function toggleAll(cb) {
    const q = document.getElementById('searchInput').value.toLowerCase().trim();
    const filtered = allAssets.filter(a => {
        if (!q) return true;
        return (a.property_number||'').toLowerCase().includes(q) ||
               (a.item_name||'').toLowerCase().includes(q) ||
               (a.description||'').toLowerCase().includes(q) ||
               (a.serial_number||'').toLowerCase().includes(q) ||
               (a.location||'').toLowerCase().includes(q);
    });
    
    filtered.forEach(a => {
        const row = document.querySelector(`.asset-row[data-id="${a.id}"]`);
        const checkbox = row ? row.querySelector('.asset-checkbox') : null;
        if (cb.checked) {
            selectedIds.add(a.id);
            if (row) row.classList.add('selected');
            if (checkbox) checkbox.checked = true;
        } else {
            selectedIds.delete(a.id);
            if (row) row.classList.remove('selected');
            if (checkbox) checkbox.checked = false;
        }
    });
    updateCount();
}

function selectAll() {
    allAssets.forEach(a => {
        selectedIds.add(a.id);
        const row = document.querySelector(`.asset-row[data-id="${a.id}"]`);
        const checkbox = row ? row.querySelector('.asset-checkbox') : null;
        if (row) row.classList.add('selected');
        if (checkbox) checkbox.checked = true;
    });
    const checkAll = document.getElementById('checkAll');
    if (checkAll) checkAll.checked = true;
    updateCount();
}

function clearAll() {
    selectedIds.clear();
    const rows = document.querySelectorAll('.asset-row');
    rows.forEach(row => {
        row.classList.remove('selected');
        const checkbox = row.querySelector('.asset-checkbox');
        if (checkbox) checkbox.checked = false;
    });
    const checkAll = document.getElementById('checkAll');
    if (checkAll) checkAll.checked = false;
    updateCount();
}

function updateCount() {
    const n = selectedIds.size;
    document.getElementById('countNum').textContent = n;
    document.getElementById('previewBtn').disabled = n === 0;
    const waybillBtn = document.getElementById('waybillBtn');
    if (waybillBtn) waybillBtn.disabled = n === 0;
}

/* ═══════════════════════════════════════
   PREVIEW & PRINT
═══════════════════════════════════════ */
let printMode = 'sheet';

function showPreview(mode) {
    printMode = mode || 'sheet';
    const selected = allAssets.filter(a => selectedIds.has(a.id));
    if (selected.length === 0) return;

    // Clean up dynamic waybill printing stylesheet if existing
    const oldStyle = document.getElementById('waybill-print-style');
    if (oldStyle) oldStyle.remove();

    let previewHTML = '';
    let printHTML = '';

    if (printMode === 'waybill') {
        // Setup dynamic style for waybill printing
        // Key: use explicit `size: 4in 2in` (width > height = landscape)
        // DO NOT use the `landscape` keyword — Chrome ignores it when
        // paper size is pre-selected in the print dialog.
        const styleEl = document.createElement('style');
        styleEl.id = 'waybill-print-style';
        styleEl.innerHTML = `
            @media print {
                @page {
                    size: 95mm 75mm;
                    margin: 0;
                }
                html, body { width: 95mm; height: 75mm; margin: 0 !important; padding: 0 !important; }
                body > * { display: none !important; }
                #printArea { display: block !important; }
                #printArea * { box-sizing: border-box; }
                .waybill-sticker-wrapper {
                    width: 95mm !important;
                    height: 75mm !important;
                    page-break-after: always !important;
                    break-after: page !important;
                    display: block !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    overflow: hidden !important;
                }
                .sticker-card {
                    width: 95mm !important;
                    height: 75mm !important;
                    border: none !important;
                }
            }
        `;
        document.head.appendChild(styleEl);

        document.getElementById('previewPanelTitle').textContent = 'Waybill Sticker Preview';
        document.getElementById('printBtnText').textContent = 'Print Stickers';

        previewHTML = buildWaybillPreviewHTML(selected);
        printHTML = buildWaybillPrintHTML(selected);
    } else {
        document.getElementById('previewPanelTitle').textContent = 'Bond Paper Preview';
        document.getElementById('printBtnText').textContent = 'Print Sheets';

        // Generate bond paper pages (3 columns × 7 rows = 21 stickers per page)
        const STICKERS_PER_PAGE = 21;
        const chunks = [];
        for (let i = 0; i < selected.length; i += STICKERS_PER_PAGE) {
            chunks.push(selected.slice(i, i + STICKERS_PER_PAGE));
        }

        previewHTML = chunks.map(page => buildBondPaperHTML(page)).join('');
        printHTML = previewHTML;
    }

    // Update preview panel
    document.getElementById('bondPaperPreview').innerHTML = previewHTML;
    
    // Show preview panel and resize selector panel
    const previewPanel = document.getElementById('previewPanel');
    previewPanel.classList.remove('hidden');
    previewPanel.classList.add('flex');

    const selectorPanel = document.getElementById('selectorPanel');
    if (selectorPanel) {
        selectorPanel.classList.add('xl:w-3/5');
    }

    // Update print area
    document.getElementById('printArea').innerHTML = printHTML;

    // Generate QRs after DOM renders
    setTimeout(() => generateAllQRs(selected), 100);
}

function closePreview() {
    const previewPanel = document.getElementById('previewPanel');
    previewPanel.classList.remove('flex');
    previewPanel.classList.add('hidden');

    const selectorPanel = document.getElementById('selectorPanel');
    if (selectorPanel) {
        selectorPanel.classList.remove('xl:w-3/5');
    }

    document.getElementById('bondPaperPreview').innerHTML = '';
    document.getElementById('printArea').innerHTML = '';
    const oldStyle = document.getElementById('waybill-print-style');
    if (oldStyle) oldStyle.remove();
}

function buildBondPaperHTML(assets) {
    const stickersHTML = assets.map(a => buildStickerHTML(a)).join('');
    return `<div class="bond-paper" style="margin-bottom:10mm;">${stickersHTML}</div>`;
}

function buildWaybillPreviewHTML(assets) {
    const stickersHTML = assets.map(a => `
        <div class="waybill-sticker-wrapper shadow-md bg-white mb-4 overflow-hidden" style="width: 95mm; height: 75mm; display: block;">
            ${buildStickerHTML(a)}
        </div>
    `).join('');
    return `<div class="waybill-preview-wrapper flex flex-col items-center gap-4 py-2">${stickersHTML}</div>`;
}

function buildWaybillPrintHTML(assets) {
    return assets.map(a => `
        <div class="waybill-sticker-wrapper">
            ${buildStickerHTML(a)}
        </div>
    `).join('');
}

function buildStickerHTML(a) {
    const propNum = (a.property_number || 'N/A').toUpperCase();
    const itemBrand = [a.item_name, a.brand, a.model].filter(Boolean).map(s => s.toUpperCase()).join(' / ');
    const serial = (a.serial_number || 'N/A').toUpperCase();

    // Auto-scale font size if itemBrand is long to prevent overflow
    let itemBrandFontSize = '10.5px';
    if (itemBrand.length > 50) {
        itemBrandFontSize = '7.5px';
    } else if (itemBrand.length > 32) {
        itemBrandFontSize = '8.5px';
    } else if (itemBrand.length > 20) {
        itemBrandFontSize = '9.5px';
    }

    // Auto-scale property number font size
    let propNumFontSize = '10.5px';
    if (propNum.length > 25) {
        propNumFontSize = '8px';
    } else if (propNum.length > 18) {
        propNumFontSize = '9px';
    }

    // Auto-scale serial number font size
    let serialFontSize = '10.5px';
    if (serial.length > 25) {
        serialFontSize = '8px';
    } else if (serial.length > 18) {
        serialFontSize = '9px';
    }

    return `<div class="sticker-card" style="width: 95mm; height: 75mm; border: 2.5px solid #c00000; padding: 0; box-sizing: border-box; background: white; display: flex; flex-direction: column; overflow: hidden; font-family: Arial, sans-serif; position: relative;">
        <!-- Header -->
        <div style="display: flex; align-items: center; border-bottom: 1.5px solid #c00000; padding: 5px 8px; gap: 6px; background: #fff; flex-shrink: 0; min-height: 46px; box-sizing: border-box; overflow: hidden;">
            <img src="/images/deped_logo.png" style="height: 34px; width: auto; flex-shrink: 0;" onerror="this.style.display='none'">
            <div style="flex-grow: 1; text-align: center; display: flex; flex-direction: column; justify-content: center; line-height: 1.2;">
                <div style="font-size: 7.5px; color: #000; font-weight: normal; font-family: Arial, sans-serif;">Republic of the Philippines</div>
                <div style="font-size: 7.5px; color: #000; font-weight: normal; font-family: Arial, sans-serif;">Department of Education</div>
                <div style="font-size: 9.5px; color: #1e3a8a; font-weight: bold; font-family: Arial, sans-serif; letter-spacing: 0.1px;">DIVISION OF ZAMBOANGA CITY</div>
                <div style="font-size: 7.5px; color: #000; font-weight: normal; font-family: Arial, sans-serif;">Region IX-Zamboanga Peninsula</div>
            </div>
        </div>

        <!-- Title -->
        <div style="text-align: center; color: #c00000; font-size: 9px; font-weight: bold; letter-spacing: 0.5px; padding: 4px 0; border-bottom: 1.5px solid #c00000; background: #fff; text-transform: uppercase; font-family: Arial, sans-serif; flex-shrink: 0; box-sizing: border-box;">
            PROPERTY INVENTORY STICKER
        </div>

        <!-- Body -->
        <div style="display: flex; flex-grow: 1; background: #fff; overflow: hidden; box-sizing: border-box;">
            <!-- Left Side (Property details) -->
            <div style="display: flex; flex-direction: column; width: 62%; border-right: 1px solid #cbd5e1; box-sizing: border-box;">
                <!-- Row 1: Property Number -->
                <div style="flex: 3; border-bottom: 1px solid #cbd5e1; padding: 4px 7px; display: flex; flex-direction: column; justify-content: center; box-sizing: border-box; overflow: hidden;">
                    <div style="font-size: 7.5px; font-weight: bold; color: #64748b; letter-spacing: 0.3px; text-transform: uppercase; line-height: 1;">PROPERTY NUMBER</div>
                    <div style="font-size: ${propNumFontSize}; font-weight: bold; color: #000; line-height: 1.2; word-break: break-all; margin-top: 2px;">${propNum}</div>
                </div>
                <!-- Row 2: Item Brand Model -->
                <div style="flex: 5; border-bottom: 1px solid #cbd5e1; padding: 4px 7px; display: flex; flex-direction: column; justify-content: center; box-sizing: border-box; overflow: hidden;">
                    <div style="font-size: 7.5px; font-weight: bold; color: #64748b; letter-spacing: 0.3px; text-transform: uppercase; line-height: 1;">ITEM/BRAND/MODEL</div>
                    <div style="font-size: ${itemBrandFontSize}; font-weight: bold; color: #000; line-height: 1.2; word-break: break-word; margin-top: 2px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;">${itemBrand}</div>
                </div>
                <!-- Row 3: Serial Number -->
                <div style="flex: 3; padding: 4px 7px; display: flex; flex-direction: column; justify-content: center; box-sizing: border-box; overflow: hidden;">
                    <div style="font-size: 7.5px; font-weight: bold; color: #64748b; letter-spacing: 0.3px; text-transform: uppercase; line-height: 1;">SERIAL NUMBER</div>
                    <div style="font-size: ${serialFontSize}; font-weight: bold; color: #000; line-height: 1.2; word-break: break-all; margin-top: 2px;">${serial}</div>
                </div>
            </div>

            <!-- Right Side (QR & Scan Me) -->
            <div style="width: 38%; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 8px 6px; box-sizing: border-box; background: #fff; overflow: hidden; gap: 4px;">
                <div id="qr-${a.id}" style="width: 72px; height: 72px; display: flex; align-items: center; justify-content: center; background: #fff; overflow: hidden; flex-shrink: 0;"></div>
                <div style="font-size: 8px; font-weight: bold; color: #64748b; letter-spacing: 0.3px; text-transform: uppercase; line-height: 1; text-align: center;">SCAN ME</div>
                <div style="font-size: 8px; font-weight: bold; color: #000; line-height: 1; text-align: center;">FOR INFO</div>
            </div>
        </div>

        <!-- Footer -->
        <div style="border-top: 1.5px solid #c00000; padding: 5px 6px; text-align: center; line-height: 1.1; background: #fff; box-sizing: border-box; flex-shrink: 0; min-height: 26px; display: flex; flex-direction: column; justify-content: center; overflow: hidden;">
            <div style="font-size: 9px; font-weight: bold; color: #c00000; letter-spacing: 0.3px; text-transform: uppercase; font-family: Arial, sans-serif; line-height: 1;">DO NOT REMOVE UNDER PENALTY OF LAW</div>
        </div>
    </div>`;
}

function generateAllQRs(assets) {
    assets.forEach(a => {
        const els = document.querySelectorAll(`#qr-${a.id}`);
        const qrData = `${window.location.origin}/assets/${a.id}/profile`;
        els.forEach(el => {
            el.innerHTML = '';
            try {
                new QRCode(el, {
                    text: qrData,
                    width: 72,
                    height: 72,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
            } catch(e) { el.innerHTML = '<div style="font-size:5pt;color:#aaa;">QR Error</div>'; }
        });
    });
}

/* ── Print override: ensure print area shows ── */
window.addEventListener('beforeprint', () => {
    document.getElementById('printArea').classList.remove('hidden');
});
window.addEventListener('afterprint', () => {
    // keep it
});
</script>

<style>
.print-only { display: none !important; }
/* Print: show only the print area */
@media print {
    body > * { display: none !important; }
    #printArea { display: block !important; }
    .print-only { display: block !important; }
    .sticker-card { border-color: #c00000 !important; }
    .bond-paper { box-shadow: none !important; }
}
body { display: flex; }
body > div:first-child { flex-shrink: 0; }
</style>
</body>
</html>
