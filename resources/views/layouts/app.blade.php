<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'DefexSnap') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- FontAwesome untuk Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts (Vite + Tailwind) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900 bg-gray-50">

    <!-- Wrapper Utama dengan Alpine.js untuk kawal Sidebar bimbit -->
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">

        <!-- ==================== SIDEBAR ==================== -->
        <!-- Latar belakang gelap untuk mobile -->
        <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-20 bg-gray-900/50 lg:hidden" @click="sidebarOpen = false"></div>

        <!-- Sidebar Menu -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-30 w-64 transition-transform duration-300 ease-in-out transform bg-slate-900 text-white lg:translate-x-0 lg:static lg:inset-auto flex flex-col shadow-xl">
            
            <!-- Logo Area -->
            <div class="flex items-center justify-center h-16 px-6 bg-slate-950/50 border-b border-slate-800">
                <i class="fa-solid fa-camera-retro text-blue-400 text-2xl mr-2"></i>
                <span class="text-xl font-bold tracking-wider text-white">Defex<span class="text-blue-400">Snap</span></span>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                <!-- Link Dashboard -->
                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                    <i class="fa-solid fa-chart-pie w-6"></i>
                    <span>Dashboard </span>
                </a>

                <!-- Link Senarai Pemeriksaan -->
                <a href="{{ route('inspection.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('inspection.index') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                    <i class="fa-solid fa-list-check w-6"></i>
                    <span>All Inspections</span>
                </a>

                <!-- Link Daftar Projek Baru -->
                <a href="{{ route('inspection.create') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('inspection.create') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                    <i class="fa-solid fa-plus-circle w-6"></i>
                    <span>Add Inspections</span>
                </a>

                <!-- ================= TAMBAHAN MENU KEUANGAN / INVOICE ================= -->
                @if(Auth::user()->role == 'admin' || Auth::user()->role == 'owner')
                <div class="pt-4 mt-4 border-t border-slate-800">
                    <p class="px-4 mb-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Financials</p>
                    
                    <!-- Link Invoices -->
                    <a href="{{ route('invoice.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('invoice.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                        <i class="fa-solid fa-file-invoice-dollar w-6"></i>
                        <span>Invoices</span>
                    </a>

                    <!-- Link Cash Flow -->
                    <a href="{{ route('cashflow.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('cashflow.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                        <i class="fa-solid fa-wallet w-6"></i>
                        <span>Cash Flow</span>
                    </a>

                    <!-- Tambah Link Report Gaji Di Sini -->
                    <a href="{{ route('reports.part_time') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('reports.part_time') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                        <i class="fa-solid fa-chart-pie w-6"></i>
                        <span>Part-Time Report</span>
                    </a>
                    
                </div>
                @endif
                <!-- ================================================================= -->

                <!-- ================= TAMBAHAN MENU PENGGUNA ================= -->
                @if(Auth::user()->role == 'admin' || Auth::user()->role == 'owner')
                <div class="pt-4 mt-4 border-t border-slate-800">
                    <p class="px-4 mb-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Management</p>
                    
                    <!-- Link Pengurusan Pengguna -->
                    <a href="{{ route('users.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('users.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                        <i class="fa-solid fa-users-gear w-6"></i>
                        <span>User Management</span>
                    </a>
                </div>
                @endif
                <!-- ========================================================= -->

            </nav>

            <!-- Profil Ringkas Bawah Sidebar -->
            <div class="p-4 border-t border-slate-800">
                <div class="flex items-center gap-3 px-4 py-2">
                    <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-sm font-bold text-slate-300">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-slate-400 truncate text-transform: capitalize">{{ Auth::user()->role ?? 'Staff' }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- ==================== MAIN CONTENT AREA ==================== -->
        <div class="flex flex-col flex-1 overflow-hidden">
            
            <!-- TOP HEADER -->
            <header class="flex items-center justify-between h-16 px-6 bg-white border-b border-gray-200 shadow-sm z-10">
                <!-- Kiri: Mobile Menu Button & Search (Optional) -->
                <div class="flex items-center">
                    <button @click="sidebarOpen = true" class="p-2 mr-4 text-gray-500 rounded-lg lg:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    
                    <!-- Tajuk page -->
                    @if (isset($header))
                        <h2 class="text-xl font-semibold leading-tight text-gray-800 hidden sm:block">
                            {{ $header }}
                        </h2>
                    @endif
                </div>

                <!-- Kanan: User Dropdown -->
                <div class="flex items-center relative" x-data="{ dropdownOpen: false }">
                    <button @click="dropdownOpen = !dropdownOpen" class="flex items-center gap-2 p-2 text-sm font-medium text-gray-700 transition-colors rounded-lg hover:bg-gray-100 focus:outline-none">
                        <span>{{ Auth::user()->name }}</span>
                        <i class="fa-solid fa-chevron-down text-xs text-gray-500 transition-transform" :class="dropdownOpen ? 'rotate-180' : ''"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-transition class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50" style="display: none;">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700">
                            <i class="fa-regular fa-user mr-2"></i> Profil Saya
                        </a>
                        <div class="my-1 border-t border-gray-100"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-medium">
                                <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i> Log Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- KAWASAN KANDUNGAN UTAMA (SCROLLABLE) -->
            <main class="flex-1 overflow-y-auto bg-gray-50/50 p-6">
                <!-- Menyokong Component $slot DAN Blade yield -->
                @isset($slot)
                    {{ $slot }}
                @endisset

                @yield('content')
            </main>

        </div>
    </div>

    <!-- Stack & Yield untuk skrip tambahan (Cropper.js, dll) -->
    @stack('scripts')
    @yield('scripts')
</body>
</html>