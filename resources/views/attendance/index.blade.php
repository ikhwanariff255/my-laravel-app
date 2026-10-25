<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Staff Attendance (Clock In / Out)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Clock In / Clock Out Card --}}
            <div class="p-6 bg-white shadow sm:rounded-lg text-center">
                <h3 class="text-lg font-bold text-gray-700 mb-1">
                    Today's Attendance Status ({{ now()->format('d M Y') }})
                </h3>
                <p class="text-sm text-gray-500 mb-4">
                    Office hours: {{ \Carbon\Carbon::parse($officeHours['start'])->format('g:i A') }} – {{ \Carbon\Carbon::parse($officeHours['end'])->format('g:i A') }}
                </p>

                <div class="flex justify-center items-center gap-4">
                    @if (!$todayAttendance)
                        <button onclick="submitAttendance('in')" id="btnClockIn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow transition">
                            <i class="fa-solid fa-location-dot mr-2"></i>Clock In
                        </button>
                    @elseif (!$todayAttendance->clock_out)
                        <div class="mr-4 text-left">
                            <span class="text-sm text-gray-500">Clock In Time:</span>
                            <div class="text-xl font-bold {{ $todayAttendance->status === 'late' ? 'text-yellow-600' : 'text-green-600' }}">{{ $todayAttendance->clock_in->format('H:i:s') }}</div>
                            @include('attendance._status', ['item' => $todayAttendance])
                        </div>
                        <button onclick="submitAttendance('out')" id="btnClockOut" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg shadow transition">
                            <i class="fa-solid fa-flag-checkered mr-2"></i>Clock Out
                        </button>
                    @else
                        <div class="text-gray-600 bg-gray-50 p-4 rounded-lg border">
                            <i class="fa-solid fa-circle-check text-green-600 mr-1"></i> You have completed your attendance for today.<br>
                            <span class="text-sm text-gray-500">
                                In: <strong>{{ $todayAttendance->clock_in->format('H:i') }}</strong> | 
                                Out: <strong>{{ $todayAttendance->clock_out->format('H:i') }}</strong>
                            </span>
                            <div class="mt-2">@include('attendance._status', ['item' => $todayAttendance])</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Personal Attendance History --}}
            <div class="p-6 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-bold text-gray-700 mb-4">My Attendance History</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b bg-gray-50 text-gray-600 text-sm">
                                <th class="p-3">Date</th>
                                <th class="p-3">Clock In</th>
                                <th class="p-3">Clock Out</th>
                                <th class="p-3">Distance from Office</th>
                                <th class="p-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-sm">
                            @forelse($attendances as $item)
                                <tr>
                                    <td class="p-3">{{ $item->created_at->format('d/m/Y') }}</td>
                                    <td class="p-3">{{ $item->clock_in ? $item->clock_in->format('H:i') : '-' }}</td>
                                    <td class="p-3">{{ $item->clock_out ? $item->clock_out->format('H:i') : '-' }}</td>
                                    <td class="p-3">{{ round($item->distance_meters) }} m</td>
                                    <td class="p-3">@include('attendance._status')</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-3 text-center text-gray-500">No attendance records found.</td>
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

    <script>
        const buttonLabels = {
            in: '<i class="fa-solid fa-location-dot mr-2"></i>Clock In',
            out: '<i class="fa-solid fa-flag-checkered mr-2"></i>Clock Out',
        };

        function submitAttendance(type, confirmEarly = false) {
            const btnId = type === 'in' ? 'btnClockIn' : 'btnClockOut';
            const btn = document.getElementById(btnId);
            btn.disabled = true;
            btn.innerText = 'Detecting GPS location...';

            if (!navigator.geolocation) {
                alert('Your browser does not support Geolocation.');
                btn.disabled = false;
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const endpoint = type === 'in' ? '{{ route("attendance.clockIn") }}' : '{{ route("attendance.clockOut") }}';

                    fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ latitude: lat, longitude: lng, confirm_early: confirmEarly })
                    })
                    .then(res => res.json())
                    .then(data => {
                        // Clocking out before office end time needs confirmation
                        if (data.status === 'confirm') {
                            if (confirm(data.message)) {
                                return submitAttendance(type, true);
                            }
                            btn.disabled = false;
                            btn.innerHTML = buttonLabels[type];
                            return;
                        }
                        alert(data.message);
                        if (data.status === 'success') {
                            location.reload();
                        } else {
                            btn.disabled = false;
                            btn.innerHTML = buttonLabels[type];
                        }
                    })
                    .catch(() => {
                        alert('Error while contacting the server.');
                        btn.disabled = false;
                        btn.innerHTML = buttonLabels[type];
                    });
                },
                (error) => {
                    alert('Unable to get your location. Please enable GPS / allow location access in your browser.');
                    btn.disabled = false;
                    btn.innerHTML = buttonLabels[type];
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }
    </script>
</x-app-layout>