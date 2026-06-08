@extends('layouts.school')

@section('title', 'AuraCampus | Collect Fee')

@section('content')
<div class="max-w-2xl mx-auto" x-data="{
    selectedFee: '',
    amountPaid: '',
    feesMap: {
        @foreach($feeStructures as $fee)
        '{{ $fee->id }}': '{{ $fee->amount }}',
        @endforeach
    },
    updateAmount() {
        this.amountPaid = this.feesMap[this.selectedFee] || '';
    }
}">
    <!-- Back Navigation -->
    <a href="{{ route('school.fees.payments') }}" class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-800 transition-colors mb-6 font-bold">
        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
        Back to Collections History
    </a>

    <!-- Header -->
    <div class="mb-8">
        <h2 class="text-xl font-bold text-slate-900 tracking-tight font-sans">Collect Student Fees</h2>
        <p class="text-xs text-slate-500 mt-1">Record manual cash, UPI, or card collections, and generate unique receipt numbers.</p>
    </div>

    <!-- Collection Form Card -->
    <div class="premium-card p-6 rounded-2xl bg-white border border-slate-200/60 shadow-sm relative overflow-hidden">
        <div class="absolute -right-20 -top-20 w-48 h-48 bg-emerald-500/5 rounded-full blur-2xl"></div>

        <form method="POST" action="{{ route('school.fees.payments.store') }}">
            @csrf
            
            <div class="space-y-4">
                <!-- Select Student -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Select Student</label>
                    <select name="student_id" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald appearance-none cursor-pointer bg-white">
                        <option value="">Choose Student</option>
                        @foreach($students as $student)
                        <option value="{{ $student->id }}">
                            {{ $student->name }} 
                            @if($student->studentDetail?->class)
                                ({{ $student->studentDetail->class->name }} - {{ $student->studentDetail->class->section }})
                            @endif
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Select Fee Structure and prefill amount -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Fee Category</label>
                        <select name="fee_structure_id" required x-model="selectedFee" @change="updateAmount()" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald appearance-none cursor-pointer bg-white">
                            <option value="">Choose Category</option>
                            @foreach($feeStructures as $fee)
                            <option value="{{ $fee->id }}">
                                {{ $fee->name }} (₹{{ number_format($fee->amount, 0) }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Amount Paid (INR)</label>
                        <input type="number" name="amount_paid" x-model="amountPaid" placeholder="0.00" step="0.01" min="0.01" required
                               class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-bold focus:outline-none focus:premium-input-focus-emerald placeholder-slate-350">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Date -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Collection Date</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald">
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Payment Method</label>
                        <select name="payment_method" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald appearance-none cursor-pointer bg-white">
                            <option value="cash">Cash</option>
                            <option value="upi">UPI (GPay, PhonePe, Paytm)</option>
                            <option value="bank_transfer">Bank Transfer / NEFT</option>
                            <option value="card">Debit/Credit Card</option>
                        </select>
                    </div>
                </div>

                <!-- Remarks -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Remarks / Transaction Notes</label>
                    <input type="text" name="remarks" placeholder="Optional notes like UPI transaction ID"
                           class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald placeholder-slate-300">
                </div>
            </div>

            <!-- Submit -->
            <div class="mt-8 border-t border-slate-100 pt-6 flex justify-end gap-3">
                <a href="{{ route('school.fees.payments') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors">Cancel</a>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">
                    Record Collection
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
