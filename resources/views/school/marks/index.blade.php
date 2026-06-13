@extends('layouts.school')

@section('title', 'AuraCampus | Enter Marks')

@section('content')
<div>
    <!-- Back to Exam Details -->
    <a href="{{ route('school.exams.show', $examSchedule->exam_id) }}" class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-800 transition-colors mb-6 font-bold">
        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
        Back to {{ $examSchedule->exam->name }}
    </a>

    <!-- Header info -->
    <div class="mb-8">
        <h2 class="text-xl font-bold text-slate-900 tracking-tight font-sans">Enter Marks & Grading</h2>
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 mt-2">
            <span class="text-xs text-slate-500 font-bold">
                Class: <span class="text-slate-900">{{ $examSchedule->class->name }} - {{ $examSchedule->class->section }}</span>
            </span>
            <span class="h-3.5 w-[1px] bg-slate-200"></span>
            <span class="text-xs text-slate-500 font-bold">
                Subject: <span class="text-slate-900">{{ $examSchedule->subject->name }} ({{ $examSchedule->subject->code }})</span>
            </span>
            <span class="h-3.5 w-[1px] bg-slate-200"></span>
            <span class="text-xs text-slate-500 font-bold">
                Max Marks: <span class="text-slate-900 font-mono">{{ $examSchedule->max_marks }}</span>
            </span>
            <span class="h-3.5 w-[1px] bg-slate-200"></span>
            <span class="text-xs text-slate-500 font-bold">
                Passing Marks: <span class="text-slate-900 font-mono text-rose-600">{{ $examSchedule->passing_marks }}</span>
            </span>
        </div>
    </div>

    <!-- Validation Error Alert -->
    @if($errors->any())
    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-xl text-xs font-semibold text-rose-700">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Form for Bulk Marks Entry -->
    <form method="POST" action="{{ route('school.marks.schedule.store') }}">
        @csrf
        <input type="hidden" name="exam_schedule_id" value="{{ $examSchedule->id }}">

        <div class="premium-card rounded-2xl overflow-hidden bg-white mb-6">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <span class="text-xs font-semibold text-slate-500">{{ $students->count() }} Students Registered</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Student Name</th>
                            <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Admission #</th>
                            <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Roll #</th>
                            <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider w-36">Marks Obtained</th>
                            <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider w-24">Grade</th>
                            <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($students as $student)
                        @php
                            $studentMark = $marks->get($student->id);
                            $obtainedValue = $studentMark ? $studentMark->marks_obtained : '';
                            $gradeValue = $studentMark ? $studentMark->grade : '—';
                            $remarksValue = $studentMark ? $studentMark->remarks : '';
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-all duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-bold shrink-0">
                                        {{ strtoupper(substr($student->name, 0, 2)) }}
                                    </div>
                                    <span class="text-xs font-bold text-slate-800">{{ $student->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-mono font-bold text-slate-500 bg-slate-50 px-2 py-0.5 border border-slate-150 rounded">
                                    {{ $student->studentDetail?->admission_number ?? '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-600">
                                {{ $student->studentDetail?->roll_number ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="relative flex items-center">
                                    <input type="number" 
                                           name="marks[{{ $student->id }}][marks_obtained]" 
                                           value="{{ old('marks.'.$student->id.'.marks_obtained', $obtainedValue) }}"
                                           placeholder="e.g. 85" 
                                           step="0.01"
                                           min="0"
                                           max="{{ $examSchedule->max_marks }}"
                                           class="w-28 px-3 py-1.5 premium-input rounded-lg text-xs font-bold text-slate-800 focus:outline-none focus:premium-input-focus-violet placeholder-slate-350 pr-8">
                                    <span class="absolute right-3.5 text-[10px] text-slate-400 font-mono">/{{ $examSchedule->max_marks }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 text-xs font-mono font-bold rounded-lg
                                    {{ $gradeValue === 'F' ? 'bg-rose-50 text-rose-700 border border-rose-100' : ($gradeValue !== '—' ? 'bg-violet-50 text-violet-700 border border-violet-100' : 'text-slate-400') }}">
                                    {{ $gradeValue }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <input type="text" 
                                       name="marks[{{ $student->id }}][remarks]" 
                                       value="{{ old('marks.'.$student->id.'.remarks', $remarksValue) }}"
                                       placeholder="Outstanding, Needs Improvement..." 
                                       class="w-full px-3 py-1.5 premium-input rounded-lg text-xs font-medium focus:outline-none focus:premium-input-focus-violet placeholder-slate-300">
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <span class="material-symbols-outlined text-violet-600 text-4xl mb-3">groups</span>
                                <h4 class="text-sm font-bold text-slate-800 mb-1">No Students Found</h4>
                                <p class="text-xs text-slate-500">There are no students enrolled in this class to enter marks.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Submit Actions -->
        @if($students->isNotEmpty())
        <div class="flex justify-end gap-3">
            <a href="{{ route('school.exams.show', $examSchedule->exam_id) }}" class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-800 transition-colors">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">
                Save All Grades
            </button>
        </div>
        @endif
    </form>
</div>
@endsection
