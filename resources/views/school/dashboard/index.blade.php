@extends('layouts.school')

@section('title', 'AuraCampus | School Admin Dashboard')

@section('content')
    <!-- Welcome Banner -->
    <section class="mb-10 relative overflow-hidden bg-gradient-to-r from-[#5f40dc] to-[#8062f6] rounded-2xl p-8 text-white">
        <div class="relative z-10">
            <h2 class="text-2xl lg:text-3xl font-bold mb-2">Good Morning, Principal.</h2>
            <p class="text-sm lg:text-base text-white/80">Here is your school overview for today. You have {{ $pendingLeavesCount }} pending leave request{{ $pendingLeavesCount !== 1 ? 's' : '' }}.</p>
        </div>
        <!-- Decorative background shapes -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 right-32 w-32 h-32 bg-white/5 rounded-full translate-y-1/2"></div>
    </section>

    <!-- Summary Cards -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Students Card -->
        <div class="bg-white p-6 rounded-2xl soft-card-shadow border border-slate-100 hover:-translate-y-1 transition-transform duration-300 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-4">
                    <div class="w-10 h-10 rounded-xl bg-[#f0ecfe] text-[#5f40dc] flex items-center justify-center">
                        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">group</span>
                    </div>
                    <span class="text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded text-xs font-semibold">+2.4%</span>
                </div>
                <p class="text-slate-400 font-bold uppercase tracking-wider text-[10px] mt-4">Total Students</p>
                <h3 class="text-2xl font-black text-slate-800 mt-1">{{ number_format($totalStudents) }}</h3>
            </div>
            <div class="w-12 h-1 bg-[#5f40dc] rounded-full mt-4"></div>
        </div>

        <!-- Classes Card -->
        <div class="bg-white p-6 rounded-2xl soft-card-shadow border border-slate-100 hover:-translate-y-1 transition-transform duration-300 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-4">
                    <div class="w-10 h-10 rounded-xl bg-[#fef3c7] text-[#d97706] flex items-center justify-center">
                        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">class</span>
                    </div>
                    <span class="text-blue-600 bg-blue-50 px-2 py-0.5 rounded text-xs font-semibold">Active</span>
                </div>
                <p class="text-slate-400 font-bold uppercase tracking-wider text-[10px] mt-4">Total Classes</p>
                <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $totalClasses }}</h3>
            </div>
            <div class="w-12 h-1 bg-[#d97706] rounded-full mt-4"></div>
        </div>

        <!-- Parents Card -->
        <div class="bg-white p-6 rounded-2xl soft-card-shadow border border-slate-100 hover:-translate-y-1 transition-transform duration-300 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-4">
                    <div class="w-10 h-10 rounded-xl bg-[#ffe4e6] text-[#e11d48] flex items-center justify-center">
                        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">family_restroom</span>
                    </div>
                    <span class="text-rose-600 bg-rose-50 px-2 py-0.5 rounded text-xs font-semibold">Registered</span>
                </div>
                <p class="text-slate-400 font-bold uppercase tracking-wider text-[10px] mt-4">Total Parents</p>
                <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $totalParents }}</h3>
            </div>
            <div class="w-12 h-1 bg-[#e11d48] rounded-full mt-4"></div>
        </div>

        <!-- Subjects Card -->
        <div class="bg-white p-6 rounded-2xl soft-card-shadow border border-slate-100 hover:-translate-y-1 transition-transform duration-300 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-4">
                    <div class="w-10 h-10 rounded-xl bg-[#d1fae5] text-[#059669] flex items-center justify-center">
                        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">book</span>
                    </div>
                    <span class="text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded text-xs font-semibold">Curriculum</span>
                </div>
                <p class="text-slate-400 font-bold uppercase tracking-wider text-[10px] mt-4">Total Subjects</p>
                <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $totalSubjects }}</h3>
            </div>
            <div class="w-12 h-1 bg-[#059669] rounded-full mt-4"></div>
        </div>
    </section>

    <!-- Grid Layout: Announcements and Leave Requests -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-10">

        <!-- Announcements / Recent Alerts -->
        <section class="lg:col-span-5 bg-white rounded-2xl soft-card-shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-bold flex items-center gap-2 text-slate-800">
                    <span class="material-symbols-outlined text-[#e11d48]">campaign</span>
                    Announcements
                </h3>
                <div class="flex items-center gap-2">
                    @if($notices->count() > 0)
                    <span class="bg-rose-50 text-rose-600 px-2.5 py-0.5 rounded-lg text-[9px] font-bold">{{ $notices->count() }} NEW</span>
                    @endif
                    <a href="{{ route('school.notices') }}" class="text-[#5f40dc] font-bold hover:underline text-xs">View All</a>
                </div>
            </div>
            <div class="space-y-4">
                @forelse($notices as $notice)
                <div class="p-3.5 bg-slate-50 border border-slate-100 border-l-4 rounded-r-xl shadow-sm
                    {{ $notice->type === 'academic' ? 'border-l-[#5f40dc]' : ($notice->type === 'event' ? 'border-l-[#d97706]' : 'border-l-[#e11d48]') }}">
                    <h4 class="font-bold text-xs text-slate-800">{{ $notice->title }}</h4>
                    <p class="text-[11px] text-slate-500 mt-1.5 leading-relaxed">{{ Str::limit($notice->content, 120) }}</p>
                    <span class="text-[9px] text-slate-400 font-medium mt-2 block">Published {{ $notice->published_at?->diffForHumans() }}</span>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <span class="material-symbols-outlined text-slate-200 text-[48px] mb-2">campaign</span>
                    <p class="text-xs text-slate-400">No announcements yet.</p>
                    <a href="{{ route('school.notices') }}" class="mt-2 text-[#5f40dc] text-xs font-bold hover:underline">Post one now →</a>
                </div>
                @endforelse
            </div>
        </section>

        <!-- Student Leave Requests -->
        <section class="lg:col-span-7 bg-white rounded-2xl soft-card-shadow p-6" 
                 x-data="{ activeLeave: null }">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-base font-bold flex items-center gap-2 text-slate-800">
                    <span class="material-symbols-outlined text-[#5f40dc]">event_busy</span>
                    Student Leave Requests
                </h3>
                @if($pendingLeavesCount > 0)
                <span class="bg-amber-50 text-amber-600 border border-amber-200 px-3 py-1 rounded-full text-[10px] font-bold">
                    {{ $pendingLeavesCount }} PENDING
                </span>
                @endif
            </div>

            @if($leaveRequests->isEmpty())
            <div class="flex flex-col items-center justify-center py-14 text-center">
                <span class="material-symbols-outlined text-slate-200 text-[56px] mb-3">event_available</span>
                <p class="text-sm font-semibold text-slate-400">No leave requests yet</p>
                <p class="text-xs text-slate-400 mt-1">Leave requests submitted by parents will appear here.</p>
            </div>
            @else
            <div class="space-y-3">
                @foreach($leaveRequests as $leave)
                <div class="border border-slate-100 rounded-xl overflow-hidden transition-all duration-200 hover:border-slate-200 hover:shadow-sm">
                    <!-- Collapsed Row -->
                    <button type="button"
                            @click="activeLeave = activeLeave === {{ $leave->id }} ? null : {{ $leave->id }}"
                            class="w-full flex items-center justify-between p-3.5 text-left cursor-pointer hover:bg-slate-50/70 transition-all">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <!-- Status Dot -->
                            <div class="shrink-0 w-9 h-9 rounded-full flex items-center justify-center
                                {{ $leave->status === 'pending' ? 'bg-amber-50 text-amber-500' : ($leave->status === 'approved' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500') }}">
                                <span class="material-symbols-outlined text-[18px]">
                                    {{ $leave->status === 'pending' ? 'hourglass_empty' : ($leave->status === 'approved' ? 'check_circle' : 'cancel') }}
                                </span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <!-- Student names -->
                                <p class="text-xs font-bold text-slate-800 truncate">
                                    {{ $leave->students->pluck('name')->join(', ') ?: 'Unknown Student(s)' }}
                                </p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <p class="text-[10px] text-slate-400 font-medium">
                                        By {{ $leave->parent->name ?? 'Parent' }}
                                    </p>
                                    <span class="text-slate-300">·</span>
                                    <p class="text-[10px] text-slate-400">
                                        {{ $leave->from_date->format('d M') }}
                                        @if($leave->from_date->ne($leave->to_date))
                                         – {{ $leave->to_date->format('d M') }}
                                        @endif
                                        ({{ $leave->leave_days }} day{{ $leave->leave_days !== 1 ? 's' : '' }})
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0 ml-2">
                            <!-- Status Badge -->
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-md
                                {{ $leave->status === 'pending' ? 'bg-amber-100 text-amber-700' : ($leave->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700') }}">
                                {{ strtoupper($leave->status) }}
                            </span>
                            <span class="material-symbols-outlined text-slate-300 text-[18px] transition-transform duration-200"
                                  :class="activeLeave === {{ $leave->id }} ? 'rotate-180' : ''">
                                expand_more
                            </span>
                        </div>
                    </button>

                    <!-- Expanded Details -->
                    <div x-show="activeLeave === {{ $leave->id }}"
                         x-collapse
                         class="border-t border-slate-100 bg-slate-50/60 px-4 py-4">
                        
                        <!-- Students list -->
                        @if($leave->students->count() > 1)
                        <div class="mb-3">
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1.5">Students on Leave</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($leave->students as $student)
                                <span class="inline-flex items-center gap-1 bg-[#f0ecfe] text-[#5f40dc] text-[10px] font-semibold px-2.5 py-1 rounded-full">
                                    <span class="material-symbols-outlined text-[12px]">person</span>
                                    {{ $student->name }}
                                    @if($student->studentDetail?->class)
                                    · {{ $student->studentDetail->class->name }} {{ $student->studentDetail->class->section }}
                                    @endif
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @else
                        @php $student = $leave->students->first(); @endphp
                        @if($student)
                        <div class="mb-3 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#5f40dc] text-[16px]">school</span>
                            <span class="text-xs text-slate-600 font-medium">
                                {{ $student->name }}
                                @if($student->studentDetail?->class)
                                — {{ $student->studentDetail->class->name }} {{ $student->studentDetail->class->section }}
                                @endif
                            </span>
                        </div>
                        @endif
                        @endif

                        <!-- Reason -->
                        <div class="mb-3">
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Reason</p>
                            <p class="text-xs text-slate-700 font-medium">{{ $leave->reason }}</p>
                            @if($leave->description)
                            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">{{ $leave->description }}</p>
                            @endif
                        </div>

                        <!-- Leave dates -->
                        <div class="mb-3 flex items-center gap-4">
                            <div>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">From</p>
                                <p class="text-xs text-slate-700 font-semibold">{{ $leave->from_date->format('d M Y') }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">To</p>
                                <p class="text-xs text-slate-700 font-semibold">{{ $leave->to_date->format('d M Y') }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Duration</p>
                                <p class="text-xs text-slate-700 font-semibold">{{ $leave->leave_days }} day{{ $leave->leave_days !== 1 ? 's' : '' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Submitted</p>
                                <p class="text-xs text-slate-500">{{ $leave->created_at->diffForHumans() }}</p>
                            </div>
                        </div>

                        <!-- Admin remarks if already reviewed -->
                        @if($leave->admin_remarks)
                        <div class="mb-3 p-2.5 bg-white rounded-lg border border-slate-100">
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Admin Remarks</p>
                            <p class="text-[11px] text-slate-600">{{ $leave->admin_remarks }}</p>
                        </div>
                        @endif

                        <!-- Action Buttons — only for pending -->
                        @if($leave->status === 'pending')
                        <div x-data="{ showRemarks: false, remarksText: '' }" class="pt-2">
                            <div x-show="!showRemarks" class="flex items-center gap-2">
                                <button @click="showRemarks = true; $nextTick(() => $el.closest('[x-data]').querySelector('textarea').focus())"
                                        class="flex-1 flex items-center justify-center gap-1.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold py-2 px-4 rounded-xl transition-all cursor-pointer">
                                    <span class="material-symbols-outlined text-[16px]">check</span>
                                    Approve
                                </button>
                                <button @click="showRemarks = true"
                                        class="flex-1 flex items-center justify-center gap-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-bold py-2 px-4 rounded-xl transition-all cursor-pointer"
                                        x-ref="rejectBtn{{ $leave->id }}">
                                    <span class="material-symbols-outlined text-[16px]">close</span>
                                    Reject
                                </button>
                            </div>

                            <!-- Remarks form with Approve/Reject -->
                            <div x-show="showRemarks" class="space-y-2">
                                <textarea x-model="remarksText"
                                          placeholder="Add remarks (optional)..."
                                          rows="2"
                                          class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-[#5f40dc]/20 focus:border-[#5f40dc] resize-none outline-none text-slate-700 bg-white"></textarea>
                                <div class="flex items-center gap-2">
                                    <!-- Approve form -->
                                    <form method="POST" action="{{ route('school.leave-requests.approve', $leave) }}" class="flex-1">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="admin_remarks" :value="remarksText">
                                        <button type="submit"
                                                class="w-full flex items-center justify-center gap-1.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold py-2 px-3 rounded-xl transition-all cursor-pointer">
                                            <span class="material-symbols-outlined text-[16px]">check</span>
                                            Confirm Approve
                                        </button>
                                    </form>
                                    <!-- Reject form -->
                                    <form method="POST" action="{{ route('school.leave-requests.reject', $leave) }}" class="flex-1">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="admin_remarks" :value="remarksText">
                                        <button type="submit"
                                                class="w-full flex items-center justify-center gap-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-bold py-2 px-3 rounded-xl transition-all cursor-pointer">
                                            <span class="material-symbols-outlined text-[16px]">close</span>
                                            Confirm Reject
                                        </button>
                                    </form>
                                    <!-- Cancel -->
                                    <button @click="showRemarks = false"
                                            type="button"
                                            class="flex items-center justify-center w-9 h-9 shrink-0 border border-slate-200 rounded-xl hover:bg-slate-100 text-slate-400 cursor-pointer">
                                        <span class="material-symbols-outlined text-[16px]">close</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="pt-1 flex items-center gap-1.5 text-slate-400">
                            <span class="material-symbols-outlined text-[14px]">person</span>
                            <span class="text-[10px]">Reviewed by {{ $leave->reviewer?->name ?? 'Admin' }} · {{ $leave->reviewed_at?->diffForHumans() }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </section>
    </div>

    <!-- Class Registry Section -->
    <section class="bg-white rounded-2xl soft-card-shadow p-6 mb-10 border border-slate-100">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-base font-bold flex items-center gap-2 text-slate-800">
                <span class="material-symbols-outlined text-[#5f40dc]">school</span>
                Class Registry
            </h3>
            <a href="{{ route('school.classes') }}" class="text-[#5f40dc] font-bold hover:underline text-xs">View All →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-4 py-3 font-bold text-slate-400 text-[10px] uppercase tracking-wider">Class</th>
                        <th class="px-4 py-3 font-bold text-slate-400 text-[10px] uppercase tracking-wider">Class Teacher</th>
                        <th class="px-4 py-3 font-bold text-slate-400 text-[10px] uppercase tracking-wider">Students</th>
                        <th class="px-4 py-3 font-bold text-slate-400 text-[10px] uppercase tracking-wider">Room</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($classes->take(6) as $class)
                    <tr class="hover:bg-slate-50/50 transition-all duration-150">
                        <td class="px-4 py-3">
                            <span class="text-xs font-bold text-slate-800">{{ $class->name }} - {{ $class->section }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs text-slate-600">{{ $class->teacher?->name ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-mono font-bold text-slate-700">{{ $class->students_count }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[10px] text-slate-400 font-mono">{{ $class->room_number ?? '—' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-xs text-slate-400">No classes created yet. <a href="{{ route('school.classes') }}" class="text-[#5f40dc] font-semibold">Add one now →</a></td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- Quick Actions Section -->
    <section class="mb-8">
        <h3 class="text-base font-bold mb-4 text-slate-800">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('school.parents') }}" class="flex flex-col items-center justify-center p-6 bg-white border border-slate-100 rounded-2xl hover:border-[#5f40dc]/30 hover:bg-[#f0ecfe]/10 transition-all duration-200 group active:scale-95 shadow-sm">
                <span class="material-symbols-outlined text-[#5f40dc] mb-3 text-[32px] group-hover:scale-110 transition-transform">person_add</span>
                <span class="text-xs font-bold text-slate-800">Add Student</span>
            </a>
            <a href="{{ route('school.timetable.index') }}" class="flex flex-col items-center justify-center p-6 bg-white border border-slate-100 rounded-2xl hover:border-[#5f40dc]/30 hover:bg-[#f0ecfe]/10 transition-all duration-200 group active:scale-95 shadow-sm">
                <span class="material-symbols-outlined text-[#5f40dc] mb-3 text-[32px] group-hover:scale-110 transition-transform">event_note</span>
                <span class="text-xs font-bold text-slate-800">Create Timetable</span>
            </a>
            <a href="{{ route('school.settings') }}" class="flex flex-col items-center justify-center p-6 bg-white border border-slate-100 rounded-2xl hover:border-[#5f40dc]/30 hover:bg-[#f0ecfe]/10 transition-all duration-200 group active:scale-95 shadow-sm">
                <span class="material-symbols-outlined text-[#5f40dc] mb-3 text-[32px] group-hover:scale-110 transition-transform">key</span>
                <span class="text-xs font-bold text-slate-800">Send Credentials</span>
            </a>
            <a href="{{ route('school.notices') }}" class="flex flex-col items-center justify-center p-6 bg-white border border-slate-100 rounded-2xl hover:border-[#5f40dc]/30 hover:bg-[#f0ecfe]/10 transition-all duration-200 group active:scale-95 shadow-sm">
                <span class="material-symbols-outlined text-[#5f40dc] mb-3 text-[32px] group-hover:scale-110 transition-transform">add_alert</span>
                <span class="text-xs font-bold text-slate-800">Post Notice</span>
            </a>
        </div>
    </section>
@endsection
