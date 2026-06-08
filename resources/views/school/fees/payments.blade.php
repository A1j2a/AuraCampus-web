@extends('layouts.school')

@section('title', 'AuraCampus | Fee Collections')

@section('content')
<div>
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight font-sans">Fees & Revenue Management</h2>
            <p class="text-xs text-slate-500 mt-1">Configure standard fee structures, track student invoices, and collect receipts.</p>
        </div>
        <a href="{{ route('school.fees.collect') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-[16px]">payments</span>
            Collect Fee
        </a>
    </div>

    <!-- Sub Navigation Tabs -->
    <div class="flex border-b border-slate-200 gap-6 mb-8 text-xs font-bold overflow-x-auto whitespace-nowrap scrollbar-none">
        <a href="{{ route('school.fees.payments') }}" class="pb-3 text-emerald-600 border-b-2 border-emerald-500">
            Collections History
        </a>
        <a href="{{ route('school.fees.index') }}" class="pb-3 text-slate-500 hover:text-slate-900 transition-colors border-b-2 border-transparent">
            Fee Categories
        </a>
        <a href="{{ route('school.fees.report') }}" class="pb-3 text-slate-500 hover:text-slate-900 transition-colors border-b-2 border-transparent">
            Revenue Analysis
        </a>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-700 flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    <!-- Filters -->
    <div class="premium-card p-6 bg-white border border-slate-200/60 shadow-sm rounded-2xl mb-6">
        <form method="GET" action="{{ route('school.fees.payments') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <!-- Filter Class -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Filter Classroom</label>
                <select name="class_id" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-semibold focus:outline-none focus:premium-input-focus-emerald appearance-none cursor-pointer bg-white">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                        {{ $class->name }} - {{ $class->section }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Payment Status</label>
                <select name="status" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-semibold focus:outline-none focus:premium-input-focus-emerald appearance-none cursor-pointer bg-white">
                    <option value="">All Statuses</option>
                    <option value="paid" {{ $selectedStatus == 'paid' ? 'selected' : '' }}>Fully Paid</option>
                    <option value="partial" {{ $selectedStatus == 'partial' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="pending" {{ $selectedStatus == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>

            <!-- Submit button -->
            <div>
                <button type="submit" class="w-full px-5 py-2.5 bg-slate-50 border border-slate-200/60 hover:bg-slate-100 text-slate-700 text-xs font-bold rounded-xl transition-all cursor-pointer flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">filter_list</span>
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Payments List Table -->
    <div class="premium-card rounded-2xl overflow-hidden bg-white border border-slate-200/60 shadow-sm">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <span class="text-xs font-semibold text-slate-500">{{ $payments->count() }} Transactions Recorded</span>
            <button onclick="exportTableToCSV('fee-collections.csv')" class="px-3 py-1 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-[10px] font-bold rounded-lg cursor-pointer transition-all flex items-center gap-1">
                <span class="material-symbols-outlined text-[13px]">download</span>
                Export CSV
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 font-mono text-[9px] text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-4">Receipt #</th>
                        <th class="px-6 py-4">Student</th>
                        <th class="px-6 py-4">Class</th>
                        <th class="px-6 py-4">Category</th>
                        <th class="px-6 py-4">Amount Paid</th>
                        <th class="px-6 py-4">Payment Date</th>
                        <th class="px-6 py-4">Method</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $payment)
                    <tr class="hover:bg-slate-50/50 transition-all duration-150">
                        <td class="px-6 py-4 font-mono font-bold text-slate-600">
                            {{ $payment->receipt_number }}
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-800">
                            {{ $payment->student?->name }}
                        </td>
                        <td class="px-6 py-4 text-slate-500 font-semibold">
                            {{ $payment->student?->studentDetail?->class ? $payment->student->studentDetail->class->name . '-' . $payment->student->studentDetail->class->section : '—' }}
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-600">
                            {{ $payment->feeStructure?->name }}
                        </td>
                        <td class="px-6 py-4 font-mono font-bold text-slate-800">
                            ₹{{ number_format($payment->amount_paid, 2) }}
                        </td>
                        <td class="px-6 py-4 text-slate-400 font-mono text-[10px]">
                            {{ $payment->payment_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 font-mono text-[10px] uppercase text-slate-500 font-semibold">
                            {{ str_replace('_', ' ', $payment->payment_method) }}
                        </td>
                        <td class="px-6 py-4">
                            @if($payment->status === 'paid')
                            <span class="px-2 py-0.5 bg-emerald-50 border border-emerald-150 text-emerald-700 text-[9px] font-bold rounded font-mono uppercase">Paid</span>
                            @else
                            <span class="px-2 py-0.5 bg-amber-50 border border-amber-150 text-amber-700 text-[9px] font-bold rounded font-mono uppercase">Partial</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('school.fees.receipt', $payment) }}" 
                               target="_blank"
                               class="px-2.5 py-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200/50 rounded-lg transition-all cursor-pointer inline-flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px]">print</span>
                                Receipt
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <span class="material-symbols-outlined text-slate-455 text-4xl mb-3">receipt_long</span>
                            <h4 class="text-sm font-bold text-slate-800 mb-1">No Collection Records</h4>
                            <p class="text-xs text-slate-500">Collect a payment above to see history logs.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
