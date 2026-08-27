@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto mt-8">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Kemaskini Pengguna</h2>
        
        <!-- Banner Global Error -->
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
                <div class="flex items-center mb-2">
                    <i class="fa-solid fa-circle-exclamation text-red-600 mr-2 text-lg"></i>
                    <h3 class="text-sm font-bold text-red-800">Terdapat ralat pada borang anda:</h3>
                </div>
                <ul class="text-sm text-red-700 list-disc list-inside pl-6">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')
            
            <!-- Nama Penuh -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Penuh</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required 
                    class="block w-full rounded-lg shadow-sm p-2.5 border transition-colors 
                    @error('name') border-red-500 focus:border-red-500 focus:ring-red-200 bg-red-50 
                    @else border-gray-300 focus:border-blue-500 focus:ring-blue-200 @enderror">
                @error('name')
                    <p class="mt-1.5 text-sm text-red-600 font-medium"><i class="fa-solid fa-triangle-exclamation mr-1"></i>{{ $message }}</p>
                @enderror
            </div>

            <!-- Username -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" name="username" value="{{ old('username', $user->username) }}" required 
                    class="block w-full rounded-lg shadow-sm p-2.5 border transition-colors 
                    @error('username') border-red-500 focus:border-red-500 focus:ring-red-200 bg-red-50 
                    @else border-gray-300 focus:border-blue-500 focus:ring-blue-200 @enderror">
                @error('username')
                    <p class="mt-1.5 text-sm text-red-600 font-medium"><i class="fa-solid fa-triangle-exclamation mr-1"></i>{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required 
                    class="block w-full rounded-lg shadow-sm p-2.5 border transition-colors 
                    @error('email') border-red-500 focus:border-red-500 focus:ring-red-200 bg-red-50 
                    @else border-gray-300 focus:border-blue-500 focus:ring-blue-200 @enderror">
                @error('email')
                    <p class="mt-1.5 text-sm text-red-600 font-medium"><i class="fa-solid fa-triangle-exclamation mr-1"></i>{{ $message }}</p>
                @enderror
            </div>

            <!-- Kata Laluan Baru -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Kata Laluan Baru 
                    <span class="text-xs font-normal text-gray-500 ml-1">(Biarkan kosong jika tidak mahu tukar)</span>
                </label>
                <input type="password" name="password" 
                    class="block w-full rounded-lg shadow-sm p-2.5 border transition-colors 
                    @error('password') border-red-500 focus:border-red-500 focus:ring-red-200 bg-red-50 
                    @else border-gray-300 focus:border-blue-500 focus:ring-blue-200 @enderror">
                @error('password')
                    <p class="mt-1.5 text-sm text-red-600 font-medium"><i class="fa-solid fa-triangle-exclamation mr-1"></i>{{ $message }}</p>
                @enderror
            </div>

            <!-- Peranan -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Peranan (Role)</label>
                <select name="role" required 
                    class="block w-full rounded-lg shadow-sm p-2.5 border transition-colors 
                    @error('role') border-red-500 focus:border-red-500 focus:ring-red-200 bg-red-50 
                    @else border-gray-300 focus:border-blue-500 focus:ring-blue-200 @enderror">
                    <option value="staff" {{ old('role', $user->role) == 'staff' ? 'selected' : '' }}>Staff</option>
                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="owner" {{ old('role', $user->role) == 'owner' ? 'selected' : '' }}>Owner</option>
                </select>
                @error('role')
                    <p class="mt-1.5 text-sm text-red-600 font-medium"><i class="fa-solid fa-triangle-exclamation mr-1"></i>{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-6 flex justify-end gap-3 border-t border-gray-100">
                <a href="{{ route('users.index') }}" class="px-5 py-2.5 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg font-bold transition-colors">Batal</a>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white hover:bg-blue-700 rounded-lg font-bold shadow-sm transition-colors">Kemaskini Pengguna</button>
            </div>
        </form>
    </div>
</div>
@endsection