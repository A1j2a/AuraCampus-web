@extends('layouts.school')

@section('title', 'AuraCampus | Exams')

@section('content')
<div x-data="{ showModal: {{ $errors->any() ? 'true' : 'false' }} }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight font-sans">Exams & Term Assessments</h2>
            <p class="text-xs text-slate-500 mt-1">Manage mid-term, final, and weekly unit assessments, and configure date schedules.</p>
        </div>
        <button @click="showModal = true" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-[16px]">add_circle</span>
            Create Exam
        </button>
    </div>

    <!-- Alerts -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-700 flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    <!-- Exams List Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($exams as $exam)
        @php
            $statusBadge = 'bg-amber-50 text-amber-700 border-amber-100';
            if ($exam->status === 'ongoing') {
                $statusBadge = 'bg-blue-50 text-blue-700 border-blue-150 animate-pulse';
            } elseif ($exam->status === 'completed') {
                $statusBadge = 'bg-emerald-50 text-emerald-700 border-emerald-150';
            }
        @endphp
        <div class="premium-card p-6 bg-white rounded-2xl shadow-sm border border-slate-200/60 relative overflow-hidden flex flex-col justify-between hover:shadow-md transition-all">
            <div class="absolute -right-10 -top-10 w-24 h-24 bg-slate-50 rounded-full blur-xl"></div>
            <div>
                <div class="flex justify-between items-start mb-4">
                    <span class="px-2 py-0.5 border text-[9px] font-mono rounded font-bold uppercase {{ $statusBadge }}">
                        {{ $exam->status }}
                    </span>
                    <span class="text-[10px] text-slate-400 font-semibold uppercase font-mono">
                        {{ str_replace('_', ' ', $exam->type) }}
                    </span>
                </div>
                <h3 class="text-sm font-bold text-slate-800 mb-2">{{ $exam->name }}</h3>
                
                <div class="space-y-1.5 my-4">
                    <div class="flex items-center gap-2 text-[11px] text-slate-500">
                        <span class="material-symbols-outlined text-[14px]">calendar_today</span>
                        <span>Start: {{ \Carbon\Carbon::parse($exam->start_date)->format('d M Y') }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-[11px] text-slate-500">
                        <span class="material-symbols-outlined text-[14px]">event_busy</span>
                        <span>End: {{ \Carbon\Carbon::parse($exam->end_date)->format('d M Y') }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-[11px] text-slate-500">
                        <span class="material-symbols-outlined text-[14px]">list_alt</span>
                        <span class="font-bold text-slate-700">{{ $exam->schedules_count }} Scheduled Papers</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-50 flex items-center justify-between">
                <a href="{{ route('school.exams.show', $exam) }}" 
                   class="w-full text-center px-4 py-2 bg-slate-50 hover:bg-emerald-50 border border-slate-200/60 hover:border-emerald-200 hover:text-emerald-700 text-slate-700 text-xs font-bold rounded-xl transition-all">
                    View & Configure Schedules
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full py-16 text-center premium-card rounded-2xl bg-white">
            <span class="material-symbols-outlined text-slate-400 text-5xl mb-3 animate-float-slow">quiz</span>
            <h4 class="text-sm font-bold text-slate-800 mb-1">No Exams Scheduled</h4>
            <p class="text-xs text-slate-500 max-w-sm mx-auto leading-normal">Create an exam and assign date sheets for subjects to allow teachers to log grading marks.</p>
        </div>
        @endforelse
    </div>

    <!-- Create Exam Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 relative z-10 border border-slate-200/60" @click.stop>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-base font-bold text-slate-900">Create New Exam Term</h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" action="{{ route('school.exams.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Exam Term Name</label>
                        <input type="text" name="name" placeholder="e.g. Mid-Term Examination" required
                               class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald placeholder-slate-300">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Start Date</label>
                            <input type="date" name="start_date" required
                                   class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">End Date</label>
                            <input type="date" name="end_date" required
                                   class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Evaluation Category</label>
                        <select name="type" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald appearance-none cursor-pointer bg-white">
                            <option value="mid_term">Mid-Term Assessment</option>
                            <option value="final">Final Assessment</option>
                            <option value="unit_test">Unit Test</option>
                            <option value="practical">Practical Evaluation</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">Save Exam</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
