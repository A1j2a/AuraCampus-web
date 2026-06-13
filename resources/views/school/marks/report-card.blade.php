<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Card - {{ $student->name }} - {{ $exam->name }}</title>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <!-- TailwindCSS for styling -->
    @vite(['resources/css/app.css'])
    
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
            }
            .report-card-container {
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-800 p-4 md:p-8">

    <!-- Top Action bar (no-print) -->
    <div class="max-w-4xl mx-auto mb-6 flex justify-between items-center no-print">
        <button onclick="window.close()" class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-800 transition-colors font-bold cursor-pointer">
            <span class="material-symbols-outlined text-[16px]">close</span>
            Close Window
        </button>
        <button onclick="window.print()" class="px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-[16px]">print</span>
            Print Report Card
        </button>
    </div>

    <!-- Printable Report Card Page -->
    <div class="max-w-4xl mx-auto bg-white border border-slate-200 shadow-md rounded-2xl p-8 md:p-12 report-card-container">
        
        <!-- School Information Header -->
        <div class="flex flex-col md:flex-row justify-between items-center border-b-2 border-slate-900 pb-6 mb-8 text-center md:text-left gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-slate-900 flex items-center justify-center text-white text-2xl font-black shadow-sm shrink-0">
                    {{ strtoupper(substr($exam->school->name, 0, 2)) }}
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-900 tracking-tight uppercase">{{ $exam->school->name }}</h1>
                    <p class="text-[10px] text-slate-500 font-medium mt-1 leading-normal max-w-md">
                        {{ $exam->school->address ?? 'School Address Not Configured' }}
                    </p>
                </div>
            </div>
            <div class="text-center md:text-right font-mono text-[10px] text-slate-500 leading-normal">
                <div>Email: {{ $exam->school->email ?? 'info@school.com' }}</div>
                <div>Phone: {{ $exam->school->phone ?? '+91-XXXXXXXXXX' }}</div>
                <div class="mt-1 text-slate-800 font-bold">Academic Session: {{ $exam->academicSession->name }}</div>
            </div>
        </div>

        <div class="text-center mb-8">
            <h2 class="text-base font-black tracking-widest text-slate-900 uppercase underline decoration-2 decoration-violet-500 underline-offset-4">
                {{ $exam->name }} Progress Report
            </h2>
        </div>

        <!-- Student Details Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-y-4 gap-x-6 bg-slate-50 border border-slate-200 rounded-xl p-6 mb-8 text-xs">
            <div>
                <span class="block text-slate-400 font-mono text-[9px] uppercase tracking-wider">Student Name</span>
                <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ $student->name }}</span>
            </div>
            <div>
                <span class="block text-slate-400 font-mono text-[9px] uppercase tracking-wider">Admission Number</span>
                <span class="font-bold font-mono text-slate-800 mt-0.5 block">{{ $studentDetail?->admission_number ?? '—' }}</span>
            </div>
            <div>
                <span class="block text-slate-400 font-mono text-[9px] uppercase tracking-wider">Roll Number</span>
                <span class="font-bold font-mono text-slate-800 mt-0.5 block">{{ $studentDetail?->roll_number ?? '—' }}</span>
            </div>
            <div>
                <span class="block text-slate-400 font-mono text-[9px] uppercase tracking-wider">Class & Section</span>
                <span class="font-bold text-slate-800 mt-0.5 block">
                    {{ $class ? $class->name . ' - ' . $class->section : '—' }}
                </span>
            </div>
        </div>

        <!-- Marks Sheet Table -->
        <div class="border border-slate-250 rounded-xl overflow-x-auto mb-8">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-900 text-white border-b border-slate-900 font-mono text-[9px] uppercase tracking-wider">
                        <th class="px-6 py-3.5">Subject & Code</th>
                        <th class="px-6 py-3.5 text-center">Max Marks</th>
                        <th class="px-6 py-3.5 text-center">Passing Marks</th>
                        <th class="px-6 py-3.5 text-center">Marks Obtained</th>
                        <th class="px-6 py-3.5 text-center">Grade</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($marks as $mark)
                    @php
                        $isPassed = $mark->marks_obtained >= $mark->examSchedule->passing_marks;
                    @endphp
                    <tr>
                        <td class="px-6 py-4">
                            <span class="font-bold text-slate-900">{{ $mark->examSchedule->subject?->name }}</span>
                            <span class="block text-[9px] text-slate-400 font-mono mt-0.5">{{ $mark->examSchedule->subject?->code }}</span>
                        </td>
                        <td class="px-6 py-4 text-center font-mono font-medium text-slate-600">{{ $mark->examSchedule->max_marks }}</td>
                        <td class="px-6 py-4 text-center font-mono font-medium text-slate-600">{{ $mark->examSchedule->passing_marks }}</td>
                        <td class="px-6 py-4 text-center font-mono font-bold text-slate-900">{{ $mark->marks_obtained }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-mono font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded">{{ $mark->grade }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($isPassed)
                            <span class="text-[9px] font-bold text-violet-700 bg-violet-50 border border-violet-150 px-2 py-0.5 rounded font-mono uppercase">Pass</span>
                            @else
                            <span class="text-[9px] font-bold text-rose-700 bg-rose-50 border border-rose-150 px-2 py-0.5 rounded font-mono uppercase">Fail</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-500 italic">{{ $mark->remarks ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-400 italic">No grading marks recorded for this assessment.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Grade Summary Metrics -->
        @if(count($marks) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12 items-start">
            <!-- Grading scale reference -->
            <div class="border border-slate-150 rounded-xl p-4 bg-slate-50/50 text-[10px] text-slate-500">
                <span class="block font-bold text-slate-700 mb-2 uppercase font-mono tracking-wider">Evaluation Key</span>
                <div class="grid grid-cols-4 gap-2 text-center font-mono">
                    <div class="p-1 bg-white border rounded"><span class="font-bold text-slate-800">A+</span> 90-100%</div>
                    <div class="p-1 bg-white border rounded"><span class="font-bold text-slate-800">A</span> 80-89%</div>
                    <div class="p-1 bg-white border rounded"><span class="font-bold text-slate-800">B+</span> 70-79%</div>
                    <div class="p-1 bg-white border rounded"><span class="font-bold text-slate-800">B</span> 60-69%</div>
                    <div class="p-1 bg-white border rounded"><span class="font-bold text-slate-800">C+</span> 50-59%</div>
                    <div class="p-1 bg-white border rounded"><span class="font-bold text-slate-800">C</span> 40-49%</div>
                    <div class="p-1 bg-white border rounded"><span class="font-bold text-slate-800">D</span> 33-39%</div>
                    <div class="p-1 bg-white border rounded"><span class="font-bold text-rose-700">F</span> &lt; 33%</div>
                </div>
            </div>

            <!-- Grade totals card -->
            <div class="bg-slate-900 text-white rounded-xl p-6 text-xs space-y-3 shadow-inner">
                <div class="flex justify-between items-center">
                    <span class="text-slate-400 font-mono text-[9px] uppercase tracking-wider">Aggregate Score</span>
                    <span class="font-bold font-mono text-sm">{{ $totalObtained }} / {{ $totalMax }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-400 font-mono text-[9px] uppercase tracking-wider">Overall Percentage</span>
                    <span class="font-bold font-mono text-sm text-violet-400">{{ $overallPercentage }}%</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-400 font-mono text-[9px] uppercase tracking-wider">Overall Grade</span>
                    <span class="font-bold font-mono text-sm px-2 py-0.5 bg-slate-850 rounded text-violet-400">{{ $overallGrade }}</span>
                </div>
                <div class="border-t border-slate-800 pt-3 flex justify-between items-center">
                    <span class="text-slate-400 font-mono text-[9px] uppercase tracking-wider">Result status</span>
                    <span class="font-black font-mono text-sm tracking-wider {{ $resultStatus === 'PASS' ? 'text-violet-400' : 'text-rose-450' }}">
                        {{ $resultStatus }}
                    </span>
                </div>
            </div>
        </div>
        @endif

        <!-- Signatures and Stamp Section -->
        <div class="grid grid-cols-2 gap-12 text-center text-xs mt-16 pt-8 border-t border-slate-100">
            <div>
                <div class="h-12 flex items-end justify-center">
                    <!-- Placeholder for signature line -->
                    <div class="w-32 border-b border-slate-350"></div>
                </div>
                <span class="block font-bold text-slate-800 mt-2">Class Teacher</span>
                <span class="text-[9px] text-slate-400 font-mono">Date: ____________</span>
            </div>
            <div>
                <div class="h-12 flex items-end justify-center">
                    <!-- Placeholder for signature line -->
                    <div class="w-32 border-b border-slate-350"></div>
                </div>
                <span class="block font-bold text-slate-800 mt-2">Principal</span>
                <span class="text-[9px] text-slate-400 font-mono">School Stamp & Seal</span>
            </div>
        </div>

    </div>
</body>
</html>
