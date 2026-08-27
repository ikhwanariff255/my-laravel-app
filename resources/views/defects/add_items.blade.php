@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .layout-container-block {
        position: relative; display: inline-block; cursor: crosshair;
        border: 2px solid #e5e7eb; border-radius: 10px; overflow: hidden; background: #fff; max-width: 100%;
    }
    .marker-point {
        position: absolute; width: 18px; height: 18px; background: #ef4444; border-radius: 50%;
        display: none; transform: translate(-50%, -50%); border: 3px solid white;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.3); pointer-events: none; z-index: 10;
    }
    .defect-saved-summary {
        border-radius: 12px; border-left: 5px solid #10b981; background: #fff; padding: 14px 20px;
        display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    /* Select2 custom tweak for consistency */
    .select2-container .select2-selection--single {
        height: 42px !important;
        border: 1px solid #d1d5db !important;
        border-radius: 0.5rem !important;
        display: flex;
        align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }
</style>

<div class="max-w-7xl mx-auto pb-20">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Add Inspection Item(s)</h1>
        <p class="text-gray-500 mt-1">Project: <span class="px-3 py-1 bg-blue-100 text-blue-800 font-semibold rounded-lg">{{ $inspection->title }}</span></p>
    </div>

    <div id="saved-summary-wrapper" class="space-y-3 mb-6"></div>

    <form id="defectForm">
        <input type="hidden" id="inspection_id" value="{{ $inspection->id }}">
        <div id="defects-wrapper"></div>
        <div class="bg-white rounded-2xl shadow-sm border-t-4 border-emerald-500 mt-6 p-6 flex flex-col sm:flex-row justify-center gap-4">
            <button type="button" id="btnAddMore" class="px-6 py-3 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-sm transition-all">
                <i class="fas fa-plus-circle mr-2"></i> SAVE & ADD ANOTHER
            </button>
            <button type="submit" id="btnSaveAll" class="px-6 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-sm transition-all">
                <i class="fas fa-save mr-2"></i> FINISH & VIEW ALL
            </button>
        </div>
    </form>
</div>

<!-- Modal Draw Canvas -->
<div id="drawModal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-75 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
        <div class="bg-blue-600 px-4 py-3 flex justify-between items-center text-white">
            <h5 class="font-bold"><i class="fas fa-paint-brush mr-2"></i> Mark Defect</h5>
            <button onclick="closeDrawModal()" class="text-white hover:text-gray-200 text-xl font-bold">&times;</button>
        </div>
        <div class="p-4 bg-gray-50 flex flex-col items-center">
            <div class="flex w-full gap-2 mb-4">
                <button type="button" class="flex-1 py-2 rounded-lg border-2 border-blue-600 text-blue-600 font-semibold" id="btnToolPen"><i class="fas fa-pen mr-1"></i> Pen</button>
                <button type="button" class="flex-1 py-2 rounded-lg bg-blue-600 text-white font-semibold" id="btnToolCircle"><i class="far fa-circle mr-1"></i> Circle</button>
            </div>
            <div class="w-full px-2 mb-2 text-center" id="circleControls">
                <label class="text-sm text-gray-500 font-bold block">Circle Size: <span id="circleSizeLabel">60</span>px</label>
                <input type="range" id="circleSizeSlider" min="20" max="250" value="60" class="w-full mt-2">
                <small class="text-red-500 font-bold block mt-2">Drag the RED circle to move it</small>
            </div>
            <div class="w-full overflow-hidden border-2 border-dashed border-blue-500 rounded-lg bg-white touch-none">
                <canvas id="drawCanvas" class="w-full h-auto cursor-crosshair"></canvas>
            </div>
        </div>
        <div class="px-4 py-3 bg-white border-t flex justify-between items-center">
            <div class="flex gap-2">
                <button type="button" class="px-3 py-1.5 bg-yellow-400 text-yellow-900 font-bold rounded-lg text-sm" onclick="undoLast()"><i class="fas fa-undo"></i></button>
                <button type="button" class="px-3 py-1.5 bg-red-100 text-red-600 hover:bg-red-200 font-bold rounded-lg text-sm" onclick="clearCanvas()"><i class="fas fa-trash"></i></button>
            </div>
            <button type="button" class="px-6 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-lg" onclick="saveDrawing()"><i class="fas fa-check mr-1"></i> Done</button>
        </div>
    </div>
</div>

<!-- Template Block -->
<template id="defect-template">
    <div class="mb-8 relative" id="block_{INDEX}">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            <div class="md:col-span-7">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="font-bold text-gray-800"><i class="fas fa-map-marker-alt text-red-500 mr-2"></i> Mark Area Location <span class="text-red-500">*</span> (Item #{DISP_INDEX})</h3>
                    </div>
                    <div class="p-6 bg-gray-50 flex flex-col items-center">
                        <div class="layout-container-block w-full max-w-md" id="layout-container-{INDEX}">
                            <img src="{{ asset('storage/' . $inspection->layout_img) }}" id="layout-img-{INDEX}" class="w-full object-contain max-h-[500px]">
                            <div class="marker-point" id="marker-{INDEX}"></div>
                        </div>
                        <p class="text-red-500 mt-3 text-sm font-bold" id="marker-warning-{INDEX}">Click on the plan to place the red dot.</p>
                        <input type="hidden" id="mx-{INDEX}" value="0">
                        <input type="hidden" id="my-{INDEX}" value="0">
                    </div>
                </div>
            </div>
            <div class="md:col-span-5">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location / Area <span class="text-red-500">*</span></label>
                        <select id="locationSelect-{INDEX}" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500" required>
                            <option value="">-- Type or Select Location --</option>
                            <option value="Living Room">Living Room</option>
                            <option value="Dining Area">Dining Area</option>
                            <option value="Kitchen">Kitchen</option>
                            <option value="Master Bedroom">Master Bedroom</option>
                            <option value="Bedroom 2">Bedroom 2</option>
                            <option value="Bedroom 3">Bedroom 3</option>
                            <option value="Bathroom 1">Bathroom 1</option>
                            <option value="Bathroom 2">Bathroom 2</option>
                            <option value="Balcony">Balcony</option>
                            <option value="Yard">Yard</option>
                            <option value="Car Porch">Car Porch</option>
                            <option value="Family Area">Family Area</option>
                            <option value="Staircase">Staircase</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                        <select id="categorySelect-{INDEX}" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500" required>
                            <option value="">-- Type or Select Category --</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Element Type <span class="text-red-500">*</span></label>
                        <select id="typeSelect-{INDEX}" class="w-full px-4 py-2 border rounded-lg bg-white" required>
                            <option value="">-- Type or Select Type --</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Defect <span class="text-red-500">*</span></label>
                        <select id="defectSelect-{INDEX}" class="w-full px-4 py-2 border rounded-lg bg-white" required>
                            <option value="">-- Type or Select Defect --</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                        <textarea id="descriptionInput-{INDEX}" rows="2" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500" placeholder="Details..." required></textarea>
                    </div>
                    <div class="border-t pt-4">
                        <label class="block font-bold text-blue-600 mb-2">Upload Evidence (Max 3) <span class="text-red-500">*</span></label>
                        <input type="file" id="ui-image-input-{INDEX}" accept="image/*" multiple class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <div id="preview-container-{INDEX}" class="flex gap-3 mt-3 flex-wrap"></div>
                        <div id="upload-status-{INDEX}" class="mt-2 text-blue-500 text-sm hidden"><i class="fas fa-spinner fa-spin mr-1"></i> Processing images...</div>
                    </div>
                </div>
            </div>
        </div>
        <hr class="border-t-2 border-dashed border-gray-200 my-8">
    </div>
</template>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    const defectData = {
        "WALL": {
            "Wall Tiles": ["Hollow", "Crack Tile", "Lippage", "Uneven", "Missing / Poor Grout", "Stain Mark", "Loose Tile", "Others"],
            "Plastered Wall": ["Crack", "Hairline Crack", "Uneven Surface", "Rough Finish", "Debonding", "Dampness", "Others"],
            "Painted Wall": ["Paint Peeling", "Uneven Colour", "Brush Mark", "Stain Mark", "Dampness", "Bubbling Paint", "Others"],
            "Skim Coat Wall": ["Hairline Crack", "Uneven Surface", "Poor Finishing", "Peeling", "Dent", "Others"],
            "Exposed Concrete Wall": ["Honeycomb", "Crack", "Uneven Surface", "Stain", "Poor Finishing", "Others"],
            "Brick Wall (Unfinished)": ["Misalignment", "Uneven Joint", "Crack", "Missing Mortar", "Others"],
            "External Wall Finishing": ["Crack", "Paint Peeling", "Dampness", "Stain", "Algae / Mold", "Others"],
            "Feature Wall": ["Misalignment", "Loose Panel", "Scratch", "Uneven", "Poor Finishing", "Others"]
        },
        "FLOOR": {
            "Floor Tiles": ["Hollow", "Crack Tile", "Lippage", "Uneven Level", "Missing Grout", "Stain", "Others"],
            "Timber Flooring": ["Scratch", "Gap", "Warping", "Uneven", "Loose", "Others"],
            "Vinyl Flooring": ["Bubble", "Peeling", "Uneven", "Gap", "Scratch", "Others"],
            "Marble / Granite": ["Crack", "Stain", "Uneven", "Lippage", "Scratch", "Others"],
            "Cement Screed": ["Crack", "Uneven", "Dusting Surface", "Poor Finishing", "Others"],
            "Concrete Floor": ["Crack", "Uneven", "Surface Damage", "Stain", "Others"],
            "External Pavement": ["Crack", "Uneven", "Settlement", "Ponding Water", "Others"],
            "Car Porch Floor": ["Crack", "Uneven", "Oil Stain", "Ponding Water", "Others"]
        },
        "WINDOW": {
            "Glass Panel": ["Crack", "Scratch", "Stain", "Broken", "Others"],
            "Window Frame": ["Misalignment", "Gap", "Dent", "Rust", "Loose", "Others"],
            "Sliding Window": ["Not Smooth", "Stuck", "Misalignment", "Loose Roller", "Others"],
            "Casement Window": ["Cannot Close Properly", "Misalignment", "Loose Hinge", "Gap", "Others"],
            "Fixed Window": ["Gap", "Improper Installation", "Sealant Issue", "Others"],
            "Window Sealant": ["Crack", "Gap", "Poor Finishing", "Leakage", "Others"]
        },
        "DOOR": {
            "Door Leaf": ["Scratch", "Dent", "Warping", "Misalignment", "Others"],
            "Door Frame": ["Misalignment", "Gap", "Crack", "Loose", "Others"],
            "Sliding Door": ["Not Smooth", "Stuck", "Misalignment", "Roller Issue", "Others"],
            "Glass Door": ["Crack", "Scratch", "Misalignment", "Others"],
            "Door Lock / Handle": ["Not Functioning", "Loose", "Hard to Operate", "Others"],
            "Door Hinge": ["Loose", "Rust", "Noise", "Others"],
            "Door Stopper": ["Loose", "Missing", "Not Functioning", "Others"]
        },
        "CEILING": {
            "Plaster Ceiling": ["Crack", "Uneven", "Water Stain", "Sagging", "Others"],
            "Gypsum Ceiling": ["Crack", "Joint Visible", "Sagging", "Water Damage", "Others"],
            "Skim Coat Ceiling": ["Hairline Crack", "Uneven", "Peeling", "Others"],
            "Concrete Slab": ["Crack", "Honeycomb", "Uneven", "Others"],
            "Ceiling Joint": ["Visible Joint", "Crack", "Poor Finishing", "Others"],
            "Cornice": ["Crack", "Gap", "Misalignment", "Others"]
        },
        "ELECTRICAL": {
            "Power Socket": ["Not Functioning", "Loose", "Burn Mark", "Improper Installation", "Others"],
            "Switch": ["Not Functioning", "Loose", "Hard to Press", "Others"],
            "Lighting Point": ["Not Functioning", "Flickering", "Loose", "Others"],
            "Distribution Board (DB)": ["Improper Labelling", "Loose Wiring", "Safety Issue", "Others"],
            "MCB / RCCB": ["Tripping Issue", "Not Functioning", "Others"],
            "Wiring": ["Exposed Wire", "Loose", "Improper Routing", "Others"],
            "Earthing": ["Not Properly Connected", "Safety Issue", "Others"]
        },
        "PLUMBING": {
            "Water Tap": ["Leakage", "Loose", "Low Pressure", "Others"],
            "Basin": ["Crack", "Leakage", "Stain", "Others"],
            "Sink": ["Leakage", "Blockage", "Scratch", "Others"],
            "WC (Toilet Bowl)": ["Leakage", "Not Flushing Properly", "Loose", "Others"],
            "Shower": ["Leakage", "Low Pressure", "Not Functioning", "Others"],
            "Floor Trap": ["Blockage", "Slow Drainage", "Odor", "Others"],
            "Pipe": ["Leakage", "Crack", "Rust", "Others"],
            "Water Heater": ["Not Functioning", "Leakage", "Low Temperature", "Others"],
            "Water Tank": ["Leakage", "Dirty / Contamination", "Float Valve Issue", "Low Pressure", "Noise", "Others"]
        },
        "ROOF": {
            "Roof Tile": ["Crack", "Broken", "Displacement", "Others"],
            "Metal Roof": ["Rust", "Leakage", "Loose", "Others"],
            "Roof Structure": ["Misalignment", "Damage", "Others"],
            "Gutter": ["Blockage", "Leakage", "Rust", "Others"],
            "Downpipe": ["Blockage", "Leakage", "Disconnected", "Others"],
            "Flashing": ["Loose", "Leakage", "Improper Installation", "Others"]
        },
        "EXTERNAL WORKS": {
            "Drain": ["Blockage", "Crack", "Poor Flow", "Others"],
            "Road / Driveway": ["Crack", "Uneven", "Settlement", "Others"],
            "Turfing / Grass": ["Patchy", "Uneven", "Poor Growth", "Others"],
            "Fence": ["Rust", "Loose", "Misalignment", "Others"],
            "Gate": ["Not Smooth", "Misalignment", "Rust", "Others"],
            "External Finishing": ["Crack", "Peeling", "Stain", "Others"]
        }
    };

    let defectCount = 0;
    let blockDataArrays = {}; 
    let currentEditBlock = -1;
    let currentEditImageIndex = -1;
    const MAX_DEFECTS = 20;
    let savedCount = 0; 
    const INSPECTION_ID = '{{ $inspection->id }}';

    function closeDrawModal() {
        document.getElementById('drawModal').classList.add('hidden');
    }

    $(document).ready(function() {
        addNewDefectBlock();

        $('#btnAddMore').click(async function() {
            if (savedCount >= MAX_DEFECTS) { alert('Maximum 20 defects only.'); return; }
            let lastIndex = defectCount - 1;
            if (!validateBlock(lastIndex)) { alert('Please complete all required fields and pin the location on the layout plan.'); return; }

            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            try {
                await ajaxSaveBlock(lastIndex);
                collapseBlock(lastIndex);
                savedCount++;
                addNewDefectBlock();
            } catch (err) { alert('Failed to save: ' + err); }

            btn.prop('disabled', false).html('<i class="fas fa-plus-circle mr-2"></i> SAVE & ADD ANOTHER');
        });

        $('#defectForm').on('submit', async function(e) {
            e.preventDefault();
            let lastIndex = defectCount - 1;
            if (!validateBlock(lastIndex)) { alert('Please complete all required fields.'); return; }

            const btn = $('#btnSaveAll');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> SAVING...');

            try {
                await ajaxSaveBlock(lastIndex);
                window.location.href = "{{ route('inspection.show', $inspection->id) }}";
            } catch (err) {
                alert('Failed to save: ' + err);
                btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i> FINISH & VIEW ALL');
            }
        });
    });

    function ajaxSaveBlock(idx) {
        return new Promise((resolve, reject) => {
            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('location', $(`#locationSelect-${idx}`).val() || '');
            fd.append('category', $(`#categorySelect-${idx}`).val() || '');
            fd.append('type', $(`#typeSelect-${idx}`).val() || '');
            fd.append('defect', $(`#defectSelect-${idx}`).val() || '');
            fd.append('description', $(`#descriptionInput-${idx}`).val() || '');
            fd.append('mx', $(`#mx-${idx}`).val() || '0');
            fd.append('my', $(`#my-${idx}`).val() || '0');

            const dt = blockDataArrays[idx];
            if (dt && dt.files.length > 0) {
                for (let i = 0; i < dt.files.length; i++) {
                    fd.append('images[]', dt.files[i]);
                }
            }

            $.ajax({
                url: "{{ route('defects.storeRapid', $inspection->id) }}",
                type: 'POST',
                data: fd,
                processData: false, contentType: false,
                success: function(resp) {
                    if (resp.success) resolve(resp.item_id);
                    else reject('Error saving data');
                },
                error: function() { reject('Network error'); }
            });
        });
    }

    function collapseBlock(idx) {
        const location = $(`#locationSelect-${idx}`).val() || '-';
        const defect = $(`#defectSelect-${idx}`).val() || '-';
        const imgCount = blockDataArrays[idx] ? blockDataArrays[idx].files.length : 0;
        const dispNum = savedCount + 1;

        const summaryHtml = `
            <div class="defect-saved-summary">
                <div class="flex items-center">
                    <span class="bg-emerald-100 text-emerald-800 px-3 py-1 rounded-full text-sm font-bold mr-3">Item #${dispNum}</span>
                    <span class="text-gray-700"><strong>${location}</strong> — <em>${defect}</em> <span class="text-gray-400 text-sm ml-2">(${imgCount} image(s))</span></span>
                </div>
                <i class="fas fa-check text-emerald-500 text-xl"></i>
            </div>`;
        $('#saved-summary-wrapper').append(summaryHtml);
        $(`#block_${idx}`).remove();
        delete blockDataArrays[idx];
    }

    function addNewDefectBlock() {
        let tpl = document.getElementById('defect-template').innerHTML;
        const dispNum = savedCount + 1;
        tpl = tpl.replace(/{INDEX}/g, defectCount).replace(/{DISP_INDEX}/g, dispNum);
        $('#defects-wrapper').append(tpl);
        initBlockLogic(defectCount);
        defectCount++;
    }

    function validateBlock(idx) {
        if (!document.getElementById(`block_${idx}`)) return true; 
        if ($(`#mx-${idx}`).val() == 0) return false;
        if (!$(`#locationSelect-${idx}`).val()) return false;
        if (!$(`#categorySelect-${idx}`).val()) return false;
        if (!$(`#typeSelect-${idx}`).val()) return false;
        if (!$(`#defectSelect-${idx}`).val()) return false;
        if (!$(`#descriptionInput-${idx}`).val()) return false;
        if (!blockDataArrays[idx] || blockDataArrays[idx].files.length === 0) return false;
        return true;
    }

    function initBlockLogic(idx) {
        blockDataArrays[idx] = new DataTransfer();

        // Enable Select2 with tags enabled for custom entry on all fields
        $(`#locationSelect-${idx}`).select2({ width: '100%', tags: true, placeholder: '-- Type or Select Location --' });
        $(`#categorySelect-${idx}`).select2({ width: '100%', tags: true, placeholder: '-- Type or Select Category --' });
        $(`#typeSelect-${idx}`).select2({ width: '100%', tags: true, placeholder: '-- Type or Select Type --' });
        $(`#defectSelect-${idx}`).select2({ width: '100%', tags: true, placeholder: '-- Type or Select Defect --' });

        const catSelect = $(`#categorySelect-${idx}`);
        for (let cat in defectData) {
            catSelect.append(new Option(cat, cat, false, false));
        }
        catSelect.trigger('change');

        catSelect.on('change', function() {
            const selectedCat = $(this).val();
            const typeSelect = $(`#typeSelect-${idx}`);
            const defectSelect = $(`#defectSelect-${idx}`);
            
            typeSelect.empty().append('<option value="">-- Type or Select Type --</option>');
            defectSelect.empty().append('<option value="">-- Type or Select Defect --</option>').trigger('change');

            if (selectedCat && defectData[selectedCat]) {
                for (let type in defectData[selectedCat]) {
                    typeSelect.append(new Option(type, type, false, false));
                }
                typeSelect.trigger('change');
            }
        });

        $(`#typeSelect-${idx}`).on('change', function() {
            const selectedType = $(this).val();
            const selectedCat = $(`#categorySelect-${idx}`).val();
            const defectSelect = $(`#defectSelect-${idx}`);
            
            defectSelect.empty().append('<option value="">-- Type or Select Defect --</option>');
            
            if (selectedCat && selectedType && defectData[selectedCat] && defectData[selectedCat][selectedType]) {
                defectData[selectedCat][selectedType].forEach(def => {
                    defectSelect.append(new Option(def, def, false, false));
                });
            }
            defectSelect.trigger('change');
        });

        document.getElementById(`layout-img-${idx}`).addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            const marker = document.getElementById(`marker-${idx}`);
            marker.style.left = x + '%'; marker.style.top = y + '%'; marker.style.display = 'block';
            document.getElementById(`mx-${idx}`).value = x.toFixed(2);
            document.getElementById(`my-${idx}`).value = y.toFixed(2);
            document.getElementById(`marker-warning-${idx}`).innerHTML = "<span class='text-emerald-500'><i class='fas fa-check'></i> Marker set!</span>";
        });

        const uiInput = document.getElementById(`ui-image-input-${idx}`);
        uiInput.addEventListener('change', async function(e) {
            const files = e.target.files;
            if (!files || files.length === 0) return;
            let dt = blockDataArrays[idx];
            if (dt.items.length + files.length > 3) {
                alert(`Maximum 3 images allowed.`); this.value = ''; return;
            }
            document.getElementById(`upload-status-${idx}`).classList.remove('hidden');
            uiInput.disabled = true;
            for (let i = 0; i < files.length; i++) {
                try {
                    const croppedFile = await processAutoCrop(files[i]);
                    dt.items.add(croppedFile);
                } catch (err) { console.error(err); }
            }
            blockDataArrays[idx] = dt;
            updateBlockPreview(idx);
            this.value = '';
            document.getElementById(`upload-status-${idx}`).classList.add('hidden');
            uiInput.disabled = false;
        });
    }

    function processAutoCrop(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = function(event) {
                const img = new Image();
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    const targetSize = 800; canvas.width = targetSize; canvas.height = targetSize;
                    let sourceX, sourceY, sourceSize;
                    if (img.width > img.height) {
                        sourceSize = img.height; sourceX = (img.width - sourceSize) / 2; sourceY = 0;
                    } else {
                        sourceSize = img.width; sourceX = 0; sourceY = (img.height - sourceSize) / 2;
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

    function updateBlockPreview(idx) {
        const previewContainer = document.getElementById(`preview-container-${idx}`);
        let dt = blockDataArrays[idx];
        previewContainer.innerHTML = '';

        for (let i = 0; i < dt.files.length; i++) {
            const file = dt.files[i];
            const imgWrapper = document.createElement('div');
            imgWrapper.style.position = 'relative';

            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.className = 'w-24 h-24 object-cover rounded-lg border-2 border-gray-200';

            const btnDel = document.createElement('button');
            btnDel.innerHTML = '&times;';
            btnDel.className = "absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center font-bold hover:bg-red-600 shadow-sm";
            btnDel.onclick = function(e) { e.preventDefault(); removeBlockImage(idx, i); };

            const btnEdit = document.createElement('button');
            btnEdit.innerHTML = '<i class="fas fa-pen"></i>';
            btnEdit.className = "absolute -top-2 right-6 bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-blue-600 shadow-sm";
            btnEdit.onclick = function(e) { e.preventDefault(); openEditorModal(idx, i); };

            imgWrapper.appendChild(img); imgWrapper.appendChild(btnEdit); imgWrapper.appendChild(btnDel);
            previewContainer.appendChild(imgWrapper);
        }
    }

    function removeBlockImage(blockIdx, imgIdx) {
        let oldDt = blockDataArrays[blockIdx]; let newDt = new DataTransfer();
        for (let i = 0; i < oldDt.files.length; i++) { if (i !== imgIdx) newDt.items.add(oldDt.files[i]); }
        blockDataArrays[blockIdx] = newDt; updateBlockPreview(blockIdx);
    }

    /* === CANVAS EDITOR LOGIC === */
    let currentTool = 'circle'; const drawCanvas = document.getElementById('drawCanvas'); const ctx = drawCanvas.getContext('2d');
    let baseImageObj = new Image(); let drawObjects = []; let currentPath = null;
    let isDrawing = false, isDragging = false, selectedObj = null, dragOffset = {x: 0, y: 0};

    $('#btnToolPen').click(function() { currentTool = 'pen'; $(this).removeClass('border-2 text-blue-600').addClass('bg-blue-600 text-white'); $('#btnToolCircle').removeClass('bg-blue-600 text-white').addClass('border-2 text-blue-600'); $('#circleControls').hide(); selectedObj = null; renderCanvas(); });
    $('#btnToolCircle').click(function() { currentTool = 'circle'; $(this).removeClass('border-2 text-blue-600').addClass('bg-blue-600 text-white'); $('#btnToolPen').removeClass('bg-blue-600 text-white').addClass('border-2 text-blue-600'); $('#circleControls').show(); });
    $('#circleSizeSlider').on('input', function() { let newSize = parseInt($(this).val()); $('#circleSizeLabel').text(newSize); if (selectedObj && selectedObj.type === 'circle') { selectedObj.r = newSize; renderCanvas(); } });

    window.openEditorModal = function(blockIdx, imgIdx) {
        currentEditBlock = blockIdx; currentEditImageIndex = imgIdx; drawObjects = []; selectedObj = null;
        const file = blockDataArrays[blockIdx].files[imgIdx]; const reader = new FileReader();
        reader.onload = function(e) {
            baseImageObj.onload = function() { drawCanvas.width = baseImageObj.width; drawCanvas.height = baseImageObj.height; renderCanvas(); document.getElementById('drawModal').classList.remove('hidden'); }
            baseImageObj.src = e.target.result;
        }
        reader.readAsDataURL(file);
    };

    function renderCanvas() {
        ctx.clearRect(0, 0, drawCanvas.width, drawCanvas.height); ctx.drawImage(baseImageObj, 0, 0);
        drawObjects.forEach(obj => {
            if (obj.type === 'pen') {
                ctx.beginPath(); ctx.lineWidth = 12; ctx.lineCap = "round"; ctx.lineJoin = "round"; ctx.strokeStyle = "#ef4444";
                obj.path.forEach((p, i) => { if (i === 0) ctx.moveTo(p.x, p.y); else ctx.lineTo(p.x, p.y); }); ctx.stroke();
            } else if (obj.type === 'circle') {
                ctx.beginPath(); ctx.arc(obj.x, obj.y, obj.r, 0, 2 * Math.PI); ctx.lineWidth = 10;
                if (obj === selectedObj) { ctx.strokeStyle = "#ff0000"; ctx.shadowColor = "#ff0000"; ctx.shadowBlur = 15; } else { ctx.strokeStyle = "#ef4444"; ctx.shadowBlur = 0; }
                ctx.stroke(); ctx.shadowBlur = 0;
            }
        });
    }

    window.undoLast = function() { if (drawObjects.length > 0) { drawObjects.pop(); selectedObj = null; renderCanvas(); } };
    window.clearCanvas = function() { drawObjects = []; selectedObj = null; renderCanvas(); };

    function getMousePos(evt) {
        const rect = drawCanvas.getBoundingClientRect(); const scaleX = drawCanvas.width / rect.width; const scaleY = drawCanvas.height / rect.height;
        let clientX = evt.clientX, clientY = evt.clientY;
        if (evt.touches && evt.touches.length > 0) { clientX = evt.touches[0].clientX; clientY = evt.touches[0].clientY; }
        return { x: (clientX - rect.left) * scaleX, y: (clientY - rect.top) * scaleY };
    }
    function isInsideCircle(pos, circle) { const dx = pos.x - circle.x, dy = pos.y - circle.y; return (dx * dx + dy * dy) <= (circle.r + 20) * (circle.r + 20); }

    function startInteraction(e) {
        const pos = getMousePos(e);
        if (currentTool === 'pen') { isDrawing = true; currentPath = { type: 'pen', path: [pos] }; drawObjects.push(currentPath); renderCanvas(); }
        else if (currentTool === 'circle') {
            selectedObj = null;
            for (let i = drawObjects.length - 1; i >= 0; i--) { let obj = drawObjects[i]; if (obj.type === 'circle' && isInsideCircle(pos, obj)) { selectedObj = obj; isDragging = true; dragOffset.x = pos.x - obj.x; dragOffset.y = pos.y - obj.y; $('#circleSizeSlider').val(obj.r); $('#circleSizeLabel').text(obj.r); break; } }
            if (!selectedObj) { let newCircle = { type: 'circle', x: pos.x, y: pos.y, r: parseInt($('#circleSizeSlider').val()) }; drawObjects.push(newCircle); selectedObj = newCircle; isDragging = true; dragOffset = {x: 0, y: 0}; }
            renderCanvas();
        }
    }

    function moveInteraction(e) {
        if (!isDrawing && !isDragging) return; const pos = getMousePos(e);
        if (currentTool === 'pen' && isDrawing) { currentPath.path.push(pos); renderCanvas(); }
        else if (currentTool === 'circle' && isDragging && selectedObj) { selectedObj.x = pos.x - dragOffset.x; selectedObj.y = pos.y - dragOffset.y; renderCanvas(); }
    }
    function endInteraction() { isDrawing = false; isDragging = false; currentPath = null; }

    drawCanvas.addEventListener('mousedown', startInteraction); drawCanvas.addEventListener('mousemove', moveInteraction); drawCanvas.addEventListener('mouseup', endInteraction); drawCanvas.addEventListener('mouseout', endInteraction);
    drawCanvas.addEventListener('touchstart', function(e) { e.preventDefault(); startInteraction(e); }, { passive: false }); drawCanvas.addEventListener('touchmove', function(e) { e.preventDefault(); moveInteraction(e); }, { passive: false }); drawCanvas.addEventListener('touchend', function(e) { e.preventDefault(); endInteraction(); }, { passive: false });

    window.saveDrawing = function() {
        selectedObj = null; renderCanvas();
        drawCanvas.toBlob((blob) => {
            let oldDt = blockDataArrays[currentEditBlock]; const oldFile = oldDt.files[currentEditImageIndex];
            const newFile = new File([blob], oldFile.name, { type: 'image/jpeg' });
            let newDt = new DataTransfer();
            for (let i = 0; i < oldDt.files.length; i++) { if (i === currentEditImageIndex) newDt.items.add(newFile); else newDt.items.add(oldDt.files[i]); }
            blockDataArrays[currentEditBlock] = newDt; updateBlockPreview(currentEditBlock);
            closeDrawModal();
        }, 'image/jpeg', 0.85);
    };
</script>
@endsection