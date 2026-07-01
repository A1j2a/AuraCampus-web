<!DOCTYPE html>
<html class="light overflow-x-hidden" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AuraCampus | School Admin Dashboard')</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Poppins:wght@600;700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Prevent sidebar layout shift/flicker before Alpine loads -->
    <script>
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    </script>
</head>
<body x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' }" 
      x-init="$watch('sidebarCollapsed', val => { 
          localStorage.setItem('sidebarCollapsed', val); 
          if (val) { document.documentElement.classList.add('sidebar-collapsed'); } 
          else { document.documentElement.classList.remove('sidebar-collapsed'); } 
      })" 
      class="bg-surface-background font-body-md text-on-surface min-h-screen relative overflow-x-hidden antialiased">

    <!-- Glowing Background Spheres for Visual Contrast -->
    <div class="absolute top-10 right-20 w-80 h-80 rounded-full bg-gradient-to-br from-violet-500/5 to-violet-600/5 blur-3xl animate-sphere-1 pointer-events-none"></div>
    <div class="absolute bottom-10 left-64 w-96 h-96 rounded-full bg-gradient-to-tr from-violet-600/5 to-violet-700/5 blur-3xl animate-sphere-2 pointer-events-none"></div>

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div x-show="sidebarOpen" x-cloak class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm z-40 lg:hidden shadow-2xl" @click="sidebarOpen = false"></div>

    <!-- Sidebar -->
    <aside :class="[
               sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
               sidebarCollapsed ? 'w-64 px-4 lg:w-20 lg:px-2' : 'w-64 px-4'
           ]"
           class="w-64 px-4 -translate-x-full lg:translate-x-0 h-screen fixed left-0 top-0 bg-sidebar-bg shadow-md flex flex-col py-6 z-50 transition-all duration-300 ease-in-out">
        
        <div :class="sidebarCollapsed ? 'flex-col gap-4 items-center justify-center' : 'flex-row justify-between gap-3'" class="mb-8 flex items-center px-2">
            <!-- logo and text -->
            <div x-show="!sidebarCollapsed" class="flex items-center gap-3 min-w-0 flex-1">
                @if(auth()->check() && auth()->user()->school && auth()->user()->school->logo_path)
                    <img src="{{ asset('storage/' . auth()->user()->school->logo_path) }}" class="w-10 h-10 rounded-lg object-cover shadow-sm shrink-0" alt="School Logo">
                @else
                    <div class="w-10 h-10 rounded-lg bg-surface-tint flex items-center justify-center text-white shrink-0 shadow-sm">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">school</span>
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <h1 class="text-base font-bold text-white leading-tight tracking-tight font-display-lg whitespace-normal break-words">
                        {{ auth()->check() && auth()->user()->school ? auth()->user()->school->name : 'Green Valley Academy' }}
                    </h1>
                    <p class="text-xs text-slate-400 mt-0.5 font-medium font-sans">School Admin</p>
                </div>
            </div>
            
            <!-- logo only when collapsed on desktop -->
            <div x-show="sidebarCollapsed" class="hidden lg:flex shrink-0">
                @if(auth()->check() && auth()->user()->school && auth()->user()->school->logo_path)
                    <img src="{{ asset('storage/' . auth()->user()->school->logo_path) }}" class="w-10 h-10 rounded-lg object-cover shadow-sm" alt="School Logo">
                @else
                    <div class="w-10 h-10 rounded-lg bg-surface-tint flex items-center justify-center text-white shadow-sm">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">school</span>
                    </div>
                @endif
            </div>

            <!-- Toggle buttons -->
            <div :class="sidebarCollapsed ? 'flex-col items-center' : 'flex-row items-center'" class="flex gap-1 shrink-0">
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white cursor-pointer flex items-center justify-center" title="Close Sidebar">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
                <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden lg:flex text-slate-400 hover:text-white cursor-pointer items-center justify-center p-1.5 hover:bg-white/10 rounded-lg transition-all" title="Toggle Sidebar">
                    <span class="material-symbols-outlined text-[20px]" x-text="sidebarCollapsed ? 'menu_open' : 'menu'">menu</span>
                </button>
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto pr-1">
            <!-- Dashboard -->
            <a href="{{ route('school.dashboard') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2.5 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.dashboard') ? 'bg-surface-tint text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-white/10 hover:text-slate-300' }}"
               title="Dashboard">
                <span class="material-symbols-outlined text-[20px]" data-icon="dashboard">dashboard</span>
                <span x-show="!sidebarCollapsed" class="text-xs font-medium">Dashboard</span>
            </a>

            <!-- Classes & Sections -->
            <a href="{{ route('school.classes') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2.5 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.classes') ? 'bg-surface-tint text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-white/10 hover:text-slate-300' }}"
               title="Academics">
                <span class="material-symbols-outlined text-[20px]" data-icon="school">school</span>
                <span x-show="!sidebarCollapsed" class="text-xs font-medium">Academics</span>
            </a>

            <!-- Subjects -->
            <a href="{{ route('school.subjects') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2.5 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.subjects') ? 'bg-surface-tint text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-white/10 hover:text-slate-300' }}"
               title="Subjects">
                <span class="material-symbols-outlined text-[20px]" data-icon="book">book</span>
                <span x-show="!sidebarCollapsed" class="text-xs font-medium">Subjects</span>
            </a>

            <!-- Teachers -->
            <a href="{{ route('school.teachers') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2.5 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.teachers') ? 'bg-surface-tint text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-white/10 hover:text-slate-300' }}"
               title="Teachers">
                <span class="material-symbols-outlined text-[20px]" data-icon="person">person</span>
                <span x-show="!sidebarCollapsed" class="text-xs font-medium">Teachers</span>
            </a>

            <!-- Students -->
            <a href="{{ route('school.students') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2.5 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.students') ? 'bg-surface-tint text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-white/10 hover:text-slate-300' }}"
               title="Students">
                <span class="material-symbols-outlined text-[20px]" data-icon="group">group</span>
                <span x-show="!sidebarCollapsed" class="text-xs font-medium">Students</span>
            </a>

            <!-- Parents -->
            <a href="{{ route('school.parents') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2.5 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.parents') ? 'bg-surface-tint text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-white/10 hover:text-slate-300' }}"
               title="Parents">
                <span class="material-symbols-outlined text-[20px]" data-icon="groups">groups</span>
                <span x-show="!sidebarCollapsed" class="text-xs font-medium">Parents</span>
            </a>

            <!-- Timetable -->
            <a href="{{ route('school.timetable.index') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2.5 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.timetable.index') ? 'bg-surface-tint text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-white/10 hover:text-slate-300' }}"
               title="Timetable">
                <span class="material-symbols-outlined text-[20px]" data-icon="schedule">schedule</span>
                <span x-show="!sidebarCollapsed" class="text-xs font-medium">Timetable</span>
            </a>

            <!-- Notice Board -->
            <a href="{{ route('school.notices') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2.5 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.notices') ? 'bg-surface-tint text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-white/10 hover:text-slate-300' }}"
               title="Notice Board">
                <span class="material-symbols-outlined text-[20px]" data-icon="campaign">campaign</span>
                <span x-show="!sidebarCollapsed" class="text-xs font-medium">Notice Board</span>
            </a>

            <!-- Exams & Schedules -->
            <a href="{{ route('school.exams') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2.5 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.exams') || request()->routeIs('school.exams.show') ? 'bg-surface-tint text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-white/10 hover:text-slate-300' }}"
               title="Exams & Schedules">
                <span class="material-symbols-outlined text-[20px]" data-icon="quiz">quiz</span>
                <span x-show="!sidebarCollapsed" class="text-xs font-medium">Exams & Schedules</span>
            </a>

            <!-- Report Cards -->
            <a href="{{ route('school.report-cards.index') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2.5 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.report-cards.index') || request()->routeIs('school.report-cards.show') ? 'bg-surface-tint text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-white/10 hover:text-slate-300' }}"
               title="Report Cards">
                <span class="material-symbols-outlined text-[20px]" data-icon="description">description</span>
                <span x-show="!sidebarCollapsed" class="text-xs font-medium">Report Cards</span>
            </a>

            <!-- Homework Management -->
            <a href="{{ route('school.homework.index') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2.5 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.homework.*') ? 'bg-surface-tint text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-white/10 hover:text-slate-300' }}"
               title="Homework">
                <span class="material-symbols-outlined text-[20px]" data-icon="assignment">assignment</span>
                <span x-show="!sidebarCollapsed" class="text-xs font-medium">Homework</span>
            </a>

            <!-- Curriculum Progress Tracker -->
            <a href="{{ route('school.curriculum.index') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2.5 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.curriculum.index') ? 'bg-surface-tint text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-white/10 hover:text-slate-300' }}"
               title="Curriculum Tracker">
                <span class="material-symbols-outlined text-[20px]" data-icon="auto_stories">auto_stories</span>
                <span x-show="!sidebarCollapsed" class="text-xs font-medium">Curriculum Tracker</span>
            </a>

            <!-- PTC Bookings -->
            <a href="{{ route('school.ptc.index') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2.5 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.ptc.index') ? 'bg-surface-tint text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-white/10 hover:text-slate-300' }}"
               title="PTC Bookings">
                <span class="material-symbols-outlined text-[20px]" data-icon="handshake">handshake</span>
                <span x-show="!sidebarCollapsed" class="text-xs font-medium">PTC Bookings</span>
            </a>
        </nav>

        <div class="mt-auto pt-4 border-t border-white/10 space-y-1">
            <!-- Credentials -->
            <a href="{{ route('school.settings') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2.5 rounded-xl transition-all duration-200 ease-in-out text-slate-400 hover:bg-white/10 hover:text-slate-300"
               title="Credentials">
                <span class="material-symbols-outlined text-[20px]" data-icon="key">key</span>
                <span x-show="!sidebarCollapsed" class="text-xs font-medium">Credentials</span>
            </a>

            <!-- Settings -->
            <a href="{{ route('school.settings') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2.5 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.settings') ? 'bg-surface-tint text-white font-semibold shadow-sm' : 'text-slate-400 hover:bg-white/10 hover:text-slate-300' }}"
               title="Settings">
                <span class="material-symbols-outlined text-[20px]" data-icon="settings">settings</span>
                <span x-show="!sidebarCollapsed" class="text-xs font-medium">Settings</span>
            </a>

            <!-- Log Out -->
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" 
                        :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
                        class="w-full flex items-center gap-3 py-2.5 rounded-xl transition-all duration-200 ease-in-out text-slate-400 hover:bg-rose-950/20 hover:text-rose-400 text-left cursor-pointer"
                        title="Log Out">
                    <span class="material-symbols-outlined text-[20px]" data-icon="logout">logout</span>
                    <span x-show="!sidebarCollapsed" class="text-xs font-medium font-sans">Log Out</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div :class="sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64'"
         class="lg:ml-64 ml-0 min-h-screen flex flex-col relative z-10 transition-all duration-300 ease-in-out">
        
        <!-- Top Navigation -->
        <header class="bg-surface border-b border-outline-variant sticky top-0 w-full z-40 shadow-sm">
            <div class="flex justify-between items-center px-4 lg:px-8 py-3 w-full max-w-container-max mx-auto h-16">
                
                <div class="flex items-center flex-1 max-w-2xl gap-2 lg:gap-6">
                    <!-- Hamburger Menu -->
                    <button @click="sidebarOpen = true" class="text-on-surface-variant hover:bg-surface-container-low p-1.5 rounded-lg border border-outline-variant lg:hidden shrink-0 cursor-pointer">
                        <span class="material-symbols-outlined text-[18px]">menu</span>
                    </button>

                    <!-- Search -->
                    <div class="relative w-full max-w-md group" x-data="{ 
                        query: '',
                        results: { students: [], teachers: [], classes: [] },
                        showDropdown: false,
                        loading: false,
                        performSearch() {
                            if (this.query.length < 2) {
                                this.results = { students: [], teachers: [], classes: [] };
                                this.showDropdown = false;
                                return;
                            }
                            this.loading = true;
                            fetch(`/school/search?query=${encodeURIComponent(this.query)}`)
                                .then(res => res.json())
                                .then(data => {
                                    this.results = data;
                                    this.loading = false;
                                    this.showDropdown = true;
                                })
                                .catch(() => {
                                    this.loading = false;
                                });
                        }
                    }" @click.away="showDropdown = false">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                        <input class="w-full pl-10 pr-4 py-2 bg-[#f1f5f9] border-none rounded-lg focus:ring-2 focus:ring-surface-tint font-body-sm transition-all focus:outline-none text-slate-800 placeholder-slate-400 text-xs" 
                               placeholder="Search for students, teachers, or reports..." 
                               type="text"
                               x-model="query"
                               @input.debounce.300ms="performSearch()"
                               @focus="if (query.length >= 2) showDropdown = true"/>

                        <!-- Search dropdown results -->
                        <div x-show="showDropdown" x-cloak class="absolute left-0 right-0 top-full mt-2 bg-white border border-slate-200 shadow-2xl rounded-2xl p-4 z-50 max-h-96 overflow-y-auto space-y-4 text-left">
                            <template x-if="loading">
                                <div class="text-xs text-slate-400 font-medium py-2 text-center">Loading matches...</div>
                            </template>
                            <template x-if="!loading && !results.students.length && !results.teachers.length && !results.classes.length">
                                <div class="text-xs text-slate-400 font-medium py-2 text-center">No matches found for "<span x-text="query"></span>"</div>
                            </template>

                            <!-- Students -->
                            <div x-show="results.students.length">
                                <span class="block text-[8px] font-mono text-slate-400 uppercase tracking-widest mb-1.5 font-bold">Students</span>
                                <div class="space-y-1">
                                    <template x-for="item in results.students" :key="item.id">
                                        <div class="flex items-center justify-between p-2 hover:bg-slate-50 rounded-xl transition-all">
                                            <div>
                                                <div class="text-xs font-bold text-slate-800" x-text="item.name"></div>
                                                <div class="text-[9px] text-slate-400 font-mono" x-text="item.email"></div>
                                            </div>
                                            <a :href="`/school/students?search=${encodeURIComponent(item.name)}`" class="text-[10px] text-[#6C4CF1] font-bold hover:underline">View Profile</a>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Teachers -->
                            <div x-show="results.teachers.length">
                                <span class="block text-[8px] font-mono text-slate-400 uppercase tracking-widest mb-1.5 font-bold">Teachers</span>
                                <div class="space-y-1">
                                    <template x-for="item in results.teachers" :key="item.id">
                                        <div class="flex items-center justify-between p-2 hover:bg-slate-50 rounded-xl transition-all">
                                            <div>
                                                <div class="text-xs font-bold text-slate-800" x-text="item.name"></div>
                                                <div class="text-[9px] text-slate-400 font-mono" x-text="item.email"></div>
                                            </div>
                                            <a :href="`/school/teachers?search=${encodeURIComponent(item.name)}`" class="text-[10px] text-[#6C4CF1] font-bold hover:underline">View Profile</a>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Classes -->
                            <div x-show="results.classes.length">
                                <span class="block text-[8px] font-mono text-slate-400 uppercase tracking-widest mb-1.5 font-bold">Classes</span>
                                <div class="space-y-1">
                                    <template x-for="item in results.classes" :key="item.id">
                                        <div class="flex items-center justify-between p-2 hover:bg-slate-50 rounded-xl transition-all">
                                            <div>
                                                <div class="text-xs font-bold text-slate-800" x-text="item.name + ' - ' + item.section"></div>
                                            </div>
                                            <a :href="`/school/classes?class_id=${item.id}`" class="text-[10px] text-[#6C4CF1] font-bold hover:underline">View Class</a>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center gap-3 sm:gap-6">
                    <!-- Academic Session Selector Badge -->
                    <div class="relative shrink-0 flex items-center">
                        <form action="{{ route('school.session.select') }}" method="POST" id="session-select-form" class="flex items-center">
                            @csrf
                            <div class="flex items-center gap-1.5 bg-secondary-container/30 text-secondary rounded-full border border-secondary/20 px-3 py-1.5 text-xs font-semibold">
                                <span class="material-symbols-outlined text-[16px] text-success-green">calendar_today</span>
                                <select name="academic_session_id" onchange="this.form.submit()" class="bg-transparent border-none p-0 pr-6 text-xs font-semibold cursor-pointer focus:ring-0 focus:outline-none appearance-none font-label-md">
                                    @foreach($sessions as $sess)
                                        <option value="{{ $sess->id }}" class="text-slate-800" {{ isset($activeSession) && $activeSession->id === $sess->id ? 'selected' : '' }}>
                                            AY: {{ $sess->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>

                    <!-- Notifications -->
                    @php
                        $unreadNotifications = auth()->check()
                            ? \App\Models\Notification::where('user_id', auth()->id())->whereNull('read_at')->latest()->get()
                            : collect();
                    @endphp
                    <div class="relative" x-data="{ open: false }">
                        <div class="relative cursor-pointer hover:bg-surface-container-low p-2 rounded-full transition-colors">
                            <button @click="open = !open" class="relative text-on-surface-variant flex items-center justify-center cursor-pointer">
                                <span class="material-symbols-outlined">notifications</span>
                                @if($unreadNotifications->count() > 0)
                                <span class="absolute top-0.5 right-0.5 w-2 h-2 bg-error rounded-full"></span>
                                @endif
                            </button>
                        </div>
                        
                        <!-- Notifications Dropdown -->
                        <div x-show="open" x-cloak @click.away="open = false" 
                             class="absolute right-0 mt-2 w-80 bg-white border border-slate-200 shadow-2xl rounded-2xl p-4 z-50 text-left">
                            <div class="flex items-center justify-between border-b pb-2.5 mb-2.5">
                                <span class="text-xs font-bold text-slate-900">Notifications ({{ $unreadNotifications->count() }})</span>
                                @if($unreadNotifications->count() > 0)
                                <form method="POST" action="{{ route('notifications.read-all') }}">
                                    @csrf
                                    <button type="submit" class="text-[10px] text-[#6C4CF1] hover:text-[#5a3dd4] font-bold cursor-pointer">Mark all read</button>
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

                    <!-- User Profile Area -->
                    <div class="flex items-center gap-3 pl-4 border-l border-outline-variant">
                        <div class="text-right hidden sm:block">
                            <p class="font-label-md text-on-surface leading-none font-semibold">
                                {{ auth()->check() ? auth()->user()->name : 'Principal Richards' }}
                            </p>
                            <p class="text-[11px] text-on-surface-variant mt-1 font-medium">School Admin</p>
                        </div>
                        @if(auth()->check() && auth()->user()->profile_image)
                            <img alt="{{ auth()->user()->name }}" class="w-10 h-10 rounded-full border-2 border-surface-tint/20 object-cover shrink-0" src="{{ asset('storage/' . auth()->user()->profile_image) }}"/>
                        @else
                            <div class="w-10 h-10 rounded-full bg-violet-50 border-2 border-surface-tint/20 flex items-center justify-center text-violet-600 font-bold text-xs shrink-0">
                                {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 2)) : 'PR' }}
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </header>

        <!-- Main Dashboard Canvas -->
        <main class="p-4 sm:p-6 md:p-8 max-w-7xl mx-auto w-full flex-grow">
            <div class="animate-fade-in">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Export Table Script -->
    <script>
        function exportTableToCSV(filename) {
            var csv = [];
            var rows = document.querySelectorAll("table tr");
            
            for (var i = 0; i < rows.length; i++) {
                var row = [], cols = rows[i].querySelectorAll("td, th");
                
                // Skip rows with no cells
                if (cols.length === 0) continue;
                
                var hasValidData = false;
                for (var j = 0; j < cols.length; j++) {
                    // Skip action cells or full spanned cells (like empty results)
                    if (cols[j].classList.contains("text-right") || cols[j].getAttribute("colspan")) {
                        continue;
                    }
                    var text = cols[j].innerText.trim().replace(/"/g, '""');
                    row.push('"' + text + '"');
                    hasValidData = true;
                }
                
                if (hasValidData) {
                    csv.push(row.join(","));
                }
            }

            var csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
            var downloadLink = document.createElement("a");
            downloadLink.download = filename;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }
    </script>
</body>
</html>
