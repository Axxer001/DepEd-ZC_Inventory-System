<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Setup | DepEd Zamboanga City</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .step-content { display: none; }
        .step-content.active { display: block; animation: fadeIn 0.3s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.98); } to { opacity: 1; transform: scale(1); } }

        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex text-slate-800 overflow-x-hidden">

    @include('partials.sidebar')

    <div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll">

        <header class="lg:hidden bg-white border-b border-slate-200 p-4 sticky top-0 z-30 flex items-center gap-4">
            <button onclick="toggleSidebar()" class="p-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-100 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/deped_logo.png') }}" class="h-6 w-auto">
                <span class="font-extrabold italic text-sm">DepEd ZC</span>
            </div>
        </header>

        <main class="p-6 lg:p-10 max-w-5xl mx-auto w-full">

            <header class="flex justify-between items-center mb-12">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight italic">Inventory Setup</h2>
                    <p class="text-slate-500 text-sm font-medium italic">Zamboanga City Division Asset Management</p>
                </div>
                <button id="backBtn" onclick="goBack()" class="hidden px-6 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm active:scale-95">
                    ← Back
                </button>
            </header>

            <div id="step1" class="step-content active">
                <h3 class="text-center text-lg font-bold text-slate-400 uppercase tracking-[0.3em] mb-10">What would you like to do?</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 px-4">
                    <div onclick="nextStep(2, 'add')" class="group bg-white p-12 rounded-[3rem] shadow-xl shadow-slate-200/60 border-2 border-transparent hover:border-[#c00000] transition-all duration-300 cursor-pointer text-center">
                        <div class="text-7xl mb-6 group-hover:scale-110 transition-transform">➕</div>
                        <h4 class="text-3xl font-black text-slate-800 tracking-tight uppercase">Add New</h4>
                        <p class="text-slate-400 text-xs font-bold uppercase mt-3 tracking-widest leading-tight text-center">Register new data to the system</p>
                    </div>
                    <div onclick="nextStep(2, 'edit')" class="group bg-white p-12 rounded-[3rem] shadow-xl shadow-slate-200/60 border-2 border-transparent hover:border-[#c00000] transition-all duration-300 cursor-pointer text-center">
                        <div class="text-7xl mb-6 group-hover:scale-110 transition-transform">📝</div>
                        <h4 class="text-3xl font-black text-slate-800 tracking-tight uppercase">Edit / Update</h4>
                        <p class="text-slate-400 text-xs font-bold uppercase mt-3 tracking-widest leading-tight text-center">Modify or update existing records</p>
                    </div>
                </div>
            </div>

            <div id="step2" class="step-content text-center">
                <h3 id="step2Title" class="text-lg font-bold text-slate-400 uppercase tracking-[0.3em] mb-10">Select Category</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div onclick="nextStep(3, 'school')" class="bg-white p-10 rounded-[2.5rem] shadow-lg border border-slate-100 hover:border-[#c00000] hover:-translate-y-2 transition-all cursor-pointer group">
                        <div class="text-5xl mb-4 group-hover:scale-110 transition-transform">🏫</div>
                        <span class="block font-extrabold text-slate-800 uppercase tracking-tight">Schools</span>
                    </div>
                    <div onclick="nextStep(3, 'district')" class="bg-white p-10 rounded-[2.5rem] shadow-lg border border-slate-100 hover:border-[#c00000] hover:-translate-y-2 transition-all cursor-pointer group">
                        <div class="text-5xl mb-4 group-hover:scale-110 transition-transform">📍</div>
                        <span class="block font-extrabold text-slate-800 uppercase tracking-tight">Districts</span>
                    </div>
                    <div onclick="nextStep(3, 'item')" class="bg-white p-10 rounded-[2.5rem] shadow-lg border border-slate-100 hover:border-[#c00000] hover:-translate-y-2 transition-all cursor-pointer group">
                        <div class="text-5xl mb-4 group-hover:scale-110 transition-transform">📦</div>
                        <span class="block font-extrabold text-slate-800 uppercase tracking-tight">Items/Sub-cat</span>
                    </div>
                </div>
            </div>

            <div id="step3" class="step-content">
                <div class="max-w-2xl mx-auto bg-white p-10 rounded-[3rem] shadow-2xl border border-slate-50">
                    <div id="formContent"></div>
                </div>
            </div>

        </main>
    </div>

    <script>
        let history = [1];
        let currentMode = '';
        let currentCategory = '';

        const districtMap = {
            "District 1": { ld: "1", quad: "1.1" },
            "District 2": { ld: "1", quad: "1.1" },
            "District 3": { ld: "1", quad: "1.1" },
            "District 4": { ld: "1", quad: "1.2" },
            "District 5": { ld: "1", quad: "1.2" },
            "District 6": { ld: "2", quad: "2.1" },
            "District 7": { ld: "2", quad: "2.1" },
            "District 8": { ld: "2", quad: "2.1" },
            "District 9": { ld: "2", quad: "2.2" },
            "District 10": { ld: "2", quad: "2.2" }
        };

        function nextStep(step, value) {
            if (step === 2) {
                currentMode = value;
                document.getElementById('step2Title').innerText = (value === 'add' ? 'ADD NEW' : 'EDIT') + ' CATEGORY';
            }
            if (step === 3) {
                currentCategory = value;
                renderForm();
            }
            document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
            document.getElementById('step' + step).classList.add('active');
            history.push(step);
            updateBackButton();
        }

        function goBack() {
            if (history.length > 1) {
                history.pop();
                const prevStep = history[history.length - 1];
                document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
                document.getElementById('step' + prevStep).classList.add('active');
                updateBackButton();
            }
        }

        function updateBackButton() {
            const btn = document.getElementById('backBtn');
            btn.classList.toggle('hidden', history[history.length - 1] === 1);
        }

        function filterQuadrants() {
            const ld = document.getElementById('dist_ld').value;
            const quadSelect = document.getElementById('dist_quad');
            quadSelect.innerHTML = '<option value="">Select Quadrant</option>';

            if (ld === "1") {
                quadSelect.innerHTML += '<option value="1.1">1.1</option><option value="1.2">1.2</option>';
            } else if (ld === "2") {
                quadSelect.innerHTML += '<option value="2.1">2.1</option><option value="2.2">2.2</option>';
            }
        }

        function renderForm() {
            const container = document.getElementById('formContent');
            const modeText = currentMode === 'add' ? 'Create New' : 'Update';
            const btnColor = 'bg-[#c00000] hover:bg-red-700 shadow-red-100';
            let html = `<h4 class="text-2xl font-black text-slate-800 mb-8 uppercase tracking-tight italic">${modeText} ${currentCategory}</h4>`;

            if (currentCategory === 'school') {
                html += `
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Select District</label>
                            <select class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-red-100 outline-none font-semibold transition-all cursor-pointer">
                                <option value="">Select the assigned District</option>
                                ${Object.keys(districtMap).map(d => `<option value="${d}">${d}</option>`).join('')}
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">School Name</label>
                            <input type="text" placeholder="e.g. Ayala National High School" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-red-100 outline-none font-semibold transition-all">
                        </div>
                        <button class="w-full py-5 ${btnColor} text-white rounded-3xl font-bold shadow-xl transition-all active:scale-95">
                            ${modeText} School Record
                        </button>
                    </div>`;
            } else if (currentCategory === 'district') {
                html += `
                    <div class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Legislative District</label>
                                <select id="dist_ld" onchange="filterQuadrants()" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-red-100 outline-none font-semibold">
                                    <option value="">Select LD</option>
                                    <option value="1">LD 1</option>
                                    <option value="2">LD 2</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Quadrant</label>
                                <select id="dist_quad" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-red-100 outline-none font-semibold">
                                    <option value="">Select Quadrant</option>
                                </select>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">District Name/Number</label>
                            <input type="text" placeholder="e.g. District 12" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-red-100 outline-none font-semibold transition-all">
                        </div>
                        <button class="w-full py-5 ${btnColor} text-white rounded-3xl font-bold shadow-xl transition-all active:scale-95">
                            ${modeText} District
                        </button>
                    </div>`;
            } else {
                html += `
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Main Category Name</label>
                            <input type="text" placeholder="e.g. DCP Packages" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-red-100 outline-none font-semibold transition-all">
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center ml-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sub-Items (Optional)</label>
                                <button type="button" onclick="addSubItemField()" class="text-[10px] font-bold bg-red-50 text-[#c00000] px-3 py-1 rounded-lg hover:bg-[#c00000] hover:text-white transition-all">+ Add Field</button>
                            </div>
                            <div id="subItemContainer" class="space-y-3 max-h-[200px] overflow-y-auto pr-2 custom-scroll">
                                <div class="flex gap-2 group">
                                    <input type="text" name="sub_items[]" placeholder="e.g. Laptop" class="flex-grow p-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-red-100 outline-none font-semibold transition-all text-sm">
                                    <button type="button" onclick="this.parentElement.remove()" class="px-4 text-slate-300 hover:text-red-500 transition-colors font-bold">✕</button>
                                </div>
                            </div>
                        </div>
                        <button class="w-full py-5 ${btnColor} text-white rounded-3xl font-bold shadow-xl transition-all active:scale-95">
                            ${modeText} Category Settings
                        </button>
                    </div>`;
            }
            container.innerHTML = html;
        }

        function addSubItemField() {
            const container = document.getElementById('subItemContainer');
            const div = document.createElement('div');
            div.className = "flex gap-2 group";
            div.innerHTML = `
                <input type="text" name="sub_items[]" placeholder="Enter sub-item name" class="flex-grow p-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-red-100 outline-none font-semibold transition-all text-sm">
                <button type="button" onclick="this.parentElement.remove()" class="px-4 text-slate-300 hover:text-red-500 transition-colors font-bold">✕</button>
            `;
            container.appendChild(div);
            container.scrollTop = container.scrollHeight;
        }
    </script>

</body>
</html>