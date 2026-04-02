<div id="detailsModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-100">
            <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                <h3 class="text-xl font-bold text-slate-900">Repair Information</h3>
                <button onclick="closeModal('detailsModal')" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
            </div>
            
            <div id="modalContent" class="p-8">
                </div>
            
            <div id="modalFooter" class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                <button onclick="closeModal('detailsModal')" class="px-6 py-2 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 transition">Close</button>
            </div>
        </div>
    </div>
</div>