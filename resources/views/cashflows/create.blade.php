@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto pb-10">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Add Manual Transaction</h2>
        <p class="text-gray-500 text-sm mt-1">Record miscellaneous cash in (receipts) or cash out (expenses/wages).</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('cashflow.store') }}" method="POST">
            @csrf
            
            <div class="p-8">
                <!-- Transaction Type Toggle -->
                <div class="mb-8">
                    <label class="block text-sm font-bold text-gray-700 mb-3">Transaction Type <span class="text-red-500">*</span></label>
                    <div class="flex gap-4">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="type" value="in" class="peer sr-only" required onchange="toggleType('in')" checked>
                            <div class="p-4 rounded-xl border-2 border-gray-200 peer-checked:border-green-500 peer-checked:bg-green-50 text-center transition">
                                <i class="fas fa-arrow-down text-xl text-green-600 mb-2 block"></i>
                                <span class="font-bold text-gray-700">Cash In (Receipt)</span>
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="type" value="out" class="peer sr-only" required onchange="toggleType('out')">
                            <div class="p-4 rounded-xl border-2 border-gray-200 peer-checked:border-red-500 peer-checked:bg-red-50 text-center transition">
                                <i class="fas fa-arrow-up text-xl text-red-600 mb-2 block"></i>
                                <span class="font-bold text-gray-700">Cash Out (Expense)</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="date" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 outline-none" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <!-- Amount -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount (RM) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" name="amount" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 outline-none" placeholder="0.00" required>
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category <span class="text-red-500">*</span></label>
                        <input type="text" name="category" list="category-list" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 outline-none" placeholder="e.g. Petrol, Part-Time, Tools..." required>
                        <datalist id="category-list">
                            <option value="General Income">
                            <option value="Petrol">
                            <option value="Part-Time Wage">
                            <option value="Tools & Equipments">
                            <option value="Food & Beverages">
                        </datalist>
                    </div>

                    <!-- Staff Selection (Hidden by default, shown if Cash Out is selected) -->
                    <div id="staff-selection" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Related Staff (Optional)</label>
                        <select name="user_id" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 outline-none bg-white">
                            <option value="">-- No Staff / General Expense --</option>
                            @foreach($staffs as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-gray-500">Select if this is a wage/allowance payment.</small>
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description / Notes <span class="text-red-500">*</span></label>
                    <textarea name="description" rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 outline-none" placeholder="Explain the transaction details..." required></textarea>
                </div>
            </div>

            <div class="bg-gray-50 px-8 py-4 border-t border-gray-100 flex justify-end gap-4">
                <a href="{{ route('cashflow.index') }}" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i> Save Transaction
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleType(type) {
        const staffDiv = document.getElementById('staff-selection');
        if (type === 'out') {
            staffDiv.style.display = 'block';
        } else {
            staffDiv.style.display = 'none';
            // Reset the select box
            staffDiv.querySelector('select').value = '';
        }
    }
</script>
@endsection