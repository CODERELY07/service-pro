<div id="statusModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
    <div class="bg-white rounded-3xl p-8 max-w-sm w-full relative shadow-2xl border border-slate-100 transform transition-all">
        <div id="statusModalIcon" class="w-16 h-16 rounded-2xl mb-6 flex items-center justify-center text-2xl"></div>
        
        <h3 id="statusModalTitle" class="text-2xl font-bold text-slate-900 mb-2">Confirm Action</h3>
        <p id="statusModalDesc" class="text-slate-500 mb-8">Are you sure you want to proceed with this change?</p>
        
        <div class="flex gap-3">
            <button onclick="closeModal('statusModal')" class="flex-1 px-6 py-3 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                No, Back
            </button>
            <button id="statusConfirmBtn" class="flex-1 px-6 py-3 rounded-xl font-bold text-white transition">
                Yes, Confirm
            </button>
        </div>
    </div>
</div>