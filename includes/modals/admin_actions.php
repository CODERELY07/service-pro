<!-- Reject Booking Modal -->
<div id="rejectModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-100">
            <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                <h3 class="text-xl font-bold text-slate-900">Reject Service Request</h3>
                <button onclick="closeModal('rejectModal')" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
            </div>

            <div class="p-6">
                <p class="text-slate-600 mb-4">Please provide a reason for rejecting this service request.</p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Reject Reason</label>
                        <textarea id="rejectReason" rows="4" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent resize-none" placeholder="Enter the reason for rejection..."></textarea>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                <button onclick="closeModal('rejectModal')" class="px-6 py-2 bg-slate-200 text-slate-700 rounded-xl font-bold hover:bg-slate-300 transition">Cancel</button>
                <button onclick="confirmReject()" class="px-6 py-2 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 transition">Reject Request</button>
            </div>
        </div>
    </div>
</div>

<!-- Accept with Price Modal -->
<div id="acceptModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-100">
            <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                <h3 class="text-xl font-bold text-slate-900">Accept Service Request</h3>
                <button onclick="closeModal('acceptModal')" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
            </div>

            <div class="p-6">
                <p class="text-slate-600 mb-4">Please enter the service price for this request.</p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Service Price (₱)</label>
                        <input type="number" id="servicePrice" step="0.01" min="0" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="0.00">
                    </div>
                </div>
            </div>

            <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                <button onclick="closeModal('acceptModal')" class="px-6 py-2 bg-slate-200 text-slate-700 rounded-xl font-bold hover:bg-slate-300 transition">Cancel</button>
                <button onclick="confirmAccept()" class="px-6 py-2 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition">Accept & Send Quote</button>
            </div>
        </div>
    </div>
</div>

<!-- Client Offer Response Modal -->
<div id="offerModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-100">
            <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                <h3 class="text-xl font-bold text-slate-900">Service Quote</h3>
                <button onclick="closeModal('offerModal')" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
            </div>

            <div class="p-6">
                <div id="offerContent">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>

            <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                <button onclick="rejectOffer()" class="px-6 py-2 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 transition">Reject Quote</button>
                <button onclick="acceptOffer()" class="px-6 py-2 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition">Accept Quote</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Offer Reason Modal -->
<div id="rejectOfferModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-100">
            <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                <h3 class="text-xl font-bold text-slate-900">Reject Service Quote</h3>
                <button onclick="closeModal('rejectOfferModal')" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
            </div>

            <div class="p-6">
                <p class="text-slate-600 mb-4">Please select the reason for rejecting this quote.</p>

                <div class="space-y-4">
                    <div>
                        <label class="flex items-center">
                            <input type="radio" name="rejectOfferReason" value="price" class="text-red-600 focus:ring-red-500">
                            <span class="ml-2 text-sm text-slate-700">Because of the price</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="radio" name="rejectOfferReason" value="other" class="text-red-600 focus:ring-red-500">
                            <span class="ml-2 text-sm text-slate-700">Other reason</span>
                        </label>
                    </div>
                    <div id="otherReasonContainer" class="hidden">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Please specify</label>
                        <textarea id="otherRejectReason" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent resize-none" placeholder="Enter your reason..."></textarea>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                <button onclick="closeModal('rejectOfferModal')" class="px-6 py-2 bg-slate-200 text-slate-700 rounded-xl font-bold hover:bg-slate-300 transition">Cancel</button>
                <button onclick="confirmRejectOffer()" class="px-6 py-2 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 transition">Reject Quote</button>
            </div>
        </div>
    </div>
</div>