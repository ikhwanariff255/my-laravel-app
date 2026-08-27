@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">

<div class="max-w-4xl mx-auto pb-10">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Generate New Invoice</h2>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="bg-blue-600 text-white px-6 py-4 rounded-t-2xl">
            <h3 class="font-semibold"><i class="fas fa-file-invoice mr-2"></i> Invoice Details</h3>
        </div>
        
        <form action="{{ route('invoice.store') }}" method="POST">
            @csrf
            <div class="p-6">
                <!-- Customer Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Customer Name <span class="text-red-500">*</span></label>
                        <input type="text" name="customer_name" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 outline-none" placeholder="e.g. ALIA" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Customer Address <span class="text-red-500">*</span></label>
                        <textarea name="customer_address" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 outline-none" rows="2" placeholder="e.g. NO.21, JALAN AROWANA 7..." required></textarea>
                    </div>
                </div>

                <hr class="my-6">
                
                <!-- Financial Expenses (Cash Out) -->
                <h5 class="font-bold text-gray-800 mb-4"><i class="fas fa-wallet text-gray-500 mr-2"></i> Cash Out / Expenses (Optional)</h5>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div class="flex justify-between items-center mb-3">
                        <label class="text-sm font-semibold text-gray-700">Part-time Staff Wages</label>
                        <button type="button" class="px-3 py-1 bg-teal-600 text-white text-xs rounded hover:bg-teal-700" onclick="addPartTimer()"><i class="fas fa-plus mr-1"></i> Add Staff</button>
                    </div>
                    <div id="pt-container"></div>
                    <p class="text-xs text-gray-500 mt-2">Grand Total invois akan masuk ke <b>Cash In</b> secara automatik. Gaji staf yang diletakkan di sini akan auto-rekod sebagai <b>Cash Out</b>.</p>
                </div>

                <hr class="my-6">
                
                <!-- Invoice Items -->
                <h5 class="font-bold text-gray-800 mb-4"><i class="fas fa-list text-gray-500 mr-2"></i> Invoice Items</h5>
                <div id="items-container">
                    <div class="item-row flex flex-wrap gap-4 items-end mb-4">
                        <div class="flex-1 min-w-[300px]">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description / Service <span class="text-red-500">*</span></label>
                            <select name="description[]" class="form-control select2-desc" required>
                                <option value="">-- Select or Type Custom Service --</option>
                                <option value="Home Defect Inspection Service including Inspection Report (Softcopy)" selected>Home Defect Inspection Service including Inspection Report (Softcopy)</option>
                                <option value="Re-inspection Service (2nd Visit)">Re-inspection Service (2nd Visit)</option>
                                <option value="Thermal Imaging Water Leak Detection">Thermal Imaging Water Leak Detection</option>
                            </select>
                        </div>
                        <div class="w-32">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price (RM) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="price[]" class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500" value="290.00" required>
                        </div>
                        <div class="w-10"></div> <!-- Placeholder for delete button space -->
                    </div>
                </div>

                <button type="button" id="btn-add-item" class="text-sm px-4 py-2 text-blue-600 border border-blue-600 rounded-lg hover:bg-blue-50 mt-2">
                    <i class="fas fa-plus mr-1"></i> Add More Item
                </button>

            </div>
            
            <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700">
                    <i class="fas fa-save mr-2"></i> Generate Invoice
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Scripts for dynamic rows -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // Staff Options loaded from Controller
    const staffOptions = `@foreach($staffs as $staff)<option value="{{ $staff->id }}">{{ $staff->name }}</option>@endforeach`;

    function addPartTimer() {
        let html = `
        <div class="flex gap-4 items-center mb-3 pt-row">
            <div class="flex-1">
                <select name="staff_id[]" class="w-full px-3 py-2 border rounded-lg outline-none bg-white">
                    <option value="">-- Pilih Staff --</option>
                    ${staffOptions}
                </select>
            </div>
            <div class="w-40">
                <input type="number" step="0.01" name="staff_amount[]" class="w-full px-3 py-2 border rounded-lg" placeholder="RM (Wages)">
            </div>
            <button type="button" class="text-red-500 hover:text-red-700 px-2" onclick="$(this).closest('.pt-row').remove()"><i class="fas fa-times text-lg"></i></button>
        </div>`;
        $('#pt-container').append(html);
    }

    $(document).ready(function() {
        function initSelect2(element) {
            $(element).select2({
                theme: 'bootstrap4', width: '100%', tags: true,
                placeholder: "-- Select or Type Custom Service --"
            });
        }

        initSelect2('.select2-desc');

        $('#btn-add-item').click(function() {
            var newRow = `
                <div class="item-row flex flex-wrap gap-4 items-end mb-4">
                    <div class="flex-1 min-w-[300px]">
                        <select name="description[]" class="form-control new-select2-desc" required>
                            <option value="">-- Select or Type Custom Service --</option>
                            <option value="Home Defect Inspection Service including Inspection Report (Softcopy)">Home Defect Inspection Service including Inspection Report (Softcopy)</option>
                            <option value="Re-inspection Service (2nd Visit)">Re-inspection Service (2nd Visit)</option>
                            <option value="Thermal Imaging Water Leak Detection">Thermal Imaging Water Leak Detection</option>
                        </select>
                    </div>
                    <div class="w-32">
                        <input type="number" step="0.01" name="price[]" class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500" placeholder="0.00" required>
                    </div>
                    <div class="w-10 text-center">
                        <button type="button" class="text-red-500 hover:text-red-700 mb-2 btn-remove-item" title="Remove"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            `;
            $('#items-container').append(newRow);
            initSelect2($('.new-select2-desc').last());
        });

        $(document).on('click', '.btn-remove-item', function() {
            $(this).closest('.item-row').remove();
        });
    });
</script>
@endsection