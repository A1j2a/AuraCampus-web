@extends('layouts.school')

@section('title', 'AuraCampus | Exam Schedules')

@section('content')
<div x-data="{ showModal: {{ $errors->any() ? 'true' : 'false' }} }">
    <!-- Back Navigation -->
    <a href="{{ route('school.exams') }}" class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-800 transition-colors mb-6 font-bold">
        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
        Back to Exams List
    </a>

    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <span class="text-[9px] font-mono bg-indigo-50 border border-indigo-150 text-indigo-700 px-2 py-0.5 font-bold uppercase rounded">
                {{ str_replace('_', ' ', $exam->type) }}
            </span>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight mt-2 font-sans">{{ $exam->name }}</h2>
            <p class="text-xs text-slate-500 mt-1">
                Duration: {{ \Carbon\Carbon::parse($exam->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($exam->end_date)->format('d M Y') }}
            </p>
        </div>
        <button @click="showModal = true" class="px-4 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-[16px]">schedule</span>
            Schedule Subject
        </button>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-violet-50 border border-violet-200 rounded-xl text-xs font-semibold text-violet-700 flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    <!-- Schedules List -->
    <div class="premium-card rounded-2xl overflow-hidden bg-white">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <span class="text-xs font-semibold text-slate-500">{{ $schedules->count() }} Subjects Scheduled</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Class</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Max Marks</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Passing Marks</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($schedules as $schedule)
                    <tr class="hover:bg-slate-50/50 transition-all duration-150">
                        <td class="px-6 py-4">
                            <span class="text-xs font-bold text-slate-800">
                                {{ $schedule->class ? $schedule->class->name . ' - ' . $schedule->class->section : '—' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div>
                                <span class="text-xs font-bold text-slate-800">{{ $schedule->subject?->name ?? '—' }}</span>
                                <span class="text-[9px] text-slate-400 font-mono block mt-0.5">{{ $schedule->subject?->code ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-mono">
                            <div class="text-xs text-slate-700 font-semibold">{{ \Carbon\Carbon::parse($schedule->exam_date)->format('d M Y') }}</div>
                            <div class="text-[10px] text-slate-450 mt-0.5">
                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-mono font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded">{{ $schedule->max_marks }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-mono font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded">{{ $schedule->passing_marks }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('school.marks.schedule', $schedule) }}" 
                               class="px-2.5 py-1 text-[11px] font-bold text-violet-600 hover:text-violet-700 bg-violet-50 hover:bg-violet-100 border border-violet-200/50 rounded-lg transition-all cursor-pointer inline-flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px]">edit_note</span>
                                Enter Marks
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <span class="material-symbols-outlined text-violet-600 text-4xl mb-3 animate-float-slow">schedule</span>
                            <h4 class="text-sm font-bold text-slate-800 mb-1">No Subject Scheduled</h4>
                            <p class="text-xs text-slate-500">Click "Schedule Subject" to configure the exam datesheet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Schedule Subject Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 relative z-10 border border-slate-200/60 max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-base font-bold text-slate-900">Schedule Exam Subject</h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" action="{{ route('school.exams.schedule.store', $exam) }}">
                @csrf
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Class / Section</label>
                            <select name="class_id" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet appearance-none cursor-pointer bg-white">
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }} - {{ $class->section }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Subject</label>
                            <select name="subject_id" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet appearance-none cursor-pointer bg-white">
                                <option value="">Select Subject</option>
                                @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }} ({{ $subject->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Exam Date</label>
                        <input type="date" name="exam_date" required min="{{ $exam->start_date }}" max="{{ $exam->end_date }}"
                               class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Start Time</label>
                            <input type="time" name="start_time" required
                                   class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">End Time</label>
                            <input type="time" name="end_time" required
                                   class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Maximum Marks</label>
                            <input type="number" name="max_marks" placeholder="e.g. 100" required min="1"
                                   class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet placeholder-slate-300">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Passing Marks</label>
                            <input type="number" name="passing_marks" placeholder="e.g. 33" required min="1"
                                   class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet placeholder-slate-300">
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">Schedule Paper</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
