@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .layout-container-block {
        position: relative; display: inline-block; cursor: crosshair;
        border: 2px solid #e5e7eb; border-radius: 10px; overflow: hidden; background: #fff; max-width: 100%;
    }
    .marker-point {
        position: absolute; width: 18px; height: 18px; background: #ef4444; border-radius: 50%;
        transform: translate(-50%, -50%); border: 3px solid white;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.3); pointer-events: none; z-index: 10;
    }
</style>

<div class="max-w-7xl mx-auto pb-20">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Edit Defect</h1>
            <p class="text-gray-500 mt-1">Project: <span class="px-3 py-1 bg-blue-100 text-blue-800 font-semibold rounded-lg">{{ $inspection->title }}</span></p>
        </div>
        <a href="{{ route('inspection.show', $inspection->id) }}" class="px-4 py-2 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300">Kembali</a>
    </div>

    <form id="editDefectForm">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-8">
            <!-- Bahagian Map Marker -->
            <div class="md:col-span-7">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="font-bold text-gray-800"><i class="fas fa-map-marker-alt text-red-500 mr-2"></i> Mark Area Location</h3>
                    </div>
                    <div class="p-6 bg-gray-50 flex flex-col items-center">
                        <div class="layout-container-block w-full max-w-md" id="layout-container">
                            <img src="{{ asset('storage/' . $inspection->layout_img) }}" id="layout-img" class="w-full object-contain max-h-[500px]">
                            <!-- Marker sedia ada diletakkan berpandukan mark_x dan mark_y dari DB -->
                            <div class="marker-point" id="marker" style="left: {{ $defect->mark_x }}%; top: {{ $defect->mark_y }}%; display: block;"></div>
                        </div>
                        <p class="text-gray-500 mt-3 text-sm font-bold">Click on the plan to change the red dot position.</p>
                        <input type="hidden" id="mx" value="{{ $defect->mark_x }}">
                        <input type="hidden" id="my" value="{{ $defect->mark_y }}">
                    </div>
                </div>
            </div>

            <!-- Bahagian Borang -->
            <div class="md:col-span-5">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location / Area <span class="text-red-500">*</span></label>
                        <input type="text" id="locationSelect" value="{{ $defect->location }}" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                        <input type="text" id="categorySelect" value="{{ $defect->category }}" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Element Type <span class="text-red-500">*</span></label>
                        <input type="text" id="typeSelect" value="{{ $defect->type }}" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Defect <span class="text-red-500">*</span></label>
                        <input type="text" id="defectSelect" value="{{ $defect->defect }}" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                        <textarea id="descriptionInput" rows="2" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500" required>{{ $defect->desc }}</textarea>
                    </div>

                    <div class="border-t pt-4">
                        <label class="block font-bold text-blue-600 mb-2">Ganti Gambar Kerosakan</label>
                        <p class="text-xs text-gray-500 mb-2">Biarkan kosong jika tidak mahu tukar gambar.</p>
                        
                        <div class="flex gap-2 mb-3">
                            @if(is_array($defect->img))
                                @foreach($defect->img as $img)
                                    <img src="{{ asset('storage/' . $img) }}" class="w-16 h-16 object-cover rounded border">
                                @endforeach
                            @endif
                        </div>

                        <input type="file" id="imageInput" accept="image/*" multiple class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700">
                    </div>

                    <button type="submit" id="btnUpdate" class="w-full mt-4 px-6 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-sm">
                        <i class="fas fa-save mr-2"></i> UPDATE DEFECT
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.getElementById('layout-img').addEventListener('click', function(e) {
        const rect = this.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width) * 100;
        const y = ((e.clientY - rect.top) / rect.height) * 100;
        
        const marker = document.getElementById('marker');
        marker.style.left = x + '%'; 
        marker.style.top = y + '%'; 
        
        document.getElementById('mx').value = x.toFixed(2);
        document.getElementById('my').value = y.toFixed(2);
    });

    $('#editDefectForm').on('submit', function(e) {
        e.preventDefault();
        
        const btn = $('#btnUpdate');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> UPDATING...');

        const fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        fd.append('location', $('#locationSelect').val());
        fd.append('category', $('#categorySelect').val());
        fd.append('type', $('#typeSelect').val());
        fd.append('defect', $('#defectSelect').val());
        fd.append('description', $('#descriptionInput').val());
        fd.append('mx', $('#mx').val());
        fd.append('my', $('#my').val());

        const files = document.getElementById('imageInput').files;
        if (files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                fd.append('images[]', files[i]);
            }
        }

        $.ajax({
            url: "{{ route('defects.update', $defect->id) }}",
            type: 'POST',
            data: fd,
            processData: false, contentType: false,
            success: function(resp) {
                if (resp.success) {
                    alert('Defect berjaya dikemaskini!');
                    window.location.href = "{{ route('inspection.show', $inspection->id) }}";
                }
            },
            error: function() { 
                alert('Ralat sistem.'); 
                btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i> UPDATE DEFECT');
            }
        });
    });
</script>
@endsection