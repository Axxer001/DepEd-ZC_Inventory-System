@if(session('download_docs'))
<div x-data="{ 
    showModal: true, 
    docs: {{ json_encode(session('download_docs')) }},
    downloadSingle(doc) {
        let url = '{{ route('admin.download_doc_template', ['type' => ':type']) }}'
            .replace(':type', doc.doc_type) + '?recipient=' + encodeURIComponent(doc.recipient_name);
        
        if (doc.assignment_id) {
            url += '&assignment_id=' + encodeURIComponent(doc.assignment_id);
        }
        if (doc.transfer_id) {
            url += '&transfer_id=' + encodeURIComponent(doc.transfer_id);
        }
        
        const link = document.createElement('a');
        link.href = url;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    },
    downloadAll() {
        this.docs.forEach((doc, index) => {
            setTimeout(() => {
                this.downloadSingle(doc);
            }, index * 800);
        });
    }
}" x-show="showModal" x-cloak class="fixed inset-0 z-[500] flex items-center justify-center">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showModal = false"></div>
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl mx-4 relative z-10 flex flex-col overflow-hidden border border-slate-100 max-h-[90vh]">
        {{-- Modal Header --}}
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center shrink-0 shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-slate-800 uppercase tracking-wide italic">Download Documentations</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase mt-0.5">Save templates for the recent transactions</p>
                </div>
            </div>
            <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 hover:bg-slate-200/50 p-2.5 rounded-full transition-colors active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Modal Content --}}
        <div class="p-6 overflow-y-auto max-h-[50vh] custom-scroll">
            <div class="border border-slate-100 rounded-2xl overflow-hidden shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-5 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Recipient / Entity</th>
                            <th class="px-5 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Document</th>
                            <th class="px-5 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Assets Count</th>
                            <th class="px-5 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="doc in docs" :key="doc.recipient_name + '_' + doc.doc_type">
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-extrabold text-slate-800 uppercase" x-text="doc.recipient_name"></span>
                                        <span class="text-[9px] font-black text-blue-500 uppercase tracking-wider mt-0.5" x-text="doc.recipient_type"></span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-lg border bg-blue-50 text-blue-700 border-blue-200" x-text="doc.doc_type"></span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="text-xs font-black text-slate-700 font-mono" x-text="doc.asset_count"></span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <button @click="downloadSingle(doc)" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 text-white hover:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-wider transition-colors active:scale-95 shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                        Download
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="bg-slate-50 border-t border-slate-100 p-6 flex items-center justify-end gap-3">
            <button @click="showModal = false" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl text-xs font-black uppercase tracking-widest transition-colors shadow-sm active:scale-95">Close</button>
            <button @click="downloadAll()" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-blue-600/30 transition-all active:scale-95 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Download All
            </button>
        </div>
    </div>
</div>
@endif
