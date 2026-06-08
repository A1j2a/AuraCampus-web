@extends('layouts.school')

@section('title', 'AuraCampus | Help Desk')

@section('content')
<div x-data="{ openModal: false }" class="space-y-8">
    <!-- Header Area -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white/40 p-6 rounded-3xl border border-slate-200/60 backdrop-blur-md">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Help Desk Support</h1>
            <p class="text-xs text-slate-500 mt-1">Submit support requests and discuss platform inquiries directly with the AuraCampus technical team.</p>
        </div>
        <button @click.stop="openModal = true" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm flex items-center justify-center gap-2">
            <span class="material-symbols-outlined text-[18px]">add_circle</span>
            <span class="text-xs font-semibold">Open New Ticket</span>
        </button>
    </div>

    <!-- Tickets Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <span class="text-xs font-bold text-slate-900">Support Ticket Log</span>
            <span class="text-[10px] text-slate-400 font-mono">{{ $tickets->count() }} tickets recorded</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-150/60 bg-slate-50/30">
                        <th class="p-4 text-[10px] font-mono text-slate-400 uppercase tracking-wider">Ticket ID</th>
                        <th class="p-4 text-[10px] font-mono text-slate-400 uppercase tracking-wider">Subject</th>
                        <th class="p-4 text-[10px] font-mono text-slate-400 uppercase tracking-wider">Priority</th>
                        <th class="p-4 text-[10px] font-mono text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="p-4 text-[10px] font-mono text-slate-400 uppercase tracking-wider">Opened Date</th>
                        <th class="p-4 text-right text-[10px] font-mono text-slate-400 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tickets as $ticket)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="p-4 text-xs font-semibold text-slate-500 font-mono">#TKT-{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td class="p-4">
                            <div class="text-xs font-bold text-slate-800">{{ $ticket->subject }}</div>
                            <div class="text-[10px] text-slate-400 truncate max-w-xs">{{ $ticket->description }}</div>
                        </td>
                        <td class="p-4">
                            @if($ticket->priority === 'high')
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-50 text-rose-600 border border-rose-100 uppercase tracking-wider">High</span>
                            @elseif($ticket->priority === 'medium')
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-50 text-amber-600 border border-amber-100 uppercase tracking-wider">Medium</span>
                            @else
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase tracking-wider">Low</span>
                            @endif
                        </td>
                        <td class="p-4">
                            @if($ticket->status === 'open')
                                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-500 text-white uppercase tracking-wider shadow-sm">Open</span>
                            @elseif($ticket->status === 'pending')
                                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-sky-500 text-white uppercase tracking-wider shadow-sm">Pending</span>
                            @else
                                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-slate-400 text-white uppercase tracking-wider shadow-sm">Closed</span>
                            @endif
                        </td>
                        <td class="p-4 text-xs text-slate-500 font-mono">{{ $ticket->created_at->format('M d, Y h:i A') }}</td>
                        <td class="p-4 text-right">
                            <a href="{{ route('school.support.show', $ticket) }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-[10px] font-bold bg-slate-100 text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition-all cursor-pointer">
                                <span class="material-symbols-outlined text-[14px]">forum</span>
                                Discuss
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-xs text-slate-400 font-medium">No support tickets found. Click "Open New Ticket" to request help.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for opening a new ticket -->
    <div x-show="openModal" x-cloak class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div @click.away="openModal = false" class="bg-white rounded-3xl w-full max-w-lg shadow-2xl border border-slate-200/80 overflow-hidden transform transition-all duration-300">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900">Create Support Ticket</h3>
                <button @click="openModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <form action="{{ route('school.support.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-mono text-slate-400 uppercase tracking-widest mb-1.5 font-bold">Subject</label>
                    <input name="subject" required type="text" class="w-full px-4 py-2.5 premium-input rounded-xl focus:outline-none focus:premium-input-focus-emerald placeholder-slate-350 text-xs font-semibold" placeholder="Brief summary of the issue..."/>
                </div>

                <div>
                    <label class="block text-[10px] font-mono text-slate-400 uppercase tracking-widest mb-1.5 font-bold">Priority</label>
                    <select name="priority" required class="w-full px-4 py-2.5 premium-input rounded-xl focus:outline-none focus:premium-input-focus-emerald text-xs font-semibold cursor-pointer bg-white">
                        <option value="low">Low - General inquiry</option>
                        <option value="medium" selected>Medium - Functional blocker</option>
                        <option value="high">High - System / critical failure</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-mono text-slate-400 uppercase tracking-widest mb-1.5 font-bold">Description & Details</label>
                    <textarea name="description" required rows="4" class="w-full px-4 py-2.5 premium-input rounded-xl focus:outline-none focus:premium-input-focus-emerald placeholder-slate-350 text-xs font-semibold resize-none" placeholder="Provide detailed steps, error messages, or questions..."></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">Submit Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
