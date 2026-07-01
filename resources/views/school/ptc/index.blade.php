@extends('layouts.school')

@section('title', 'AuraCampus | PTC Bookings')

@section('content')
<div x-data="{ 
    cancelModal: false,
    rescheduleModal: false,
    actionUrl: '',
    bookingStudent: '',
    currentDate: '',
    currentTime: '',
    confirmCancel(url, student) {
        this.actionUrl = url;
        this.bookingStudent = student;
        this.cancelModal = true;
    },
    confirmReschedule(url, student, date, time) {
        this.actionUrl = url;
        this.bookingStudent = student;
        this.currentDate = date;
        this.currentTime = time;
        this.rescheduleModal = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight text-gradient">Parent-Teacher Conference (PTC) Bookings</h2>
            <p class="text-xs text-slate-500 mt-1">Monitor and manage parent-teacher conferences. Reschedule or cancel slots in case of teacher unavailability.</p>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-violet-50 border border-violet-200 rounded-xl text-xs font-semibold text-violet-700 flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    <!-- Filters Section -->
    <div class="bg-white rounded-2xl border border-slate-200/60 p-5 shadow-sm mb-6">
        <form method="GET" action="{{ route('school.ptc.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Date</label>
                <input type="date" name="date" value="{{ request('date') }}" class="w-full px-3 py-2 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Class</label>
                <select name="class_id" class="w-full px-3 py-2 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet appearance-none bg-white">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            Class {{ $class->name }} {{ $class->section }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Teacher</label>
                <select name="teacher_id" class="w-full px-3 py-2 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet appearance-none bg-white">
                    <option value="">All Teachers</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm text-center">
                    Filter
                </button>
                <a href="{{ route('school.ptc.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-650 text-xs font-bold rounded-xl transition-all cursor-pointer text-center flex items-center justify-center" title="Reset Filters">
                    <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                </a>
            </div>
        </form>
    </div>

    <!-- PTC bookings list -->
    <div class="bg-white border border-slate-200/60 rounded-2xl shadow-sm overflow-hidden mb-6">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 font-display-md">Scheduled PTC Sessions</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-150 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Student & Class</th>
                        <th class="px-6 py-4">Parent Details</th>
                        <th class="px-6 py-4">Teacher Name</th>
                        <th class="px-6 py-4">Scheduled Slot</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($bookings as $booking)
                        @php
                            $class = $booking->student?->studentDetail?->class;
                            $teacher = $class?->teacher;
                            $statusColor = match($booking->status) {
                                'booked' => 'bg-blue-50 text-blue-750 border border-blue-150',
                                'completed' => 'bg-emerald-50 text-emerald-750 border border-emerald-150',
                                'cancelled' => 'bg-rose-50 text-rose-750 border border-rose-150',
                                default => 'bg-slate-100 text-slate-700',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800">{{ $booking->student?->name ?? 'N/A' }}</div>
                                <div class="text-[10px] text-slate-450 mt-0.5">
                                    {{ $class ? 'Class ' . $class->name . ' ' . $class->section : 'Class N/A' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-850">{{ $booking->parent?->name ?? 'N/A' }}</div>
                                <div class="text-[10px] text-slate-450 font-mono mt-0.5">{{ $booking->parent?->phone ?? 'No Phone' }}</div>
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-750">
                                {{ $teacher?->name ?? 'No Class Teacher' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800 font-mono">{{ $booking->ptc_date->format('d M Y') }}</div>
                                <div class="text-[10px] text-slate-500 font-mono mt-0.5">{{ $booking->time_slot }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $statusColor }}">
                                    {{ $booking->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($booking->status === 'booked')
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="confirmReschedule('{{ route('school.ptc.reschedule', $booking) }}', '{{ addslashes($booking->student?->name ?? '') }}', '{{ $booking->ptc_date->toDateString() }}', '{{ $booking->time_slot }}')" class="px-2.5 py-1.5 bg-slate-50 hover:bg-violet-50 border border-slate-200 hover:border-violet-300 text-slate-650 hover:text-violet-650 rounded-lg text-[10px] font-bold transition-all cursor-pointer">
                                            Reschedule
                                        </button>
                                        <button type="button" @click="confirmCancel('{{ route('school.ptc.cancel', $booking) }}', '{{ addslashes($booking->student?->name ?? '') }}')" class="px-2.5 py-1.5 bg-slate-50 hover:bg-rose-50 border border-slate-200 hover:border-rose-300 text-slate-650 hover:text-rose-650 rounded-lg text-[10px] font-bold transition-all cursor-pointer">
                                            Cancel
                                        </button>
                                    </div>
                                @else
                                    <span class="text-[10px] text-slate-400 italic">No Actions Available</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                No PTC bookings scheduled for this criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $bookings->links() }}
    </div>

    <!-- Cancel Confirmation Modal -->
    <div x-show="cancelModal" x-cloak class="fixed inset-0 z-[110] flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="cancelModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 relative z-10 border border-slate-200/60 text-center" @click.stop>
            <div class="w-12 h-12 rounded-full bg-rose-50 border border-rose-100 flex items-center justify-center mx-auto mb-4 text-rose-600">
                <span class="material-symbols-outlined text-[24px]">cancel</span>
            </div>
            <h3 class="text-sm font-bold text-slate-950 mb-1">Cancel PTC Session</h3>
            <p class="text-xs text-slate-500 leading-relaxed px-2">
                Are you sure you want to cancel the conference booking for <strong class="text-slate-800" x-text="bookingStudent"></strong>?
            </p>
            <form method="POST" :action="actionUrl" class="mt-6 flex justify-center gap-3">
                @csrf
                <button type="button" @click="cancelModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors cursor-pointer">
                    No, Keep
                </button>
                <button type="submit" class="px-4 py-2 bg-rose-650 hover:bg-rose-705 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">
                    Yes, Cancel PTC
                </button>
            </form>
        </div>
    </div>

    <!-- Reschedule Modal -->
    <div x-show="rescheduleModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="rescheduleModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 relative z-10 border border-slate-200/60" @click.stop>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-base font-bold text-slate-900">Reschedule PTC Booking</h3>
                <button @click="rescheduleModal = false" class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            
            <p class="text-xs text-slate-500 mb-4">
                Updating the slot for <strong class="text-slate-800" x-text="bookingStudent"></strong>. The parent will be notified immediately of the new details.
            </p>

            <form method="POST" :action="actionUrl" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">New Date</label>
                    <input type="date" name="ptc_date" x-model="currentDate" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">New Time Slot</label>
                    <input type="text" name="time_slot" x-model="currentTime" placeholder="e.g. 10:00 AM - 10:15 AM" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet placeholder-slate-350">
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="rescheduleModal = false" class="px-4 py-2 text-xs font-semibold text-slate-650 hover:text-slate-800 transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">
                        Reschedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
