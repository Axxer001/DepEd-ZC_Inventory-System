<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DepEd Zamboanga City - Inventory Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* WEB ONLY STYLES */
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        
        /* PRINT ONLY STYLES (DITO YUNG TEMPLATE) */
        @media print {
            @page { size: portrait; margin: 0.5in; }
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            
            /* Professional Form Styling */
            .print-wrapper { width: 100% !important; border: 2px solid black; padding: 0 !important; }
            .header-section { border-bottom: 2px solid black; padding: 10px; text-align: center; }
            
            table { width: 100% !important; border-collapse: collapse !important; }
            th { 
                background-color: #f2f2f2 !important; 
                border: 1px solid black !important; 
                text-transform: uppercase; 
                font-size: 10px !important; 
                padding: 5px !important;
            }
            td { 
                border: 1px solid black !important; 
                padding: 6px !important; 
                font-size: 10px !important; 
                vertical-align: top;
            }
            .signatory-cell { border: 1px solid black; padding: 15px; width: 50%; }
        }

        /* UI Styling for Web */
        .custom-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body x-data="inventoryApp()">

    <div class="no-print max-w-6xl mx-auto mt-10 p-6 bg-white rounded-3xl custom-shadow border border-slate-200 mb-10">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-black text-slate-900 uppercase italic">DepEd Inventory System</h1>
                <p class="text-slate-500 font-medium">Zamboanga City Division - Property Management</p>
            </div>
            <button @click="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg transition-all flex items-center gap-2">
                <span>Print Professional Form</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="group relative">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-2 mb-1 block">Quick Search</label>
                <input type="text" x-model="search" placeholder="Type school name or item description..." class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
            </div>
            <div>
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-2 mb-1 block">Filter by District</label>
                <select x-model="districtFilter" class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl outline-none">
                    <option value="all">All Districts</option>
                    <template x-for="d in getDistricts()" :key="d">
                        <option :value="d" x-text="d"></option>
                    </template>
                </select>
            </div>
        </div>
    </div>

    <div class="print-wrapper max-w-5xl mx-auto bg-white shadow-2xl mb-20 overflow-hidden">
        
        <div class="header-section flex items-center justify-between px-8 py-4">
            <div class="w-20 flex-none text-[8px] border border-dashed border-gray-300 h-20 flex items-center justify-center">DepEd Logo</div>
            <div class="text-center">
                <p class="text-xs">Republic of the Philippines</p>
                <h2 class="text-lg font-bold uppercase text-blue-900 leading-tight">Department of Education</h2>
                <p class="text-[10px] italic">Region IX, Zamboanga Peninsula</p>
                <p class="text-[11px] font-bold">SCHOOLS DIVISION OF ZAMBOANGA CITY</p>
                <p class="text-[9px] mt-1 italic">Pilar St., Zamboanga City | depedzamboangacity.ph</p>
            </div>
            <div class="w-20 flex-none text-[8px] border border-dashed border-gray-300 h-20 flex items-center justify-center">Division Logo</div>
        </div>

        <div class="bg-gray-100 no-print px-8 py-2 border-b text-[10px] font-bold text-gray-500 flex justify-between">
            <span>PREVIEW MODE: This is how it looks on paper</span>
            <span x-text="'Records found: ' + filteredItems().length"></span>
        </div>

        <div class="text-center py-6 border-b-2 border-black">
            <h1 class="text-xl font-black uppercase tracking-widest">Property Allocation & Inventory Report</h1>
            <p class="text-[10px] italic mt-1" x-text="'Date Generated: ' + new Date().toLocaleDateString('en-US', {month: 'long', day:'numeric', year:'numeric'})"></p>
        </div>

        <table class="w-full">
            <thead>
                <tr>
                    <th style="width: 15%;">Property No.</th>
                    <th style="width: 10%;">Unit</th>
                    <th style="width: 35%;">Item Description / Specification</th>
                    <th style="width: 10%;">Qty</th>
                    <th style="width: 20%;">Stakeholder Institution</th>
                    <th style="width: 10%;">Status</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(item, index) in filteredItems()" :key="item.id">
                    <tr>
                        <td class="text-center font-mono" x-text="item.property_no"></td>
                        <td class="text-center" x-text="item.unit"></td>
                        <td>
                            <div class="font-bold uppercase" x-text="item.name"></div>
                            <div class="text-[9px] italic text-gray-600" x-text="item.specs"></div>
                        </td>
                        <td class="text-center font-bold" x-text="item.qty"></td>
                        <td>
                            <div class="font-bold" x-text="item.school"></div>
                            <div class="text-[9px]" x-text="item.district"></div>
                        </td>
                        <td class="text-center">
                            <span class="text-[9px] uppercase font-bold" x-text="item.status"></span>
                        </td>
                    </tr>
                </template>
                <template x-for="i in Math.max(0, 10 - filteredItems().length)">
                    <tr class="h-8">
                        <td></td><td></td><td></td><td></td><td></td><td></td>
                    </tr>
                </template>
            </tbody>
        </table>

        <div class="flex w-full">
            <div class="signatory-cell border-r-2 border-black">
                <p class="text-[10px] italic mb-10">Prepared & Recorded by:</p>
                <div class="text-center">
                    <p class="font-bold underline uppercase text-sm">Juan Dela Cruz</p>
                    <p class="text-[9px]">Administrative Officer V / Supply Officer</p>
                    <p class="text-[9px] mt-2 italic text-gray-400 underline">Date Signed</p>
                </div>
            </div>
            <div class="signatory-cell">
                <p class="text-[10px] italic mb-10">Noted & Verified by:</p>
                <div class="text-center">
                    <p class="font-bold underline uppercase text-sm">Maria Clara, EdD</p>
                    <p class="text-[9px]">Assistant Schools Division Superintendent</p>
                    <p class="text-[9px] mt-2 italic text-gray-400 underline">Date Signed</p>
                </div>
            </div>
        </div>

        <div class="border-t-2 border-black p-2 flex justify-between items-center text-[8px] font-bold uppercase">
            <span>Form No: ZC-PROP-2024-001</span>
            <span>Document Code: DepEd-ZC-Inventory</span>
            <span>Page 1 of 1</span>
        </div>
    </div>

    <script>
        function inventoryApp() {
            return {
                search: '',
                districtFilter: 'all',
                // SAMPLE DATA (I-link mo ito sa database mo)
                items: [
                    { id: 1, property_no: '2024-001-A', unit: 'pcs', name: 'Dell Latitude 3420', specs: 'Intel i5, 16GB RAM, 512GB SSD', qty: 15, school: 'Ayala NHS', district: 'Ayala District', status: 'Serviceable' },
                    { id: 2, property_no: '2023-045-B', unit: 'unit', name: 'Smart TV 65"', specs: 'Crystal UHD, Wall Mounted', qty: 2, school: 'Zamboanga Central', district: 'Central District', status: 'Serviceable' },
                    { id: 3, property_no: '2024-012-F', unit: 'pcs', name: 'Monoblock Chairs', specs: 'Plastic, Color Blue, Heavy Duty', qty: 50, school: 'Tetuan ES', district: 'Central District', status: 'Serviceable' },
                    { id: 4, property_no: '2024-099-L', unit: 'unit', name: 'DCP Laptop Package', specs: 'Complete Accessories, Celerio', qty: 45, school: 'Baliwasan SHS', district: 'West District', status: 'In-Storage' }
                ],
                getDistricts() {
                    return [...new Set(this.items.map(i => i.district))].sort();
                },
                filteredItems() {
                    return this.items.filter(item => {
                        const matchSearch = item.name.toLowerCase().includes(this.search.toLowerCase()) || 
                                          item.school.toLowerCase().includes(this.search.toLowerCase());
                        const matchDistrict = this.districtFilter === 'all' || item.district === this.districtFilter;
                        return matchSearch && matchDistrict;
                    });
                }
            }
        }
    </script>
</body>
</html>