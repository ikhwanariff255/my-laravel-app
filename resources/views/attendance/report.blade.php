<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Staff Attendance Report (Owner View)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow sm:rounded-lg">
                
                {{-- Date Filter --}}
                <form method="GET" action="{{ route('attendance.report') }}" class="mb-6 flex gap-4">
                    <input type="date" name="date" value="{{ request('date') }}" class="border-gray-300 rounded-md shadow-sm">
                    <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-md">Filter</button>
                    <a href="{{ route('attendance.report') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md">Reset</a>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b bg-gray-50 text-gray-600 text-sm">
                                <th class="p-3">Staff Name</th>
                                <th class="p-3">Role</th>
                                <th class="p-3">Date</th>
                                <th class="p-3">Clock In</th>
                                <th class="p-3">Clock Out</th>
                                <th class="p-3">Distance (m)</th>
                                <th class="p-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-sm">
                            @forelse($attendances as $item)
                                <tr>
                                    <td class="p-3 font-semibold">{{ $item->user->name ?? '-' }}</td>
                                    <td class="p-3"><span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded">{{ $item->user ? ucwords(str_replace('_', ' ', $item->user->role)) : '-' }}</span></td>
                                    <td class="p-3">{{ $item->created_at->format('d/m/Y') }}</td>
                                    <td class="p-3">{{ $item->clock_in ? $item->clock_in->format('H:i') : '-' }}</td>
                                    <td class="p-3">{{ $item->clock_out ? $item->clock_out->format('H:i') : '-' }}</td>
                                    <td class="p-3">{{ round($item->distance_meters) }} m</td>
                                    <td class="p-3">
                                        <span class="px-2 py-1 rounded text-xs font-bold {{ $item->status === 'present' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-3 text-center text-gray-500">No records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $attendances->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>