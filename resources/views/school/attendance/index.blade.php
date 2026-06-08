@extends('layouts.school')

@section('title', 'AuraCampus | Attendance')

@section('content')
<div>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Attendance Roster</h2>
            <p class="text-xs text-slate-500 mt-1">Mark daily attendance for each class section.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-700 flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    <!-- Class & Date Selector -->
    <div class="premium-card p-5 rounded-2xl mb-6">
        <form method="GET" action="{{ route('school.attendance') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Select Class</label>
                <select name="class_id" onchange="this.form.submit()" class="px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald appearance-none cursor-pointer bg-white min-w-[180px]">
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                        {{ $class->name }} - Section {{ $class->section }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Date</label>
                <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()"
                       class="px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald">
            </div>
        </form>
    </div>

    <!-- Attendance Grid -->
    @if($students->count() > 0)
    <form method="POST" action="{{ route('school.attendance.store') }}">
        @csrf
        <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
        <input type="hidden" name="date" value="{{ $selectedDate }}">

        <div class="premium-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-500">{{ $students->count() }} Students</span>
                    <button type="button" onclick="exportTableToCSV('attendance-roster.csv')" class="px-2 py-0.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-[9px] font-bold rounded cursor-pointer transition-all flex items-center gap-0.5">
                        <span class="material-symbols-outlined text-[11px]">download</span>
                        Export CSV
                    </button>
                </div>
                <span class="text-[10px] font-mono text-slate-400">{{ \Carbon\Carbon::parse($selectedDate)->format('l, d M Y') }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Roll</th>
                            <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider text-center">Present</th>
                            <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider text-center">Absent</th>
                            <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider text-center">Late</th>
                            <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider text-center">Excused</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($students as $student)
                        @php
                            $existingStatus = $attendanceMap[$student->user_id]['status'] ?? 'present';
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-all duration-150">
                            <td class="px-6 py-3">
                                <span class="text-xs font-mono font-bold text-slate-500">{{ $student->roll_number ?? '—' }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-[10px] font-bold shrink-0">
                                        {{ strtoupper(substr($student->user->name, 0, 2)) }}
                                    </div>
                                    <span class="text-xs font-semibold text-slate-800">{{ $student->user->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <input type="radio" name="attendance[{{ $student->user_id }}]" value="present" {{ $existingStatus === 'present' ? 'checked' : '' }}
                                       class="w-4 h-4 text-emerald-600 border-slate-300 focus:ring-emerald-500 cursor-pointer">
                            </td>
                            <td class="px-6 py-3 text-center">
                                <input type="radio" name="attendance[{{ $student->user_id }}]" value="absent" {{ $existingStatus === 'absent' ? 'checked' : '' }}
                                       class="w-4 h-4 text-rose-600 border-slate-300 focus:ring-rose-500 cursor-pointer">
                            </td>
                            <td class="px-6 py-3 text-center">
                                <input type="radio" name="attendance[{{ $student->user_id }}]" value="late" {{ $existingStatus === 'late' ? 'checked' : '' }}
                                       class="w-4 h-4 text-amber-600 border-slate-300 focus:ring-amber-500 cursor-pointer">
                            </td>
                            <td class="px-6 py-3 text-center">
                                <input type="radio" name="attendance[{{ $student->user_id }}]" value="excused" {{ $existingStatus === 'excused' ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500 cursor-pointer">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">save</span>
                    Save Attendance
                </button>
            </div>
        </div>
    </form>
    @else
    <div class="premium-card p-12 rounded-2xl text-center">
        <span class="material-symbols-outlined text-emerald-600 text-4xl mb-3 animate-float-slow">calendar_month</span>
        <h4 class="text-sm font-bold text-slate-800 mb-1">No Students in This Class</h4>
        <p class="text-xs text-slate-500">Register students to this class before marking attendance.</p>
    </div>
    @endif
</div>
@endsection
