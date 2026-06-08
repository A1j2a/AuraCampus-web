@extends('layouts.school')

@section('title', 'AuraCampus | Settings')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h2 class="text-xl font-bold text-slate-900 tracking-tight font-sans">School Configurations</h2>
        <p class="text-xs text-slate-500 mt-1">Manage school profile details, localized preferences, and grading metrics.</p>
    </div>

    <!-- Alert Message -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-700 flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Sidebar Navigation (within settings) -->
        <div class="space-y-2">
            <a href="#" class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-white border border-slate-200/60 shadow-sm text-emerald-600 font-bold text-xs">
                <span class="material-symbols-outlined text-[18px]">school</span>
                School Profile
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition-all text-xs opacity-60 pointer-events-none">
                <span class="material-symbols-outlined text-[18px]">notifications_active</span>
                Alert Configurations
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition-all text-xs opacity-60 pointer-events-none">
                <span class="material-symbols-outlined text-[18px]">security</span>
                Data Security
            </a>
        </div>

        <!-- Settings Form Area -->
        <div class="col-span-2 space-y-6">
            <div class="premium-card p-6 rounded-2xl bg-white border border-slate-200/60 shadow-sm relative overflow-hidden">
                <div class="absolute -right-20 -top-20 w-48 h-48 bg-emerald-500/5 rounded-full blur-2xl"></div>

                <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 mb-6">Profile Details</h3>
                <form method="POST" action="{{ route('school.settings.update') }}">
                    @csrf
                    @method('PATCH')
                    
                    <div class="space-y-4">
                        <!-- School Name -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">School Name</label>
                            <input type="text" name="name" value="{{ old('name', $school->name) }}" required
                                   class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald">
                        </div>

                        <!-- Email & Phone -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Contact Email</label>
                                <input type="email" name="email" value="{{ old('email', $school->email) }}"
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Contact Phone</label>
                                <input type="text" name="phone" value="{{ old('phone', $school->phone) }}"
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald">
                            </div>
                        </div>

                        <!-- Address -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Postal Address</label>
                            <textarea name="address" rows="3"
                                      class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald resize-none">{{ old('address', $school->address) }}</textarea>
                        </div>

                        <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 pt-4 mb-4">Localization & System Preferences</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Timezone -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Default Timezone</label>
                                <select name="timezone" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald appearance-none cursor-pointer bg-white">
                                    <option value="Asia/Kolkata" {{ old('timezone', $school->settings['timezone'] ?? '') == 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata (IST)</option>
                                    <option value="UTC" {{ old('timezone', $school->settings['timezone'] ?? '') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                    <option value="America/New_York" {{ old('timezone', $school->settings['timezone'] ?? '') == 'America/New_York' ? 'selected' : '' }}>America/New_York (EST)</option>
                                    <option value="Europe/London" {{ old('timezone', $school->settings['timezone'] ?? '') == 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT)</option>
                                    <option value="Asia/Dubai" {{ old('timezone', $school->settings['timezone'] ?? '') == 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai (GST)</option>
                                    <option value="Asia/Singapore" {{ old('timezone', $school->settings['timezone'] ?? '') == 'Asia/Singapore' ? 'selected' : '' }}>Asia/Singapore (SGT)</option>
                                </select>
                            </div>

                            <!-- Grading System -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Grading System</label>
                                <select name="grading_system" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald appearance-none cursor-pointer bg-white">
                                    <option value="percentage" {{ old('grading_system', $school->settings['grading_system'] ?? '') == 'percentage' ? 'selected' : '' }}>Percentage (0 - 100%)</option>
                                    <option value="grade" {{ old('grading_system', $school->settings['grading_system'] ?? '') == 'grade' ? 'selected' : '' }}>Alphabetical Grades (A+, A, B, etc.)</option>
                                    <option value="cgpa" {{ old('grading_system', $school->settings['grading_system'] ?? '') == 'cgpa' ? 'selected' : '' }}>CGPA (10-Point Scale)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-8 border-t border-slate-100 pt-6 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">
                            Save Configuration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
