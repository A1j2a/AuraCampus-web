@extends('layouts.school')

@section('title', 'AuraCampus | Timetable')

@section('content')
<div x-data="{
    showModal: false,
    selectedDay: '',
    selectedDayName: '',
    selectedPeriod: '',
    selectedPeriodName: '',
    startTime: '',
    endTime: '',
    selectedSubjectId: '{{ old('subject_id') }}',
    selectedTeacherId: '{{ old('teacher_id') }}',
    teachers: {{ json_encode($allTeachers) }},
    get filteredTeachers() {
        if (!this.selectedSubjectId) return [];
        var self = this;
        return this.teachers.filter(function(t) {
            return t.subject_ids.includes(parseInt(self.selectedSubjectId));
        });
    },
    openAssignModal(dayNum, dayName, periodNum, periodName, defaultStart, defaultEnd) {
        this.selectedDay = dayNum;
        this.selectedDayName = dayName;
        this.selectedPeriod = periodNum;
        this.selectedPeriodName = periodName;
        this.startTime = defaultStart;
        this.endTime = defaultEnd;
        this.selectedSubjectId = '';
        this.selectedTeacherId = '';
        this.showModal = true;
    },
    showTimingsModal: false,
    periodsList: {{ json_encode(collect($periods)->map(fn($p, $k) => array_merge($p, ['id' => $k]))->values()->toArray()) }},
    addPeriod() {
        let nextNum = this.periodsList.length + 1;
        let lastPeriod = this.periodsList[this.periodsList.length - 1];
        let defaultStart = '08:00';
        let defaultEnd = '08:45';
        if (lastPeriod && lastPeriod.end) {
            defaultStart = lastPeriod.end;
            let parts = defaultStart.split(':');
            let hours = parseInt(parts[0]);
            let minutes = parseInt(parts[1]) + 45;
            if (minutes &gt;= 60) {
                hours += Math.floor(minutes / 60);
                minutes = minutes % 60;
            }
            if (hours &gt;= 24) hours = 0;
            defaultEnd = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
        }
        this.periodsList.push({
            id: nextNum,
            name: 'Period ' + nextNum,
            start: defaultStart,
            end: defaultEnd
        });
    },
    removePeriod(index) {
        if (this.periodsList.length &gt; 1) {
            this.periodsList.splice(index, 1);
            this.periodsList.forEach(function(p, idx) {
                p.id = idx + 1;
                p.name = 'Period ' + (idx + 1);
            });
        }
    },
    deleteModal: false,
    deleteUrl: '',
    deleteItemName: '',
    confirmDelete(url, name) {
        this.deleteUrl = url;
        this.deleteItemName = name;
        this.deleteModal = true;
    }
}" x-init="$watch('selectedSubjectId', function(val) {
    if (val) {
        let ft = teachers.filter(function(t) {
            return t.subject_ids.includes(parseInt(val));
        });
        if (ft.length === 1) {
            selectedTeacherId = ft[0].id.toString();
        } else {
            selectedTeacherId = '';
        }
    } else {
        selectedTeacherId = '';
    }
})">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Timetable Management</h2>
            <p class="text-xs text-slate-500 mt-1">Assign subjects and teachers to each period. Clash detection is automatic.</p>
        </div>
        <button type="button" @click="showTimingsModal = true" class="px-4 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-[16px]">settings</span>
            Period Timings
        </button>
    </div>

    <!-- Class Selector -->
    <div class="premium-card p-6 bg-white border border-slate-200/60 shadow-sm rounded-2xl mb-6">
        <form method="GET" action="{{ route('school.timetable.index') }}" class="flex flex-col md:flex-row items-end gap-4">
            <div class="flex-grow max-w-md">
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Select Class</label>
                <select name="class_id" onchange="this.form.submit()" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-semibold focus:outline-none appearance-none cursor-pointer bg-white">
                    <option value="">Choose Classroom</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                        {{ $class->name }} - Section {{ $class->section }}
                    </option>
                    @endforeach
                </select>
            </div>
            @if($selectedClass)
            <div class="flex items-center gap-2 text-xs text-slate-500 pb-2.5">
                <span class="material-symbols-outlined text-[16px] text-violet-600">info</span>
                <span>Timetable for <span class="font-bold text-slate-800">{{ $selectedClass->name }} - {{ $selectedClass->section }}</span></span>
            </div>
            @endif
        </form>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-violet-50 border border-violet-200 rounded-xl text-xs font-semibold text-violet-700 flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-xl text-xs font-semibold text-rose-700">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if($selectedClass)

    {{-- Warnings if setup incomplete --}}
    @if($classSubjects->isEmpty())
    <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700 font-medium flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">warning</span>
        No subjects assigned to this class. <a href="{{ route('school.subjects') }}" class="font-bold underline ml-1">Assign Subjects</a>
    </div>
    @endif

    @if($classTeachers->isEmpty())
    <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700 font-medium flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">warning</span>
        No teachers assigned to this class. <a href="{{ route('school.teachers') }}" class="font-bold underline ml-1">Assign Teachers</a>
    </div>
    @endif

    <!-- Timetable Grid -->
    <div class="premium-card rounded-2xl overflow-hidden bg-white border border-slate-200/60 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-mono text-[9px] uppercase tracking-wider">
                        <th class="px-6 py-4 w-32 border-r border-slate-100">Period / Day</th>
                        @foreach($days as $dayNum => $dayName)
                        <th class="px-4 py-4 text-center">{{ $dayName }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($periods as $periodNum => $pData)
                    <tr class="hover:bg-slate-50/20 transition-all duration-150">
                        <td class="px-6 py-4 font-semibold text-slate-800 border-r border-slate-100 bg-slate-50/30">
                            <div>{{ $pData['name'] }}</div>
                            <div class="text-[9px] text-slate-400 font-mono mt-0.5">{{ $pData['start'] }} - {{ $pData['end'] }}</div>
                        </td>

                        @foreach($days as $dayNum => $dayName)
                        @php $slot = $slotsByDayAndPeriod[$dayNum][$periodNum] ?? null; @endphp
                        <td class="px-3 py-3 text-center align-middle min-w-[150px]">
                            @if($slot)
                            <div class="p-3 bg-indigo-50/50 border border-indigo-100 rounded-xl text-left relative group">
                                <button type="button" @click="confirmDelete('{{ route('school.timetable.destroy', $slot) }}', '{{ $slot->subject?->name }} ({{ $slot->teacher?->name }})')" class="absolute right-2 top-2 opacity-0 group-hover:opacity-100 transition-opacity text-slate-300 hover:text-rose-500 cursor-pointer">
                                    <span class="material-symbols-outlined text-[14px]">close</span>
                                </button>
                                <div class="font-bold text-slate-800 text-[11px] leading-tight mb-0.5">{{ $slot->subject?->name }}</div>
                                <div class="text-[9px] text-slate-400 font-mono uppercase mb-2">{{ $slot->subject?->code }}</div>
                                <div class="flex items-center gap-1 text-[9px] text-slate-500 font-medium">
                                    <span class="material-symbols-outlined text-[11px]">person</span>
                                    <span class="truncate max-w-[110px]">{{ $slot->teacher?->name }}</span>
                                </div>
                                <div class="flex items-center justify-between mt-1 text-[9px] text-slate-400 font-mono">
                                    <span>{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}</span>
                                    @if($slot->room_number)
                                    <span class="bg-indigo-100 text-indigo-700 px-1 rounded">{{ $slot->room_number }}</span>
                                    @endif
                                </div>
                            </div>
                            @else
                            <button @click="openAssignModal({{ $dayNum }}, '{{ $dayName }}', {{ $periodNum }}, '{{ $pData['name'] }}', '{{ $pData['start'] }}', '{{ $pData['end'] }}')"
                                    class="w-full py-6 border border-dashed border-slate-200 hover:border-violet-300 rounded-xl text-slate-400 hover:text-violet-600 hover:bg-violet-50/20 text-[10px] font-bold transition-all cursor-pointer flex flex-col items-center justify-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">add_circle</span>
                                Assign
                            </button>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Timetable Analytics Section -->
    <div class="mt-8 mb-6">
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Timetable Analytics & Insights</h3>
        <p class="text-[11px] text-slate-500 mt-0.5">Overview of class period coverage and teacher workload distribution.</p>
    </div>

    <!-- Analytics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Card 1: Period Coverage -->
        <div class="premium-card p-5 bg-white border border-slate-200/60 shadow-sm rounded-2xl flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-violet-50 border border-violet-100 flex items-center justify-center text-violet-600 shrink-0">
                <span class="material-symbols-outlined text-[24px]">schedule</span>
            </div>
            <div class="flex-grow">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Period Coverage</p>
                @php $totalWeeklySlots = count($periods) * 6; @endphp
                <h4 class="text-base font-bold text-slate-800 mt-0.5">
                    {{ $slots->count() }} / {{ $totalWeeklySlots }} Slots
                </h4>
                @php $percentage = $totalWeeklySlots > 0 ? round(($slots->count() / $totalWeeklySlots) * 100) : 0; @endphp
                <div class="flex items-center gap-2 mt-1.5">
                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-violet-600 h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
                    </div>
                    <span class="text-[9px] font-mono font-bold text-slate-500 shrink-0">{{ $percentage }}%</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Assigned Teachers -->
        <div class="premium-card p-5 bg-white border border-slate-200/60 shadow-sm rounded-2xl flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                <span class="material-symbols-outlined text-[24px]">groups</span>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Assigned Teachers</p>
                <h4 class="text-base font-bold text-slate-800 mt-0.5">
                    {{ $teacherStats->where('class_periods', '>', 0)->count() }} Faculty
                </h4>
                <p class="text-[9px] text-slate-400 font-medium mt-1 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    Active in this classroom weekly
                </p>
            </div>
        </div>

        <!-- Card 3: Daily Load -->
        <div class="premium-card p-5 bg-white border border-slate-200/60 shadow-sm rounded-2xl flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                <span class="material-symbols-outlined text-[24px]">bar_chart</span>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Daily Distribution</p>
                <h4 class="text-base font-bold text-slate-800 mt-0.5">
                    {{ number_format($slots->count() / 6, 1) }} Periods / Day
                </h4>
                <p class="text-[9px] text-slate-400 font-medium mt-1 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Average spread over 6 days
                </p>
            </div>
        </div>
    </div>

    <!-- Teacher Load Details Table -->
    <div class="premium-card rounded-2xl overflow-hidden bg-white border border-slate-200/60 shadow-sm mb-8">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <span class="text-xs font-semibold text-slate-600">Faculty Load & Period Allocations</span>
            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Weekly Workload Status</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-mono text-[9px] uppercase tracking-wider">
                        <th class="px-6 py-4 w-64">Teacher</th>
                        <th class="px-6 py-4">Assigned Subjects (This Class)</th>
                        <th class="px-6 py-4 text-center">Periods (This Class)</th>
                        <th class="px-6 py-4">School-Wide Load (Weekly)</th>
                        <th class="px-6 py-4 text-right">Workload Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($teacherStats as $stat)
                    <tr class="hover:bg-slate-50/20 transition-all duration-150">
                        <!-- Teacher Info -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($stat['profile_image'])
                                    <img src="{{ asset('storage/' . $stat['profile_image']) }}" class="w-8 h-8 rounded-lg object-cover border border-violet-100 shadow-sm shrink-0" alt="{{ $stat['name'] }}">
                                @else
                                    <div class="w-8 h-8 rounded-lg bg-violet-50 border border-violet-100 flex items-center justify-center text-violet-600 text-[10px] font-bold shadow-sm shrink-0">
                                        {{ strtoupper(substr($stat['name'], 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="text-xs font-bold text-slate-800 leading-none">{{ $stat['name'] }}</p>
                                    @if(count($stat['teacher_subjects']) > 0)
                                        <p class="text-[9px] text-slate-400 mt-0.5 leading-tight">
                                            {{ implode(', ', $stat['teacher_subjects']) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Subjects Taught -->
                        <td class="px-6 py-4">
                            @if(count($stat['class_subjects']) > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($stat['class_subjects'] as $subName)
                                        <span class="bg-violet-50 text-violet-700 text-[9px] font-semibold px-2 py-0.5 rounded-md border border-violet-100">
                                            {{ $subName }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                {{-- Show school-wide free periods (globally available) --}}
                                @if(count($stat['free_period_nums']) > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($stat['free_period_nums'] as $pNum)
                                            <span class="bg-emerald-50 text-emerald-600 text-[9px] font-mono font-bold px-1.5 py-0.5 rounded border border-emerald-200 border-dashed" title="Period {{ $pNum }} is free school-wide">
                                                P{{ $pNum }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-violet-500 text-[9px] font-semibold">● Fully Booked</span>
                                @endif
                            @endif
                        </td>

                        <!-- Periods count in this class -->
                        <td class="px-6 py-4 text-center font-semibold text-slate-700">
                            {{ $stat['class_periods'] }} {{ Str::plural('Period', $stat['class_periods']) }}
                        </td>

                        <!-- Workload Progress Bar -->
                        <td class="px-6 py-4">
                            @php
                                // Max load = total possible school slots per week (periods × 6 days)
                                $maxLoad = count($periods) * 6;
                                $loadVal = $stat['total_periods'];
                                $freePeriods = max(0, $maxLoad - $loadVal);
                                $barPercentage = $maxLoad > 0 ? min(100, round(($loadVal / $maxLoad) * 100)) : 0;

                                // Status based on actual school schedule
                                if ($loadVal == 0) {
                                    $barColor = 'bg-slate-300';
                                    $statusName = 'No Load';
                                    $statusBg = 'bg-slate-50 text-slate-500 border-slate-200';
                                } elseif ($freePeriods == 0) {
                                    $barColor = 'bg-violet-500';
                                    $statusName = 'Full Load';
                                    $statusBg = 'bg-violet-50 text-violet-700 border-violet-100';
                                } elseif ($barPercentage >= 75) {
                                    $barColor = 'bg-amber-500';
                                    $statusName = 'High Load';
                                    $statusBg = 'bg-amber-50 text-amber-700 border-amber-100';
                                } elseif ($barPercentage >= 40) {
                                    $barColor = 'bg-emerald-500';
                                    $statusName = 'Moderate';
                                    $statusBg = 'bg-emerald-50 text-emerald-700 border-emerald-100';
                                } else {
                                    $barColor = 'bg-sky-500';
                                    $statusName = 'Light Load';
                                    $statusBg = 'bg-sky-50 text-sky-700 border-sky-100';
                                }
                            @endphp
                            <div class="w-full max-w-[180px]">
                                <div class="flex justify-between text-[9px] font-mono font-bold mb-1">
                                    <span class="text-slate-600">{{ $loadVal }} / {{ $maxLoad }} Periods</span>
                                    <span class="text-slate-400">{{ $barPercentage }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="{{ $barColor }} h-1.5 rounded-full transition-all duration-300" style="width: {{ $barPercentage }}%"></div>
                                </div>
                                <div class="mt-0.5 text-[8px] font-semibold">
                                    @if($freePeriods == 0 && $loadVal > 0)
                                        <span class="text-violet-500">● Fully scheduled this week</span>
                                    @elseif($freePeriods > 0)
                                        <span class="text-slate-400">{{ $freePeriods }} {{ Str::plural('period', $freePeriods) }} free this week</span>
                                    @else
                                        <span class="text-slate-300">Not yet scheduled</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Workload Badge -->
                        <td class="px-6 py-4 text-right">
                            <span class="inline-flex items-center gap-1 text-[9px] font-bold px-2.5 py-0.5 rounded-full border {{ $statusBg }}">
                                <span class="w-1 h-1 rounded-full bg-current {{ $freePeriods == 0 && $loadVal > 0 ? 'animate-pulse' : '' }}"></span>
                                {{ $statusName }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-400 font-medium">
                            No statistics available. Start allocating periods to see faculty workload analytics.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @else
    <div class="py-16 text-center premium-card rounded-2xl bg-white border border-slate-200/60 shadow-sm">
        <span class="material-symbols-outlined text-slate-400 text-5xl mb-3">schedule</span>
        <h4 class="text-sm font-bold text-slate-800 mb-1">Select a Classroom</h4>
        <p class="text-xs text-slate-500 max-w-sm mx-auto">Choose a class to view or build its weekly timetable.</p>
    </div>
    @endif

    <!-- Assign Slot Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 border border-slate-200/60 max-h-[90vh] flex flex-col overflow-hidden" @click.stop>

            <div class="flex justify-between items-center p-6 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Assign Period</h3>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="selectedDayName + ' · ' + selectedPeriodName"></p>
                </div>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <form method="POST" action="{{ route('school.timetable.store') }}" class="flex-1 flex flex-col min-h-0">
                @csrf
                <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                <input type="hidden" name="day_of_week" :value="selectedDay">
                <input type="hidden" name="period_number" :value="selectedPeriod">

                <div class="p-6 space-y-4 overflow-y-auto flex-1">

                    <!-- Subject — only class assigned -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Subject
                            <span class="text-[9px] text-slate-400 font-normal ml-1">(assigned to this class only)</span>
                        </label>
                        @if($classSubjects->isNotEmpty())
                        <select name="subject_id" x-model="selectedSubjectId" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none appearance-none cursor-pointer bg-white">
                            <option value="">Choose Subject</option>
                            @foreach($classSubjects as $subject)
                            <option value="{{ $subject->id }}">
                                {{ $subject->name }} ({{ $subject->code }})
                            </option>
                            @endforeach
                        </select>
                        @else
                        <div class="px-4 py-2.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700 font-medium">
                            No subjects assigned. <a href="{{ route('school.subjects') }}" class="font-bold underline">Assign first</a>
                        </div>
                        @endif
                    </div>

                    <!-- Teacher — dynamically filtered by subject -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Teacher
                            <span class="text-[9px] text-slate-400 font-normal ml-1">(qualified to teach chosen subject)</span>
                        </label>
                        <select name="teacher_id" x-model="selectedTeacherId" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none appearance-none cursor-pointer bg-white" :disabled="!selectedSubjectId">
                            <option value="">Choose Teacher</option>
                            <template x-for="teacher in filteredTeachers" :key="teacher.id">
                                <option :value="teacher.id" x-text="teacher.name"></option>
                            </template>
                        </select>
                        <div x-show="selectedSubjectId && filteredTeachers.length === 0" class="mt-1.5 text-[10px] text-amber-600 font-semibold flex items-center gap-1">
                            <span class="material-symbols-outlined text-[12px]">warning</span>
                            No teachers qualified to teach this subject. <a href="{{ route('school.teachers') }}" class="underline font-bold">Assign qualified subjects to teachers</a>
                        </div>
                    </div>

                    <!-- Times -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Start Time</label>
                            <input type="time" name="start_time" x-model="startTime" required
                                   class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">End Time</label>
                            <input type="time" name="end_time" x-model="endTime" required
                                   class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none">
                        </div>
                    </div>

                    <!-- Room dropdown -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Room / Lab</label>
                        <select name="room_number" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none appearance-none cursor-pointer bg-white">
                            <option value="">No Room</option>
                            @php
                                $allRooms = $rooms;
                                if ($selectedClass?->room_number && !in_array($selectedClass->room_number, $allRooms)) {
                                    $allRooms[] = $selectedClass->room_number;
                                }
                            @endphp
                            @foreach($allRooms as $room)
                            <option value="{{ $room }}" {{ (old('room_number') ?: ($selectedClass?->room_number)) == $room ? 'selected' : '' }}>{{ $room }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Daily Frequency Checkbox -->
                    <div class="flex items-center gap-2 py-1">
                        <input type="checkbox" name="is_daily" id="is_daily" value="1" class="rounded text-violet-600 focus:ring-violet-500 h-4 w-4 cursor-pointer">
                        <label for="is_daily" class="text-xs font-semibold text-slate-700 cursor-pointer select-none">
                            Assign to all days of the week (Monday - Saturday)
                        </label>
                    </div>

                    <!-- Validations Info -->
                    <div class="bg-slate-50 rounded-xl p-3 space-y-1">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Auto Validations</p>
                        <p class="text-[10px] text-slate-500 flex items-center gap-1"><span class="material-symbols-outlined text-[12px] text-violet-500">check</span> Teacher clash detection</p>
                        <p class="text-[10px] text-slate-500 flex items-center gap-1"><span class="material-symbols-outlined text-[12px] text-violet-500">check</span> Same subject twice on same day</p>
                        <p class="text-[10px] text-slate-500 flex items-center gap-1"><span class="material-symbols-outlined text-[12px] text-violet-500">check</span> Room double booking</p>
                    </div>

                </div>

                <div class="p-6 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-3 rounded-b-2xl">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl cursor-pointer shadow-sm">Allocate Period</button>
                </div>
            </form>
        </div>
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
            <h3 class="text-sm font-bold text-slate-950 mb-1">Delete Period Allocation</h3>
            <p class="text-xs text-slate-500 leading-relaxed px-2">
                Are you sure you want to delete the schedule entry for <strong class="text-slate-800" x-text="deleteItemName"></strong>? This slot will be freed in the timetable.
            </p>
            <div class="mt-6 flex justify-center gap-3">
                <button type="button" @click="deleteModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="button" @click="document.getElementById('global-delete-form').submit()" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-md shadow-rose-600/10">
                    Confirm Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Configure Period Timings Modal -->
    <div x-show="showTimingsModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="showTimingsModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg relative z-10 border border-slate-200/60 max-h-[90vh] flex flex-col overflow-hidden" @click.stop>

            <div class="flex justify-between items-center p-6 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Configure Period Timings</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Set the default start and end times for each period session.</p>
                </div>
                <button @click="showTimingsModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <form method="POST" action="{{ route('school.timetable.periods.update') }}" class="flex-1 flex flex-col min-h-0">
                @csrf
                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <template x-for="(p, index) in periodsList" :key="index">
                        <div class="premium-card p-4 bg-slate-50/50 border border-slate-200/40 rounded-xl space-y-3 relative group">
                            <!-- Remove button -->
                            <button type="button" @click="removePeriod(index)" x-show="periodsList.length > 1"
                                    class="absolute right-3 top-3 text-slate-300 hover:text-rose-500 transition-colors cursor-pointer flex items-center justify-center p-1 rounded-lg hover:bg-rose-50">
                                <span class="material-symbols-outlined text-[16px]">delete</span>
                            </button>

                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-700" x-text="p.name"></span>
                                <input type="hidden" :name="'periods[' + p.id + '][name]'" :value="p.name">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Start Time</label>
                                    <input type="time" :name="'periods[' + p.id + '][start]'" x-model="p.start" required
                                           class="w-full px-3 py-2 premium-input rounded-lg text-xs font-medium focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">End Time</label>
                                    <input type="time" :name="'periods[' + p.id + '][end]'" x-model="p.end" required
                                           class="w-full px-3 py-2 premium-input rounded-lg text-xs font-medium focus:outline-none">
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Add Period Button -->
                    <button type="button" @click="addPeriod()" x-show="periodsList.length < 12"
                            class="w-full py-4 border border-dashed border-violet-200 hover:border-violet-300 rounded-xl text-violet-600 hover:bg-violet-50/30 text-xs font-bold transition-all cursor-pointer flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">add</span>
                        Add Period
                    </button>
                </div>

                <div class="p-6 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-3 rounded-b-2xl">
                    <button type="button" @click="showTimingsModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl cursor-pointer shadow-sm">Save Timings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
