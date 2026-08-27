@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Update Inspection Project</h2>
        <p class="text-gray-500 text-sm mt-1">Modify details or re-upload images if necessary.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                <ul class="list-disc ml-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('inspection.update', $inspection->id) }}" method="POST" enctype="multipart/form-data">
            @csrf 
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Project Title -->
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Project Title / Property Name</label>
                    <input type="text" name="title" value="{{ old('title', $inspection->title) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                </div>

                <!-- Client Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Client / Owner Name</label>
                    <input type="text" name="clientname" value="{{ old('clientname', $inspection->clientname) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                </div>

                <!-- Property Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Property Type</label>
                    <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none bg-white" required>
                        <option value="1-Storey Terrace" {{ old('type', $inspection->type) == '1-Storey Terrace' || $inspection->type == 'Teres Setingkat' ? 'selected' : '' }}>1-Storey Terrace</option>
                        <option value="2-Storey Terrace" {{ old('type', $inspection->type) == '2-Storey Terrace' || $inspection->type == 'Teres 2 Tingkat' ? 'selected' : '' }}>2-Storey Terrace</option>
                        <option value="Semi-D" {{ old('type', $inspection->type) == 'Semi-D' ? 'selected' : '' }}>Semi-D</option>
                        <option value="Bungalow" {{ old('type', $inspection->type) == 'Bungalow' || $inspection->type == 'Banglo' ? 'selected' : '' }}>Bungalow</option>
                        <option value="Condominium / Apartment" {{ old('type', $inspection->type) == 'Condominium / Apartment' || $inspection->type == 'Kondominium / Apartment' ? 'selected' : '' }}>Condominium / Apartment</option>
                    </select>
                </div>

                <!-- Assigned Staff (Multiple Selection) -->
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Assigned Staff In Charge (Select one or more)</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-4 border border-gray-300 rounded-lg bg-gray-50 max-h-48 overflow-y-auto">
                        @php
                            $assignedStaffIds = old('user_id', $inspection->staffs->pluck('id')->toArray());
                        @endphp
                        @foreach($staffs as $staff)
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="checkbox" name="user_id[]" value="{{ $staff->id }}" 
                                    {{ in_array($staff->id, $assignedStaffIds) ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="text-sm text-gray-700 font-medium">{{ $staff->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Address -->
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Address</label>
                    <textarea name="address" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none" required>{{ old('address', $inspection->address) }}</textarea>
                </div>

                <!-- State -->
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">State</label>
                    <select name="state" id="stateSelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none bg-white" required>
                        <option value="" disabled>Select state...</option>
                        <option value="Johor" {{ old('state', $inspection->state) == 'Johor' ? 'selected' : '' }}>Johor</option>
                        <option value="Kedah" {{ old('state', $inspection->state) == 'Kedah' ? 'selected' : '' }}>Kedah</option>
                        <option value="Kelantan" {{ old('state', $inspection->state) == 'Kelantan' ? 'selected' : '' }}>Kelantan</option>
                        <option value="Melaka" {{ old('state', $inspection->state) == 'Melaka' ? 'selected' : '' }}>Melaka</option>
                        <option value="Negeri Sembilan" {{ old('state', $inspection->state) == 'Negeri Sembilan' ? 'selected' : '' }}>Negeri Sembilan</option>
                        <option value="Pahang" {{ old('state', $inspection->state) == 'Pahang' ? 'selected' : '' }}>Pahang</option>
                        <option value="Perak" {{ old('state', $inspection->state) == 'Perak' ? 'selected' : '' }}>Perak</option>
                        <option value="Perlis" {{ old('state', $inspection->state) == 'Perlis' ? 'selected' : '' }}>Perlis</option>
                        <option value="Pulau Pinang" {{ old('state', $inspection->state) == 'Pulau Pinang' ? 'selected' : '' }}>Pulau Pinang</option>
                        <option value="Sabah" {{ old('state', $inspection->state) == 'Sabah' ? 'selected' : '' }}>Sabah</option>
                        <option value="Sarawak" {{ old('state', $inspection->state) == 'Sarawak' ? 'selected' : '' }}>Sarawak</option>
                        <option value="Selangor" {{ old('state', $inspection->state) == 'Selangor' ? 'selected' : '' }}>Selangor</option>
                        <option value="Terengganu" {{ old('state', $inspection->state) == 'Terengganu' ? 'selected' : '' }}>Terengganu</option>
                        <option value="W.P. Kuala Lumpur" {{ old('state', $inspection->state) == 'W.P. Kuala Lumpur' ? 'selected' : '' }}>W.P. Kuala Lumpur</option>
                        <option value="W.P. Labuan" {{ old('state', $inspection->state) == 'W.P. Labuan' ? 'selected' : '' }}>W.P. Labuan</option>
                        <option value="W.P. Putrajaya" {{ old('state', $inspection->state) == 'W.P. Putrajaya' ? 'selected' : '' }}>W.P. Putrajaya</option>
                    </select>
                </div>

                <!-- City -->
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                    <select name="city" id="citySelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none bg-white disabled:bg-gray-100 disabled:text-gray-400" required disabled>
                        <option value="" disabled selected>Select state first...</option>
                    </select>
                </div>

                <!-- HOUSE IMAGE -->
                <div class="col-span-1 md:col-span-2 border-t border-gray-100 pt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">House Image (Front View)</label>
                    @if($inspection->img)
                        <div class="mb-3">
                            <p class="text-xs text-gray-400 mb-1">Current Image:</p>
                            <img src="{{ asset('storage/' . $inspection->img) }}" class="w-32 h-24 object-cover rounded-lg border">
                        </div>
                    @endif
                    <input type="file" id="imageInput" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700">
                    <input type="hidden" name="cropped_image" id="croppedImageOutput">

                    <div id="cropperContainer" class="mt-4 hidden bg-gray-50 p-4 rounded-xl border border-dashed border-gray-300">
                        <div class="max-w-md mb-4 overflow-hidden">
                            <img id="imagePreview" src="" alt="Preview" class="max-h-72 block">
                        </div>
                        <button type="button" id="cropButton" class="px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700">Confirm Crop House Image</button>
                        <span id="cropStatus" class="ml-3 text-sm text-emerald-600 font-medium hidden">✓ Successfully cropped!</span>
                    </div>
                </div>

                <!-- LAYOUT PLAN -->
                <div class="col-span-1 md:col-span-2 border-t border-gray-100 pt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Layout Plan (Portrait)</label>
                    @if($inspection->layout_img)
                        <div class="mb-3">
                            <p class="text-xs text-gray-400 mb-1">Current Plan:</p>
                            <img src="{{ asset('storage/' . $inspection->layout_img) }}" class="w-24 h-32 object-cover rounded-lg border">
                        </div>
                    @endif
                    <input type="file" id="layoutInput" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700">
                    <input type="hidden" name="cropped_layout" id="croppedLayoutOutput">

                    <div id="layoutCropperContainer" class="mt-4 hidden bg-gray-50 p-4 rounded-xl border border-dashed border-gray-300">
                        <div class="max-w-xs mb-4 overflow-hidden">
                            <img id="layoutPreview" src="" alt="Layout Preview" class="max-h-96 block">
                        </div>
                        <button type="button" id="cropLayoutButton" class="px-4 py-2 bg-purple-600 text-white text-sm font-semibold rounded-lg hover:bg-purple-700">Confirm Crop Layout</button>
                        <span id="layoutCropStatus" class="ml-3 text-sm text-purple-600 font-medium hidden">✓ Layout successfully cropped!</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-100">
                <a href="{{ route('inspection.index') }}" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">Cancel</a>
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Cropper.js Script -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // --- DATA BINDING FOR STATE & CITY ---
        const malaysiaCities = {
            "Johor": ["Johor Bahru", "Tebrau", "Pasir Gudang", "Bukit Indah", "Skudai", "Batu Pahat", "Kluang", "Muar", "Kulai", "Segamat", "Pontian", "Kota Tinggi", "Mersing", "Tangkak", "Yong Peng", "Pekan Nanas", "Labis", "Simpang Renggam"],
            "Kedah": ["Alor Setar", "Sungai Petani", "Kulim", "Langkawi", "Baling", "Jitra", "Yan", "Sik", "Padang Terap", "Kuala Nerang", "Pokok Sena", "Pendang", "Gurun", "Bedong", "Kuala Ketil"],
            "Kelantan": ["Kota Bharu", "Pasir Mas", "Tumpat", "Bachok", "Tanah Merah", "Pasir Puteh", "Kuala Krai", "Gua Musang", "Jeli", "Tok Bali", "Rantau Panjang", "Pengkalan Chepa"],
            "Melaka": ["Melaka City", "Alor Gajah", "Jasin", "Masjid Tanah", "Ayer Keroh", "Sungai Udang", "Batu Berendam", "Klebang", "Bemban"],
            "Negeri Sembilan": ["Seremban", "Port Dickson", "Nilai", "Jempol", "Tampin", "Kuala Pilah", "Rembau", "Bahau", "Senawang", "Mantir", "Labu"],
            "Pahang": ["Kuantan", "Temerloh", "Bentong", "Mentakab", "Pekan", "Raub", "Maran", "Kuala Lipis", "Cameron Highlands", "Jerantut", "Bera", "Rompin", "Muadzam Shah", "Genting Highlands"],
            "Perak": ["Ipoh", "Taiping", "Sitiawan", "Teluk Intan", "Batu Gajah", "Lumut", "Kuala Kangsar", "Kampar", "Tapah", "Bidor", "Tanjung Malim", "Parit Buntar", "Bagan Serai", "Gerik", "Seri Manjung", "Pantai Remis"],
            "Perlis": ["Kangar", "Arau", "Padang Besar", "Kuala Perlis", "Simpang Empat"],
            "Pulau Pinang": ["George Town", "Butterworth", "Bukit Mertajam", "Bayan Lepas", "Perai", "Kepala Batas", "Gelugor", "Ayer Itam", "Tanjung Bungah", "Nibong Tebal", "Simpang Ampat", "Balik Pulau"],
            "Sabah": ["Kota Kinabalu", "Sandakan", "Tawau", "Lahad Datu", "Keningau", "Putatan", "Donggongon", "Semporna", "Kudat", "Kunta", "Beaufort", "Ranau", "Papar", "Tenom", "Kota Belud"],
            "Sarawak": ["Kuching", "Miri", "Sibu", "Bintulu", "Limbang", "Sarikei", "Sri Aman", "Kapit", "Kota Samarahan", "Mukah", "Betong", "Bau", "Lundu", "Lawas", "Serian"],
            "Selangor": ["Shah Alam", "Klang", "Petaling Jaya", "Subang Jaya", "Puchong", "Kajang", "Rawang", "Semenyih", "Banting", "Sepang", "Cyberjaya", "Ampang", "Cheras", "Seri Kembangan", "Puncak Alam", "Sungi Buloh", "Hulu Langat", "Kuala Selangor", "Kuala Kubu Bharu", "Sabak Bernam", "Gombak", "Damansara", "Petaling"],
            "Terengganu": ["Kuala Terengganu", "Cukai", "Dungun", "Kerteh", "Kuala Berang", "Marang", "Besut", "Setiu", "Jerteh", "Paka", "Kuala Nerus"],
            "W.P. Kuala Lumpur": ["Kuala Lumpur", "Kepong", "Cheras", "Setapak", "Bukit Bintang", "Bangsar", "Mont Kiara", "Sentul", "Pudu", "Wangsa Maju", "Segambut", "Lembah Pantai"],
            "W.P. Labuan": ["Labuan", "Victoria"],
            "W.P. Putrajaya": ["Putrajaya", "Presint 1", "Presint 5", "Presint 11", "Presint 15"]
        };

        const stateSelect = document.getElementById('stateSelect');
        const citySelect = document.getElementById('citySelect');
        
        function populateCities(selectedState, selectedCity = null) {
            citySelect.innerHTML = '<option value="" disabled selected>Select city...</option>';
            
            if (selectedState && malaysiaCities[selectedState]) {
                citySelect.disabled = false;
                malaysiaCities[selectedState].forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    if(selectedCity === city) {
                        option.selected = true;
                    }
                    citySelect.appendChild(option);
                });
            } else {
                citySelect.disabled = true;
                citySelect.innerHTML = '<option value="" disabled selected>Select state first...</option>';
            }
        }

        stateSelect.addEventListener('change', function() {
            populateCities(this.value);
        });

        // Load saved state & city for Edit mode
        const dbState = "{{ old('state', $inspection->state ?? '') }}";
        const dbCity = "{{ old('city', $inspection->city ?? '') }}";
        
        if (dbState) {
            stateSelect.value = dbState;
            populateCities(dbState, dbCity);
        }

        // --- CROPPER SCRIPT ---
        let cropper;
        const imageInput = document.getElementById('imageInput');
        const imagePreview = document.getElementById('imagePreview');
        const cropperContainer = document.getElementById('cropperContainer');
        const cropButton = document.getElementById('cropButton');
        const croppedImageOutput = document.getElementById('croppedImageOutput');
        const cropStatus = document.getElementById('cropStatus');

        if(imageInput) {
            imageInput.addEventListener('change', function (e) {
                const files = e.target.files;
                if (files && files.length > 0) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        imagePreview.src = e.target.result;
                        cropperContainer.classList.remove('hidden');
                        cropStatus.classList.add('hidden');
                        cropButton.textContent = "Confirm Crop House Image";
                        if (cropper) cropper.destroy();
                        cropper = new Cropper(imagePreview, { aspectRatio: 4 / 3, viewMode: 1 });
                    };
                    reader.readAsDataURL(files[0]);
                }
            });
        }

        if(cropButton) {
            cropButton.addEventListener('click', function () {
                if (cropper) {
                    const canvas = cropper.getCroppedCanvas({ width: 800, height: 600 });
                    croppedImageOutput.value = canvas.toDataURL('image/jpeg', 0.8);
                    cropStatus.classList.remove('hidden');
                    cropButton.textContent = "Re-Crop";
                }
            });
        }

        let layoutCropper;
        const layoutInput = document.getElementById('layoutInput');
        const layoutPreview = document.getElementById('layoutPreview');
        const layoutCropperContainer = document.getElementById('layoutCropperContainer');
        const cropLayoutButton = document.getElementById('cropLayoutButton');
        const croppedLayoutOutput = document.getElementById('croppedLayoutOutput');
        const layoutCropStatus = document.getElementById('layoutCropStatus');

        if(layoutInput) {
            layoutInput.addEventListener('change', function (e) {
                const files = e.target.files;
                if (files && files.length > 0) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        layoutPreview.src = e.target.result;
                        layoutCropperContainer.classList.remove('hidden');
                        layoutCropStatus.classList.add('hidden');
                        cropLayoutButton.textContent = "Confirm Crop Layout";
                        if (layoutCropper) layoutCropper.destroy();
                        layoutCropper = new Cropper(layoutPreview, { aspectRatio: 3 / 4, viewMode: 1 });
                    };
                    reader.readAsDataURL(files[0]);
                }
            });
        }

        if(cropLayoutButton) {
            cropLayoutButton.addEventListener('click', function () {
                if (layoutCropper) {
                    const canvas = layoutCropper.getCroppedCanvas({ width: 600, height: 800 });
                    croppedLayoutOutput.value = canvas.toDataURL('image/jpeg', 0.8);
                    layoutCropStatus.classList.remove('hidden');
                    cropLayoutButton.textContent = "Re-Crop";
                }
            });
        }
    });
</script>
@endsection