@extends('layouts.school')

@section('title', 'AuraCampus | School Dashboard')

@section('content')
    <!-- Dashboard Header -->
    <header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">{{ auth()->user()->school->name ?? 'School' }} Dashboard</h2>
            <p class="text-xs text-slate-500 mt-1">Manage daily school operations, academics, and student tracking.</p>
        </div>
        <div class="px-3.5 py-1.5 rounded-xl border border-violet-200 bg-violet-50 text-[10px] font-mono text-violet-700 flex items-center gap-2 shadow-sm">
            <span class="w-1.5 h-1.5 rounded-full bg-violet-500 shadow-[0_0_6px_#6C4CF1] animate-pulse"></span>
            SCHOOL LIVE
        </div>
    </header>

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-violet-50 border border-violet-200 rounded-xl text-xs font-semibold text-violet-700 flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    <!-- School Metric Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Card 1: Students -->
        <div class="premium-card p-6 rounded-2xl hover:scale-[1.01] hover:shadow-md transition-all duration-300 relative group overflow-hidden cursor-pointer">
            <div class="absolute -right-10 -top-10 w-24 h-24 bg-indigo-500/5 rounded-full blur-2xl group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="p-2 bg-indigo-50 border border-indigo-100 text-indigo-600 rounded-xl shadow-sm">
                    <span class="material-symbols-outlined text-[20px]">groups</span>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 font-semibold tracking-wide uppercase font-mono">Total Students</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-1 tracking-tight">{{ number_format($totalStudents) }}</h3>
        </div>

        <!-- Card 2: Total Classes -->
        <div class="premium-card p-6 rounded-2xl hover:scale-[1.01] hover:shadow-md transition-all duration-300 relative group overflow-hidden cursor-pointer">
            <div class="absolute -right-10 -top-10 w-24 h-24 bg-cyan-500/5 rounded-full blur-2xl group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="p-2 bg-cyan-50 border border-cyan-100 text-cyan-600 rounded-xl shadow-sm">
                    <span class="material-symbols-outlined text-[20px]">class</span>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 font-semibold tracking-wide uppercase font-mono">Total Classes</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-1 tracking-tight">{{ $totalClasses }}</h3>
        </div>

        <!-- Card 3: Total Parents -->
        <div class="premium-card p-6 rounded-2xl hover:scale-[1.01] hover:shadow-md transition-all duration-300 relative group overflow-hidden cursor-pointer">
            <div class="absolute -right-10 -top-10 w-24 h-24 bg-violet-500/5 rounded-full blur-2xl group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="p-2 bg-violet-50 border border-violet-100 text-violet-600 rounded-xl shadow-sm">
                    <span class="material-symbols-outlined text-[20px]">family_restroom</span>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 font-semibold tracking-wide uppercase font-mono">Total Parents</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-1 tracking-tight">{{ $totalParents }}</h3>
        </div>

        <!-- Card 4: Total Subjects -->
        <div class="premium-card p-6 rounded-2xl hover:scale-[1.01] hover:shadow-md transition-all duration-300 relative group overflow-hidden cursor-pointer">
            <div class="absolute -right-10 -top-10 w-24 h-24 bg-amber-500/5 rounded-full blur-2xl group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="p-2 bg-amber-50 border border-amber-100 text-amber-600 rounded-xl shadow-sm">
                    <span class="material-symbols-outlined text-[20px]">book</span>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 font-semibold tracking-wide uppercase font-mono">Total Subjects</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-1 tracking-tight">{{ $totalSubjects }}</h3>
        </div>
    </div>

    <!-- Bento Grid Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- School Noticeboard -->
        <div class="premium-card rounded-2xl p-6 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-6">
                    <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-500 text-[20px]">campaign</span>
                        School Noticeboard
                    </h4>
                </div>
                <div class="space-y-4">
                    @forelse($notices as $notice)
                    <div class="p-4 bg-slate-50/30 rounded-xl border border-slate-200/60 border-l-4 
                        {{ $notice->type === 'academic' ? 'border-l-indigo-500' : ($notice->type === 'event' ? 'border-l-violet-500' : ($notice->type === 'holiday' ? 'border-l-rose-500' : 'border-l-slate-400')) }}">
                        <span class="text-[9px] font-mono font-bold uppercase tracking-wider 
                            {{ $notice->type === 'academic' ? 'text-indigo-600' : ($notice->type === 'event' ? 'text-violet-600' : ($notice->type === 'holiday' ? 'text-rose-600' : 'text-slate-500')) }}">
                            {{ $notice->type }}
                        </span>
                        <h5 class="text-xs font-bold text-slate-800 mt-1">{{ $notice->title }}</h5>
                        <p class="text-xs text-slate-500 mt-1 leading-normal">{{ Str::limit($notice->content, 120) }}</p>
                        <span class="text-[9px] text-slate-400 mt-2 block font-mono">{{ $notice->published_at?->diffForHumans() }}</span>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 text-center py-4">No notices published yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Class Registry Quick View -->
        <div class="premium-card rounded-2xl p-6 flex flex-col justify-between lg:col-span-2">
            <div>
                <div class="flex justify-between items-center mb-6">
                    <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-indigo-500 text-[20px]">school</span>
                        Class Registry
                    </h4>
                    <a href="{{ route('school.classes') }}" class="text-violet-600 hover:text-violet-700 text-xs font-semibold transition-colors">View All →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="px-4 py-3 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Class</th>
                                <th class="px-4 py-3 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Class Teacher</th>
                                <th class="px-4 py-3 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Students</th>
                                <th class="px-4 py-3 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Room</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($classes->take(6) as $class)
                            <tr class="hover:bg-slate-50/50 transition-all duration-150">
                                <td class="px-4 py-3">
                                    <span class="text-xs font-bold text-slate-800">{{ $class->name }} - {{ $class->section }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs text-slate-600">{{ $class->teacher?->name ?? '—' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs font-mono font-bold text-slate-700">{{ $class->students_count }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $class->room_number ?? '—' }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-xs text-slate-400">No classes created yet. <a href="{{ route('school.classes') }}" class="text-violet-600 font-semibold">Add one now →</a></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
