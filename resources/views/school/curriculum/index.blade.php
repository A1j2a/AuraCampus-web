@extends('layouts.school')

@section('title', 'AuraCampus | Curriculum Tracker')

@section('content')
<div>
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight text-gradient">Curriculum Syllabus Progress Tracker</h2>
            <p class="text-xs text-slate-500 mt-1">Audit the academic syllabus completion status of different subjects across classes.</p>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-2xl border border-slate-200/60 p-5 shadow-sm mb-6">
        <form method="GET" action="{{ route('school.curriculum.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Select Class</label>
                <select name="class_id" onchange="this.form.submit()" class="w-full px-3 py-2 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet appearance-none bg-white">
                    <option value="">Choose Class...</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                            Class {{ $class->name }} {{ $class->section }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Select Subject</label>
                <select name="subject_id" onchange="this.form.submit()" class="w-full px-3 py-2 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet appearance-none bg-white">
                    <option value="">Choose Subject...</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ $selectedSubjectId == $sub->id ? 'selected' : '' }}>
                            {{ $sub->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <a href="{{ route('school.curriculum.index') }}" class="w-full inline-block px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-750 text-xs font-bold rounded-xl transition-all cursor-pointer text-center">
                    Reset Filters
                </a>
            </div>
        </form>
    </div>

    @if($selectedClassId && $selectedSubjectId)
        <!-- Progress Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Overall Progress Bar Card -->
            <div class="bg-white border border-slate-200/60 rounded-2xl p-5 shadow-sm md:col-span-2 flex flex-col justify-between">
                <div>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-2">Overall Syllabus Completion</h3>
                    <div class="flex items-baseline gap-2 mb-4">
                        <span class="text-3xl font-black text-violet-650">{{ $stats['completed_pct'] }}%</span>
                        <span class="text-xs text-slate-400 font-medium">completed</span>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden flex">
                        <div class="bg-emerald-500 h-full transition-all duration-500" style="width: {{ $stats['completed_pct'] }}%" title="Completed: {{ $stats['completed_pct'] }}%"></div>
                        <div class="bg-amber-400 h-full transition-all duration-500" style="width: {{ $stats['in_progress_pct'] }}%" title="In Progress: {{ $stats['in_progress_pct'] }}%"></div>
                        <div class="bg-slate-200 h-full transition-all duration-500" style="width: {{ $stats['not_started_pct'] }}%" title="Not Started: {{ $stats['not_started_pct'] }}%"></div>
                    </div>
                    <div class="flex justify-between text-[9px] text-slate-450 font-bold uppercase tracking-wide">
                        <span>Syllabus Progress Breakdown</span>
                        <span>{{ $stats['completed'] }}/{{ $stats['total'] }} Chapters</span>
                    </div>
                </div>
            </div>

            <!-- Mini Status Cards -->
            <div class="bg-white border border-slate-200/60 rounded-2xl p-5 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-450 uppercase block mb-1">In Progress</span>
                    <span class="text-2xl font-black text-amber-500">{{ $stats['in_progress'] }}</span>
                    <span class="text-[10px] text-slate-400 block mt-1">{{ $stats['in_progress_pct'] }}% of syllabus</span>
                </div>
                <div class="w-10 x-10 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center p-2 shrink-0">
                    <span class="material-symbols-outlined text-[24px]">trending_up</span>
                </div>
            </div>

            <div class="bg-white border border-slate-200/60 rounded-2xl p-5 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-450 uppercase block mb-1">Not Started</span>
                    <span class="text-2xl font-black text-slate-400">{{ $stats['not_started'] }}</span>
                    <span class="text-[10px] text-slate-400 block mt-1">{{ $stats['not_started_pct'] }}% of syllabus</span>
                </div>
                <div class="w-10 x-10 bg-slate-50 text-slate-400 rounded-xl flex items-center justify-center p-2 shrink-0">
                    <span class="material-symbols-outlined text-[24px]">pending</span>
                </div>
            </div>
        </div>

        <!-- Chapters List Table -->
        <div class="bg-white border border-slate-200/60 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Chapters & Syllabus Milestones</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-150 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="px-6 py-4 w-24">Chapter No.</th>
                            <th class="px-6 py-4">Title & Description</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Deadline Date</th>
                            <th class="px-6 py-4">Teacher Assigned</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($chapters as $chapter)
                            @php
                                $statusColor = match($chapter->status) {
                                    'completed' => 'bg-emerald-50 text-emerald-700 border border-emerald-150',
                                    'in_progress' => 'bg-amber-50 text-amber-700 border border-amber-150',
                                    'not_started' => 'bg-slate-100 text-slate-600 border border-slate-200',
                                    default => 'bg-slate-50 text-slate-700',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-6 py-4 font-mono font-bold text-slate-800">
                                    Ch #{{ $chapter->chapter_no }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800">{{ $chapter->title }}</div>
                                    @if($chapter->description)
                                        <div class="text-[10px] text-slate-500 mt-0.5 line-clamp-1 max-w-[400px]">{{ $chapter->description }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $statusColor }}">
                                        {{ str_replace('_', ' ', $chapter->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-mono text-slate-500">
                                    {{ $chapter->deadline_date ? $chapter->deadline_date->format('d M Y') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-750">
                                    {{ $chapter->teacher?->name ?? 'Unassigned' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-400">
                                    No syllabus chapters have been configured for this class and subject.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="py-16 text-center premium-card rounded-2xl bg-white border border-slate-200/60">
            <span class="material-symbols-outlined text-indigo-500 text-5xl mb-3 animate-float-slow" data-icon="auto_stories">auto_stories</span>
            <h4 class="text-sm font-bold text-slate-800 mb-1">Select Class and Subject</h4>
            <p class="text-xs text-slate-500 max-w-sm mx-auto leading-normal">Choose a class and subject from the dropdown filters above to audit and track syllabus progression.</p>
        </div>
    @endif
</div>
@endsection
