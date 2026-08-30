@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .layout-container-block {
    position: relative; 
    display: inline-block; /* Supaya kotak ikut bentuk sebenar gambar */
    cursor: crosshair;
    border: 2px solid #e5e7eb; 
    border-radius: 10px; 
    overflow: hidden; 
    max-width: 450px;
    line-height: 0; /* Penting: Buang gap putih di bahagian bawah gambar */
}
.layout-container-block img {
    width: 100%;
    height: auto;
    display: block; /* Penting: Jadikan imej sebagai blok supaya tak ada ruang lebih */
}
    /* Wajib ada untuk paparkan titik merah */
    .marker-point {
        position: absolute; 
        width: 18px; 
        height: 18px; 
        background: #ef4444; 
        border-radius: 50%;
        transform: translate(-50%, -50%); 
        border: 3px solid white;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.3); 
        pointer-events: none; 
        z-index: 10;
        display: none; /* Akan ditukar jadi block melalui JS lepas klik */
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
                        <img src="{{ $inspection->layout_url }}" id="layout-img" style="width: 100%; height: auto; display: block; border-radius: 8px;">                                <div class="marker-point" id="marker" style="left: {{ $defect->mark_x }}%; top: {{ $defect->mark_y }}%; display: block;"></div>
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
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-sm font-medium text-gray-700">Description <span class="text-red-500">*</span></label>
                            <span id="word-count" class="text-xs text-gray-400 font-medium">0 / 30 words</span>
                        </div>
                        <textarea id="descriptionInput" rows="2" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500" placeholder="Details (Max 30 words)..." required>{{ $defect->desc }}</textarea>
                    </div>

                    <div class="border-t pt-4">
                        <label class="block font-bold text-blue-600 mb-2">Upload Evidence (Max 4)</label>
                        <p class="text-xs text-gray-500 mb-2">Gambar sedia ada akan dikekalkan sekiranya anda tidak memuat naik gambar baharu.</p>
                        
                        <!-- Paparan Gambar Sedia Ada -->
