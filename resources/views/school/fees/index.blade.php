@extends('layouts.school')

@section('title', 'AuraCampus | Fee Structures')

@section('content')
<div x-data="{ showModal: {{ $errors->any() ? 'true' : 'false' }} }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight font-sans">Fees & Revenue Management</h2>
            <p class="text-xs text-slate-500 mt-1">Configure standard fee structures, track student invoices, and collect receipts.</p>
        </div>
        <button @click="showModal = true" class="px-4 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-[16px]">add_circle</span>
            Add Category
        </button>
    </div>

    <!-- Sub Navigation Tabs -->
    <div class="flex border-b border-slate-200 gap-6 mb-8 text-xs font-bold overflow-x-auto whitespace-nowrap scrollbar-none">
        <a href="{{ route('school.fees.payments') }}" class="pb-3 text-slate-500 hover:text-slate-900 transition-colors border-b-2 border-transparent">
            Collections History
        </a>
        <a href="{{ route('school.fees.index') }}" class="pb-3 text-violet-600 border-b-2 border-violet-500">
            Fee Categories
        </a>
        <a href="{{ route('school.fees.report') }}" class="pb-3 text-slate-500 hover:text-slate-900 transition-colors border-b-2 border-transparent">
            Revenue Analysis
        </a>
    </div>

    <!-- Alert -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-violet-50 border border-violet-200 rounded-xl text-xs font-semibold text-violet-700 flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    <!-- Fee Categories Table -->
    <div class="premium-card rounded-2xl overflow-hidden bg-white border border-slate-200/60 shadow-sm">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50">
            <span class="text-xs font-semibold text-slate-500">{{ $feeStructures->count() }} Fee Categories Configured</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 font-mono text-[9px] text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-4">Fee Name</th>
                        <th class="px-6 py-4">Frequency</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Applicable Classes</th>
                        <th class="px-6 py-4">Created Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($feeStructures as $structure)
                    <tr class="hover:bg-slate-50/50 transition-all duration-150">
                        <td class="px-6 py-4">
                            <span class="font-bold text-slate-800">{{ $structure->name }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 bg-indigo-50 border border-indigo-100 text-indigo-700 text-[10px] font-semibold rounded-lg capitalize">
                                {{ str_replace('_', ' ', $structure->frequency) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-mono font-bold text-slate-700">
                            ₹{{ number_format($structure->amount, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            @if(empty($structure->applicable_classes))
                            <span class="px-2 py-0.5 bg-slate-100 border border-slate-200 text-slate-600 text-[10px] font-semibold rounded-lg">
                                All Classes
                            </span>
                            @else
                            <div class="flex flex-wrap gap-1">
                                @foreach($classes as $c)
                                    @if(in_array($c->id, $structure->applicable_classes))
                                    <span class="px-1.5 py-0.5 bg-slate-100 text-slate-600 text-[9px] font-semibold rounded">
                                        {{ $c->name }}
                                    </span>
                                    @endif
                                @endforeach
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-400 font-mono text-[10px]">
                            {{ $structure->created_at->format('d M Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <span class="material-symbols-outlined text-slate-455 text-4xl mb-3">credit_card</span>
                            <h4 class="text-sm font-bold text-slate-800 mb-1">No Fee Categories</h4>
                            <p class="text-xs text-slate-500">Configure tuition, admission, and transportation fee standards.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 relative z-10 border border-slate-200/60 max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-base font-bold text-slate-900">Add Fee Category</h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            
            <form method="POST" action="{{ route('school.fees.store') }}">
                @csrf
                <div class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Fee Category Name</label>
                        <input type="text" name="name" placeholder="e.g. Tuition Fee - Q1" required
                               class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet placeholder-slate-300">
                    </div>

                    <!-- Amount & Frequency -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Amount (INR)</label>
                            <input type="number" name="amount" placeholder="e.g. 15000" step="0.01" min="0" required
                                   class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet placeholder-slate-300">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Frequency</label>
                            <select name="frequency" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet appearance-none cursor-pointer bg-white">
                                <option value="one_time">One-time payment</option>
                                <option value="monthly">Monthly billing</option>
                                <option value="quarterly">Quarterly billing</option>
                                <option value="annually">Annual billing</option>
                            </select>
                        </div>
                    </div>

                    <!-- Classes (multiple checkboxes) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-2">Applicable Classes (Optional - leave unselected for all)</label>
                        <div class="grid grid-cols-3 gap-2 bg-slate-50 p-4 border rounded-xl max-h-40 overflow-y-auto">
                            @foreach($classes as $c)
                            <label class="flex items-center gap-2 text-xs text-slate-600 font-medium cursor-pointer">
                                <input type="checkbox" name="applicable_classes[]" value="{{ $c->id }}" class="rounded text-violet-600 focus:ring-violet-500 h-3.5 w-3.5">
                                <span>{{ $c->name }} - {{ $c->section }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
