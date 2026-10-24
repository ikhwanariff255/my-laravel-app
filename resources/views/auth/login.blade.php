<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - DefectGURU</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans antialiased">

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 sm:p-10 rounded-3xl shadow-xl border border-gray-100">
            
            <!-- Logo & Header -->
            <div class="text-center">
                @php
                    $logoPath = isset($settings) && $settings->logo_path ? asset('storage/' . $settings->logo_path) : asset('assets/img/defectguru_logo.png');
                @endphp

                <div class="flex justify-center mb-4">
                    <img class="h-14 w-auto object-contain" src="{{ $logoPath }}" alt="Logo">
                </div>

                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                    Welcome Back
                </h2>

                
                <p class="mt-2 text-sm text-gray-500">
                    Please sign in to your <span class="font-semibold text-blue-600">DefectGURU</span> account
                </p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-3 rounded-xl text-center">
                    {{ session('status') }}
                </div>
            @endif

            <form class="mt-8 space-y-6" method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email / Username Input -->
                <div>
                    <label for="email" class="block text-gray-700 font-semibold text-xs uppercase tracking-wider mb-1">Email </label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-regular fa-envelope"></i>
                        </div>
                        <input id="email" class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" type="text" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nama@email.com atau username" />
                    </div>
                    @error('email')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-gray-700 font-semibold text-xs uppercase tracking-wider mb-1">Password</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input id="password" class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm outline-none"
                                    type="password"
                                    name="password"
                                    required autocomplete="current-password" 
                                    placeholder="••••••••" />
                    </div>
                    @error('password')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between text-sm">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer">
                        <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 w-4 h-4" name="remember">
                        <span class="ms-2 text-gray-600 text-xs sm:text-sm">Remember me</span>
                    </label>
<!-- 
                    @if (Route::has('password.request'))
                        <a class="text-xs sm:text-sm font-medium text-blue-600 hover:text-blue-500 transition-colors" href="{{ route('password.request') }}">
                            Forgot password?
                        </a>
                    @endif -->
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        Log In to System
                    </button>
                </div>
            </form>

            <!-- Footer info -->
            <div class="mt-6 text-center border-t border-gray-100 pt-4">
                <p class="text-xs text-gray-400">
                    &copy; {{ date('Y') }} DefectGURU. All rights reserved.
                </p>
            </div>

        </div>
    </div>

</body>
</html>