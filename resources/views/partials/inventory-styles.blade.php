<style>
        .step-content { display: none; }
        .step-content.active { display: block; animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        
        @keyframes fadeIn { 
            from { opacity: 0; transform: translateY(10px) scale(0.98); } 
            to { opacity: 1; transform: translateY(0) scale(1); } 
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        .toast-enter { animation: slideInRight 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .toast-exit { animation: slideOutRight 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

        .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

        .back-btn-cool {
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .back-btn-cool:hover {
            border-color: #c00000;
            color: #c00000;
            box-shadow: 0 10px 15px -3px rgba(192, 0, 0, 0.1);
            transform: translateX(-4px);
        }
        html.dark .back-btn-cool {
            background: #141f33;
            border-color: #1e2e47;
            color: #94a3b8;
        }
        html.dark .back-btn-cool:hover {
            border-color: #c00000;
            color: white;
            background: #c00000;
        }

        /* ── Excel-like registration table ── */
        .xls-th {
            padding: 14px 16px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #64748b;
            white-space: nowrap;
            border-right: 1px solid #e2e8f0;
        }
        .xls-input {
            width: 100%;
            height: 100%;
            padding: 9px 13px;
            font-size: 12px;
            font-weight: 600;
            outline: none;
            border: 1px solid transparent;
            transition: all 0.15s ease;
        }
        .xls-input:focus:not([readonly]) {
            border-color: #c00000;
            background-color: rgba(192, 0, 0, 0.02) !important;
        }
        .xls-input::placeholder { color: #cbd5e1; font-weight: 500; }
        .xls-const {
            display: flex;
            align-items: center;
            padding: 0 16px;
            height: 100%;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            white-space: nowrap;
            font-style: normal;
        }
        /* Scroll container: min-height = 10 rows, scrollable beyond */
        .xls-scroll-wrap {
            position: relative;
            overflow-x: auto;
            overflow-y: auto;
            width: 100%;
            max-width: 100%;
            min-height: 400px;
            max-height: calc(100vh - 450px);
            flex-grow: 1;
            background: #ffffff;
        }
        .pg-btn {
            padding: 8px 18px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            border-radius: 9999px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #e2e8f0;
            background: white;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        html.dark .pg-btn {
            background: white;
            color: #1e293b;
            border-color: rgba(255,255,255,0.1);
        }
        .xls-sticky-col {
            position: sticky;
            left: 0;
            z-index: 10;
            background-color: #ffffff !important;
        }
        .xls-row:hover .xls-sticky-col {
            background-color: #f8fafc !important;
        }
        html.dark .xls-const { color: #94a3b8 !important; }
        html.dark .xls-sticky-col { background-color: #0f172a !important; }

        /* Custom Autocomplete */
        .custom-autocomplete {
            position: absolute;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            max-height: 200px;
            overflow-y: auto;
            z-index: 9999;
            min-width: 150px;
            font-family: inherit;
        }
        html.dark .custom-autocomplete {
            background: #141f33;
            border-color: #1e293b;
        }
        .custom-autocomplete-item {
            padding: 10px 14px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            color: #334155;
            transition: background 0.1s;
        }
        html.dark .custom-autocomplete-item {
            color: #cbd5e1;
        }
        .custom-autocomplete-item:hover {
            background: #f8fafc;
        }
        html.dark .custom-autocomplete-item:hover {
            background: #1a2535;
        }

        /* NEW Badge */
        .new-badge {
            position: absolute;
            top: 3px;
            right: 3px;
            font-size: 8px;
            font-weight: 900;
            background: #10b981;
            color: white;
            padding: 1px 4px;
            border-radius: 4px;
            text-transform: uppercase;
            pointer-events: none;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
            letter-spacing: 0.5px;
        }
        html.dark .new-badge {
            background: #059669;
            color: #ecfdf5;
            box-shadow: none;
        }

        html.dark .xls-const { color: #2e4060 !important; }
        /* Dark: section 1 card */
        html.dark #acqSourceCard { background-color: #141f33 !important; border-color: #1e2e47 !important; }
        html.dark #acqSourceCard .border-b { border-color: #1e2e47 !important; }
        html.dark #acqSourceInput {
            background-color: #0d1525 !important;
            border-color: #1e2e47 !important;
            color: #94a3b8 !important;
        }
        /* Dark: section 2 table card */
        html.dark #assetTableCard { background-color: #141f33 !important; border-color: #1e2e47 !important; }
        html.dark #assetToolbar { background-color: #141f33 !important; border-color: #1e2e47 !important; }
        html.dark #assetToolbar .bg-slate-100 { background-color: #0d1525 !important; }
        html.dark #assetToolbar .bg-slate-50 { background-color: #0d1525 !important; border-color: #1e2e47 !important; }
        html.dark #assetToolbar .text-slate-600 { color: #64748b !important; }
        html.dark .xls-scroll-wrap { background-color: #141f33 !important; }
        html.dark #assetSourceEmpty, html.dark #assetDistEmpty { background: #141f33 !important; }
        html.dark #assetSourceEmpty p, html.dark #assetDistEmpty p { color: #253550 !important; }
        html.dark #assetSourceEmpty svg, html.dark #assetDistEmpty svg { color: #253550 !important; }
        /* Dark: footer */
        html.dark #assetTableFooter { background-color: #0d1525 !important; border-color: #1e2e47 !important; }
        html.dark #assetTableFooter #rowCountLabel { color: #2e4060 !important; }
        /* Dark: sticky row num col */
        html.dark .xls-sticky-col { background-color: #141f33 !important; }
        html.dark .xls-row:hover .xls-sticky-col { background-color: #0d1525 !important; }

        /* Pagination Styles */
        .pg-btn {
            padding: 8px 16px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            border-radius: 12px;
            transition: all 0.2s;
            display: flex;
            items-center: center;
            gap: 6px;
        }
        .pg-btn:not(:disabled):hover { background: #f1f5f9; color: #c00000; }
        .pg-btn:disabled { opacity: 0.3; cursor: not-allowed; }
        html.dark .pg-btn { background: #1e293b !important; color: #cbd5e1 !important; }
        html.dark .pg-btn:not(:disabled):hover { background: #c00000 !important; color: white !important; }

        /* Column Coloring */
        .col-identity { background-color: #eff6ff !important; border-color: #dbeafe !important; }
        .col-context  { background-color: #f8fafc !important; border-color: #f1f5f9 !important; }
        .col-personnel{ background-color: #fffbeb !important; border-color: #fef3c7 !important; }
        .col-financial{ background-color: #eef2ff !important; border-color: #e0e7ff !important; }
        .col-temporal { background-color: #ecfdf5 !important; border-color: #d1fae5 !important; }
        .col-status   { background-color: #f5f3ff !important; border-color: #ede9fe !important; }

        html.dark .col-identity { background-color: rgba(30, 58, 138, 0.15) !important; border-color: rgba(30, 58, 138, 0.3) !important; }
        html.dark .col-context  { background-color: rgba(30, 41, 59, 0.15) !important; border-color: rgba(30, 41, 59, 0.3) !important; }
        html.dark .col-personnel{ background-color: rgba(120, 53, 15, 0.15) !important; border-color: rgba(120, 53, 15, 0.3) !important; }
        html.dark .col-financial{ background-color: rgba(49, 46, 129, 0.15) !important; border-color: rgba(49, 46, 129, 0.3) !important; }
        html.dark .col-temporal { background-color: rgba(6, 78, 59, 0.15) !important; border-color: rgba(6, 78, 59, 0.3) !important; }
        html.dark .col-status   { background-color: rgba(76, 29, 149, 0.15) !important; border-color: rgba(76, 29, 149, 0.3) !important; }

        /* Stronger background for TH */
        th.col-identity { background-color: #dbeafe !important; }
        th.col-context  { background-color: #f1f5f9 !important; }
        th.col-personnel{ background-color: #fef3c7 !important; }
        th.col-financial{ background-color: #e0e7ff !important; }
        th.col-temporal { background-color: #d1fae5 !important; }
        th.col-status   { background-color: #ede9fe !important; }

        html.dark th.col-identity { background-color: rgba(30, 58, 138, 0.4) !important; }
        html.dark th.col-context  { background-color: rgba(30, 41, 59, 0.4) !important; }
        html.dark th.col-personnel{ background-color: rgba(120, 53, 15, 0.4) !important; }
        html.dark th.col-financial{ background-color: rgba(49, 46, 129, 0.4) !important; }
        html.dark th.col-temporal { background-color: rgba(6, 78, 59, 0.4) !important; }
        html.dark th.col-status   { background-color: rgba(76, 29, 149, 0.4) !important; }
    </style>