<div class="flex gap-3 mb-3 flex-wrap">
                            @if(!empty($defect->image_urls))
                                @foreach($defect->image_urls as $secureUrl)
                                    <div class="relative">
                                        <!-- Directly use the secure S3 URL -->
                                        <img src="{{ $secureUrl }}" class="w-20 h-20 object-cover rounded-lg border-2 border-gray-200">
                                        <span class="absolute -top-2 -right-2 bg-blue-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] font-bold shadow-sm">DB</span>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <input type="file" id="imageInput" accept="image/*" multiple class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <div id="preview-container" class="flex gap-3 mt-3 flex-wrap"></div>
                        <div id="upload-status" class="mt-2 text-blue-500 text-sm hidden"><i class="fas fa-spinner fa-spin mr-1"></i> Processing & auto-cropping images...</div>
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
    let editBlockData = new DataTransfer();

    // --- 1. KAUNTER & HAD 30 PERKATAAN UNTUK DESCRIPTION ---
    const descInput = document.getElementById('descriptionInput');
    const wordCounter = document.getElementById('word-count');

    function updateWordCount() {
        let text = descInput.value.trim();
        let words = text === '' ? [] : text.split(/\s+/);
        
        if (words.length > 30) {
            descInput.value = words.slice(0, 30).join(' ');
            words = words.slice(0, 30);
        }
        
        wordCounter.textContent = `${words.length} / 30 words`;
        if (words.length >= 30) {
            wordCounter.classList.add('text-red-500', 'font-bold');
        } else {
            wordCounter.classList.remove('text-red-500', 'font-bold');
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        updateWordCount();
    });
    descInput.addEventListener('input', updateWordCount);

    // --- 2. LOGIK MARKER MAP ---
    // --- 2. LOGIK MARKER MAP YANG TEPAT ---
   document.getElementById('layout-img').addEventListener('click', function(e) {
    const rect = this.getBoundingClientRect();
    
    // Kira kedudukan klik tepat di atas permukaan render imej
    const clickX = e.clientX - rect.left;
    const clickY = e.clientY - rect.top;
    
    // Tukar kepada peratusan (%) berpandukan saiz imej yang terpapar
    let x = (clickX / rect.width) * 100;
    let y = (clickY / rect.height) * 100;
    
    // Hadkan peratusan dalam lingkungan 0-100% sahaja
    x = Math.max(0, Math.min(100, x));
    y = Math.max(0, Math.min(100, y));
    
    const marker = document.getElementById('marker');
    marker.style.left = x + '%'; 
    marker.style.top = y + '%'; 
    marker.style.display = 'block';
    
    document.getElementById('mx').value = x.toFixed(2);
    document.getElementById('my').value = y.toFixed(2);
});
    // Pastikan titik merah sedia ada terpapar jika nilai DB wujud semasa halaman dimuatkan
    document.addEventListener("DOMContentLoaded", function() {
        updateWordCount();
        
        const mxVal = document.getElementById('mx').value;
        const myVal = document.getElementById('my').value;
        if (mxVal && myVal && mxVal > 0) {
            const marker = document.getElementById('marker');
            marker.style.left = mxVal + '%';
            marker.style.top = myVal + '%';
            marker.style.display = 'block';
        }
    });

    // --- 3. AUTO-CROP & PREVIEW GAMBAR BARU ---
    const imageInput = document.getElementById('imageInput');
    imageInput.addEventListener('change', async function(e) {
        const files = e.target.files;
        if (!files || files.length === 0) return;

        if (editBlockData.items.length + files.length > 4) {
            alert('Maximum 4 images allowed.');
            this.value = '';
            return;
        }

        document.getElementById('upload-status').classList.remove('hidden');
        imageInput.disabled = true;

        for (let i = 0; i < files.length; i++) {
            try {
                const croppedFile = await processAutoCrop(files[i]);
                editBlockData.items.add(croppedFile);
            } catch (err) {
                console.error(err);
            }
        }

        updatePreview();
        this.value = '';
        document.getElementById('upload-status').classList.add('hidden');
        imageInput.disabled = false;
    });

    function processAutoCrop(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = function(event) {
                const img = new Image();
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    const targetSize = 800; 
                    canvas.width = targetSize; 
                    canvas.height = targetSize;
                    let sourceX, sourceY, sourceSize;
                    if (img.width > img.height) {
                        sourceSize = img.height; 
                        sourceX = (img.width - sourceSize) / 2; 
                        sourceY = 0;
                    } else {
                        sourceSize = img.width; 
                        sourceX = 0; 
                        sourceY = (img.height - sourceSize) / 2;
                    }
                    ctx.drawImage(img, sourceX, sourceY, sourceSize, sourceSize, 0, 0, targetSize, targetSize);
                    canvas.toBlob((blob) => {
                        resolve(new File([blob], "crop_" + file.name, { type: 'image/jpeg' }));
                    }, 'image/jpeg', 0.85);
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    function updatePreview() {
        const previewContainer = document.getElementById('preview-container');
        previewContainer.innerHTML = '';

        for (let i = 0; i < editBlockData.files.length; i++) {
            const file = editBlockData.files[i];
            const imgWrapper = document.createElement('div');
            imgWrapper.style.position = 'relative';

            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.className = 'w-20 h-20 object-cover rounded-lg border-2 border-gray-200';

            const btnDel = document.createElement('button');
            btnDel.innerHTML = '&times;';
            btnDel.className = "absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center font-bold hover:bg-red-600 shadow-sm";
            btnDel.onclick = function(e) {
                e.preventDefault();
                let newDt = new DataTransfer();
                for (let j = 0; j < editBlockData.files.length; j++) {
                    if (j !== i) newDt.items.add(editBlockData.files[j]);
                }
                editBlockData = newDt;
                updatePreview();
            };

            imgWrapper.appendChild(img);
            imgWrapper.appendChild(btnDel);
            previewContainer.appendChild(imgWrapper);
        }
    }

    // --- 4. SUBMIT BORANG KEMASKINI ---
    $('#editDefectForm').on('submit', function(e) {
        e.preventDefault();
        
        // Semak had 30 perkataan
        const descVal = $('#descriptionInput').val().trim();
        let wordCount = descVal === '' ? 0 : descVal.split(/\s+/).length;
        if (wordCount > 30) {
            alert('Description cannot exceed 30 words.');
            return;
        }

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

        if (editBlockData.files.length > 0) {
            for (let i = 0; i < editBlockData.files.length; i++) {
                fd.append('images[]', editBlockData.files[i]);
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
                } else {
                    alert('Gagal mengemas kini.');
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i> UPDATE DEFECT');
                }
            },
            error: function() { 
                alert('Ralat sistem berlaku.'); 
                btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i> UPDATE DEFECT');
            }
        });
    });
</script>
@endsection