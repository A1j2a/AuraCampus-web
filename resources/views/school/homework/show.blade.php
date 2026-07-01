@extends('layouts.school')

@section('title', 'AuraCampus | Homework Submissions')

@section('content')
<div>
    <!-- Back Header -->
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('school.homework.index') }}" class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition-colors text-slate-600">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        </a>
        <div>
            <span class="text-[10px] font-bold text-violet-650 uppercase font-mono">Homework Details & Submissions</span>
            <h2 class="text-lg font-bold text-slate-900 leading-tight">{{ $homework->title }}</h2>
        </div>
    </div>

    <!-- Homework Summary Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Main details -->
        <div class="bg-white border border-slate-200/60 rounded-2xl p-6 shadow-sm lg:col-span-2 space-y-4">
            <div>
                <h3 class="text-xs font-semibold text-slate-450 uppercase tracking-wider mb-2">Description</h3>
                <p class="text-xs text-slate-700 leading-relaxed whitespace-pre-wrap">{{ $homework->description }}</p>
            </div>

            @if($homework->attachments && count($homework->attachments) > 0)
            <div>
                <h3 class="text-xs font-semibold text-slate-450 uppercase tracking-wider mb-2">Attachments</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($homework->attachments as $attachment)
                        @php
                            $fileName = $attachment['name'] ?? 'File';
                            $fileUrl = $attachment['url'] ?? '#';
                        @endphp
                        <a href="{{ $fileUrl }}" target="_blank" class="flex items-center gap-2 px-3 py-1.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg text-xs font-medium text-slate-700 transition-colors">
                            <span class="material-symbols-outlined text-[16px] text-violet-600">description</span>
                            <span class="max-w-[150px] truncate">{{ $fileName }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Meta info box -->
        <div class="bg-white border border-slate-200/60 rounded-2xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-slate-900 border-b border-slate-100 pb-2">Academic Information</h3>
            
            <div class="grid grid-cols-2 gap-y-4 gap-x-2 text-xs">
                <div>
                    <span class="text-slate-400 block mb-0.5">Class / Section</span>
                    <span class="font-bold text-slate-800">Class {{ $homework->class?->name ?? 'N/A' }} {{ $homework->class?->section ?? '' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Subject</span>
                    <span class="font-bold text-slate-800">{{ $homework->subject?->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Created By</span>
                    <span class="font-bold text-slate-800">{{ $homework->teacher?->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Due Date</span>
                    <span class="font-bold text-slate-850 font-mono">{{ $homework->due_date ? $homework->due_date->format('d M Y') : 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Max Marks</span>
                    <span class="font-bold text-slate-800">{{ $homework->max_marks ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Status</span>
                    <span class="px-2 py-0.5 rounded font-bold uppercase text-[9px] {{ $homework->status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-650' }}">
                        {{ $homework->status }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Submissions Table -->
    <div class="bg-white border border-slate-200/60 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Student Submissions Log</h3>
            <span class="text-xs text-slate-450 font-medium">Total Class Strength: {{ count($students) }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-150 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Student Details</th>
                        <th class="px-6 py-4">Submission Status</th>
                        <th class="px-6 py-4">Submitted At</th>
                        <th class="px-6 py-4">Grade / Score</th>
                        <th class="px-6 py-4">Attachments & Feedback</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($students as $student)
                        @php
                            $sub = $submissions->get($student->id);
                            $statusColor = 'text-slate-500 bg-slate-100';
                            $statusText = 'Pending';
                            
                            if ($sub) {
                                $statusText = match($sub->status) {
                                    'submitted' => 'Submitted',
                                    'approved' => 'Approved',
                                    'revision_requested' => 'Revision Requested',
                                    'late' => 'Submitted Late',
                                    default => ucfirst($sub->status),
                                };
                                $statusColor = match($sub->status) {
                                    'submitted' => 'bg-blue-50 text-blue-700 border border-blue-150',
                                    'approved' => 'bg-emerald-50 text-emerald-700 border border-emerald-150',
                                    'revision_requested' => 'bg-amber-50 text-amber-700 border border-amber-150',
                                    'late' => 'bg-rose-50 text-rose-700 border border-rose-150',
                                    default => 'bg-slate-100 text-slate-700',
                                };
                            }
                        @endphp
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($student->profile_image)
                                        <img src="{{ url('storage/' . $student->profile_image) }}" class="w-8 h-8 rounded-full object-cover shrink-0" alt="">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-bold shrink-0">
                                            {{ strtoupper(substr($student->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-bold text-slate-800">{{ $student->name }}</div>
                                        <div class="text-[10px] text-slate-450">Roll No: {{ $student->studentDetail?->roll_number ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $statusColor }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 font-mono">
                                {{ $sub && $sub->submitted_at ? $sub->submitted_at->format('d M Y, h:i A') : '--' }}
                            </td>
                            <td class="px-6 py-4 font-semibold">
                                @if($sub && $sub->grade)
                                    <span class="text-violet-650 font-bold">{{ $sub->grade }}</span>
                                @else
                                    <span class="text-slate-400">Not Graded</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($sub)
                                    @if($sub->files && count($sub->files) > 0)
                                        <div class="flex flex-col gap-1.5 mb-2">
                                            @foreach($sub->files as $file)
                                                @php
                                                    $fName = $file['name'] ?? 'File';
                                                    $fUrl = $file['url'] ?? '#';
                                                @endphp
                                                <a href="{{ $fUrl }}" target="_blank" class="flex items-center gap-1.5 text-violet-600 hover:text-violet-850 font-semibold truncate max-w-[180px]">
                                                    <span class="material-symbols-outlined text-[14px]">attachment</span>
                                                    {{ $fName }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if($sub->feedback)
                                        <div class="text-[10px] text-slate-500 bg-slate-50 p-2 rounded-lg border border-slate-100 italic">
                                            "{{ $sub->feedback }}"
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-[10px] italic">No feedback provided</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">--</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                No students found in this class.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
