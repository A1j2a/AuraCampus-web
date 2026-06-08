@extends('layouts.superadmin')

@section('title', 'AuraCampus | Super Admin Dashboard')

@section('content')
    <!-- Dashboard Header -->
    <header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">System Overview</h2>
            <p class="text-xs text-slate-500 mt-1">Real-time performance metrics for the AuraCampus ecosystem.</p>
        </div>
        <div class="px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 text-[10px] font-mono text-slate-500 flex items-center gap-2 shadow-sm">
            <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 animate-pulse shadow-[0_0_6px_#4f46e5]"></span>
            LIVE ECOSYSTEM MONITOR
        </div>
    </header>

    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Card 1: Total Schools -->
        <div class="premium-card p-6 rounded-2xl hover:scale-[1.01] hover:shadow-md transition-all duration-300 relative group overflow-hidden cursor-pointer">
            <div class="absolute -right-10 -top-10 w-24 h-24 bg-indigo-500/5 rounded-full blur-2xl group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="p-2 bg-indigo-50 border border-indigo-100 text-indigo-600 rounded-xl shadow-sm">
                    <span class="material-symbols-outlined text-[20px]" data-icon="school">school</span>
                </div>
                <span class="flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-150 px-2 py-0.5 rounded-full">
                    <span class="material-symbols-outlined text-[11px] font-bold" data-icon="trending_up">trending_up</span>
                    +12%
                </span>
            </div>
            <p class="text-[11px] text-slate-400 font-semibold tracking-wide uppercase font-mono">Total Schools</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-1 tracking-tight">1,284</h3>
            <div class="mt-4 h-1 w-full bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-indigo-600" style="width: 75%"></div>
            </div>
        </div>

        <!-- Card 2: Active Students -->
        <div class="premium-card p-6 rounded-2xl hover:scale-[1.01] hover:shadow-md transition-all duration-300 relative group overflow-hidden cursor-pointer">
            <div class="absolute -right-10 -top-10 w-24 h-24 bg-purple-500/5 rounded-full blur-2xl group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="p-2 bg-purple-50 border border-purple-100 text-purple-600 rounded-xl shadow-sm">
                    <span class="material-symbols-outlined text-[20px]" data-icon="group">group</span>
                </div>
                <span class="flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-150 px-2 py-0.5 rounded-full">
                    <span class="material-symbols-outlined text-[11px] font-bold" data-icon="trending_up">trending_up</span>
                    +8%
                </span>
            </div>
            <p class="text-[11px] text-slate-400 font-semibold tracking-wide uppercase font-mono">Active Students</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-1 tracking-tight">45.2k</h3>
            <div class="mt-4 h-1 w-full bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-purple-600" style="width: 60%"></div>
            </div>
        </div>

        <!-- Card 3: Monthly Revenue -->
        <div class="premium-card p-6 rounded-2xl hover:scale-[1.01] hover:shadow-md transition-all duration-300 relative group overflow-hidden cursor-pointer">
            <div class="absolute -right-10 -top-10 w-24 h-24 bg-cyan-500/5 rounded-full blur-2xl group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="p-2 bg-cyan-50 border border-cyan-100 text-cyan-600 rounded-xl shadow-sm">
                    <span class="material-symbols-outlined text-[20px]" data-icon="payments">payments</span>
                </div>
                <span class="flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-150 px-2 py-0.5 rounded-full">
                    <span class="material-symbols-outlined text-[11px] font-bold" data-icon="trending_up">trending_up</span>
                    +18%
                </span>
            </div>
            <p class="text-[11px] text-slate-400 font-semibold tracking-wide uppercase font-mono">Monthly Revenue</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-1 tracking-tight">$2.4M</h3>
            <div class="mt-4 h-1 w-full bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-cyan-600" style="width: 85%"></div>
            </div>
        </div>

        <!-- Card 4: Open Tickets -->
        <div class="premium-card p-6 rounded-2xl hover:scale-[1.01] hover:shadow-md transition-all duration-300 relative group overflow-hidden cursor-pointer">
            <div class="absolute -right-10 -top-10 w-24 h-24 bg-rose-500/5 rounded-full blur-2xl group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="p-2 bg-rose-50 border border-rose-100 text-rose-600 rounded-xl shadow-sm">
                    <span class="material-symbols-outlined text-[20px]" data-icon="confirmation_number">confirmation_number</span>
                </div>
                <span class="flex items-center gap-1 text-[10px] font-bold text-rose-700 bg-rose-50 border border-rose-100 px-2 py-0.5 rounded-full">
                    <span class="material-symbols-outlined text-[11px] font-bold" data-icon="trending_down">trending_down</span>
                    -4%
                </span>
            </div>
            <p class="text-[11px] text-slate-400 font-semibold tracking-wide uppercase font-mono">Open Tickets</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-1 tracking-tight">42</h3>
            <div class="mt-4 h-1 w-full bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-rose-600" style="width: 40%"></div>
            </div>
        </div>
    </div>

    <!-- Bento Grid Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Activity Timeline (Span 2) -->
        <div class="lg:col-span-2 premium-card rounded-2xl p-6 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-6">
                    <h4 class="text-sm font-bold text-slate-900">System Feed & Activities</h4>
                    <button class="text-indigo-600 hover:text-indigo-700 text-xs font-semibold transition-colors">View System Logs</button>
                </div>
                <div class="space-y-6 relative before:absolute before:left-[19px] before:top-2 before:bottom-2 before:w-[1px] before:bg-slate-100">
                    <!-- Activity Item 1 -->
                    <div class="flex gap-4 relative">
                        <div class="w-10 h-10 rounded-xl bg-cyan-50 border border-cyan-100 text-cyan-600 flex items-center justify-center z-10 shrink-0 shadow-sm">
                            <span class="material-symbols-outlined text-[18px]" data-icon="add_business">add_business</span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-800">St. Mary's International School joined</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">Onboarded under Enterprise plan with 4 dedicated administrator accounts.</p>
                            <span class="text-[9px] text-slate-400 mt-1.5 block font-mono">2 mins ago</span>
                        </div>
                    </div>
                    <!-- Activity Item 2 -->
                    <div class="flex gap-4 relative">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center z-10 shrink-0 shadow-sm">
                            <span class="material-symbols-outlined text-[18px]" data-icon="paid">paid</span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-800">Revenue Milestone Achieved</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">Global subscription billing revenue passed the $2.4M milestone for this quarter.</p>
                            <span class="text-[9px] text-slate-400 mt-1.5 block font-mono">1 hour ago</span>
                        </div>
                    </div>
                    <!-- Activity Item 3 -->
                    <div class="flex gap-4 relative">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 border border-purple-100 text-purple-600 flex items-center justify-center z-10 shrink-0 shadow-sm">
                            <span class="material-symbols-outlined text-[18px]" data-icon="security_update_good">security_update_good</span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-800">Security Hotfix Patch v4.8.2 Deployed</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">Successfully deployed to all application server nodes. Zero downtime reported.</p>
                            <span class="text-[9px] text-slate-400 mt-1.5 block font-mono">4 hours ago</span>
                        </div>
                    </div>
                    <!-- Activity Item 4 -->
                    <div class="flex gap-4 relative">
                        <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center z-10 shrink-0 shadow-sm">
                            <span class="material-symbols-outlined text-[18px]" data-icon="person_alert">person_alert</span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-800">Blocked login attempt from IP 192.168.1.45</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">Suspicious activities flagged and temporary IP ban applied to access node.</p>
                            <span class="text-[9px] text-slate-400 mt-1.5 block font-mono">Yesterday, 11:45 PM</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Panel -->
        <div class="premium-card rounded-2xl p-6 flex flex-col justify-between gap-6">
            <div>
                <h4 class="text-sm font-bold text-slate-900 mb-4">Quick Operations</h4>
                <div class="grid grid-cols-1 gap-3">
                    <button class="flex items-center gap-3.5 p-3 rounded-xl border border-slate-100 hover:border-indigo-500/30 hover:bg-slate-50/50 transition-all group text-left cursor-pointer bg-slate-50/20">
                        <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-indigo-50 group-hover:text-[#4f46e5] transition-all shrink-0">
                            <span class="material-symbols-outlined text-[20px]" data-icon="add_circle">add_circle</span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-800">Register New School</p>
                            <p class="text-[10px] text-slate-450">Onboard a campus node</p>
                        </div>
                    </button>
                    <button class="flex items-center gap-3.5 p-3 rounded-xl border border-slate-100 hover:border-emerald-500/30 hover:bg-slate-50/50 transition-all group text-left cursor-pointer bg-slate-50/20">
                        <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-all shrink-0">
                            <span class="material-symbols-outlined text-[20px]" data-icon="analytics">analytics</span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-800">Generate Audit Report</p>
                            <p class="text-[10px] text-slate-450">Export system stats PDF</p>
                        </div>
                    </button>
                    <button class="flex items-center gap-3.5 p-3 rounded-xl border border-slate-100 hover:border-cyan-500/30 hover:bg-slate-50/50 transition-all group text-left cursor-pointer bg-slate-50/20">
                        <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-cyan-50 group-hover:text-cyan-600 transition-all shrink-0">
                            <span class="material-symbols-outlined text-[20px]" data-icon="campaign">campaign</span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-800">Ecosystem Broadcast</p>
                            <p class="text-[10px] text-slate-450">Global alert to all schools</p>
                        </div>
                    </button>
                    <button class="flex items-center gap-3.5 p-3 rounded-xl border border-slate-100 hover:border-rose-500/30 hover:bg-slate-50/50 transition-all group text-left cursor-pointer bg-slate-50/20">
                        <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-rose-50 group-hover:text-rose-600 transition-all shrink-0">
                            <span class="material-symbols-outlined text-[20px]" data-icon="security">security</span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-800">Core System Logs</p>
                            <p class="text-[10px] text-slate-450">Review server nodes access</p>
                        </div>
                    </button>
                </div>
            </div>
            
            <div class="p-5 bg-slate-50 rounded-xl border border-slate-200/80 relative overflow-hidden group cursor-pointer">
                <div class="absolute -right-6 -top-6 w-20 h-20 bg-indigo-500/5 rounded-full group-hover:scale-125 transition-transform duration-500 blur-xl"></div>
                <h5 class="text-xs font-bold text-slate-900 relative z-10 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[#4f46e5] text-[18px]">workspace_premium</span>
                    Premium Integrations
                </h5>
                <p class="text-[11px] text-slate-500 mt-1.5 relative z-10 leading-normal">Configure centralized payment gateways & custom school domains.</p>
                <button class="mt-4 w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg transition-colors relative z-10 shadow-sm">
                    Enterprise Portal
                </button>
            </div>
        </div>
    </div>

    <!-- School Status Table -->
    <div class="mt-8 premium-card rounded-2xl overflow-hidden bg-white">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div>
                <h4 class="text-sm font-bold text-slate-900">School Onboarding & Node Status</h4>
                <p class="text-xs text-slate-500 mt-0.5">Ecosystem nodes registry detailing subscriptions, capacity, and sync states.</p>
            </div>
            <div class="flex gap-2">
                <button class="p-2 border border-slate-200 hover:bg-slate-50 rounded-xl transition-all text-slate-500 hover:text-slate-900 flex items-center justify-center bg-white cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]" data-icon="filter_list">filter_list</span>
                </button>
                <button class="p-2 border border-slate-200 hover:bg-slate-50 rounded-xl transition-all text-slate-500 hover:text-slate-900 flex items-center justify-center bg-white cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]" data-icon="download">download</span>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/30">
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">School Workspace</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Plan License</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Registered Nodes</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Node Status</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Renewal Term</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr class="hover:bg-slate-50/10 transition-all duration-150">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center font-bold text-indigo-600 text-xs">GH</div>
                                <div>
                                    <span class="text-xs font-bold text-slate-800">Global Heights Academy</span>
                                    <p class="text-[9px] text-slate-400 font-mono mt-0.5">ID: node-gh-829</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 bg-indigo-50 text-indigo-700 border border-indigo-100 text-[9px] font-bold rounded-full uppercase tracking-wider font-mono">Enterprise</span>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-600">2,450 Students</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1.5 text-emerald-600 font-semibold text-xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_4px_#10b981]"></span>
                                <span>Active</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-500">Nov 24, 2026</td>
                        <td class="px-6 py-4 text-right">
                            <button class="material-symbols-outlined text-slate-400 hover:text-slate-600 transition-colors cursor-pointer" data-icon="more_vert">more_vert</button>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50/10 transition-all duration-150">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center font-bold text-amber-600 text-xs">LV</div>
                                <div>
                                    <span class="text-xs font-bold text-slate-800">Lake View High</span>
                                    <p class="text-[9px] text-slate-400 font-mono mt-0.5">ID: node-lv-103</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 bg-slate-100 text-slate-600 border border-slate-200 text-[9px] font-bold rounded-full uppercase tracking-wider font-mono">Basic</span>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-600">890 Students</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1.5 text-amber-600 font-semibold text-xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                <span>Syncing Data</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-500">Jan 12, 2027</td>
                        <td class="px-6 py-4 text-right">
                            <button class="material-symbols-outlined text-slate-400 hover:text-slate-600 transition-colors cursor-pointer" data-icon="more_vert">more_vert</button>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50/10 transition-all duration-150">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-purple-50 border border-purple-100 flex items-center justify-center font-bold text-purple-600 text-xs">SC</div>
                                <div>
                                    <span class="text-xs font-bold text-slate-800">Summit Collegiate</span>
                                    <p class="text-[9px] text-slate-400 font-mono mt-0.5">ID: node-sc-442</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 bg-purple-50 text-purple-750 border border-purple-100 text-[9px] font-bold rounded-full uppercase tracking-wider font-mono">Professional</span>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-600">1,120 Students</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1.5 text-rose-600 font-semibold text-xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shadow-[0_0_4px_#f43f5e]"></span>
                                <span>Grace Period</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-500">Oct 01, 2026</td>
                        <td class="px-6 py-4 text-right">
                            <button class="material-symbols-outlined text-slate-400 hover:text-slate-600 transition-colors cursor-pointer" data-icon="more_vert">more_vert</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
