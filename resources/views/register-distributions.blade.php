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
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
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

<body class="bg-slate-50 min-h-screen flex text-slate-800">

@include('partials.sidebar')

<div class="flex-grow flex flex-col min-w-0 h-screen overflow-y-auto custom-scroll">
    <main class="p-6 lg:p-10 max-w-4xl mx-auto w-full">
        
        <header class="flex justify-between items-center mb-12">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight italic uppercase">Inventory Setup</h2>
                <p class="text-slate-500 text-sm font-medium italic">Single Window Recipient Registration</p>
            </div>
            <a href="{{ url()->previous() }}" class="px-6 py-3 back-btn-cool rounded-2xl text-sm font-bold text-slate-600 flex items-center gap-2 shadow-sm active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back
            </a>
        </header>

        <div class="bg-white p-10 lg:p-12 main-card shadow-2xl shadow-slate-200/60 border border-slate-50">
            <div class="mb-10">
                <h4 class="text-2xl font-black text-slate-800 uppercase tracking-tight italic">Recipient Registry</h4>
                <p class="text-slate-400 text-xs font-bold uppercase mt-1 tracking-widest">Register new entities to the master list</p>
            </div>

            <div class="space-y-6">
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
                    <input type="text" id="externalInput" name="external_classification" class="w-full p-5 input-style font-bold text-slate-700" placeholder="e.g. City Health Office, Private Contractor, Regional Director...">
                </div>

                <div id="personnelSection" class="hidden pt-8 border-t border-slate-100 animate-in fade-in duration-500">
                    <div class="flex justify-between items-end mb-6 ml-1">
                        <div>
                            <h3 class="font-black text-slate-800 uppercase tracking-tight italic text-lg">Personnel Details <span class="text-slate-400 text-sm font-medium italic lowercase">(optional)</span></h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">Assign authorized receivers</p>
                        </div>
                        <button onclick="addRow()" class="px-5 py-2.5 bg-slate-900 text-white text-[10px] font-black uppercase rounded-2xl hover:bg-slate-700 transition-all tracking-widest shadow-lg">+ Add Personnel</button>
                    </div>

                    <div id="container" class="space-y-4">
                        <div class="flex flex-col sm:flex-row gap-3 p-4 bg-slate-50/50 rounded-3xl border border-slate-100 group animate-in fade-in duration-300">
                            <input type="text" placeholder="Personnel Name (Optional)" class="flex-grow p-4 input-style font-bold text-slate-700 text-sm">
                            <input type="text" placeholder="Job Description (Optional)" class="flex-grow p-4 input-style font-bold text-slate-700 text-sm">
                            <button onclick="this.parentElement.remove()" class="px-4 text-slate-300 hover:text-red-500 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>

                    <div class="pt-10">
                        <button onclick="submitFake()" class="w-full py-5 bg-[#c00000] hover:bg-red-700 text-white rounded-3xl font-black uppercase tracking-[0.2em] shadow-xl transition-all hover:-translate-y-1 active:scale-95">
                            Register Recipient
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>

<script>
    const schools = [
        "Zamboanga National High School",
        "Western Mindanao State University",
        "Ayala National High School",
        "Tetuan Central School",
        "Zamboanga City High School"
    ];

    function toggleSource() {
        const type = document.getElementById("sourceType").value;
        const sBox = document.getElementById("schoolBox");
        const eBox = document.getElementById("externalBox");
        const pSection = document.getElementById("personnelSection");

        // Reset display
        sBox.classList.add("hidden");
        eBox.classList.add("hidden");
        pSection.classList.add("hidden");

        if (type !== "") {
            // Show the Personnel section for any selection
            pSection.classList.remove("hidden");
            
            if (type === "school") {
                sBox.classList.remove("hidden");
            } else if (type === "external") {
                eBox.classList.remove("hidden");
            }
        }
    }

    function filterSchools() {
        let input = document.getElementById("schoolInput").value.toLowerCase();
        let dropdown = document.getElementById("schoolDropdown");
        let results = schools.filter(s => s.toLowerCase().includes(input));
        dropdown.innerHTML = "";
        if (results.length === 0 || input === "") { dropdown.classList.add("hidden"); return; }

        results.forEach(s => {
            let div = document.createElement("div");
            div.className = "px-6 py-4 hover:bg-red-50 hover:text-[#c00000] cursor-pointer font-bold text-sm border-b border-slate-50 last:border-0 transition-colors";
            div.innerText = s;
            div.onclick = () => {
                document.getElementById("schoolInput").value = s;
                dropdown.classList.add("hidden");
            };
            dropdown.appendChild(div);
        });
        dropdown.classList.remove("hidden");
    }

    function addRow() {
        const div = document.createElement("div");
        div.className = "flex flex-col sm:flex-row gap-3 p-4 bg-slate-50/50 rounded-3xl border border-slate-100 animate-in fade-in slide-in-from-top-2 duration-300";
        div.innerHTML = `
            <input type="text" placeholder="Personnel Name (Optional)" class="flex-grow p-4 input-style font-bold text-slate-700 text-sm">
            <input type="text" placeholder="Job Description (Optional)" class="flex-grow p-4 input-style font-bold text-slate-700 text-sm">
            <button onclick="this.parentElement.remove()" class="px-4 text-slate-300 hover:text-red-500 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>`;
        document.getElementById("container").appendChild(div);
    }

    function submitFake() {
        Swal.fire({
            title: 'Confirm Registration',
            text: "Are you sure you want to register this recipient?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#c00000',
            confirmButtonText: 'Yes, Register',
            customClass: {
                popup: 'rounded-[2.5rem]',
                confirmButton: 'rounded-xl font-bold px-6 py-3'
            }
        });
    }
</script>

</body>
</html>