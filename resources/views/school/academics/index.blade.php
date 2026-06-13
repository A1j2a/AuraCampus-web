@extends('layouts.school')

@section('title', 'AuraCampus | Academics')

@section('content')
<div class="premium-card p-8 rounded-2xl relative overflow-hidden bg-white">
    <div class="absolute -right-20 -top-20 w-48 h-48 bg-violet-500/5 rounded-full blur-2xl"></div>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Academics & Curriculums</h2>
            <p class="text-xs text-slate-500 mt-1">Configure class syllabus bounds, review daily homework logs, and track curriculum completion rates.</p>
        </div>
        <button class="px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">
            Configure Exams
        </button>
    </div>

    <!-- Mock academic syllabus meters -->
    <div class="space-y-4 opacity-75">
        <div>
            <div class="flex justify-between items-center mb-1">
                <span class="text-xs font-semibold text-slate-800">Class 10 - Mathematics Syllabus</span>
                <span class="text-xs font-mono font-bold text-violet-600">78% Complete</span>
            </div>
            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                <div class="bg-gradient-to-r from-violet-500 to-cyan-500 h-full" style="width: 78%"></div>
            </div>
        </div>
        <div>
            <div class="flex justify-between items-center mb-1">
                <span class="text-xs font-semibold text-slate-800">Class 12 - Physics Syllabus</span>
                <span class="text-xs font-mono font-bold text-indigo-600">62% Complete</span>
            </div>
            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-full" style="width: 62%"></div>
            </div>
        </div>
    </div>

    <div class="mt-8 p-6 border border-dashed border-slate-200 rounded-xl bg-slate-50/20 text-center">
        <span class="material-symbols-outlined text-violet-600 text-4xl mb-3 animate-float-slow" data-icon="menu_book">menu_book</span>
        <h4 class="text-sm font-bold text-slate-800 mb-1">Academic Syllabus Ledger Under Development</h4>
        <p class="text-xs text-slate-500 max-w-md mx-auto leading-normal">Course curriculum timeline tracking models are active. We are designing central portals for teachers to input homework assignments.</p>
    </div>
</div>
@endsection
