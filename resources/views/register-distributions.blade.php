<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipient Setup | DepEd ZC</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .main-card { border-radius: 3rem; }
        
        .input-style {
            background-color: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 1.25rem;
            transition: all 0.2s ease;
        }
        .input-style:focus {
            background: white;
            border-color: #c00000;
            box-shadow: 0 0 0 4px rgba(192, 0, 0, 0.05);
            outline: none;
        }

        .back-btn-cool {
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .back-btn-cool:hover {
            border-color: #c00000; color: #c00000;
            box-shadow: 0 10px 15px -3px rgba(192, 0, 0, 0.12);
            transform: translateX(-4px);
        }
        .hidden { display: none; }
    </style>
</head>

<body class="min-h-screen flex text-slate-800">

<div class="flex-grow flex flex-col min-w-0 h-screen overflow-hidden">
    <main class="p-6 lg:p-10 w-full h-full flex flex-col">
        
        <header class="flex justify-between items-center mb-8 max-w-[1600px] mx-auto w-full px-4">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight italic uppercase leading-none">Inventory Setup</h2>
                <p class="text-slate-500 text-sm font-medium italic">Recipient Registration & Batch Management</p>
            </div>
            <a href="#" class="px-6 py-3 back-btn-cool rounded-2xl text-sm font-bold text-slate-600 flex items-center gap-2 shadow-sm active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back
            </a>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 max-w-[1600px] mx-auto w-full flex-grow overflow-hidden pb-6">
            
            <div class="lg:col-span-8 bg-white p-8 lg:p-12 main-card shadow-2xl shadow-slate-200/60 border border-slate-50 overflow-y-auto custom-scroll flex flex-col">
                <div class="mb-10">
                    <h4 class="text-2xl font-black text-slate-800 uppercase tracking-tight italic">Recipient Registry</h4>
                    <p class="text-slate-400 text-xs font-bold uppercase mt-1 tracking-widest">Register new entities to the master list</p>
                </div>

                <div class="space-y-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Source Type <span class="text-red-500">*</span></label>
                        <select id="sourceType" class="w-full p-5 input-style font-bold text-slate-700 cursor-pointer" onchange="toggleSource()">
                            <option value="">-- Select Source Type --</option>
                            <option value="school">School</option>
                            <option value="external">External (Offices / Individuals)</option>
                        </select>
                    </div>

                    <div id="schoolBox" class="hidden space-y-2 animate-in fade-in slide-in-from-top-2 duration-300">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Search School <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input id="schoolInput" class="w-full p-5 input-style font-bold text-slate-700" placeholder="Type school name..." oninput="filterSchools()">
                            <div id="schoolDropdown" class="hidden absolute z-10 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-48 overflow-y-auto custom-scroll"></div>
                        </div>
                    </div>

                    <div id="externalBox" class="hidden space-y-2 animate-in fade-in slide-in-from-top-2 duration-300">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Classification / Specific Recipient <span class="text-red-500">*</span></label>
                        <input type="text" id="externalInput" class="w-full p-5 input-style font-bold text-slate-700" placeholder="e.g. City Health Office, Private Contractor...">
                    </div>

                    <div id="personnelSection" class="hidden pt-8 border-t border-slate-100 space-y-6">
                        <div class="flex justify-between items-end mb-4 ml-1">
                            <div>
                                <h3 class="font-black text-slate-800 uppercase tracking-tight italic text-lg">Personnel Details <span class="text-slate-400 text-sm font-medium italic lowercase">(optional)</span></h3>
                                <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">Assign authorized receivers</p>
                            </div>
                            <button onclick="addRow()" class="px-6 py-3 bg-slate-900 text-white text-[10px] font-black uppercase rounded-2xl hover:bg-slate-700 transition-all tracking-widest shadow-lg">+ Add Personnel</button>
                        </div>

                        <div id="container" class="space-y-4">
                            <div class="flex flex-col sm:flex-row gap-3 p-4 bg-slate-50/50 rounded-3xl border border-slate-100 group animate-in fade-in duration-300">
                                <input type="text" placeholder="Personnel Name (Optional)" class="flex-grow p-5 input-style font-bold text-slate-700 text-sm">
                                <input type="text" placeholder="Job Description (Optional)" class="flex-grow p-5 input-style font-bold text-slate-700 text-sm">
                                <button onclick="this.parentElement.remove()" class="px-5 text-slate-300 hover:text-red-500 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>

                        <div class="pt-10">
                            <button onclick="addRecipientToList()" class="w-full py-6 bg-[#c00000] hover:bg-red-700 text-white rounded-[2rem] font-black uppercase tracking-[0.2em] shadow-xl transition-all hover:-translate-y-1 active:scale-95">
                                Add Recipient
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 bg-slate-900 p-8 main-card shadow-2xl border border-slate-800 flex flex-col overflow-hidden relative">
                <div class="mb-8 flex justify-between items-start">
                    <div>
                        <h4 class="text-xl font-black text-white uppercase tracking-tight italic">Recipient List</h4>
                        <p class="text-slate-400 text-[10px] font-bold uppercase mt-1 tracking-widest">Selected targets for batch</p>
                    </div>
                    <span id="recipientCount" class="bg-white/10 text-white px-4 py-1 rounded-full text-[10px] font-black uppercase">0 People</span>
                </div>

                <div id="activeList" class="space-y-3 flex-grow overflow-y-auto custom-scroll pr-2">
                    <div id="emptyState" class="text-center py-20">
                        <div class="w-16 h-16 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4 border border-white/5">
                            <svg class="w-8 h-8 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </div>
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-widest italic">List is empty</p>
                    </div>
                </div>

                <div id="listFooter" class="hidden pt-6 mt-6 border-t border-white/10">
                    <button onclick="Swal.fire('Success', 'Distribution process started.', 'success')" class="w-full py-4 bg-white text-slate-900 rounded-2xl font-black uppercase tracking-widest hover:bg-slate-200 transition-all text-xs">
                        Proceed to Assigned Assets
                    </button>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
    const schools = ["Zamboanga National High School", "Western Mindanao State University", "Ayala National High School", "Tetuan Central School", "ZC State College Marine Sciences"];
    let count = 0;

    function toggleSource() {
        const type = document.getElementById("sourceType").value;
        const sBox = document.getElementById("schoolBox");
        const eBox = document.getElementById("externalBox");
        const pSection = document.getElementById("personnelSection");

        sBox.classList.toggle("hidden", type !== "school");
        eBox.classList.toggle("hidden", type !== "external");
        pSection.classList.toggle("hidden", type === "");
    }

    function filterSchools() {
        let input = document.getElementById("schoolInput").value.toLowerCase();
        let dropdown = document.getElementById("schoolDropdown");
        let results = schools.filter(s => s.toLowerCase().includes(input));
        dropdown.innerHTML = results.map(s => `<div onclick="selectSchool('${s}')" class="px-6 py-4 hover:bg-red-50 hover:text-[#c00000] cursor-pointer font-bold text-sm border-b border-slate-50 last:border-0">${s}</div>`).join('');
        dropdown.classList.toggle("hidden", results.length === 0 || input === "");
    }

    function selectSchool(name) {
        document.getElementById('schoolInput').value = name;
        document.getElementById('schoolDropdown').classList.add('hidden');
    }

    function addRow() {
        const div = document.createElement("div");
        div.className = "flex flex-col sm:flex-row gap-3 p-4 bg-slate-50/50 rounded-3xl border border-slate-100 animate-in fade-in slide-in-from-top-1";
        div.innerHTML = `<input type="text" placeholder="Personnel Name (Optional)" class="flex-grow p-5 input-style font-bold text-slate-700 text-sm"><input type="text" placeholder="Job Description (Optional)" class="flex-grow p-5 input-style font-bold text-slate-700 text-sm"><button onclick="this.parentElement.remove()" class="px-5 text-slate-300 hover:text-red-500 transition-colors"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>`;
        document.getElementById("container").appendChild(div);
    }

    function addRecipientToList() {
        const type = document.getElementById("sourceType").value;
        const orgName = type === 'school' ? document.getElementById("schoolInput").value : document.getElementById("externalInput").value;
        
        if (!orgName) {
            Swal.fire('Required', 'Please provide an Organization or School name.', 'warning');
            return;
        }

        const personRows = document.querySelectorAll("#container > div");
        let addedInBatch = 0;

        personRows.forEach(row => {
            const name = row.querySelectorAll('input')[0].value || "General Personnel";
            const job = row.querySelectorAll('input')[1].value || "Unauthorized Position";

            count++;
            addedInBatch++;
            const card = document.createElement("div");
            card.className = "bg-white/5 border border-white/10 p-4 rounded-2xl flex justify-between items-center animate-in slide-in-from-right duration-300";
            card.innerHTML = `
                <div class="overflow-hidden">
                    <p class="text-white font-bold text-xs truncate">${name}</p>
                    <p class="text-slate-500 text-[9px] uppercase font-black tracking-widest truncate">${job} • ${orgName}</p>
                </div>
                <button onclick="this.parentElement.remove(); updateCount(-1);" class="text-slate-600 hover:text-red-400 transition-colors ml-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            `;
            document.getElementById("activeList").appendChild(card);
        });
        
        if(addedInBatch > 0) {
            document.getElementById("emptyState").classList.add("hidden");
            document.getElementById("listFooter").classList.remove("hidden");
            updateCount(0);
        }
    }

    function updateCount(diff) {
        if (diff === -1) count--;
        document.getElementById("recipientCount").innerText = `${count} People`;
        if (count === 0) {
            document.getElementById("emptyState").classList.remove("hidden");
            document.getElementById("listFooter").classList.add("hidden");
        }
    }
</script>

</body>
</html>