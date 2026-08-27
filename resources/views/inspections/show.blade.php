@extends('layouts.app')

@section('content')

<style>
    /* CSS Override to change Pagination color to Emerald theme */
    nav[role="navigation"] a, 
    nav[role="navigation"] span[aria-disabled="true"] {
        background-color: #ffffff !important;
        color: #4b5563 !important;
        border-color: #e5e7eb !important;
    }

    nav[role="navigation"] a:hover {
        background-color: #d1fae5 !important; /* bg-emerald-100 */
        color: #047857 !important; /* text-emerald-700 */
    }

    nav[role="navigation"] span[aria-current="page"] > span {
        background-color: #d1fae5 !important; /* bg-emerald-100 */
        color: #047857 !important; /* text-emerald-700 */
        border-color: #6ee7b7 !important; /* border-emerald-300 */
        font-weight: bold !important;
    }
</style>

<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Inspection Project Details</h2>
            <p class="text-gray-500 text-sm mt-1">Complete property and client information.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('inspection.edit', $inspection->id) }}" class="px-4 py-2 bg-amber-500 text-white text-sm font-semibold rounded-lg hover:bg-amber-600 shadow-sm">
                <i class="fa-solid fa-pen-to-square mr-1"></i> Edit Project
            </a>
            <a href="{{ route('inspection.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200">
                Back
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Project Title</span>
                <p class="text-lg font-bold text-gray-900 mt-1">{{ $inspection->title }}</p>
            </div>
            <div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Property Type</span>
                <p class="mt-1"><span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded-full">{{ $inspection->type }}</span></p>
            </div>
            <div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Client Name</span>
                <p class="text-base font-semibold text-gray-800 mt-1">{{ $inspection->clientname }}</p>
            </div>
            <div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">State</span>
                <p class="text-base text-gray-800 mt-1 font-medium">{{ $inspection->state }}</p>
            </div>
            <div class="col-span-1 md:col-span-2">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Full Address</span>
                <p class="text-base text-gray-700 mt-1 bg-gray-50 p-4 rounded-xl border border-gray-100">{{ $inspection->address }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-gray-100">
            <div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-2">House Image (Front View)</span>
                @if($inspection->img)
                    <img src="{{ asset('storage/' . $inspection->img) }}" alt="House Image" class="w-full h-64 object-cover rounded-xl shadow-sm border">
                @else
                    <p class="text-sm text-gray-400 italic">No image uploaded.</p>
                @endif
            </div>
            <div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-2">Layout Plan</span>
                @if($inspection->layout_img)
                    <img src="{{ asset('storage/' . $inspection->layout_img) }}" alt="Layout Plan" class="w-full h-64 object-cover rounded-xl shadow-sm border">
                @else
                    <p class="text-sm text-gray-400 italic">No layout plan uploaded.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="max-w-4xl mx-auto mt-6">
    <div class="flex flex-col sm:flex-row justify-end gap-3">
        <a href="{{ route('inspection.pdf', ['id' => $inspection->id, 'template_type' => 'template1']) }}" class="px-5 py-2.5 bg-red-50 text-red-600 hover:bg-red-100 font-bold rounded-xl text-sm transition-colors shadow-sm border border-red-200 flex items-center justify-center">
            <i class="fa-solid fa-file-pdf mr-2 text-lg"></i> Generate PDF (Classic)
        </a>
        <a href="{{ route('inspection.pdf', ['id' => $inspection->id, 'template_type' => 'template2']) }}" class="px-5 py-2.5 bg-blue-50 text-blue-600 hover:bg-blue-100 font-bold rounded-xl text-sm transition-colors shadow-sm border border-blue-200 flex items-center justify-center">
            <i class="fa-solid fa-file-pdf mr-2 text-lg"></i> Generate PDF (Table)
        </a>
    </div>
</div>

<div class="max-w-6xl mx-auto mt-8">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Defects & Damages List</h3>
                <p class="text-sm text-gray-500">Total defects recorded: <span class="font-bold text-blue-600">{{ $inspection->defects->count() }}</span></p>
            </div>
            <a href="{{ route('defects.rapid', $inspection->id) }}" class="px-4 py-2 bg-emerald-100 text-emerald-700 hover:bg-emerald-200 font-bold rounded-lg text-sm transition-colors whitespace-nowrap">
                <i class="fas fa-plus mr-1"></i> Add Defect
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="py-3 px-6 font-semibold whitespace-nowrap">No.</th>
                        <th class="py-3 px-6 font-semibold whitespace-nowrap">Location</th>
                        <th class="py-3 px-6 font-semibold min-w-[120px]">Category / Type</th>
                        <th class="py-3 px-6 font-semibold min-w-[150px]">Issue (Defect)</th>
                        <th class="py-3 px-6 font-semibold whitespace-nowrap">Images</th>
                        <th class="py-3 px-6 font-semibold text-center whitespace-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($defects as $index => $defect)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-4 px-6 font-medium text-gray-400">{{ $defects->firstItem() + $index }}</td>
                            <td class="py-4 px-6 font-bold text-gray-900">{{ $defect->location }}</td>
                            <td class="py-4 px-6">
                                <span class="block text-xs font-semibold text-gray-500 uppercase">{{ $defect->category }}</span>
                                <span class="block mt-1">{{ $defect->type }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <p class="font-bold text-red-600">{{ $defect->defect }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $defect->desc }}</p>
                            </td>
                            
                            <!-- IMAGES SECTION -->
                            <td class="py-4 px-6">
                                <div class="flex gap-1 flex-wrap min-w-max">
                                    @if($defect->img && is_array($defect->img))
                                        @foreach($defect->img as $imagePath)
                                            <img src="{{ asset('storage/' . $imagePath) }}" class="w-10 h-10 object-cover rounded border border-gray-200">
                                        @endforeach
                                    @else
                                        <span class="text-xs text-gray-400">None</span>
                                    @endif
                                </div>
                            </td>
                            
                            <!-- ACTION SECTION -->
                            <td class="py-4 px-6 text-center">
                               <div class="flex flex-col sm:flex-row justify-center items-center gap-2 min-w-max">
                                    <!-- Edit Button -->
                                    <a href="{{ route('defects.edit', $defect->id) }}" class="w-full sm:w-auto px-3 py-1.5 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded text-xs font-bold transition-colors block text-center">
                                        Edit
                                    </a>
                                    
                                    <!-- Delete Button -->
                                    <form action="{{ route('defects.destroy', $defect->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this defect?');" class="w-full sm:w-auto block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded text-xs font-bold transition-colors">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-400">
                                <i class="fas fa-clipboard-check text-3xl mb-2 block"></i>
                                No defects have been recorded for this project yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $defects->links() }}
        </div>
        
    </div>
</div>
@endsection