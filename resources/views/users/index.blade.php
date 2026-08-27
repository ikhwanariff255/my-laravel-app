@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto mt-8">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <div>
                <h3 class="text-lg font-bold text-gray-800">User Management</h3>
                <p class="text-sm text-gray-500">List of administrators, staff, and system owners.</p>
            </div>
            <a href="{{ route('users.create') }}" class="px-4 py-2 bg-emerald-100 text-emerald-700 hover:bg-emerald-200 font-bold rounded-lg text-sm transition-colors">
                <i class="fas fa-plus mr-1"></i> Add User
            </a>
        </div>

        @if(session('success'))
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 mx-6 mt-4">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 mx-6 mt-4">
                {{ session('error') }}
            </div>
        @endif
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="py-3 px-6 font-semibold">Name</th>
                        <th class="py-3 px-6 font-semibold">Email</th>
                        <th class="py-3 px-6 font-semibold">Role</th>
                        <th class="py-3 px-6 font-semibold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @foreach($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-4 px-6 font-bold text-gray-900">{{ $user->name }}</td>
                            <td class="py-4 px-6">{{ $user->email }}</td>
                            <td class="py-4 px-6">
                                @if($user->role == 'admin')
                                    <span class="px-2 py-1 bg-purple-100 text-purple-700 text-xs font-bold rounded">ADMIN</span>
                                @elseif($user->role == 'owner')
                                    <span class="px-2 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded">OWNER</span>
                                @else
                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded">STAFF</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center">
                               <div class="flex justify-center gap-2">
                                    <a href="{{ route('users.edit', $user->id) }}" class="px-3 py-1.5 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded text-xs font-bold transition-colors">Edit</a>
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded text-xs font-bold transition-colors">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection