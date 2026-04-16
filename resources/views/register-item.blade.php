<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Registration | DepEd Command Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .step-active { color: #c00000; }
        .step-active .icon-box { background-color: #c00000; color: white; border-color: #c00000; box-shadow: 0 10px 15px -3px rgba(192, 0, 0, 0.2); transform: scale(1.05); }
        .step-inactive { color: #cbd5e1; }
        .step-inactive .icon-box { background-color: white; color: #cbd5e1; border-color: #e2e8f0; }
        .step-complete .icon-box { background-color: #1e293b; color: white; border-color: #1e293b; }
        
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade { animation: fadeIn 0.4s ease-out forwards; }

        .stepper-line { flex-grow: 1; height: 3px; background-color: #e2e8f0; position: relative; top: -14px; margin: 0 20px; border-radius: 99px; }
        .stepper-line.active { background-color: #c00000; transition: all 0.5s ease; }
        
        .custom-scroll::-webkit-scrollbar { width: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="antialiased text-slate-900">

    <div class="max-w-[1100px] mx-auto p-6 lg:p-12 min-h-screen flex flex-col">
        
        {{-- Header Section --}}
        <div class="flex justify-between items-center mb-16 px-2">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-[#c00000] animate-pulse"></div>
                    <span class="text-[10px] font-black text-[#c00000] uppercase tracking-[0.3em]">System Live</span>
                </div>
                <h2 class="text-3xl font-black text-slate-800 uppercase italic leading-none">Register new Inventory Item</h2>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-[0.2em] mt-2">Department of Education • Zamboanga City</p>
            </div>
            <div class="flex items-center gap-4 bg-white p-2.5 px-6 rounded-[2rem] shadow-sm border border-slate-100">
                <div class="text-right hidden sm:block">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block leading-none mb-1">Authorized Operator</span>

                </div>
                <div class="w-11 h-11 bg-[#c00000] rounded-2xl flex items-center justify-center text-white font-black shadow-lg shadow-red-100 italic">K</div>
            </div>
        </div>

        {{-- Sleek Stepper --}}
        <div class="flex items-center justify-between mb-20 px-12 relative">
            <div id="step1-indicator" class="flex flex-col items-center gap-4 step-active z-10 transition-all duration-500">
                <div class="icon-box w-16 h-16 rounded-[1.8rem] border-2 flex items-center justify-center transition-all duration-500 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest leading-none italic">Asset Source</span>
            </div>
            <div id="line-1" class="stepper-line"></div>
            <div id="step2-indicator" class="flex flex-col items-center gap-4 step-inactive z-10 transition-all duration-500">
                <div class="icon-box w-16 h-16 rounded-[1.8rem] border-2 flex items-center justify-center transition-all duration-500 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest leading-none italic">Specifications</span>
            </div>
            <div id="line-2" class="stepper-line"></div>
            <div id="step3-indicator" class="flex flex-col items-center gap-4 step-inactive z-10 transition-all duration-500">
                <div class="icon-box w-16 h-16 rounded-[1.8rem] border-2 flex items-center justify-center transition-all duration-500 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest leading-none italic">Confirmation</span>
            </div>
        </div>

        <div class="flex-grow">
            
            {{-- STEP 1: SOURCE --}}
            <div id="step1-content" class="animate-fade space-y-8">
                <div class="bg-white border border-slate-100 rounded-[3.5rem] p-12 shadow-sm">
                    <h3 class="text-2xl font-black text-slate-800 uppercase italic mb-10">01. Source Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-10">
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-[#c00000] uppercase tracking-widest ml-1 italic font-black underline underline-offset-4 decoration-2">Entity Type <span class="text-red-500">*</span></label>
                            <select id="sourceEntityType" onchange="handleEntityTypeChange()" class="w-full p-6 bg-slate-50 border border-slate-100 rounded-3xl font-black text-slate-700 outline-none focus:ring-4 focus:ring-red-50 transition-all cursor-pointer appearance-none">
                                <option value="" selected disabled>-- Select Entity Type --</option>
                                <option value="school">School (Internal)</option>
                                <option value="external">External (Supplier / Provider)</option>
                            </select>
                        </div>
                        <div class="space-y-3">
                            <label id="sourceDynamicLabel" class="text-[10px] font-black text-slate-300 uppercase tracking-widest ml-1 italic">Provider Name</label>
                            <input type="text" id="sourceDynamicInput" disabled placeholder="Select type first..." class="w-full p-6 bg-slate-100 border border-slate-100 rounded-3xl font-bold text-slate-700 outline-none transition-all placeholder:text-slate-300 shadow-inner">
                        </div>
                    </div>
                    
                    <div class="pt-10 border-t border-slate-50 grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Authorized Receiver (Full Name)</label>
                            <input type="text" id="receiverName" placeholder="e.g. Juan Dela Cruz" class="w-full p-5 bg-slate-50 border border-slate-100 rounded-2xl font-bold text-slate-700 outline-none focus:border-slate-300 transition-all">
                        </div>
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Job Title / Position</label>
                            <input type="text" id="receiverPos" placeholder="e.g. Supply Officer" class="w-full p-5 bg-slate-50 border border-slate-100 rounded-2xl font-bold text-slate-700 outline-none focus:border-slate-300 transition-all">
                        </div>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button id="step1-next" onclick="goToStep(2)" disabled class="group px-14 py-6 bg-slate-200 text-slate-400 rounded-[2.5rem] font-black uppercase tracking-widest text-xs transition-all flex items-center gap-4 cursor-not-allowed">
                        Next Step
                        <svg class="w-5 h-5 transition-transform group-hover:translate-x-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </button>
                </div>
            </div>

            {{-- STEP 2: SPECS --}}
            <div id="step2-content" class="hidden animate-fade space-y-8">
                <div class="bg-white border border-slate-100 rounded-[3.5rem] p-12 shadow-sm">
                    <div class="flex justify-between items-center mb-10">
                        <h3 class="text-2xl font-black text-slate-800 uppercase italic">02. Asset Details</h3>
                        <button onclick="addSubItemField()" class="text-[10px] font-black text-[#c00000] bg-red-50 px-7 py-3 rounded-2xl uppercase hover:bg-red-100 border border-red-100 transition-all">+ Add New Specification</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 italic">Main Category</label>
                            <input type="text" id="categoryName" placeholder="e.g. ICT, Furniture" class="w-full p-6 bg-slate-50 border border-slate-100 rounded-3xl font-black text-sm outline-none focus:ring-4 focus:ring-red-50 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 italic">Item Name</label>
                            <input type="text" id="itemName" placeholder="e.g. Laptop, Table" class="w-full p-6 bg-slate-50 border border-slate-100 rounded-3xl font-black text-sm outline-none focus:ring-4 focus:ring-red-50 transition-all">
                        </div>
                    </div>
                    <div id="subItemContainer" class="space-y-8 pt-10 border-t border-slate-50"></div>
                </div>
                <div class="flex justify-between">
                    <button onclick="goToStep(1)" class="group px-10 py-6 bg-white border border-slate-200 text-slate-600 rounded-[2.5rem] font-black uppercase tracking-widest text-xs hover:bg-slate-50 transition-all flex items-center gap-4 italic font-bold">
                        <svg class="w-5 h-5 transition-transform group-hover:-translate-x-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path></svg>
                        Source
                    </button>
                    <button onclick="goToStep(3)" class="group px-14 py-6 bg-[#c00000] text-white rounded-[2.5rem] font-black uppercase tracking-widest text-xs shadow-xl shadow-red-100 hover:scale-105 transition-all flex items-center gap-4 italic font-black">
                        Final Review
                        <svg class="w-5 h-5 transition-transform group-hover:translate-x-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </button>
                </div>
            </div>

            {{-- STEP 3: REVIEW --}}
            <div id="step3-content" class="hidden animate-fade space-y-10 pb-20">
                <div class="bg-slate-900 rounded-[4rem] p-16 shadow-2xl relative overflow-hidden">
                    <div class="relative z-10">
                        <h3 class="text-3xl font-black text-white uppercase italic mb-8 tracking-tight">Batch Summary Preview</h3>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
                            <div class="bg-white/5 border border-white/10 rounded-[2.5rem] p-8 shadow-inner">
                                <span class="text-[#c00000] text-[9px] font-black uppercase tracking-[0.3em] block mb-3">Asset Source</span>
                                <h4 id="sumSource" class="text-2xl font-black text-white italic underline decoration-red-500 underline-offset-8">--</h4>
                                <p id="sumType" class="text-slate-500 text-[10px] font-bold uppercase mt-4 tracking-widest italic tracking-[0.2em]">--</p>
                            </div>
                            <div class="bg-white/5 border border-white/10 rounded-[2.5rem] p-8 shadow-inner">
                                <span class="text-emerald-500 text-[9px] font-black uppercase tracking-[0.3em] block mb-3">Item Name</span>
                                <h4 id="sumItem" class="text-2xl font-black text-white italic underline decoration-emerald-500 underline-offset-8">--</h4>
                                <p id="sumCat" class="text-slate-500 text-[10px] font-bold uppercase mt-4 tracking-widest italic tracking-[0.2em]">--</p>
                            </div>
                        </div>
                        <div class="bg-white/5 border border-white/10 rounded-[2.5rem] overflow-hidden">
                            <table class="w-full text-left">
                                <thead class="border-b border-white/10 bg-white/5">
                                    <tr>
                                        <th class="p-6 text-[9px] font-black text-slate-500 uppercase italic tracking-[0.2em]">Specifications / Materials</th>
                                        <th class="p-6 text-[9px] font-black text-slate-500 uppercase italic tracking-[0.2em]">Unit Price</th>
                                        <th class="p-6 text-[9px] font-black text-slate-500 uppercase italic tracking-[0.2em]">Quantity</th>
                                        <th class="p-6 text-[9px] font-black text-slate-500 uppercase italic text-right tracking-[0.2em]">Identifier</th>
                                    </tr>
                                </thead>
                                <tbody id="summaryTable" class="text-white text-xs font-bold uppercase tracking-tight"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="flex justify-between">
                    <button onclick="goToStep(2)" class="group px-10 py-6 bg-slate-200 text-slate-600 rounded-[2.5rem] font-black uppercase tracking-widest text-xs flex items-center gap-4 Italic font-bold">
                        <svg class="w-5 h-5 transition-transform group-hover:-translate-x-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path></svg>
                        Edit Specs
                    </button>
                    <button class="px-20 py-6 bg-slate-900 text-white rounded-[2.5rem] font-black uppercase tracking-widest text-xs hover:bg-black transition-all shadow-2xl italic font-black">REGISTER TO MASTERLIST ⚡</button>
                </div>
            </div>

        </div>
    </div>

    <script>
        function handleEntityTypeChange() {
            const type = document.getElementById('sourceEntityType').value;
            const input = document.getElementById('sourceDynamicInput');
            const label = document.getElementById('sourceDynamicLabel');
            const nextBtn = document.getElementById('step1-next');
            input.disabled = false;
            input.classList.remove('bg-slate-100');
            input.classList.add('bg-white', 'border-slate-200');
            label.classList.add('text-[#c00000]', 'font-black');
            nextBtn.disabled = false;
            nextBtn.classList.remove('bg-slate-200', 'text-slate-400');
            nextBtn.classList.add('bg-slate-900', 'text-white', 'hover:bg-black', 'shadow-xl');
            label.innerText = type === 'school' ? '02. Search School Name *' : '02. External Provider Name *';
        }

        function goToStep(step) {
            document.getElementById('step1-content').classList.add('hidden');
            document.getElementById('step2-content').classList.add('hidden');
            document.getElementById('step3-content').classList.add('hidden');
            document.getElementById(`step${step}-content`).classList.remove('hidden');
            
            document.getElementById('step1-indicator').className = 'flex flex-col items-center gap-4 ' + (step === 1 ? 'step-active' : 'step-complete');
            document.getElementById('step2-indicator').className = 'flex flex-col items-center gap-4 ' + (step === 2 ? 'step-active' : (step > 2 ? 'step-complete' : 'step-inactive'));
            document.getElementById('step3-indicator').className = 'flex flex-col items-center gap-4 ' + (step === 3 ? 'step-active' : 'step-inactive');
            
            document.getElementById('line-1').className = 'stepper-line ' + (step >= 2 ? 'active' : '');
            document.getElementById('line-2').className = 'stepper-line ' + (step >= 3 ? 'active' : '');

            if(step === 3) {
                document.getElementById('sumSource').innerText = document.getElementById('sourceDynamicInput').value || 'RHU Zamboanga';
                document.getElementById('sumType').innerText = (document.getElementById('sourceEntityType').value || 'External').toUpperCase() + ' PROVIDER';
                document.getElementById('sumItem').innerText = document.getElementById('itemName').value || "Unnamed Asset";
                document.getElementById('sumCat').innerText = document.getElementById('categoryName').value || 'General Category';
                
                const table = document.getElementById('summaryTable');
                table.innerHTML = '';
                document.querySelectorAll('.row-container').forEach(row => {
                    const spec = row.querySelector('.spec-val').value || 'No Specs provided';
                    const price = row.querySelector('.price-val').value || '0.00';
                    const prop = row.querySelector('.prop-val').value || 'N/A';
                    table.innerHTML += `<tr class="border-b border-white/5 hover:bg-white/5 transition-colors"><td class="p-6 italic">${spec}</td><td class="p-6 text-emerald-400 italic">₱ ${price}</td><td class="p-6 text-right text-slate-400">ID: ${prop}</td></tr>`;
                });
            }
            if(step === 2 && document.getElementById('subItemContainer').children.length === 0) { addSubItemField(); }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function addSubItemField() {
            const container = document.getElementById('subItemContainer');
            const id = Date.now();
            const html = `
                <div id="row-${id}" class="row-container p-10 bg-slate-50 border border-slate-100 rounded-[3.5rem] animate-fade relative group shadow-sm transition-all hover:border-[#c00000]/30 hover:bg-white">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-end">
                        <div class="lg:col-span-5 space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block ml-1 italic">Specifications / Materials / Dimensions</label>
                            <input type="text" placeholder="e.g. Mahogany Wood, Core i7, 4ft x 2ft Steel Frame" class="spec-val w-full p-4 bg-white border border-slate-100 rounded-2xl font-bold text-sm outline-none focus:border-red-200 shadow-sm transition-all">
                        </div>
                        <div class="lg:col-span-3 space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block ml-1 italic">₱ Unit Price</label>
                            <input type="number" placeholder="0.00" class="price-val w-full p-4 bg-white border border-slate-100 rounded-2xl font-bold text-sm outline-none shadow-sm transition-all">
                        </div>

                       
                        <div class="lg:col-span-3 space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block ml-1 italic">Quantity</label>
                            <input type="number" placeholder="0" class="qty-val w-15 p-4 bg-white border border-slate-100 rounded-2xl font-bold text-sm outline-none shadow-sm transition-all">
                        </div>
                        <div class="lg:col-span-4 flex gap-3">
                             <div class="flex-grow space-y-2">
                                <label class="text-[9px] font-black text-[#c00000] uppercase tracking-widest block ml-1 italic font-black underline">📅 Date Acquired</label>
                                <input type="date" class="w-full p-4 bg-white border border-red-50 rounded-2xl font-black text-xs outline-none shadow-sm transition-all">
                             </div>
                             <button type="button" onclick="toggleSerial(${id})" class="p-4 bg-white border border-slate-200 text-slate-500 rounded-2xl text-[9px] font-black uppercase italic tracking-tighter hover:bg-slate-900 hover:text-white transition-all shadow-sm italic mt-auto">⚙ Serial</button>
                        </div>
                    </div>
                    <div id="serial-panel-${id}" class="hidden mt-8 pt-8 border-t border-slate-200 flex flex-col md:flex-row gap-8 items-center animate-fade">
                        <label class="flex items-center gap-3 cursor-pointer min-w-[150px] italic font-black uppercase text-[10px] text-slate-700">
                            <input type="checkbox" class="w-6 h-6 rounded-lg border-slate-200 accent-[#c00000]"> Serialized?
                        </label>
                        <div class="flex gap-6 w-full">
                            <input type="text" placeholder="Property No." class="prop-val flex-1 p-4 bg-white border border-slate-100 rounded-xl font-bold text-[10px] outline-none shadow-sm italic">
                            <input type="text" placeholder="Serial No." class="sn-val flex-1 p-4 bg-white border border-slate-100 rounded-xl font-bold text-[10px] outline-none shadow-sm italic">
                        </div>
                    </div>
                    <button onclick="document.getElementById('row-${id}').remove()" class="absolute -top-3 -right-3 w-10 h-10 bg-white border border-slate-100 text-slate-300 rounded-full hover:text-red-500 shadow-md flex items-center justify-center font-bold opacity-0 group-hover:opacity-100 transition-all italic shadow-lg">✕</button>
                </div>`;
            container.insertAdjacentHTML('beforeend', html);
        }

        function toggleSerial(id) { document.getElementById(`serial-panel-${id}`).classList.toggle('hidden'); }
    </script>
</body>
</html>