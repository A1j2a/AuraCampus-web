@extends('layouts.superadmin')

@section('title', 'AuraCampus | Subscriptions')

@section('content')
<div x-data="{ showPlanModal: false, showAssignModal: false }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">SaaS Subscription Licensing</h2>
            <p class="text-xs text-slate-500 mt-1">Configure plan tiers, resource allocations, and manage active licenses across school zones.</p>
        </div>
        <div class="flex flex-wrap sm:flex-nowrap gap-2">
            <button @click="showPlanModal = true" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">
                Add Subscription Plan
            </button>
            <button @click="showAssignModal = true" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">
                Assign Plan to School
            </button>
        </div>
    </div>

    <!-- Success message -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-700 flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    <!-- Plans Cards -->
    <h3 class="text-sm font-bold text-slate-800 mb-4 uppercase tracking-wider font-mono">Available Plans</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        @forelse($plans as $plan)
        <div class="premium-card p-6 bg-white rounded-2xl shadow-sm border border-slate-200/60 relative overflow-hidden flex flex-col justify-between hover:shadow-md transition-all">
            <div>
                <h4 class="text-base font-extrabold text-slate-900 leading-none mb-1">{{ $plan->name }}</h4>
                <div class="my-4">
                    <span class="text-2xl font-black text-slate-900 font-mono">₹{{ number_format($plan->price, 0) }}</span>
                    <span class="text-[10px] text-slate-400 font-semibold">/ year</span>
                </div>
                <div class="space-y-2.5 my-6 text-xs text-slate-600 font-medium">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px] text-indigo-500">groups</span>
                        <span>Students Limit: <strong class="text-slate-800">{{ $plan->max_students == -1 ? 'Unlimited' : $plan->max_students }}</strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px] text-indigo-500">badge</span>
                        <span>Teachers Limit: <strong class="text-slate-800">{{ $plan->max_teachers == -1 ? 'Unlimited' : $plan->max_teachers }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-8 text-center text-slate-400 bg-white border rounded-2xl">
            No subscription plans configured. Create one to get started.
        </div>
        @endforelse
    </div>

    <!-- Active Subscriptions Table -->
    <h3 class="text-sm font-bold text-slate-800 mb-4 uppercase tracking-wider font-mono">Active Licenses</h3>
    <div class="premium-card rounded-2xl overflow-hidden bg-white border border-slate-200/60 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 font-mono text-[9px] text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-4">School / Campus</th>
                        <th class="px-6 py-4">Subscription Plan</th>
                        <th class="px-6 py-4">Price / Year</th>
                        <th class="px-6 py-4">Duration</th>
                        <th class="px-6 py-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($subscriptions as $sub)
                    <tr class="hover:bg-slate-50/50 transition-all duration-150">
                        <td class="px-6 py-4 font-bold text-slate-800">
                            {{ $sub->school?->name }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 bg-indigo-50 border border-indigo-150 text-indigo-700 text-[10px] font-bold rounded">
                                {{ $sub->plan?->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-mono text-slate-600 font-bold">
                            ₹{{ number_format($sub->plan?->price, 2) }}
                        </td>
                        <td class="px-6 py-4 font-mono text-slate-500 text-[10px]">
                            {{ $sub->start_date->format('d M Y') }} - {{ $sub->end_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $badge = $sub->status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-150' : 'bg-slate-50 text-slate-400 border-slate-200';
                            @endphp
                            <span class="px-2 py-0.5 border text-[9px] font-mono rounded font-bold uppercase {{ $badge }}">
                                {{ $sub->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-405 italic">
                            <span class="material-symbols-outlined text-4xl mb-3 block">receipt_long</span>
                            No schools assigned to active plans.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Plan Modal -->
    <div x-show="showPlanModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="showPlanModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 relative z-10 border border-slate-200/60 max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-base font-bold text-slate-900">Create Subscription Plan</h3>
                <button @click="showPlanModal = false" class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            
            <form method="POST" action="{{ route('superadmin.subscriptions.plan.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Plan Name</label>
                        <input type="text" name="name" placeholder="e.g. Growth Plan" required
                               class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-indigo placeholder-slate-350">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Price Per Year (INR)</label>
                        <input type="number" name="price" placeholder="e.g. 25000" step="0.01" min="0" required
                               class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-indigo placeholder-slate-350">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Max Students (-1 unlimited)</label>
                            <input type="number" name="max_students" placeholder="e.g. 500" required min="-1"
                                   class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-indigo">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Max Teachers (-1 unlimited)</label>
                            <input type="number" name="max_teachers" placeholder="e.g. 50" required min="-1"
                                   class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-indigo">
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showPlanModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">Save Plan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Assign Plan Modal -->
    <div x-show="showAssignModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="showAssignModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 relative z-10 border border-slate-200/60 max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-base font-bold text-slate-900">Assign Subscription to Campus</h3>
                <button @click="showAssignModal = false" class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            
            <form method="POST" action="{{ route('superadmin.subscriptions.school.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Select Campus School</label>
                        <select name="school_id" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-indigo appearance-none cursor-pointer bg-white">
                            <option value="">Select School</option>
                            @foreach($schools as $school)
                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Subscription Plan</label>
                        <select name="subscription_plan_id" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-indigo appearance-none cursor-pointer bg-white">
                            <option value="">Select Plan</option>
                            @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }} (₹{{ number_format($plan->price, 0) }} / year)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Start Date</label>
                            <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required
                                   class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-indigo">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">End Date</label>
                            <input type="date" name="end_date" value="{{ date('Y-m-d', strtotime('+1 year')) }}" required
                                   class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-indigo">
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showAssignModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">Save Allocation</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
