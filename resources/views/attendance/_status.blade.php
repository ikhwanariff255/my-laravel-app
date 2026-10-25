{{-- Status badges for one attendance record: $item --}}
<span class="px-2 py-1 rounded text-xs font-bold {{ $item->status === 'present' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
    {{ $item->status === 'present' ? 'On Time' : ucfirst($item->status) }}
</span>
@if ($item->early_out)
    <span class="px-2 py-1 rounded text-xs font-bold bg-orange-100 text-orange-800">Early Clock-Out</span>
@endif
