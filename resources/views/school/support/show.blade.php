@extends('layouts.school')

@section('title', 'AuraCampus | Help Desk Thread')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Back Header -->
    <div class="flex items-center justify-between">
        <a href="{{ route('school.support.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-900 transition-colors">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            Back to Tickets List
        </a>
        <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-slate-400 font-mono">#TKT-{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }}</span>
            @if($ticket->status === 'open')
                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-500 text-white uppercase tracking-wider">Open</span>
            @elseif($ticket->status === 'pending')
                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-sky-500 text-white uppercase tracking-wider">Pending</span>
            @else
                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-slate-400 text-white uppercase tracking-wider">Closed</span>
            @endif
        </div>
    </div>

    <!-- Ticket Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Left Side: Ticket Meta Info Card -->
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm p-6 space-y-5 lg:col-span-1">
            <div>
                <span class="block text-[8px] font-mono text-slate-400 uppercase tracking-widest mb-1 font-bold">Subject</span>
                <h2 class="text-sm font-bold text-slate-900 leading-snug">{{ $ticket->subject }}</h2>
            </div>
            
            <div class="h-[1px] bg-slate-100"></div>

            <div>
                <span class="block text-[8px] font-mono text-slate-400 uppercase tracking-widest mb-1.5 font-bold">Ticket Details</span>
                <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-4 rounded-2xl border border-slate-100 font-medium whitespace-pre-wrap">{{ $ticket->description }}</p>
            </div>

            <div class="h-[1px] bg-slate-100"></div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="block text-[8px] font-mono text-slate-400 uppercase tracking-widest mb-1 font-bold">Priority</span>
                    @if($ticket->priority === 'high')
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-50 text-rose-600 border border-rose-100 uppercase tracking-wider">High</span>
                    @elseif($ticket->priority === 'medium')
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-50 text-amber-600 border border-amber-100 uppercase tracking-wider">Medium</span>
                    @else
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase tracking-wider">Low</span>
                    @endif
                </div>
                <div>
                    <span class="block text-[8px] font-mono text-slate-400 uppercase tracking-widest mb-1 font-bold">Created On</span>
                    <span class="text-xs font-semibold text-slate-500 font-mono">{{ $ticket->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Right Side: Discussion Board Chat -->
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden lg:col-span-2 flex flex-col h-[520px]">
            <!-- Chat Header -->
            <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between shrink-0">
                <span class="text-xs font-bold text-slate-900">Discussion History</span>
                <span class="text-[9px] text-slate-400 font-mono tracking-wider uppercase">Technical Support Desk</span>
            </div>

            <!-- Messages Stream Area -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50/20" id="chat-messages-container">
                <!-- Original Description Post -->
                <div class="flex gap-3 max-w-[85%]">
                    <div class="w-8 h-8 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center text-[10px] font-bold text-emerald-600 shrink-0 shadow-sm">
                        {{ strtoupper(substr($ticket->user->name, 0, 2)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-slate-800">{{ $ticket->user->name }}</span>
                            <span class="text-[8px] text-slate-400 font-mono">{{ $ticket->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="mt-1 bg-white border border-slate-200/65 rounded-2xl rounded-tl-none p-3.5 text-xs text-slate-700 leading-normal shadow-sm">
                            {{ $ticket->description }}
                        </div>
                    </div>
                </div>

                <!-- Message Thread replies -->
                @foreach($ticket->messages as $msg)
                    @php
                        $isMe = $msg->user_id === auth()->id();
                    @endphp
                    <div class="flex gap-3 max-w-[85%] {{ $isMe ? 'ml-auto flex-row-reverse' : '' }}">
                        <!-- Avatar -->
                        @if($isMe)
                            <div class="w-8 h-8 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center text-[10px] font-bold text-emerald-600 shrink-0 shadow-sm">
                                ME
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-600 shrink-0 shadow-sm" title="AuraCampus Support">
                                SP
                            </div>
                        @endif

                        <div>
                            <div class="flex items-center gap-2 {{ $isMe ? 'justify-end' : '' }}">
                                <span class="text-[10px] font-bold text-slate-800">{{ $isMe ? 'You' : 'AuraCampus Support' }}</span>
                                <span class="text-[8px] text-slate-400 font-mono">{{ $msg->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="mt-1 p-3.5 text-xs leading-normal shadow-sm {{ $isMe ? 'bg-emerald-600 text-white rounded-2xl rounded-tr-none' : 'bg-white border border-slate-200/65 text-slate-700 rounded-2xl rounded-tl-none' }}">
                                {{ $msg->message }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Chat input form -->
            <div class="p-4 border-t border-slate-100 bg-white shrink-0">
                @if($ticket->status === 'closed')
                    <div class="text-center py-2 text-xs text-slate-400 font-medium bg-slate-50 rounded-xl mb-2">This ticket is marked as closed. Sending a message will reopen the ticket.</div>
                @endif
                <form action="{{ route('school.support.message', $ticket) }}" method="POST" class="flex gap-3 items-center mt-2">
                    @csrf
                    <input name="message" required type="text" class="flex-1 px-4 py-2.5 premium-input rounded-xl focus:outline-none focus:premium-input-focus-emerald placeholder-slate-400 text-xs font-semibold" placeholder="Type your reply message here..."/>
                    <button type="submit" class="p-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl cursor-pointer shadow-sm transition-all flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]">send</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Scroll chat area to the bottom automatically
    document.addEventListener("DOMContentLoaded", function() {
        var container = document.getElementById("chat-messages-container");
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });
</script>
@endsection
