<!DOCTYPE html>
<html class="light overflow-x-hidden" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AuraCampus | Super Admin Dashboard')</title>

    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ sidebarOpen: false }" class="font-sans antialiased tech-canvas text-slate-800 min-h-screen relative overflow-x-hidden">

    <!-- Glowing Background Spheres for Visual Contrast -->
    <div class="absolute top-10 right-20 w-80 h-80 rounded-full bg-gradient-to-br from-indigo-500/5 to-purple-600/5 blur-3xl animate-sphere-1 pointer-events-none"></div>
    <div class="absolute bottom-10 left-64 w-96 h-96 rounded-full bg-gradient-to-tr from-blue-600/5 to-indigo-700/5 blur-3xl animate-sphere-2 pointer-events-none"></div>

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div x-show="sidebarOpen" x-cloak class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm z-40 lg:hidden shadow-2xl" @click="sidebarOpen = false"></div>

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="h-screen w-64 fixed left-0 top-0 bg-white border-r border-slate-200/60 shadow-sm flex flex-col py-6 px-4 z-50 transition-transform duration-300 ease-in-out">
        <div class="mb-8 px-4 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center shrink-0 shadow-sm">
                <svg class="w-5 h-5 text-[#4f46e5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M12 2L2 7l10 5 10-5-10-5z" fill="currentColor" fill-opacity="0.1"/>
                    <path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-900 tracking-tight leading-none">AuraCampus</h1>
                <p class="text-[9px] text-slate-400 uppercase font-mono tracking-widest mt-1">Super Admin</p>
            </div>
            <button @click="sidebarOpen = false" class="lg:hidden ml-auto text-slate-400 hover:text-slate-600 cursor-pointer flex items-center justify-center" title="Close Sidebar">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <nav class="flex-1 space-y-1">
            <!-- Dashboard -->
            <a href="{{ route('superadmin.dashboard') }}" 
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('superadmin.dashboard') ? 'text-indigo-600 bg-indigo-50/50 border-l-2 border-indigo-600 font-semibold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="material-symbols-outlined text-[20px]" data-icon="dashboard">dashboard</span>
                <span class="text-xs">Dashboard</span>
            </a>

            <!-- Schools -->
            <a href="{{ route('superadmin.schools') }}" 
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('superadmin.schools') ? 'text-indigo-600 bg-indigo-50/50 border-l-2 border-indigo-600 font-semibold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="material-symbols-outlined text-[20px]" data-icon="school">school</span>
                <span class="text-xs">Schools</span>
            </a>

            <!-- School Admins -->
            <a href="{{ route('superadmin.admins') }}" 
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('superadmin.admins') ? 'text-indigo-600 bg-indigo-50/50 border-l-2 border-indigo-600 font-semibold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="material-symbols-outlined text-[20px]" data-icon="supervisor_account">supervisor_account</span>
                <span class="text-xs">School Admins</span>
            </a>

            <!-- Subscriptions -->
            <a href="{{ route('superadmin.subscriptions') }}" 
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('superadmin.subscriptions') ? 'text-indigo-600 bg-indigo-50/50 border-l-2 border-indigo-600 font-semibold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="material-symbols-outlined text-[20px]" data-icon="payments">payments</span>
                <span class="text-xs">Subscriptions</span>
            </a>

            <!-- Support -->
            <a href="{{ route('superadmin.support') }}" 
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('superadmin.support') ? 'text-indigo-600 bg-indigo-50/50 border-l-2 border-indigo-600 font-semibold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="material-symbols-outlined text-[20px]" data-icon="contact_support">contact_support</span>
                <span class="text-xs">Support</span>
            </a>

            <!-- Settings -->
            <a href="{{ route('superadmin.settings') }}" 
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('superadmin.settings') ? 'text-indigo-600 bg-indigo-50/50 border-l-2 border-indigo-600 font-semibold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="material-symbols-outlined text-[20px]" data-icon="settings">settings</span>
                <span class="text-xs">Settings</span>
            </a>
        </nav>

        <div class="mt-auto px-2 py-4 border-t border-slate-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-bold shadow-sm shrink-0">
                        {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 2)) : 'SA' }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-800 truncate max-w-[110px]">{{ auth()->check() ? auth()->user()->name : 'Alex Rivera' }}</p>
                        <p class="text-[9px] text-slate-400 uppercase font-mono tracking-wider">System Owner</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-rose-500 transition-colors p-1.5 rounded-lg cursor-pointer" title="Log Out">
                        <span class="material-symbols-outlined text-[18px]">logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="ml-0 lg:ml-64 min-h-screen flex flex-col relative z-10">
        <!-- Top Navigation -->
        <header class="sticky top-0 w-full z-40 bg-white/80 border-b border-slate-200/60 backdrop-blur-md flex justify-between items-center h-16 px-4 lg:px-8">
            <div class="flex items-center flex-1 max-w-xl gap-2">
                <!-- Hamburger Menu -->
                <button @click="sidebarOpen = true" class="text-slate-500 hover:bg-slate-50 p-1.5 rounded-lg border border-slate-200/60 lg:hidden shrink-0 cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">menu</span>
                </button>
                <div class="relative w-full group">
                    <span class="material-symbols-outlined absolute left-2.5 sm:left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[16px] sm:text-[18px]" data-icon="search">search</span>
                    <input class="w-full pl-8 sm:pl-10 pr-3 sm:pr-4 py-1.5 sm:py-2 premium-input rounded-xl focus:outline-none focus:premium-input-focus placeholder-slate-300 text-[10px] sm:text-xs font-medium" placeholder="Search schools, transactions, or admins..." type="text"/>
                </div>
            </div>
            <div class="flex items-center gap-3 sm:gap-6">
                <!-- Notifications -->
                @php
                    $unreadNotifications = auth()->check()
                        ? \App\Models\Notification::where('user_id', auth()->id())->whereNull('read_at')->latest()->get()
                        : collect();
                @endphp
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="relative text-slate-500 hover:bg-slate-50 border border-slate-100 p-2 rounded-xl transition-all duration-200 cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]" data-icon="notifications">notifications</span>
                        @if($unreadNotifications->count() > 0)
                        <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 bg-indigo-600 rounded-full shadow-[0_0_4px_#4f46e5]"></span>
                        @endif
                    </button>
                    
                    <!-- Notifications Dropdown -->
                    <div x-show="open" x-cloak @click.away="open = false" 
                         class="absolute right-0 mt-2 w-80 bg-white border border-slate-200 shadow-2xl rounded-2xl p-4 z-50 text-left">
                        <div class="flex items-center justify-between border-b pb-2.5 mb-2.5">
                            <span class="text-xs font-bold text-slate-900">Notifications ({{ $unreadNotifications->count() }})</span>
                            @if($unreadNotifications->count() > 0)
                            <form method="POST" action="{{ route('notifications.read-all') }}">
                                @csrf
                                <button type="submit" class="text-[10px] text-indigo-600 hover:text-indigo-700 font-bold cursor-pointer">Mark all read</button>
                            </form>
                            @endif
                        </div>
                        <div class="space-y-3 max-h-60 overflow-y-auto">
                            @forelse($unreadNotifications as $notif)
                            <div class="text-xs">
                                <h4 class="font-bold text-slate-800 leading-snug">{{ $notif->title }}</h4>
                                <p class="text-slate-500 text-[10px] leading-normal mt-0.5">{{ $notif->body }}</p>
                                <span class="text-[9px] text-slate-400 font-mono mt-1 block">{{ $notif->created_at->diffForHumans() }}</span>
                            </div>
                            @empty
                            <div class="text-xs text-slate-400 font-medium py-4 text-center">No new notifications.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="h-5 w-[1px] bg-slate-200"></div>
                <a href="{{ route('superadmin.support') }}" class="flex items-center gap-1.5 sm:gap-2 hover:bg-slate-50 border border-slate-200/60 px-2.5 sm:px-3 py-1.5 rounded-xl transition-all hover:border-indigo-600/30 cursor-pointer text-slate-600 hover:text-slate-900" title="Support Desk">
                    <span class="material-symbols-outlined text-[#4f46e5] text-[18px]">support_agent</span>
                    <span class="text-xs font-semibold hidden sm:inline">Support Desk</span>
                </a>
            </div>
        </header>

        <!-- Main Dashboard Canvas -->
        <main class="p-4 sm:p-6 md:p-8 max-w-7xl mx-auto w-full flex-grow">
            <div class="animate-fade-in">
                @yield('content')
            </div>
        </main>
    </div>

</body>
</html>
