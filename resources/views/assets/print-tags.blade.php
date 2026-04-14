<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Asset Tags</title>
    <!-- Use Tailwind for quick styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; }
        
        /* Print Specific Styles */
        @media print {
            body { background: white; }
            .no-print { display: none !important; }
            .print-container { 
                margin: 0 !important; 
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }
            .page-break { page-break-after: always; }
        }

        /* A4 Paper Dimensions */
        .a4-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: white;
            padding: 10mm; /* Outer margin for the printer */
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }

        /* 3x8 Grid for Labels (Standard Avery 5160ish layout but scaled for QR) */
        .tag-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 5mm; /* Space between stickers */
        }

        /* Individual Sticker */
        .asset-tag {
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            height: 33mm; /* Approx height to fit 8 rows on A4 */
            box-sizing: border-box;
            page-break-inside: avoid;
        }

        .tag-logo { width: 30px; height: auto; margin-right: 5px; }
        .tag-header { display: flex; items-center; justify-content: center; margin-bottom: 5px; }
        .tag-title { font-size: 8px; font-weight: 800; line-height: 1; text-transform: uppercase; color: #1e293b; }
        .tag-subtitle { font-size: 6px; font-weight: 600; color: #64748b; }
        .tag-qr { margin: 2px 0; }
        .tag-hash { font-size: 7px; font-family: monospace; color: #475569; letter-spacing: 0.5px; margin-top: 2px; }
    </style>
</head>
<body>

    <!-- Controls Ribbon -->
    <div class="no-print bg-slate-900 text-white p-4 fixed w-full top-0 z-50 flex justify-between items-center shadow-lg">
        <div>
            <h1 class="font-black italic tracking-tight text-xl">Asset Tag Generator</h1>
            <p class="text-xs text-slate-400 font-semibold">Ready to print {{ count($tags) }} QR Code stickers</p>
        </div>
        <div class="flex gap-4">
            <button onclick="window.history.back()" class="px-5 py-2 bg-slate-800 hover:bg-slate-700 rounded-lg text-sm font-bold transition-colors border border-slate-700">Cancel</button>
            <button onclick="window.print()" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-500 rounded-lg text-sm font-bold shadow-lg shadow-emerald-900 transition-colors flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0v2.796c0 1.176.836 2.19 2.013 2.342a40.52 40.52 0 006.474 0 2.25 2.25 0 002.013-2.342V9.034z" />
                </svg>
                Print Tags
            </button>
        </div>
    </div>

    <!-- Padding for fixed header -->
    <div class="h-24 no-print"></div>

    <div class="print-container">
        @php
            $chunks = array_chunk($tags, 24); // 24 tags per A4 sheet (3x8 grid)
        @endphp

        @foreach($chunks as $index => $pageTags)
            <div class="a4-sheet {{ !$loop->last ? 'page-break' : '' }}">
                <div class="tag-grid">
                    @foreach($pageTags as $tag)
                        <div class="asset-tag">
                            <div class="tag-header">
                                <!-- Using placeholder logo if actual fails, or actual relative path if it works -->
                                <img src="{{ asset('images/deped_logo.png') }}" class="tag-logo" alt="DepEd Logo" onerror="this.style.display='none'">
                                <div>
                                    <div class="tag-title">DepEd Zamboanga City</div>
                                    <div class="tag-subtitle">Official Property Tag</div>
                                </div>
                            </div>
                            <div class="tag-qr">
                                <!-- Generate the QR pointing to the scan route -->
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->margin(0)->generate(url('/scan?tag=' . $tag)) !!}
                            </div>
                            <div class="tag-hash">{{ substr($tag, 0, 8) }}-{{ substr($tag, 9, 4) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>
