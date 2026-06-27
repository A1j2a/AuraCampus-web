<x-guest-layout>
    @section('title', 'AuraCampus | Authentication')

    <div class="min-h-screen w-screen flex flex-col items-center justify-center p-6 tech-canvas relative overflow-hidden select-none bg-[#fafbfc]">
        
        <!-- Animated Background Spheres (Layered minimalism) -->
        <div class="absolute top-10 right-20 w-80 h-80 rounded-full bg-gradient-to-br from-indigo-500/5 to-purple-600/5 blur-3xl animate-sphere-1 pointer-events-none"></div>
        <div class="absolute bottom-10 left-10 w-96 h-96 rounded-full bg-gradient-to-tr from-blue-600/5 to-indigo-700/5 blur-3xl animate-sphere-2 pointer-events-none"></div>

        @php
            $superEmail = $superAdmin ? $superAdmin->email : 'admin@auracampus.com';
            $defaultSchoolEmail = 'principal@greenwood.com';
            
            if ($schoolAdmins->isNotEmpty()) {
                $defaultSchoolAdmin = $schoolAdmins->first();
                $defaultSchoolEmail = $defaultSchoolAdmin->email;
            }
            
            $passwordsMap = [
                'admin@auracampus.com' => 'password',
                'principal@greenwood.com' => 'password',
                'school@auracampus.com' => 'Aura@123',
            ];
            $jsonMap = json_encode($passwordsMap);
            
            $oldEmail = old('email');
            $initialRole = $oldEmail && str_contains($oldEmail, 'admin@') ? 'super' : 'school';
            $initialEmail = $oldEmail ?? $defaultSchoolEmail;
        @endphp
        <!-- Master Card -->
        <div 
            x-data="{ 
                role: '{{ $initialRole }}', 
                showPass: false, 
                email: '{{ $initialEmail }}', 
                password: '{{ $passwordsMap[$initialEmail] ?? "" }}',
                superAdminEmail: '{{ $superEmail }}',
                schoolAdminEmail: '{{ $defaultSchoolEmail }}',
                passwordsMap: {{ $jsonMap }},
                setRole(newRole) {
                    this.role = newRole;
                    let targetEmail = newRole === 'super' ? this.superAdminEmail : this.schoolAdminEmail;
                    this.email = targetEmail;
                    this.password = this.passwordsMap[targetEmail] || '';
                },
                updateEmail(val) {
                    this.email = val;
                    this.password = this.passwordsMap[val] || '';
                }
            }" 
            class="w-full max-w-[420px] p-8 rounded-2xl bg-white border border-slate-200/60 shadow-[0_1px_3px_rgba(0,0,0,0.01),0_24px_48px_-16px_rgba(15,23,42,0.08)] relative z-10 text-slate-800"
        >
            <!-- Brand Header -->
            <div class="flex flex-col items-center mb-6">
                <div class="w-10 h-10 rounded-xl overflow-hidden flex items-center justify-center mb-3 border border-slate-200 shadow-sm bg-white">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-cover">
                </div>
                <h3 class="text-xl font-bold text-slate-900 tracking-tight">Sign in to AuraCampus</h3>
                <p class="text-xs text-slate-500 mt-1 text-center">Access your custom school workspace dashboard</p>
            </div>

            <!-- Role Selector Segmented Tabs -->
            <div class="grid grid-cols-2 gap-1 p-1.5 bg-slate-100/60 border border-slate-200/40 rounded-xl mb-4">
                <button 
                    type="button" 
                    @click="setRole('super')" 
                    class="py-2 text-xs font-bold rounded-lg transition-all duration-200 text-center cursor-pointer select-none flex items-center justify-center gap-1.5"
                    :class="role === 'super' ? 'bg-white text-indigo-600 shadow-sm border border-slate-200/30' : 'text-slate-500 hover:text-slate-800'"
                >
                    <span class="material-symbols-outlined text-[15px]">shield</span>
                    Super Admin
                </button>
                <button 
                    type="button" 
                    @click="setRole('school')" 
                    class="py-2 text-xs font-bold rounded-lg transition-all duration-200 text-center cursor-pointer select-none flex items-center justify-center gap-1.5"
                    :class="role === 'school' ? 'bg-white text-emerald-600 shadow-sm border border-slate-200/30' : 'text-slate-500 hover:text-slate-800'"
                >
                    <span class="material-symbols-outlined text-[15px]">school</span>
                    School Admin
                </button>
            </div>

            <!-- Campus Preset Selector (only shown for school role) -->
            <div x-show="role === 'school'" class="mb-4">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Select Campus Preset</label>
                <div class="relative">
                    <select 
                        @change="schoolAdminEmail = $event.target.value; updateEmail($event.target.value);"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200/80 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-slate-400 focus:ring-1 focus:ring-slate-400/20 transition-all appearance-none cursor-pointer"
                    >
                        @foreach($schoolAdmins as $sa)
                            <option value="{{ $sa->email }}" {{ ($oldEmail ?? $defaultSchoolEmail) === $sa->email ? 'selected' : '' }}>
                                {{ $sa->school->name }} ({{ $sa->name }})
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400">
                        <span class="material-symbols-outlined text-[18px]">keyboard_arrow_down</span>
                    </div>
                </div>
            </div>

            <!-- Dynamic Role Context Alert / Badge -->
            <div class="mb-4 p-3.5 rounded-xl border text-[11px] leading-relaxed flex gap-2.5 items-start transition-all duration-300"
                 :class="role === 'super' ? 'bg-indigo-50/50 border-indigo-100/50 text-indigo-700' : 'bg-emerald-50/50 border-emerald-100/50 text-emerald-700'">
                <span class="material-symbols-outlined text-[16px] shrink-0 mt-0.5" :class="role === 'super' ? 'text-indigo-500' : 'text-emerald-550'">info</span>
                <div>
                    <span class="font-bold uppercase tracking-wider text-[9px] block mb-0.5" :class="role === 'super' ? 'text-indigo-600' : 'text-emerald-600'">Active Preset Info</span>
                    <span x-html="role === 'super' ? 'Alex Rivera (Super Admin). Password is <code class=\'bg-indigo-100 px-1 py-0.5 rounded font-mono font-bold text-[10px]\'>password</code>' : 'Greenwood Academy password is <code class=\'bg-emerald-100 px-1 py-0.5 rounded font-mono font-bold text-[10px]\'>password</code>. Aura International password is <code class=\'bg-emerald-100 px-1 py-0.5 rounded font-mono font-bold text-[10px]\'>Aura@123</code>.'"></span>
                </div>
            </div>

            <!-- Session Status Alerts -->
            <x-auth-session-status class="mb-5" :status="session('status')" />

            <!-- Validation Errors -->
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
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <!-- Username/Email Field -->
                <div>
                    <label for="email" class="block text-xs font-medium text-slate-500 mb-1.5">Username or Email</label>
                    <input 
                        id="email" 
                        type="email" 
                        name="email" 
                        x-model="email"
                        required 
                        autofocus 
                        autocomplete="username"
                        class="w-full px-3.5 py-2 bg-white border border-slate-200/80 rounded-lg text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-slate-400 focus:ring-1 focus:ring-slate-400/20 transition-all font-normal"
                        placeholder="you@domain.com"
                    />
                </div>

                <!-- Password Field -->
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label for="password" class="block text-xs font-medium text-slate-500">Password</label>
                    </div>
                    <div class="relative">
                        <input 
                            id="password" 
                            :type="showPass ? 'text' : 'password'" 
                            name="password" 
                            x-model="password"
                            required 
                            autocomplete="current-password"
                            class="w-full pl-3.5 pr-12 py-2 bg-white border border-slate-200/80 rounded-lg text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-slate-400 focus:ring-1 focus:ring-slate-400/20 transition-all font-normal"
                            placeholder="••••••••"
                        />
                        <!-- Show/Hide Password Toggle inside the input field -->
                        <button 
                            type="button" 
                            @click="showPass = !showPass" 
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-medium text-slate-400 hover:text-slate-600 transition-colors select-none focus:outline-none cursor-pointer"
                        >
                          <span x-text="showPass ? 'Hide' : 'Show'">Show</span>
                        </button>
                    </div>
                </div>

                <!-- Checkbox & Forgot Password Row -->
                <div class="flex items-center justify-between text-xs pt-1">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                        <input 
                            id="remember_me" 
                            type="checkbox" 
                            name="remember"
                            class="w-4 h-4 rounded border-slate-200 bg-white text-slate-900 focus:ring-slate-900/10 cursor-pointer transition-colors"
                        />
                        <span class="ms-2 text-slate-500 font-medium">Remember me</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a class="text-slate-500 hover:text-slate-850 font-medium transition-colors" href="{{ route('password.request') }}">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <!-- Action Submit Button -->
                <div class="pt-2">
                    <button 
                        type="submit" 
                        class="w-full py-2.5 text-white font-bold rounded-xl transition-all duration-300 active:scale-[0.98] flex items-center justify-center gap-1.5 cursor-pointer text-xs shadow-md border-0"
                        :class="role === 'super' ? 'bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 shadow-indigo-600/10' : 'bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 shadow-emerald-600/10'"
                    >
                        <span class="material-symbols-outlined text-[16px]">login</span>
                        <span>Sign in to Dashboard</span>
                    </button>
                </div>
            </form>

            <!-- Divider -->
            <div class="relative flex items-center justify-center my-5">
                <div class="border-t border-slate-100 flex-grow"></div>
                <span class="px-3 text-[10px] text-slate-400 font-sans tracking-wider uppercase bg-white relative z-10 font-bold">or continue with</span>
                <div class="border-t border-slate-100 flex-grow"></div>
            </div>

            <!-- Social Integrations -->
            <div class="grid grid-cols-2 gap-2.5">
                <a href="#" class="flex items-center justify-center gap-2 py-2 border border-slate-200/80 hover:border-slate-300 hover:bg-slate-50/50 rounded-lg bg-white text-xs font-medium text-slate-600 hover:text-slate-800 transition-all shadow-sm">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24">
                        <path fill="#EA4335" d="M12 5.04c1.66 0 3.2.57 4.38 1.69l3.27-3.27C17.68 1.54 14.98 1 12 1 7.35 1 3.37 3.65 1.46 7.5l3.86 3C6.22 7.78 8.89 5.04 12 5.04z"/>
                        <path fill="#4285F4" d="M23.49 12.27c0-.81-.07-1.59-.2-2.36H12v4.47h6.46c-.28 1.48-1.12 2.74-2.38 3.58v2.98h3.84c2.25-2.07 3.57-5.12 3.57-8.67z"/>
                        <path fill="#FBBC05" d="M5.32 14.9C5.07 14.15 4.93 13.36 4.93 12.5s.14-1.65.39-2.4V7.1H1.46C.53 8.97 0 11.08 0 13.33s.53 4.36 1.46 6.23l3.86-3.66z"/>
                        <path fill="#34A853" d="M12 23c3.24 0 5.97-1.08 7.96-2.92l-3.84-2.98c-1.06.71-2.42 1.13-4.12 1.13-3.11 0-5.78-2.74-6.72-5.46l-3.86 3C3.37 19.85 7.35 23 12 23z"/>
                    </svg>
                    <span>Google</span>
                </a>
                <a href="#" class="flex items-center justify-center gap-2 py-2 border border-slate-200/80 hover:border-slate-300 hover:bg-slate-50/50 rounded-lg bg-white text-xs font-medium text-slate-600 hover:text-slate-800 transition-all shadow-sm">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 23 23">
                        <rect width="10" height="10" fill="#f25022"/>
                        <rect x="11" width="10" height="10" fill="#7fba00"/>
                        <rect y="11" width="10" height="10" fill="#00a4ef"/>
                        <rect x="11" y="11" width="10" height="10" fill="#ffb900"/>
                    </svg>
                    <span>Microsoft</span>
                </a>
            </div>

            <!-- Footer Action Links -->
            <div class="flex justify-between items-center text-xs border-t border-slate-100 pt-5 mt-6">
                <a href="{{ route('register') }}" class="text-slate-500 hover:text-slate-850 font-semibold transition-colors">Create an account</a>
                <a href="#" class="text-slate-400 hover:text-slate-600 transition-colors">Contact support</a>
            </div>

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
            <div class="hidden sm:inline-block w-1 h-1 rounded-full bg-slate-300"></div>
            <div class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[12px] text-slate-400 font-bold">verified_user</span>
                256-Bit SSL Secured
            </div>
        </div>

    </div>
</x-guest-layout>
