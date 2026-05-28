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

        /* ── Checkbox custom ── */
        .asset-checkbox:checked { accent-color: #c00000; }

        /* ── Sticker card (64mm × 60mm) ── */
        .sticker-card {
            width: 64mm;
            height: 60mm;
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

        /* ── Long bond paper container (216mm × 330mm) ── */
        .bond-paper {
            width: 216mm;
            height: 330mm;
            margin: 0 auto;
            background: white;
            padding: 7mm 8mm;
            box-shadow: 0 10px 40px rgba(0,0,0,0.12);
            display: grid;
            grid-template-columns: repeat(3, 64mm);
            grid-template-rows: repeat(5, 60mm);
            gap: 3mm;
            box-sizing: border-box;
        }

        /* ── Print media ── */
        @media print {
            .no-print { display: none !important; }
            body { background: white; margin: 0; padding: 0; }
            .bond-paper { box-shadow: none; margin: 0; }
            .print-area { padding: 0; }
        }

        /* ── Sticker inner layout ── */
        .sticker-header { display: flex; align-items: center; gap: 3mm; border-bottom: 1px solid #16a34a; padding-bottom: 2mm; margin-bottom: 2mm; }
        .sticker-logo { width: 12mm; height: auto; }
        .sticker-agency { font-size: 5.5pt; color: #1e3a5f; text-align: right; line-height: 1.3; }
        .sticker-agency strong { display: block; font-size: 6.5pt; color: #c00000; }
        .sticker-title { font-size: 8pt; font-weight: 900; color: #c00000; text-align: center; text-transform: uppercase; letter-spacing: 0.5px; margin: 1mm 0; }
        .sticker-body { display: flex; gap: 3mm; flex: 1; }
        .sticker-info { flex: 1; }
        .sticker-label { font-size: 5pt; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .sticker-value { font-size: 7.5pt; font-weight: 800; color: #0f172a; line-height: 1.2; margin-bottom: 1.5mm; }
        .sticker-qr-wrap { display: flex; flex-direction: column; align-items: center; justify-content: center; min-width: 22mm; }
        .sticker-qr-wrap canvas, .sticker-qr-wrap img { width: 22mm !important; height: 22mm !important; }
        .sticker-scan-label { font-size: 5pt; font-weight: 700; color: #334155; text-align: center; margin-top: 1mm; }
        .sticker-footer { border-top: 1px solid #16a34a; margin-top: 2mm; padding-top: 1.5mm; text-align: center; font-size: 5.5pt; font-weight: 700; color: #c00000; text-transform: uppercase; }
        .sticker-footer small { display: block; color: #64748b; font-size: 4.5pt; font-style: italic; font-weight: 400; }

        /* ── Selection panel ── */
        .asset-row { transition: background 0.15s; cursor: pointer; }
        .asset-row:hover { background: #fef2f2; }
        .asset-row.selected { background: #fef2f2; border-left: 3px solid #c00000; }
        .selected-badge { animation: pop 0.2s ease; }
        @keyframes pop { 0%,100% { transform: scale(1); } 50% { transform: scale(1.15); } }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800">

@include('partials.sidebar')

<div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto" style="margin-left: 0;">

    {{-- ── Fixed Top Bar ── --}}
    <div class="no-print sticky top-0 z-50 bg-white border-b border-slate-100 shadow-sm px-6 py-3 flex items-center justify-between gap-4" style="margin-left:80px;">
        <div class="flex items-center gap-4">
            <a href="{{ route('assets.view') }}" class="text-slate-400 hover:text-[#c00000] transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-black tracking-tight text-slate-900 uppercase italic">Print QR Stickers</h1>
                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-widest">Select assets → preview → print</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span id="selectedCount" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 text-[#c00000] border border-red-100 rounded-full text-[11px] font-black">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span id="countNum">0</span> Selected
            </span>
            <button onclick="showPreview()" id="previewBtn"
                class="px-5 py-2 bg-[#c00000] text-white rounded-xl text-[11px] font-black uppercase tracking-wider hover:bg-red-800 transition-all shadow-sm disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2"
                disabled>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Preview & Print
            </button>
        </div>
    </div>

    {{-- ── Main Content ── --}}
    <div class="flex gap-0 h-full" style="margin-left:80px;">

        {{-- ── LEFT: Asset Selector ── --}}
        <div class="no-print w-full xl:w-3/5 p-6 border-r border-slate-100 overflow-y-auto">

            {{-- Search & Filters --}}
            <div class="flex gap-3 mb-5">
                <div class="relative flex-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input id="searchInput" type="text" placeholder="Search property number, item, serial..." class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-[11px] font-semibold outline-none focus:border-[#c00000] focus:ring-2 focus:ring-red-100 transition-all">
                </div>
                <button onclick="selectAll()" class="px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-[11px] font-black text-slate-600 hover:border-[#c00000] hover:text-[#c00000] transition-all">Select All</button>
                <button onclick="clearAll()" class="px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-[11px] font-black text-slate-600 hover:border-red-200 hover:text-red-400 transition-all">Clear</button>
            </div>

            {{-- Loading State --}}
            <div id="loadingState" class="flex flex-col items-center justify-center py-20 gap-3">
                <div class="w-8 h-8 border-4 border-red-100 border-t-[#c00000] rounded-full animate-spin"></div>
                <p class="text-[11px] text-slate-400 font-semibold">Loading assets...</p>
            </div>

            {{-- Asset Table --}}
            <div id="assetTableWrap" class="hidden bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Asset Records</p>
                    <span id="tableCount" class="text-[10px] font-bold text-slate-500"></span>
                </div>
                <div class="overflow-x-auto max-h-[65vh] overflow-y-auto">
                    <table class="w-full text-left">
                        <thead class="sticky top-0 bg-slate-50 z-10">
                            <tr>
                                <th class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest w-8">
                                    <input type="checkbox" id="checkAll" class="asset-checkbox w-4 h-4 rounded" onchange="toggleAll(this)">
                                </th>
                                <th class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">Property No.</th>
                                <th class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">Item / Description</th>
                                <th class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">Serial No.</th>
                                <th class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">Location</th>
                                <th class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">Condition</th>
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

        {{-- ── RIGHT: Preview Panel (hidden on small screens, shown before print) ── --}}
        <div id="previewPanel" class="hidden xl:flex w-2/5 flex-col bg-slate-100 p-4 overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Bond Paper Preview</p>
                <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 bg-[#c00000] text-white rounded-xl text-[11px] font-black hover:bg-red-800 transition-all shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0v2.796c0 1.176.836 2.19 2.013 2.342a40.52 40.52 0 006.474 0 2.25 2.25 0 002.013-2.342V9.034z"/></svg>
                    Print
                </button>
            </div>
            <div id="bondPaperPreview" class="scale-[0.55] origin-top-left" style="width:182%;">
                {{-- Bond paper pages rendered here by JS --}}
            </div>
        </div>
    </div>
</div>

{{-- ── PRINT AREA (hidden on screen, shown when printing) ── --}}
<div id="printArea" class="print-area hidden">
    {{-- Bond paper pages generated by JS --}}
</div>

<script>
/* ═══════════════════════════════════════
   DATA & STATE
═══════════════════════════════════════ */
let allAssets = [];
let selectedIds = new Set();

/* ═══════════════════════════════════════
   BOOTSTRAP
═══════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    loadAssets();
    document.getElementById('searchInput').addEventListener('input', renderTable);
});

async function loadAssets() {
    try {
        const res = await fetch('/api/assets/print-list', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        if (!res.ok) throw new Error('Failed to load');
        const data = await res.json();
        allAssets = data.assets || [];
        renderTable();
    } catch (e) {
        document.getElementById('loadingState').innerHTML = `<p class="text-red-500 font-bold text-sm">Error loading assets. Please refresh.</p>`;
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
        return `<tr class="asset-row ${checked ? 'selected' : ''}" onclick="toggleRow(${a.id})" data-id="${a.id}">
            <td class="px-4 py-3">
                <input type="checkbox" class="asset-checkbox w-4 h-4 rounded" ${checked ? 'checked' : ''} onclick="event.stopPropagation(); toggleRow(${a.id})">
            </td>
            <td class="px-4 py-3">
                <span class="font-black text-[11px] text-slate-800">${a.property_number || '<span class="text-slate-300 italic">No # yet</span>'}</span>
            </td>
            <td class="px-4 py-3">
                <p class="font-bold text-[11px] text-slate-800">${a.item_name || '—'}</p>
                <p class="text-[10px] text-slate-400">${a.description || ''}</p>
            </td>
            <td class="px-4 py-3 font-mono text-[10px] text-slate-600">${a.serial_number || '—'}</td>
            <td class="px-4 py-3 text-[10px] text-slate-500">${a.location || '—'}</td>
            <td class="px-4 py-3">
                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase ${conditionClass(a.condition)}">${a.condition || 'N/A'}</span>
            </td>
        </tr>`;
    }).join('');
}

function conditionClass(c) {
    if (!c) return 'bg-slate-100 text-slate-500';
    const l = c.toLowerCase();
    if (l.includes('service')) return 'bg-emerald-50 text-emerald-700';
    if (l.includes('repair')) return 'bg-amber-50 text-amber-700';
    if (l.includes('condemn') || l.includes('dispose')) return 'bg-red-50 text-red-700';
    return 'bg-slate-100 text-slate-500';
}

/* ═══════════════════════════════════════
   SELECTION
═══════════════════════════════════════ */
function toggleRow(id) {
    if (selectedIds.has(id)) selectedIds.delete(id);
    else selectedIds.add(id);
    updateCount();
    renderTable();
}

function toggleAll(cb) {
    const q = document.getElementById('searchInput').value.toLowerCase().trim();
    const filtered = allAssets.filter(a => {
        if (!q) return true;
        return (a.property_number||'').toLowerCase().includes(q) ||
               (a.item_name||'').toLowerCase().includes(q);
    });
    if (cb.checked) filtered.forEach(a => selectedIds.add(a.id));
    else filtered.forEach(a => selectedIds.delete(a.id));
    updateCount();
    renderTable();
}

function selectAll() {
    allAssets.forEach(a => selectedIds.add(a.id));
    updateCount();
    renderTable();
}

function clearAll() {
    selectedIds.clear();
    updateCount();
    renderTable();
}

function updateCount() {
    const n = selectedIds.size;
    document.getElementById('countNum').textContent = n;
    document.getElementById('previewBtn').disabled = n === 0;
}

/* ═══════════════════════════════════════
   PREVIEW & PRINT
═══════════════════════════════════════ */
function showPreview() {
    const selected = allAssets.filter(a => selectedIds.has(a.id));
    if (selected.length === 0) return;

    // Generate bond paper pages (2 stickers per row, ~5 rows per page)
    const STICKERS_PER_PAGE = 10;
    const chunks = [];
    for (let i = 0; i < selected.length; i += STICKERS_PER_PAGE) {
        chunks.push(selected.slice(i, i + STICKERS_PER_PAGE));
    }

    const pagesHTML = chunks.map(page => buildBondPaperHTML(page)).join('');

    // Update preview panel
    document.getElementById('bondPaperPreview').innerHTML = pagesHTML;
    document.getElementById('previewPanel').classList.remove('hidden');
    document.getElementById('previewPanel').classList.add('flex');

    // Update print area
    document.getElementById('printArea').innerHTML = pagesHTML;
    document.getElementById('printArea').classList.remove('hidden');

    // Generate QRs after DOM renders
    setTimeout(() => generateAllQRs(selected), 100);
}

function buildBondPaperHTML(assets) {
    const stickersHTML = assets.map(a => buildStickerHTML(a)).join('');
    return `<div class="bond-paper" style="margin-bottom:10mm;">${stickersHTML}</div>`;
}

function buildStickerHTML(a) {
    const propNum = a.property_number || 'N/A';
    const itemBrand = [a.item_name, a.brand, a.model].filter(Boolean).join(' / ');
    const serial = a.serial_number || 'N/A';
    const qrData = `${window.location.origin}/assets/${a.id}/profile`;

    return `<div class="sticker-card">
        <div class="sticker-header">
            <img src="/images/deped_logo.png" class="sticker-logo" alt="DepEd" onerror="this.style.display='none'">
            <div style="flex:1;">
                <div style="font-size:5pt; color:#1e3a5f; font-weight:700;">Republic of the Philippines</div>
                <div style="font-size:5pt; color:#1e3a5f; font-weight:700;">Department of Education</div>
                <div style="font-size:6pt; color:#c00000; font-weight:900;">DIVISION OF ZAMBOANGA CITY</div>
                <div style="font-size:4.5pt; color:#475569;">Region IX-Zamboanga Peninsula</div>
            </div>
            <div style="width:18mm; height:12mm; background:#ffd700; border:1px solid #e5c100; border-radius:2px; display:flex; align-items:center; justify-content:center;">
                <span style="font-size:4pt; color:#1e3a5f; font-weight:700;">LOGO/SEAL</span>
            </div>
        </div>
        <div class="sticker-title">Property Inventory Sticker</div>
        <div class="sticker-body">
            <div class="sticker-info">
                <div class="sticker-label">Property Number</div>
                <div class="sticker-value">${propNum}</div>
                <div class="sticker-label">Item / Brand / Model</div>
                <div class="sticker-value">${itemBrand || '—'}</div>
                <div class="sticker-label">Serial Number</div>
                <div class="sticker-value">${serial}</div>
            </div>
            <div class="sticker-qr-wrap">
                <div id="qr-${a.id}" style="width:22mm; height:22mm; display:flex; align-items:center; justify-content:center; background:#f8fafc; border:0.5px solid #e2e8f0; border-radius:2px;"></div>
                <div class="sticker-scan-label">SCAN FOR DETAILS</div>
            </div>
        </div>
        <div class="sticker-footer">
            NOTE - PLEASE DO NOT REMOVE
            <small>"Unauthorized removal or tampering will be subject to disciplinary action."</small>
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
                    width: 83,   // ~22mm at 96dpi
                    height: 83,
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
/* Print: show only the print area */
@media print {
    body > * { display: none !important; }
    #printArea { display: block !important; }
    .sticker-card { border-color: #c00000 !important; }
    .bond-paper { box-shadow: none !important; }
}
body { display: flex; }
body > div:first-child { flex-shrink: 0; }
</style>

</body>
</html>
