@extends('layouts.app')

@section('content')

<style>
    /* CSS Override to change Pagination color to Blue theme */
    nav[role="navigation"] a, 
    nav[role="navigation"] span[aria-disabled="true"] {
        background-color: #ffffff !important;
        color: #6b7280 !important; 
        border-color: #e5e7eb !important;
    }

    nav[role="navigation"] a:hover {
        background-color: #eff6ff !important; 
        color: #2563eb !important; 
        border-color: #bfdbfe !important; 
    }

    nav[role="navigation"] span[aria-current="page"] > span {
        background-color: #eff6ff !important; 
        color: #1d4ed8 !important; 
        border-color: #93c5fd !important; 
        font-weight: bold !important;
    }
</style>

<div class="max-w-7xl mx-auto">
    
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Inspection Project List</h2>
            <p class="text-gray-500 text-sm mt-1">All registered client property inspection records.</p>
        </div>
        <a href="{{ route('inspection.create') }}" class="px-4 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 shadow-sm transition-colors flex items-center">
            <i class="fa-solid fa-plus mr-2"></i> Register New Project
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-lg text-sm flex items-center">
            <i class="fa-solid fa-circle-check mr-2 text-lg"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        
        <div class="p-5 border-b border-gray-100 bg-gray-50/50">
            <form action="{{ route('inspection.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search project name, client name or address..." 
                           class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm transition-colors shadow-sm">
                </div>

                <div class="sm:w-48">
                    <select name="type" onchange="this.form.submit()" 
                            class="block w-full py-2.5 px-3 border border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm transition-colors shadow-sm cursor-pointer">
                        <option value="">All Types</option>
                        <option value="Banglo" {{ request('type') == 'Banglo' ? 'selected' : '' }}>Bungalow</option>
                        <option value="Semi-D" {{ request('type') == 'Semi-D' ? 'selected' : '' }}>Semi-D</option>
                        <option value="Teres" {{ request('type') == 'Teres' ? 'selected' : '' }}>Terrace</option>
                        <option value="Kondominium" {{ request('type') == 'Kondominium' ? 'selected' : '' }}>Condominium</option>
                        <option value="Komersial" {{ request('type') == 'Komersial' ? 'selected' : '' }}>Commercial</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-5 py-2.5 bg-slate-800 text-white text-sm font-semibold rounded-xl hover:bg-slate-900 transition-colors shadow-sm">
                        Search
                    </button>
                    
                    @if(request('search') || request('type'))
                        <a href="{{ route('inspection.index') }}" class="px-4 py-2.5 bg-red-50 text-red-600 text-sm font-semibold rounded-xl hover:bg-red-100 transition-colors flex items-center justify-center">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="py-4 px-6 font-semibold">No.</th>
                        <th class="py-4 px-6 font-semibold">Image</th>
                        <th class="py-4 px-6 font-semibold">Title & Type</th>
                        <th class="py-4 px-6 font-semibold">Client & Address</th>
                        <th class="py-4 px-6 font-semibold">State</th>
                        <th class="py-4 px-6 font-semibold text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($inspections as $index => $item)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="py-4 px-6 font-medium text-gray-400">{{ $inspections->firstItem() + $index }}</td>
                            
                            <td class="py-4 px-6">
                                @if($item->img)
                                    <img src="{{ asset('storage/' . $item->img) }}" alt="Rumah" class="w-16 h-12 object-cover rounded-lg shadow-sm border">
                                @else
                                    <span class="text-xs text-gray-400 italic">No image</span>
                                @endif
                            </td>

                            <td class="py-4 px-6">
                                <a href="{{ route('inspection.show', $item->id) }}" class="font-bold text-gray-900 hover:text-blue-600 transition-colors">{{ $item->title }}</a>
                                <span class="inline-block mt-1 px-2.5 py-0.5 bg-blue-50 text-blue-700 text-xs font-medium rounded-full">
                                    {{ $item->type }}
                                </span>
                            </td>

                            <td class="py-4 px-6">
                                <p class="font-semibold text-gray-800">{{ $item->clientname }}</p>
                                <p class="text-xs text-gray-500 mt-0.5 truncate max-w-xs">{{ $item->address }}</p>
                            </td>

                            <td class="py-4 px-6">
                                <span class="text-gray-600 font-medium">{{ $item->state }}</span>
                            </td>

                            <td class="py-4 px-6 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('inspection.show', $item->id) }}" class="px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-xs font-semibold transition-colors" title="View Details">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('inspection.edit', $item->id) }}" class="px-3 py-1.5 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-lg text-xs font-semibold transition-colors" title="Edit Project">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-400">
                                <i class="fa-solid fa-magnifying-glass text-4xl mb-3 text-gray-300 block"></i>
                                <span class="text-base font-medium text-gray-500">No inspection project records found.</span>
                                @if(request('search') || request('type'))
                                    <p class="text-sm mt-1">Please try a different search keyword.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($inspections->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $inspections->links() }}
        </div>
        @endif
        
    </div>
</div>
@endsection