@extends('layouts.school')

@section('title', 'AuraCampus | Report Cards')

@section('content')
<div>
    <!-- Header -->
    <div class="mb-8">
        <h2 class="text-xl font-bold text-slate-900 tracking-tight font-sans">Student Report Cards</h2>
        <p class="text-xs text-slate-500 mt-1">Select an assessment term and classroom to view and print official report cards.</p>
    </div>

    <!-- Filters Panel -->
    <div class="premium-card p-6 bg-white rounded-2xl border border-slate-200/60 shadow-sm mb-6">
        <form method="GET" action="{{ route('school.report-cards.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <!-- Select Exam -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Assessment Term</label>
                <select name="exam_id" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald appearance-none cursor-pointer bg-white">
                    <option value="">Choose Exam Term</option>
                    @foreach($exams as $exam)
                    <option value="{{ $exam->id }}" {{ $selectedExamId == $exam->id ? 'selected' : '' }}>
                        {{ $exam->name }} ({{ str_replace('_', ' ', $exam->type) }})
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Select Class -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Classroom & Section</label>
                <select name="class_id" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald appearance-none cursor-pointer bg-white">
                    <option value="">Choose Class</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                        {{ $class->name }} - {{ $class->section }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Submit Filters -->
            <div>
                <button type="submit" class="w-full px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">filter_alt</span>
                    Fetch Student List
                </button>
            </div>
        </form>
    </div>

    <!-- Students List Table -->
    @if($selectedExamId && $selectedClassId)
    <div class="premium-card rounded-2xl overflow-hidden bg-white">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50">
            <span class="text-xs font-semibold text-slate-500">{{ $students->count() }} Students Enrolled</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Roll #</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Student Name</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Admission #</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Gender</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider text-right">Report Card</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $student)
                    <tr class="hover:bg-slate-50/50 transition-all duration-150">
                        <td class="px-6 py-4 font-mono text-xs text-slate-600">
                            {{ $student->studentDetail?->roll_number ?? '—' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-teal-50 border border-teal-100 flex items-center justify-center text-teal-600 text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($student->name, 0, 2)) }}
                                </div>
                                <span class="text-xs font-bold text-slate-800">{{ $student->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-600">
                            {{ $student->studentDetail?->admission_number ?? '—' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 text-[9px] font-bold rounded-full uppercase
                                {{ $student->studentDetail?->gender === 'male' ? 'bg-blue-50 text-blue-700 border border-blue-100' : ($student->studentDetail?->gender === 'female' ? 'bg-pink-50 text-pink-700 border border-pink-100' : 'bg-slate-50 text-slate-500 border border-slate-100') }}">
                                {{ $student->studentDetail?->gender ?? '—' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('school.report-cards.show', ['student' => $student->id, 'exam' => $selectedExamId]) }}" 
                               target="_blank"
                               class="px-2.5 py-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200/50 rounded-lg transition-all cursor-pointer inline-flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px]">description</span>
                                View Report Card
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <span class="material-symbols-outlined text-teal-600 text-4xl mb-3">groups</span>
                            <h4 class="text-sm font-bold text-slate-800 mb-1">No Students Enrolled</h4>
                            <p class="text-xs text-slate-500">There are no students enrolled in this class.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="py-16 text-center premium-card rounded-2xl bg-white border border-slate-200/60">
        <span class="material-symbols-outlined text-slate-400 text-5xl mb-3 animate-float-slow">filter_list</span>
        <h4 class="text-sm font-bold text-slate-800 mb-1">Filter Report Cards</h4>
        <p class="text-xs text-slate-500 max-w-sm mx-auto leading-normal">Choose an assessment term and class above to retrieve academic report cards.</p>
    </div>
    @endif
</div>
@endsection
