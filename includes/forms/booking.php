

<?php
   require 'includes.php';
?>
<div class="bg-white w-full max-w-xl rounded-3xl shadow-2xl relative p-8 border border-slate-100 transform scale-95 transition-transform duration-300" id="modalCard">
    
    <div class="flex justify-between items-center mb-8 pb-4 border-b border-slate-100">
        <div>
            <h3 class="text-3xl font-extrabold text-slate-900">New Service Request</h3>
            <p class="text-slate-500 mt-2">Describe the problem to get your unique Tracking ID.</p>
        </div>
        <button onclick="closeModal('bookingModal')" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600 text-2xl p-2 rounded-full hover:bg-slate-50">&times;</button>
    </div>
    
    <form id="bookingForm" class="space-y-6">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold mb-2 text-slate-700">Device Category</label>
                <select name="category" class="w-full px-5 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-slate-50 focus:bg-white" required>
                    <option value="" disabled selected>Select category</option>
                    <option value="Laptop">Laptop / Desktop</option>
                    <option value="Mobile">Smartphone / Tablet</option>
                    <option value="Appliance">Home Appliance</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2 text-slate-700">Model / Name</label>
                <input type="text" name="model" placeholder="e.g. iPhone 13 Pro" class="w-full px-5 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-slate-50 focus:bg-white" required>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-2 text-slate-700">Detailed Issue Description</label>
            <textarea name="description" rows="5" placeholder="Please provide specific details about the issue..." class="w-full px-5 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-slate-50 focus:bg-white resize-none" required></textarea>
            <p class="text-xs text-slate-400 mt-2">Example: "Screen is cracked after dropping it." or "Laptop shuts down randomly."</p>
        </div>
        
        <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-xl font-bold hover:bg-black shadow-lg transition-all active:scale-[0.98]">
            Submit & Generate Tracking ID
        </button>
    </form>
</div>
