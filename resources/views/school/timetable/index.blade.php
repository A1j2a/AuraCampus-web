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
    
    openAssignModal(dayNum, dayName, periodNum, periodName, defaultStart, defaultEnd) {
        this.selectedDay = dayNum;
        this.selectedDayName = dayName;
        this.selectedPeriod = periodNum;
        this.selectedPeriodName = periodName;
        this.startTime = defaultStart;
        this.endTime = defaultEnd;
        this.showModal = true;
    }
}">
    <!-- Header -->
    <div class="mb-8">
        <h2 class="text-xl font-bold text-slate-900 tracking-tight font-sans">Timetable Management</h2>
        <p class="text-xs text-slate-500 mt-1">Design weekly classroom agendas, assign subject teachers, and manage period schedules.</p>
    </div>

    <!-- Class Selection and Info -->
    <div class="premium-card p-6 bg-white border border-slate-200/60 shadow-sm rounded-2xl mb-6">
        <form method="GET" action="{{ route('school.timetable.index') }}" class="flex flex-col md:flex-row items-end gap-4">
            <div class="flex-grow max-w-md">
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Classroom & Section</label>
                <select name="class_id" required onchange="this.form.submit()" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-semibold focus:outline-none focus:premium-input-focus-emerald appearance-none cursor-pointer bg-white">
                    <option value="">Select Classroom</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                        {{ $class->name }} - {{ $class->section }}
                    </option>
                    @endforeach
                </select>
            </div>
            @if($selectedClass)
            <div class="flex items-center gap-2 text-xs text-slate-500 pb-2.5">
                <span class="material-symbols-outlined text-[16px] text-emerald-600">info</span>
                <span>Configuring timetable for <span class="font-bold text-slate-800">{{ $selectedClass->name }} - {{ $selectedClass->section }}</span></span>
            </div>
            @endif
        </form>
    </div>

    <!-- Alerts -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-700 flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-xl text-xs font-semibold text-rose-750">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if($selectedClass)
    <!-- Timetable Weekly Grid -->
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
                        <!-- Period Label -->
                        <td class="px-6 py-4 font-semibold text-slate-800 border-r border-slate-100 bg-slate-50/30">
                            <div>{{ $pData['name'] }}</div>
                            <div class="text-[9px] text-slate-400 font-mono mt-0.5">{{ $pData['start'] }} - {{ $pData['end'] }}</div>
                        </td>

                        <!-- Weekly slots -->
                        @foreach($days as $dayNum => $dayName)
                        @php
                            $slot = $slotsByDayAndPeriod[$dayNum][$periodNum] ?? null;
                        @endphp
                        <td class="px-3 py-3 text-center align-middle min-w-[150px]">
                            @if($slot)
                            <!-- Occupied Slot -->
                            <div class="p-3 bg-indigo-50/50 border border-indigo-150 rounded-xl text-left relative group">
                                <!-- Delete Action -->
                                <form method="POST" action="{{ route('school.timetable.destroy', $slot) }}" class="absolute right-2 top-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-rose-500 p-0.5 rounded cursor-pointer" title="Remove slot">
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
                            <!-- Empty slot -->
                            <button @click="openAssignModal({{ $dayNum }}, '{{ $dayName }}', {{ $periodNum }}, '{{ $pData['name'] }}', '{{ $pData['start'] }}', '{{ $pData['end'] }}')" 
                                    class="w-full py-6 border border-dashed border-slate-200 hover:border-emerald-300 rounded-xl text-slate-400 hover:text-emerald-600 hover:bg-emerald-50/20 text-[10px] font-bold transition-all cursor-pointer flex flex-col items-center justify-center gap-1">
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
        <span class="material-symbols-outlined text-slate-400 text-5xl mb-3 animate-float-slow">schedule</span>
        <h4 class="text-sm font-bold text-slate-800 mb-1">Select Classroom</h4>
        <p class="text-xs text-slate-500 max-w-sm mx-auto leading-normal">Choose a class section from the dropdown list to view or construct their weekly timetable schedules.</p>
    </div>
    @endif

    <!-- Assign Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 relative z-10 border border-slate-200/60 max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Assign Timetable Period</h3>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="selectedDayName + ' · ' + selectedPeriodName"></p>
                </div>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            
            <form method="POST" action="{{ route('school.timetable.store') }}">
                @csrf
                <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                <input type="hidden" name="day_of_week" :value="selectedDay">
                <input type="hidden" name="period_number" :value="selectedPeriod">

                <div class="space-y-4">
                    <!-- Subject selection -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Select Subject</label>
                        <select name="subject_id" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald appearance-none cursor-pointer bg-white">
                            <option value="">Choose Subject</option>
                            @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }} ({{ $subject->code }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Teacher selection -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Select Teacher</label>
                        <select name="teacher_id" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald appearance-none cursor-pointer bg-white">
                            <option value="">Choose Teacher</option>
                            @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Times -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Start Time</label>
                            <input type="time" name="start_time" x-model="startTime" required
                                   class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">End Time</label>
                            <input type="time" name="end_time" x-model="endTime" required
                                   class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald">
                        </div>
                    </div>

                    <!-- Room number -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Room / Lab Number</label>
                        <input type="text" name="room_number" value="{{ old('room_number') }}" placeholder="e.g. Lab 3, Room 102"
                               class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald placeholder-slate-300">
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">Allocate Period</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
