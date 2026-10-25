<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>DefeXsnap</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts (Vite + Tailwind) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900 bg-gray-50">

    <!-- Main Wrapper with Alpine.js for Mobile Sidebar -->
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">

        <!-- ==================== SIDEBAR ==================== -->
        <!-- Mobile Overlay -->
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
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                    <i class="fa-solid fa-chart-pie w-6"></i>
                    <span>Dashboard</span>
                </a>

                <!-- All Inspections -->
                <a href="{{ route('inspection.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('inspection.index') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                    <i class="fa-solid fa-list-check w-6"></i>
                    <span>All Inspections</span>
                </a>

                <!-- Add Inspections -->
                <a href="{{ route('inspection.create') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('inspection.create') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                    <i class="fa-solid fa-plus-circle w-6"></i>
                    <span>Add Inspections</span>
                </a>

                <!-- My Attendance -->
                @if(Auth::user()->canClockIn())
                <a href="{{ route('attendance.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('attendance.index') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                    <i class="fa-solid fa-location-dot w-6"></i>
                    <span>My Attendance</span>
                </a>
                @endif

                <!-- My Leaves / Apply Leave (not for staff) -->
                @can('apply-leave')
                <a href="{{ route('leaves.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('leaves.index') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                    <i class="fa-solid fa-calendar-minus w-6"></i>
                    <span>My Leaves</span>
                </a>
                @endcan

                <!-- ================= FINANCIALS SECTION ================= -->
                @if(Auth::user()->canManageSystem())
                <div class="pt-4 mt-4 border-t border-slate-800">
                    <p class="px-4 mb-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Financials</p>
                    
                    <!-- Invoices -->
                    <a href="{{ route('invoice.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('invoice.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                        <i class="fa-solid fa-file-invoice-dollar w-6"></i>
                        <span>Invoices</span>
                    </a>

                    <!-- Cash Flow -->
                    <a href="{{ route('cashflow.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('cashflow.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                        <i class="fa-solid fa-wallet w-6"></i>
                        <span>Cash Flow</span>
                    </a>

                    <!-- Part-Time Report -->
                    <a href="{{ route('reports.part_time') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('reports.part_time') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                        <i class="fa-solid fa-chart-line w-6"></i>
                        <span>Part-Time Report</span>
                    </a>
                </div>
                @endif

                <!-- ================= MANAGEMENT SECTION ================= -->
                @if(Auth::user()->canManageSystem())
                <div class="pt-4 mt-4 border-t border-slate-800">
                    <p class="px-4 mb-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Management</p>
                    
                    <!-- User Management -->
                    <a href="{{ route('users.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('users.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                        <i class="fa-solid fa-users-gear w-6"></i>
                        <span>User Management</span>
                    </a>

                    <!-- Leave Approvals -->
                    <a href="{{ route('leaves.manage') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('leaves.manage') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                        <i class="fa-solid fa-calendar-check w-6"></i>
                        <span>Leave Approvals</span>
                    </a>

                    <!-- Attendance Dashboard incl. records (Exclusive to Owner) -->
                    @if(Auth::user()->role == 'owner')
                    <a href="{{ route('attendance.monthly') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('attendance.monthly') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                        <i class="fa-solid fa-calendar-days w-6"></i>
                        <span>Attendance Dashboard</span>
                    </a>
                    @endif
                </div>
                @endif

            </nav>

            <!-- Bottom Profile Summary -->
            <div class="p-4 border-t border-slate-800">
                <div class="flex items-center gap-3 px-4 py-2">
                    <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-sm font-bold text-slate-300">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-slate-400 truncate capitalize">{{ str_replace('_', ' ', Auth::user()->role ?? 'Staff') }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- ==================== MAIN CONTENT AREA ==================== -->
        <div class="flex flex-col flex-1 overflow-hidden">
            
            <!-- TOP HEADER -->
            <header class="flex items-center justify-between h-16 px-6 bg-white border-b border-gray-200 shadow-sm z-10">
                <!-- Left: Mobile Menu Button & Page Title -->
                <div class="flex items-center">
                    <button @click="sidebarOpen = true" class="p-2 mr-4 text-gray-500 rounded-lg lg:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    
                    @if (isset($header))
                        <h2 class="text-xl font-semibold leading-tight text-gray-800 hidden sm:block">
                            {{ $header }}
                        </h2>
                    @endif
                </div>

                <!-- Right: User Dropdown -->
                <div class="flex items-center relative" x-data="{ dropdownOpen: false }">
                    <button @click="dropdownOpen = !dropdownOpen" class="flex items-center gap-2 p-2 text-sm font-medium text-gray-700 transition-colors rounded-lg hover:bg-gray-100 focus:outline-none">
                        <span>{{ Auth::user()->name }}</span>
                        <i class="fa-solid fa-chevron-down text-xs text-gray-500 transition-transform" :class="dropdownOpen ? 'rotate-180' : ''"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-transition class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50" style="display: none;">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700">
                            <i class="fa-regular fa-user mr-2"></i> My Profile
                        </a>
                        <div class="my-1 border-t border-gray-100"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-medium">
                                <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i> Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- MAIN CONTENT (SCROLLABLE) -->
            <main class="flex-1 overflow-y-auto bg-gray-50/50 p-6">
                @isset($slot)
                    {{ $slot }}
                @endisset

                @yield('content')
            </main>

        </div>
    </div>

    <!-- Script Stacks & Yields -->
    @stack('scripts')
    @yield('scripts')
</body>
</html>