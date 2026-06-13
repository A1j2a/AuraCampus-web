<!DOCTYPE html>
<html class="light overflow-x-hidden" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AuraCampus | School Admin Dashboard')</title>

    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ sidebarOpen: false, sidebarCollapsed: false }" class="font-sans antialiased tech-canvas text-slate-800 min-h-screen relative overflow-x-hidden">

    <!-- Glowing Background Spheres for Visual Contrast (Teal/Emerald theme for School) -->
    <div class="absolute top-10 right-20 w-80 h-80 rounded-full bg-gradient-to-br from-violet-500/5 to-violet-600/5 blur-3xl animate-sphere-1 pointer-events-none"></div>
    <div class="absolute bottom-10 left-64 w-96 h-96 rounded-full bg-gradient-to-tr from-violet-600/5 to-violet-700/5 blur-3xl animate-sphere-2 pointer-events-none"></div>

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div x-show="sidebarOpen" x-cloak class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm z-40 lg:hidden shadow-2xl" @click="sidebarOpen = false"></div>

    <!-- Sidebar -->
    <aside :class="[
               sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
               sidebarCollapsed ? 'w-64 px-4 lg:w-20 lg:px-2' : 'w-64 px-4'
           ]"
           class="h-screen fixed left-0 top-0 bg-white border-r border-slate-200/60 shadow-sm flex flex-col py-6 z-50 transition-all duration-300 ease-in-out">
        <div :class="sidebarCollapsed ? 'flex-col gap-4 items-center justify-center' : 'flex-row justify-between gap-3'" class="mb-8 flex items-center px-2">
            <!-- logo and text -->
            <div x-show="!sidebarCollapsed" class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg overflow-hidden flex items-center justify-center shrink-0 shadow-sm border border-slate-100">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-cover">
                </div>
                <div>
                    <h1 class="text-base font-bold text-slate-900 tracking-tight leading-none">AuraCampus</h1>
                    <p class="text-[9px] text-[#6C4CF1] uppercase font-mono tracking-widest mt-1">School Admin</p>
                </div>
            </div>
            
            <!-- logo only when collapsed on desktop -->
            <div x-show="sidebarCollapsed" class="hidden lg:block w-8 h-8 rounded-lg overflow-hidden flex items-center justify-center shrink-0 shadow-sm border border-slate-100">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-cover">
            </div>

            <!-- Toggle buttons -->
            <div :class="sidebarCollapsed ? 'flex-col items-center' : 'flex-row items-center'" class="flex gap-1">
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-slate-600 cursor-pointer flex items-center justify-center" title="Close Sidebar">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
                <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden lg:flex text-slate-400 hover:text-[#6C4CF1] cursor-pointer items-center justify-center p-1.5 hover:bg-slate-50 rounded-lg transition-all" title="Toggle Sidebar">
                    <span class="material-symbols-outlined text-[20px]" x-text="sidebarCollapsed ? 'menu_open' : 'menu'">menu</span>
                </button>
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto pr-1">
            <!-- Dashboard -->
            <a href="{{ route('school.dashboard') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.dashboard') ? 'text-[#6C4CF1] bg-violet-50/50 border-l-2 border-[#6C4CF1] font-semibold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}"
               title="Dashboard">
                <span class="material-symbols-outlined text-[20px]" data-icon="dashboard">dashboard</span>
                <span x-show="!sidebarCollapsed" class="text-xs">Dashboard</span>
            </a>

            <!-- Classes & Sections -->
            <a href="{{ route('school.classes') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.classes') ? 'text-[#6C4CF1] bg-violet-50/50 border-l-2 border-[#6C4CF1] font-semibold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}"
               title="Classes & Sections">
                <span class="material-symbols-outlined text-[20px]" data-icon="class">class</span>
                <span x-show="!sidebarCollapsed" class="text-xs">Classes & Sections</span>
            </a>

            <!-- Subjects -->
            <a href="{{ route('school.subjects') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.subjects') ? 'text-[#6C4CF1] bg-violet-50/50 border-l-2 border-[#6C4CF1] font-semibold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}"
               title="Subjects">
                <span class="material-symbols-outlined text-[20px]" data-icon="book">book</span>
                <span x-show="!sidebarCollapsed" class="text-xs">Subjects</span>
            </a>

            <!-- Teachers -->
            <a href="{{ route('school.teachers') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.teachers') ? 'text-[#6C4CF1] bg-violet-50/50 border-l-2 border-[#6C4CF1] font-semibold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}"
               title="Teachers">
                <span class="material-symbols-outlined text-[20px]" data-icon="badge">badge</span>
                <span x-show="!sidebarCollapsed" class="text-xs">Teachers</span>
            </a>

            <!-- Students -->
            <a href="{{ route('school.students') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.students') ? 'text-[#6C4CF1] bg-violet-50/50 border-l-2 border-[#6C4CF1] font-semibold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}"
               title="Students">
                <span class="material-symbols-outlined text-[20px]" data-icon="groups">groups</span>
                <span x-show="!sidebarCollapsed" class="text-xs">Students</span>
            </a>

            <!-- Parents -->
            <a href="{{ route('school.parents') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.parents') ? 'text-[#6C4CF1] bg-violet-50/50 border-l-2 border-[#6C4CF1] font-semibold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}"
               title="Parents">
                <span class="material-symbols-outlined text-[20px]" data-icon="family_restroom">family_restroom</span>
                <span x-show="!sidebarCollapsed" class="text-xs">Parents</span>
            </a>

            <!-- Timetable -->
            <a href="{{ route('school.timetable.index') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.timetable.index') ? 'text-[#6C4CF1] bg-violet-50/50 border-l-2 border-[#6C4CF1] font-semibold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}"
               title="Timetable">
                <span class="material-symbols-outlined text-[20px]" data-icon="schedule">schedule</span>
                <span x-show="!sidebarCollapsed" class="text-xs">Timetable</span>
            </a>

            <!-- Notice Board -->
            <a href="{{ route('school.notices') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.notices') ? 'text-[#6C4CF1] bg-violet-50/50 border-l-2 border-[#6C4CF1] font-semibold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}"
               title="Notice Board">
                <span class="material-symbols-outlined text-[20px]" data-icon="campaign">campaign</span>
                <span x-show="!sidebarCollapsed" class="text-xs">Notice Board</span>
            </a>

            <!-- Exams & Schedules -->
            <a href="{{ route('school.exams') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.exams') || request()->routeIs('school.exams.show') ? 'text-[#6C4CF1] bg-violet-50/50 border-l-2 border-[#6C4CF1] font-semibold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}"
               title="Exams & Schedules">
                <span class="material-symbols-outlined text-[20px]" data-icon="quiz">quiz</span>
                <span x-show="!sidebarCollapsed" class="text-xs">Exams & Schedules</span>
            </a>

            <!-- Report Cards -->
            <a href="{{ route('school.report-cards.index') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.report-cards.index') || request()->routeIs('school.report-cards.show') ? 'text-[#6C4CF1] bg-violet-50/50 border-l-2 border-[#6C4CF1] font-semibold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}"
               title="Report Cards">
                <span class="material-symbols-outlined text-[20px]" data-icon="description">description</span>
                <span x-show="!sidebarCollapsed" class="text-xs">Report Cards</span>
            </a>

            <!-- Settings -->
            <a href="{{ route('school.settings') }}" 
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'"
               class="flex items-center gap-3 py-2 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('school.settings') ? 'text-[#6C4CF1] bg-violet-50/50 border-l-2 border-[#6C4CF1] font-semibold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}"
               title="Settings">
                <span class="material-symbols-outlined text-[20px]" data-icon="settings">settings</span>
                <span x-show="!sidebarCollapsed" class="text-xs">Settings</span>
            </a>
        </nav>

        <div class="mt-auto px-1 py-4 border-t border-slate-100">
            <div :class="sidebarCollapsed ? 'flex-col gap-3 justify-center items-center' : 'justify-between'" class="flex items-center">
                <div :class="sidebarCollapsed ? 'justify-center' : ''" class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-violet-50 border border-violet-100 flex items-center justify-center text-[#6C4CF1] text-xs font-bold shadow-sm shrink-0 font-sans"
                         :title="sidebarCollapsed ? 'Logged in as {{ auth()->check() ? auth()->user()->name : 'Principal James' }}' : ''">
                        {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 2)) : 'GA' }}
                    </div>
                    <div x-show="!sidebarCollapsed" class="min-w-0">
                        <p class="text-xs font-semibold text-slate-800 truncate max-w-[110px]">{{ auth()->check() ? auth()->user()->name : 'Principal James' }}</p>
                        <p class="text-[9px] text-slate-400 uppercase font-mono tracking-wider">School Admin</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" :class="sidebarCollapsed ? 'w-full flex justify-center' : ''">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-rose-500 transition-colors p-1.5 rounded-lg cursor-pointer" title="Log Out">
                        <span class="material-symbols-outlined text-[18px]">logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div :class="sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64'"
         class="ml-0 min-h-screen flex flex-col relative z-10 transition-all duration-300 ease-in-out">
        <!-- Top Navigation -->
        <header class="sticky top-0 w-full z-40 bg-white/80 border-b border-slate-200/60 backdrop-blur-md flex justify-between items-center h-16 px-4 lg:px-8">
            <div class="flex items-center flex-1 max-w-2xl gap-2 lg:gap-4">
                <!-- Hamburger Menu -->
                <button @click="sidebarOpen = true" class="text-slate-500 hover:bg-slate-50 p-1.5 rounded-lg border border-slate-200/60 lg:hidden shrink-0 cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">menu</span>
                </button>
                <!-- Academic Year Selector -->
                <div class="relative shrink-0">
                    <form action="{{ route('school.session.select') }}" method="POST" id="session-select-form">
                        @csrf
                        <select name="academic_session_id" onchange="this.form.submit()" class="premium-input rounded-xl pl-2.5 sm:pl-4 pr-7 sm:pr-9 py-1.5 sm:py-2 focus:outline-none focus:ring-2 focus:ring-violet-400 text-[10px] sm:text-xs font-semibold cursor-pointer appearance-none bg-white">
                            @foreach($sessions as $sess)
                                <option value="{{ $sess->id }}" {{ isset($activeSession) && $activeSession->id === $sess->id ? 'selected' : '' }}>
                                    Session: {{ $sess->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                    <span class="material-symbols-outlined absolute right-2 sm:right-2.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-[14px] sm:text-[16px]">keyboard_arrow_down</span>
                </div>

                <!-- Search -->
                <div class="relative w-full group" x-data="{ 
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
                    <span class="material-symbols-outlined absolute left-2.5 sm:left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[16px] sm:text-[18px]" data-icon="search">search</span>
                    <input class="w-full pl-8 sm:pl-10 pr-3 sm:pr-4 py-1.5 sm:py-2 premium-input rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-400 placeholder-slate-300 text-[10px] sm:text-xs font-medium" 
                           placeholder="Search students, staff, or classes..." 
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
                        <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 bg-violet-500 rounded-full shadow-[0_0_4px_#6C4CF1]"></span>
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
                <!-- Support Desk Link -->
                <a href="{{ route('school.support.index') }}" class="flex items-center gap-1.5 sm:gap-2 hover:bg-slate-50 border border-slate-200/60 px-2 sm:px-3 py-1.5 rounded-xl transition-all hover:border-[#6C4CF1]/30 text-slate-600 hover:text-slate-900" title="Help Desk">
                    <span class="material-symbols-outlined text-[#6C4CF1] text-[18px]" data-icon="help_outline">help_outline</span>
                    <span class="text-xs font-semibold hidden sm:inline">Help Desk</span>
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
