@extends('layouts.school')

@section('title', 'AuraCampus | Fee Report')

@section('content')
<div>
    <!-- Header -->
    <div class="mb-8">
        <h2 class="text-xl font-bold text-slate-900 tracking-tight font-sans">Fees & Revenue Management</h2>
        <p class="text-xs text-slate-500 mt-1">Configure standard fee structures, track student invoices, and collect receipts.</p>
    </div>

    <!-- Sub Navigation Tabs -->
    <div class="flex border-b border-slate-200 gap-6 mb-8 text-xs font-bold overflow-x-auto whitespace-nowrap scrollbar-none">
        <a href="{{ route('school.fees.payments') }}" class="pb-3 text-slate-500 hover:text-slate-900 transition-colors border-b-2 border-transparent">
            Collections History
        </a>
        <a href="{{ route('school.fees.index') }}" class="pb-3 text-slate-500 hover:text-slate-900 transition-colors border-b-2 border-transparent">
            Fee Categories
        </a>
        <a href="{{ route('school.fees.report') }}" class="pb-3 text-violet-600 border-b-2 border-violet-500">
            Revenue Analysis
        </a>
    </div>

    <!-- Metrics Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Total Collected -->
        <div class="premium-card p-6 bg-white rounded-2xl border border-slate-200/60 shadow-sm relative overflow-hidden">
            <div class="absolute -right-10 -top-10 w-24 h-24 bg-violet-500/5 rounded-full blur-xl"></div>
            <span class="block text-[9px] font-mono text-slate-400 uppercase tracking-wider">Total Collection</span>
            <span class="block text-xl font-black text-slate-900 mt-2 font-mono">₹{{ number_format($totalCollected, 2) }}</span>
            <div class="flex items-center gap-1 text-[9px] text-violet-600 font-bold mt-2">
                <span class="material-symbols-outlined text-[12px]">trending_up</span>
                <span>Cumulative Revenue</span>
            </div>
        </div>

        <!-- UPI -->
        <div class="premium-card p-6 bg-white rounded-2xl border border-slate-200/60 shadow-sm relative overflow-hidden">
            <div class="absolute -right-10 -top-10 w-24 h-24 bg-indigo-500/5 rounded-full blur-xl"></div>
            <span class="block text-[9px] font-mono text-slate-400 uppercase tracking-wider">UPI Collections</span>
            <span class="block text-xl font-black text-slate-900 mt-2 font-mono">₹{{ number_format($upiCollected, 2) }}</span>
            <span class="block text-[9px] text-slate-450 mt-2 font-medium">Digital payments gateway</span>
        </div>

        <!-- Cash -->
        <div class="premium-card p-6 bg-white rounded-2xl border border-slate-200/60 shadow-sm relative overflow-hidden">
            <div class="absolute -right-10 -top-10 w-24 h-24 bg-teal-500/5 rounded-full blur-xl"></div>
            <span class="block text-[9px] font-mono text-slate-400 uppercase tracking-wider">Cash Collections</span>
            <span class="block text-xl font-black text-slate-900 mt-2 font-mono">₹{{ number_format($cashCollected, 2) }}</span>
            <span class="block text-[9px] text-slate-450 mt-2 font-medium">Direct counter deposits</span>
        </div>

        <!-- Bank & Card -->
        <div class="premium-card p-6 bg-white rounded-2xl border border-slate-200/60 shadow-sm relative overflow-hidden">
            <div class="absolute -right-10 -top-10 w-24 h-24 bg-amber-500/5 rounded-full blur-xl"></div>
            <span class="block text-[9px] font-mono text-slate-400 uppercase tracking-wider">Bank & Cards</span>
            <span class="block text-xl font-black text-slate-900 mt-2 font-mono">₹{{ number_format($bankCollected + $cardCollected, 2) }}</span>
            <span class="block text-[9px] text-slate-450 mt-2 font-medium">Direct bank wire & POS</span>
        </div>
    </div>

    <!-- Collection breakdown by Class -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Table -->
        <div class="col-span-2 premium-card rounded-2xl overflow-hidden bg-white border border-slate-200/60 shadow-sm">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50">
                <span class="text-xs font-bold text-slate-800">Collections Breakdown by Class</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 font-mono text-[9px] text-slate-400 uppercase tracking-wider">
                            <th class="px-6 py-4">Classroom Name</th>
                            <th class="px-6 py-4 text-right">Revenue Collected</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($classCollections as $classColl)
                        <tr class="hover:bg-slate-50/50 transition-all duration-150">
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-800">{{ $classColl['name'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-right font-mono font-bold text-slate-700">
                                ₹{{ number_format($classColl['amount'], 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="px-6 py-8 text-center text-slate-450 italic">No classroom collection breakdown logs recorded.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Secondary card displaying payment methods mix -->
        <div class="premium-card p-6 bg-white rounded-2xl border border-slate-200/60 shadow-sm">
            <h4 class="text-xs font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Payment Modes Mix</h4>
            <div class="space-y-4">
                @php
                    $total = $totalCollected > 0 ? $totalCollected : 1;
                    $upiPct = ($upiCollected / $total) * 100;
                    $cashPct = ($cashCollected / $total) * 100;
                    $bankPct = (($bankCollected + $cardCollected) / $total) * 100;
                @endphp
                
                <!-- UPI -->
                <div>
                    <div class="flex justify-between text-[11px] font-semibold text-slate-600 mb-1.5">
                        <span>UPI Payments</span>
                        <span class="font-mono">{{ round($upiPct, 1) }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div class="bg-indigo-500 bg-indigo-500 h-full rounded-full" style="width: {{ $upiPct }}%"></div>
                    </div>
                </div>

                <!-- Cash -->
                <div>
                    <div class="flex justify-between text-[11px] font-semibold text-slate-600 mb-1.5">
                        <span>Cash at Counter</span>
                        <span class="font-mono">{{ round($cashPct, 1) }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div class="bg-violet-500 bg-violet-500 h-full rounded-full" style="width: {{ $cashPct }}%"></div>
                    </div>
                </div>

                <!-- Bank / Cards -->
                <div>
                    <div class="flex justify-between text-[11px] font-semibold text-slate-600 mb-1.5">
                        <span>Bank Wires & Cards</span>
                        <span class="font-mono">{{ round($bankPct, 1) }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div class="bg-amber-500 bg-amber-500 h-full rounded-full" style="width: {{ $bankPct }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
