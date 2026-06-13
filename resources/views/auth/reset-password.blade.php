<x-guest-layout>
    @section('title', 'AuraCampus | Recovery Key Reset')

    <div class="min-h-screen w-screen flex flex-col items-center justify-center p-6 tech-canvas relative overflow-hidden select-none bg-[#fafbfc]">
        
        <!-- Animated Background Spheres (Layered minimalism) -->
        <div class="absolute top-10 right-20 w-80 h-80 rounded-full bg-gradient-to-br from-indigo-500/5 to-purple-600/5 blur-3xl animate-sphere-1 pointer-events-none"></div>
        <div class="absolute bottom-10 left-10 w-96 h-96 rounded-full bg-gradient-to-tr from-blue-600/5 to-indigo-700/5 blur-3xl animate-sphere-2 pointer-events-none"></div>

        <!-- Master Card -->
        <div class="w-full max-w-[420px] p-8 rounded-2xl bg-white border border-slate-200/60 shadow-[0_1px_3px_rgba(0,0,0,0.01),0_24px_48px_-16px_rgba(15,23,42,0.08)] relative z-10 text-slate-800">
            
            <!-- Logo Header -->
            <div class="flex flex-col items-center mb-6">
                <div class="w-10 h-10 rounded-xl overflow-hidden flex items-center justify-center mb-3 border border-slate-200 shadow-sm bg-white">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-cover">
                </div>
                <h3 class="text-xl font-bold text-slate-900 tracking-tight">Create key</h3>
                <p class="text-xs text-slate-500 mt-1 text-center">Enter credentials and write your new password below</p>
            </div>

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="mb-5 p-3 bg-rose-50 border border-rose-100 text-rose-800 rounded-xl flex gap-2.5 items-start animate-shake">
                    <span class="material-symbols-outlined text-rose-500 text-[18px] shrink-0 mt-0.5">error_outline</span>
                    <div class="text-left">
                        <ul class="list-disc pl-3.5 text-xs space-y-0.5 text-rose-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-medium text-slate-500 mb-1.5">Workspace Email</label>
                    <input 
                        id="email" 
                        type="email" 
                        name="email" 
                        value="{{ old('email', $request->email) }}" 
                        required 
                        autofocus 
                        autocomplete="username"
                        class="w-full px-3.5 py-2 bg-white border border-slate-200/80 rounded-lg text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/20 transition-all font-normal"
                        placeholder="admin@school.com"
                    />
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-xs font-medium text-slate-500 mb-1.5">New Security Password</label>
                    <input 
                        id="password" 
                        type="password" 
                        name="password" 
                        required 
                        autocomplete="new-password"
                        class="w-full px-3.5 py-2 bg-white border border-slate-200/80 rounded-lg text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/20 transition-all font-normal"
                        placeholder="Min. 8 characters"
                    />
                </div>

                <!-- Confirm Password Input -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-medium text-slate-500 mb-1.5">Verify Password</label>
                    <input 
                        id="password_confirmation" 
                        type="password" 
                        name="password_confirmation" 
                        required 
                        autocomplete="new-password"
                        class="w-full px-3.5 py-2 bg-white border border-slate-200/80 rounded-lg text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/20 transition-all font-normal"
                        placeholder="••••••••"
                    />
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button 
                        type="submit" 
                        class="w-full py-2 bg-slate-900 hover:bg-slate-950 text-white font-medium rounded-lg transition-all duration-150 active:scale-[0.98] flex items-center justify-center gap-1.5 cursor-pointer text-sm shadow-[0_1px_2px_rgba(0,0,0,0.05)] border border-transparent"
                    >
                        <span>Update Password</span>
                    </button>
                </div>
            </form>

        </div>

        <!-- System Status Bar below Card -->
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6 text-[10px] font-sans text-slate-400 uppercase tracking-widest relative z-10 select-none">
            <div class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_6px_rgba(16,185,129,0.5)]"></span>
                Ecosystem Status: Operational
            </div>
            <div class="hidden sm:inline-block w-1 h-1 rounded-full bg-slate-300"></div>
            <div>
                Version: 4.8.2-stable
            </div>
        </div>

    </div>
</x-guest-layout>
