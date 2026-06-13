@extends('layouts.school')

@section('title', 'AuraCampus | Timetable')

@section('content')
<div x-data="{
    showModal: {{ $errors->any() ? 'true' : 'false' }},
    selectedDay: '',
    selectedDayName: '',
    selectedPeriod: '',
    selectedPeriodName: '',
    startTime: '',
    endTime: '',
    selectedSubjectId: '{{ old('subject_id') }}',
    selectedTeacherId: '{{ old('teacher_id') }}',
    teachers: @js($allTeachers),
    get filteredTeachers() {
        if (!this.selectedSubjectId) return [];
        return this.teachers.filter(t => t.subject_ids.includes(parseInt(this.selectedSubjectId)));
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
    }
}">
    <!-- Header -->
    <div class="mb-8">
        <h2 class="text-xl font-bold text-slate-900 tracking-tight">Timetable Management</h2>
        <p class="text-xs text-slate-500 mt-1">Assign subjects and teachers to each period. Clash detection is automatic.</p>
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
                                <form method="POST" action="{{ route('school.timetable.destroy', $slot) }}" class="absolute right-2 top-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-300 hover:text-rose-500 cursor-pointer">
                                        <span class="material-symbols-outlined text-[14px]">close</span>
                                    </button>
                                </form>
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
                            @foreach($rooms as $room)
                            <option value="{{ $room }}" {{ old('room_number') == $room ? 'selected' : '' }}>{{ $room }}</option>
                            @endforeach
                        </select>
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

</div>
@endsection
