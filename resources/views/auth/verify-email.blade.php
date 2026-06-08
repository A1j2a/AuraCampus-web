<x-guest-layout>
    @section('title', 'AuraCampus | Workspace Verification')

    <div class="min-h-screen w-screen flex flex-col items-center justify-center p-6 tech-canvas relative overflow-hidden select-none bg-[#fafbfc]">
        
        <!-- Animated Background Spheres (Layered minimalism) -->
        <div class="absolute top-10 right-20 w-80 h-80 rounded-full bg-gradient-to-br from-indigo-500/5 to-purple-600/5 blur-3xl animate-sphere-1 pointer-events-none"></div>
        <div class="absolute bottom-10 left-10 w-96 h-96 rounded-full bg-gradient-to-tr from-blue-600/5 to-indigo-700/5 blur-3xl animate-sphere-2 pointer-events-none"></div>

        <!-- Master Card -->
        <div class="w-full max-w-[420px] p-8 rounded-2xl bg-white border border-slate-200/60 shadow-[0_1px_3px_rgba(0,0,0,0.01),0_24px_48px_-16px_rgba(15,23,42,0.08)] relative z-10 text-slate-800">
            
            <!-- Logo Header -->
            <div class="flex flex-col items-center mb-6">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100/80 flex items-center justify-center mb-3 text-indigo-600 shadow-sm">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M12 2L2 7l10 5 10-5-10-5z" fill="currentColor" fill-opacity="0.1"/>
                        <path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 tracking-tight">Verify email</h3>
                <p class="text-xs text-slate-500 mt-1 text-center">Please verify your email address by clicking the link we just sent to your inbox.</p>
            </div>

            <!-- Session Status Alerts -->
            @if (session('status') == 'verification-link-sent')
                <div class="mb-5 p-3 bg-indigo-50/50 border border-indigo-150 text-indigo-800 rounded-xl flex gap-2.5 items-start">
                    <span class="material-symbols-outlined text-indigo-600 text-[18px] shrink-0 mt-0.5">check_circle_outline</span>
                    <p class="text-xs font-medium text-left text-indigo-700">A new verification link has been sent to the email address you registered with.</p>
                </div>
            @endif

            <!-- Form Row -->
            <div class="space-y-4">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button 
                        type="submit" 
                        class="w-full py-2 bg-slate-900 hover:bg-slate-950 text-white font-medium rounded-lg transition-all duration-150 active:scale-[0.98] flex items-center justify-center gap-1.5 cursor-pointer text-sm shadow-[0_1px_2px_rgba(0,0,0,0.05)] border border-transparent"
                    >
                        <span>Resend Verification Email</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" class="text-center">
                    @csrf
                    <button 
                        type="submit" 
                        class="text-xs text-slate-500 hover:text-slate-800 font-semibold transition-colors cursor-pointer"
                    >
                        Log Out
                    </button>
                </form>
            </div>

        </div>

        <!-- System Status Bar below Card -->
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6 text-[10px] font-sans text-slate-400 uppercase tracking-widest relative z-10 select-none">
            <div class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_6px_rgba(16,185,129,0.5)]"></span>
                Ecosystem Status: Operational
            </div>
            <div class="hidden sm:inline-block w-1.5 h-1.5 rounded-full bg-slate-350"></div>
            <div>
                Version: 4.8.2-stable
            </div>
        </div>

    </div>
</x-guest-layout>
