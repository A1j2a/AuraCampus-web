@extends('layouts.superadmin')

@section('title', 'AuraCampus | Support Desk')

@section('content')
<div class="premium-card p-8 rounded-2xl relative overflow-hidden bg-white border border-slate-200/60 shadow-sm">
    <div class="absolute -right-20 -top-20 w-48 h-48 bg-purple-500/5 rounded-full blur-2xl"></div>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Support Desk Queue</h2>
            <p class="text-xs text-slate-500 mt-1">Review system support tickets submitted by school campus administrators and update statuses.</p>
        </div>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-700 flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    <!-- Support Tickets Table -->
    <div class="overflow-x-auto border border-slate-100 rounded-xl bg-white">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100 font-mono text-[9px] text-slate-400 uppercase tracking-wider">
                    <th class="px-6 py-4">Ticket ID</th>
                    <th class="px-6 py-4">Campus School</th>
                    <th class="px-6 py-4">Subject & Description</th>
                    <th class="px-6 py-4">Priority</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Update Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($tickets as $ticket)
                @php
                    $priorityColor = 'bg-slate-50 text-slate-600 border-slate-200';
                    if ($ticket->priority === 'high') {
                        $priorityColor = 'bg-rose-50 text-rose-700 border-rose-150';
                    } elseif ($ticket->priority === 'medium') {
                        $priorityColor = 'bg-amber-50 text-amber-700 border-amber-150';
                    }
                    
                    $statusColor = 'bg-slate-50 text-slate-400 border-slate-200';
                    if ($ticket->status === 'open') {
                        $statusColor = 'bg-indigo-50 text-indigo-700 border-indigo-150';
                    } elseif ($ticket->status === 'pending') {
                        $statusColor = 'bg-blue-50 text-blue-700 border-blue-150';
                    } elseif ($ticket->status === 'closed') {
                        $statusColor = 'bg-emerald-50 text-emerald-700 border-emerald-150';
                    }
                @endphp
                <tr class="hover:bg-slate-50/50 transition-all duration-150">
                    <td class="px-6 py-4 font-mono font-bold text-slate-600">
                        #TKT-{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-bold text-slate-800">{{ $ticket->school?->name }}</span>
                        <span class="block text-[9px] text-slate-400 font-mono mt-0.5">By: {{ $ticket->user?->name }}</span>
                    </td>
                    <td class="px-6 py-4 max-w-sm">
                        <div class="font-bold text-slate-850 mb-1 leading-snug">{{ $ticket->subject }}</div>
                        <p class="text-[10px] text-slate-500 leading-normal">{{ $ticket->description }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 border text-[9px] font-mono rounded font-bold uppercase {{ $priorityColor }}">
                            {{ $ticket->priority }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 border text-[9px] font-mono rounded font-bold uppercase {{ $statusColor }}">
                            {{ $ticket->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="inline-flex items-center justify-end gap-3">
                            <form method="POST" action="{{ route('superadmin.support.update', $ticket) }}" class="inline-flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="px-2 py-1 bg-white border border-slate-200 rounded-lg text-[10px] font-bold text-slate-700 cursor-pointer focus:outline-none focus:border-indigo-500">
                                    <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="pending" {{ $ticket->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </form>
                            <a href="{{ route('superadmin.support.show', $ticket) }}" class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg text-[10px] font-bold transition">
                                <span class="material-symbols-outlined text-[13px]">forum</span>
                                Discuss
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-405 italic">
                        <span class="material-symbols-outlined text-4xl mb-3 block">help_center</span>
                        No support tickets submitted.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
