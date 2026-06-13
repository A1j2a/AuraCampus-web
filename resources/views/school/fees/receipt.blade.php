<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $payment->receipt_number }}</title>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <!-- TailwindCSS for styling -->
    @vite(['resources/css/app.css'])
    
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
            }
            .receipt-container {
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-800 p-4 md:p-8">

    <!-- Top Action bar (no-print) -->
    <div class="max-w-xl mx-auto mb-6 flex justify-between items-center no-print">
        <button onclick="window.close()" class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-800 transition-colors font-bold cursor-pointer">
            <span class="material-symbols-outlined text-[16px]">close</span>
            Close
        </button>
        <button onclick="window.print()" class="px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-[16px]">print</span>
            Print Receipt
        </button>
    </div>

    <!-- Printable Receipt Container -->
    <div class="max-w-xl mx-auto bg-white border border-slate-200 shadow-md rounded-2xl p-6 md:p-8 receipt-container">
        
        <!-- School Info Header -->
        <div class="flex items-center justify-between border-b pb-4 mb-6 gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-white text-base font-black shrink-0">
                    {{ strtoupper(substr($payment->school->name, 0, 2)) }}
                </div>
                <div>
                    <h1 class="text-sm font-black text-slate-900 tracking-tight uppercase leading-none">{{ $payment->school->name }}</h1>
                    <span class="text-[8px] text-slate-450 font-medium block mt-1 leading-normal max-w-[280px]">
                        {{ $payment->school->address }}
                    </span>
                </div>
            </div>
            <div class="text-right font-mono text-[8px] text-slate-400 leading-normal">
                <div>Receipt: <span class="text-slate-800 font-bold">{{ $payment->receipt_number }}</span></div>
                <div>Date: {{ $payment->payment_date->format('d M Y') }}</div>
            </div>
        </div>

        <div class="text-center mb-6">
            <h2 class="text-xs font-bold tracking-widest text-slate-850 uppercase underline decoration-2 decoration-violet-500 underline-offset-2">
                FEE TRANSACTION RECEIPT
            </h2>
        </div>

        <!-- Student Info -->
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mb-6 text-xs space-y-2">
            <div class="flex justify-between">
                <span class="text-slate-450 font-mono text-[9px] uppercase tracking-wider">Student Name</span>
                <span class="font-bold text-slate-900">{{ $payment->student?->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-450 font-mono text-[9px] uppercase tracking-wider">Admission / Roll #</span>
                <span class="font-mono text-slate-800">
                    {{ $payment->student?->studentDetail?->admission_number ?? '—' }} / {{ $payment->student?->studentDetail?->roll_number ?? '—' }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-450 font-mono text-[9px] uppercase tracking-wider">Classroom & Section</span>
                <span class="font-bold text-slate-800">
                    {{ $payment->student?->studentDetail?->class ? $payment->student->studentDetail->class->name . ' - ' . $payment->student->studentDetail->class->section : '—' }}
                </span>
            </div>
        </div>

        <!-- Invoice ledger table -->
        <div class="border border-slate-200 rounded-xl overflow-hidden mb-6 text-xs">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-mono text-[8px] uppercase tracking-wider">
                        <th class="px-4 py-2.5">Category Description</th>
                        <th class="px-4 py-2.5 text-right">Invoice Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="px-4 py-3">
                            <span class="font-bold text-slate-800">{{ $payment->feeStructure?->name }}</span>
                            <span class="block text-[8px] text-slate-400 font-mono uppercase mt-0.5">Billing frequency: {{ $payment->feeStructure?->frequency }}</span>
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-slate-600">
                            ₹{{ number_format($payment->feeStructure?->amount, 2) }}
                        </td>
                    </tr>
                    <tr class="bg-slate-900 text-white font-bold">
                        <td class="px-4 py-3 font-mono text-[9px] uppercase tracking-wider">Amount Paid</td>
                        <td class="px-4 py-3 text-right font-mono">
                            ₹{{ number_format($payment->amount_paid, 2) }}
                        </td>
                    </tr>
                    @php
                        $balance = $payment->feeStructure->amount - $payment->amount_paid;
                    @endphp
                    @if($balance > 0)
                    <tr class="bg-rose-50 text-rose-800 font-bold">
                        <td class="px-4 py-2.5 font-mono text-[9px] uppercase tracking-wider">Pending Balance</td>
                        <td class="px-4 py-2.5 text-right font-mono">
                            ₹{{ number_format($balance, 2) }}
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Transaction specifications -->
        <div class="grid grid-cols-2 gap-4 text-xs mb-8">
            <div>
                <span class="block text-slate-450 font-mono text-[8px] uppercase tracking-wider">Payment Method</span>
                <span class="font-bold text-slate-800 uppercase font-mono mt-0.5 block">{{ $payment->payment_method }}</span>
            </div>
            <div>
                <span class="block text-slate-450 font-mono text-[8px] uppercase tracking-wider">Transaction Note</span>
                <span class="font-medium text-slate-600 mt-0.5 block italic">{{ $payment->remarks ?? 'No reference logs' }}</span>
            </div>
        </div>

        <!-- Signatures and stamp -->
        <div class="grid grid-cols-2 gap-8 text-center text-[10px] mt-12 pt-6 border-t border-slate-100">
            <div>
                <span class="block text-slate-400">Recorded By</span>
                <span class="block font-bold text-slate-800 mt-1">{{ $payment->collector?->name ?? 'System' }}</span>
            </div>
            <div>
                <div class="h-6 flex items-end justify-center mb-1">
                    <div class="w-24 border-b border-slate-205"></div>
                </div>
                <span class="block font-bold text-slate-800">Authorized Signatory</span>
            </div>
        </div>

    </div>
</body>
</html>
