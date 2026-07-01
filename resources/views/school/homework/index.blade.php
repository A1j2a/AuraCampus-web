@extends('layouts.school')

@section('title', 'AuraCampus | Homework Dashboard')

@section('content')
<div x-data="{ 
    deleteModal: false,
    deleteUrl: '',
    deleteTitle: '',
    confirmDelete(url, title) {
        this.deleteUrl = url;
        this.deleteTitle = title;
        this.deleteModal = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight text-gradient">Homework & Submissions Dashboard</h2>
            <p class="text-xs text-slate-500 mt-1">Monitor, audit, and clean up homework data created by teachers across all sections.</p>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-violet-50 border border-violet-200 rounded-xl text-xs font-semibold text-violet-700 flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    <!-- Filters Section -->
    <div class="bg-white rounded-2xl border border-slate-200/60 p-5 shadow-sm mb-6">
        <form method="GET" action="{{ route('school.homework.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Class</label>
                <select name="class_id" class="w-full px-3 py-2 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet appearance-none bg-white">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            Class {{ $class->name }} {{ $class->section }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Subject</label>
                <select name="subject_id" class="w-full px-3 py-2 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet appearance-none bg-white">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ request('subject_id') == $sub->id ? 'selected' : '' }}>
                            {{ $sub->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Teacher</label>
                <select name="teacher_id" class="w-full px-3 py-2 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet appearance-none bg-white">
                    <option value="">All Teachers</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Status</label>
                <select name="status" class="w-full px-3 py-2 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet appearance-none bg-white">
                    <option value="">All Statuses</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm text-center">
                    Filter
                </button>
                <a href="{{ route('school.homework.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-650 text-xs font-bold rounded-xl transition-all cursor-pointer text-center flex items-center justify-center" title="Reset Filters">
                    <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Homework Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        @forelse($homeworks as $hw)
            @php
                $totalStudents = $classStudentCounts[$hw->class_id] ?? 0;
                $submitted = $hw->submissions_count;
                $pct = $totalStudents > 0 ? round(($submitted / $totalStudents) * 100) : 0;
            @endphp
            <div class="bg-white border border-slate-200/60 rounded-2xl shadow-sm p-5 relative flex flex-col justify-between transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                <div>
                    <div class="flex justify-between items-start gap-4 mb-3">
                        <span class="px-2.5 py-0.5 text-[9px] font-bold rounded-full uppercase {{ $hw->status === 'published' ? 'bg-emerald-50 text-emerald-700 border border-emerald-150' : 'bg-slate-150 text-slate-700 border border-slate-200' }}">
                            {{ $hw->status }}
                        </span>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('school.homework.show', $hw) }}" class="text-slate-400 hover:text-violet-600 transition-colors p-1" title="View Details">
                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                            </a>
                            <button type="button" @click="confirmDelete('{{ route('school.homework.destroy', $hw) }}', '{{ addslashes($hw->title) }}')" class="text-slate-400 hover:text-rose-500 transition-colors p-1 rounded-lg cursor-pointer" title="Delete Homework">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </div>
                    </div>

                    <h3 class="text-sm font-bold text-slate-900 leading-snug mb-1">{{ $hw->title }}</h3>
                    <p class="text-[11px] font-semibold text-slate-500 mb-3">
                        Class {{ $hw->class?->name ?? 'N/A' }} {{ $hw->class?->section ?? '' }} • {{ $hw->subject?->name ?? 'N/A' }}
                    </p>
                    <p class="text-xs text-slate-650 line-clamp-3 mb-4 leading-relaxed">{{ $hw->description }}</p>
                </div>

                <div class="mt-auto space-y-3 pt-3 border-t border-slate-100">
                    <!-- Progress Bar -->
                    <div>
                        <div class="flex justify-between text-[10px] font-semibold text-slate-500 mb-1">
                            <span>Submissions Log</span>
                            <span>{{ $submitted }}/{{ $totalStudents }} ({{ $pct }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-violet-650 h-full rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-[10px] text-slate-500 pt-1">
                        <span class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">person</span>
                            {{ $hw->teacher?->name ?? 'Unknown Teacher' }}
                        </span>
                        <span class="flex items-center gap-1 font-mono">
                            <span class="material-symbols-outlined text-[14px]">calendar_today</span>
                            Due: {{ $hw->due_date ? $hw->due_date->format('d M Y') : 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center premium-card rounded-2xl bg-white">
                <span class="material-symbols-outlined text-indigo-500 text-5xl mb-3 animate-float-slow" data-icon="assignment">assignment</span>
                <h4 class="text-sm font-bold text-slate-800 mb-1">No Homework Records Found</h4>
                <p class="text-xs text-slate-500 max-w-sm mx-auto leading-normal">There is no homework matching your search or filters in this academic session.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $homeworks->links() }}
    </div>

    <!-- Global Delete Form -->
    <form id="global-delete-form" method="POST" :action="deleteUrl" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <!-- Custom Delete Confirmation Modal -->
    <div x-show="deleteModal" x-cloak class="fixed inset-0 z-[110] flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="deleteModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 relative z-10 border border-slate-200/60 text-center" @click.stop>
            <div class="w-12 h-12 rounded-full bg-rose-50 border border-rose-100 flex items-center justify-center mx-auto mb-4 text-rose-600">
                <span class="material-symbols-outlined text-[24px]">delete_forever</span>
            </div>
            <h3 class="text-sm font-bold text-slate-950 mb-1">Delete Homework Record</h3>
            <p class="text-xs text-slate-500 leading-relaxed px-2">
                Are you sure you want to delete <strong class="text-slate-800" x-text="deleteTitle"></strong>? This will permanently delete the homework and all student submissions associated with it.
            </p>
            <div class="mt-6 flex justify-center gap-3">
                <button type="button" @click="deleteModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="button" @click="document.getElementById('global-delete-form').submit()" class="px-4 py-2 bg-rose-650 hover:bg-rose-705 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
