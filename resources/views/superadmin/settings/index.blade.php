@extends('layouts.superadmin')

@section('title', 'AuraCampus | System Settings')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h2 class="text-xl font-bold text-slate-900 tracking-tight">System Core Settings</h2>
        <p class="text-xs text-slate-500 mt-1">Configure global platform branding, support contacts, API keys, and maintenance controls.</p>
    </div>

    <!-- Success message -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-700 flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Sidebar Navigation within settings -->
        <div class="space-y-2">
            <a href="#" class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-white border border-slate-200/60 shadow-sm text-indigo-600 font-bold text-xs">
                <span class="material-symbols-outlined text-[18px]">display_settings</span>
                Platform Branding
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition-all text-xs opacity-60 pointer-events-none">
                <span class="material-symbols-outlined text-[18px]">dns</span>
                SMTP & Mail Server
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition-all text-xs opacity-60 pointer-events-none">
                <span class="material-symbols-outlined text-[18px]">backup</span>
                Automated Backups
            </a>
        </div>

        <!-- Form Area -->
        <div class="col-span-2">
            <div class="premium-card p-6 rounded-2xl bg-white border border-slate-200/60 shadow-sm relative overflow-hidden">
                <div class="absolute -right-20 -top-20 w-48 h-48 bg-indigo-500/5 rounded-full blur-2xl"></div>

                <form method="POST" action="{{ route('superadmin.settings.store') }}">
                    @csrf
                    
                    <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 mb-6">Platform Branding</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Platform Title</label>
                                <input type="text" name="platform_title" value="AuraCampus" required
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-indigo">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Platform Tagline</label>
                                <input type="text" name="platform_tagline" value="Next-Gen School ERP Ecosystem" required
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-indigo">
                            </div>
                        </div>

                        <!-- Font Family Selector -->
                        <div x-data="{ selectedFont: 'Inter' }">
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                <span class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px] text-indigo-500">font_download</span>
                                    Global Font Family
                                </span>
                            </label>
                            <p class="text-[9px] text-slate-400 mb-2 font-medium">This sets the default font across the entire platform for all campuses.</p>
                            <select name="font_family" x-model="selectedFont"
                                    class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-indigo text-slate-700 cursor-pointer">
                                <option value="Inter">Inter (Default)</option>
                                <option value="Poppins">Poppins</option>
                                <option value="Roboto">Roboto</option>
                                <option value="Outfit">Outfit</option>
                                <option value="Manrope">Manrope</option>
                                <option value="Nunito">Nunito</option>
                                <option value="Open Sans">Open Sans</option>
                                <option value="Lato">Lato</option>
                                <option value="Montserrat">Montserrat</option>
                                <option value="Raleway">Raleway</option>
                                <option value="Source Sans Pro">Source Sans Pro</option>
                                <option value="Work Sans">Work Sans</option>
                            </select>
                            <!-- Font Preview -->
                            <div class="mt-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                                <p class="text-[9px] font-mono text-slate-400 uppercase tracking-wider mb-2 font-bold">Preview</p>
                                <p class="text-sm text-slate-800 font-semibold" :style="'font-family: ' + selectedFont + ', sans-serif'">
                                    The quick brown fox jumps over the lazy dog.
                                </p>
                                <p class="text-xs text-slate-500 mt-1" :style="'font-family: ' + selectedFont + ', sans-serif'">
                                    AaBbCcDdEeFfGg 0123456789
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Support Email</label>
                                <input type="email" name="support_email" value="support@auracampus.com" required
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-indigo">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Support Hotline</label>
                                <input type="text" name="support_hotline" value="+1-800-AURA-EDU" required
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-indigo">
                            </div>
                        </div>

                        <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 pt-6 mb-4">Environment Controls</h3>
                        
                        <!-- Toggle switch row: Maintenance mode -->
                        <div class="flex items-center justify-between p-4 bg-slate-50 border border-slate-200/60 rounded-xl">
                            <div>
                                <h4 class="text-xs font-bold text-slate-800">Platform Maintenance Mode</h4>
                                <p class="text-[9px] text-slate-500 mt-0.5 font-medium">Temporarily disable access to all tenant dashboard portals during updates.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="maintenance_mode" value="1" class="sr-only peer">
                                <div class="w-8 h-5 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Actions -->
                    <div class="mt-8 border-t border-slate-100 pt-6 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">
                            Save System Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
