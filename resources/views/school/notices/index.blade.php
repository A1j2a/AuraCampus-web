@extends('layouts.school')

@section('title', 'AuraCampus | Notice Board')

@section('content')
<div x-data="{ 
    showModal: {{ $errors->any() ? 'true' : 'false' }},
    deleteModal: false,
    deleteUrl: '',
    deleteNoticeTitle: '',
    confirmDelete(url, title) {
        this.deleteUrl = url;
        this.deleteNoticeTitle = title;
        this.deleteModal = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Notice Board</h2>
            <p class="text-xs text-slate-500 mt-1">Publish circulars, holiday announcements, and school events to students, parents, and teachers.</p>
        </div>
        <button @click="showModal = true" class="px-4 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-[16px]">campaign</span>
            Publish Notice
        </button>
    </div>

    <!-- Alert Message -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-violet-50 border border-violet-200 rounded-xl text-xs font-semibold text-violet-700 flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    <!-- Notices Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($notices as $notice)
        @php
            $borderColor = 'border-slate-350';
            $badgeClass = 'bg-slate-50 text-slate-600 border-slate-100';
            if ($notice->type === 'academic') {
                $borderColor = 'border-indigo-500';
                $badgeClass = 'bg-indigo-50 text-indigo-700 border-indigo-150';
            } elseif ($notice->type === 'event') {
                $borderColor = 'border-violet-500';
                $badgeClass = 'bg-violet-50 text-violet-700 border-violet-150';
            } elseif ($notice->type === 'holiday') {
                $borderColor = 'border-rose-500';
                $badgeClass = 'bg-rose-50 text-rose-700 border-rose-150';
            }
        @endphp
        <div class="bg-white border-l-4 {{ $borderColor }} rounded-2xl shadow-sm p-6 relative flex flex-col justify-between transition-all duration-200 hover:shadow-md border-y border-r border-slate-200/50">
            <div>
                <div class="flex justify-between items-start gap-4 mb-3">
                    <span class="px-2 py-0.5 border text-[9px] font-mono rounded font-bold uppercase {{ $badgeClass }}">
                        {{ $notice->type }}
                    </span>
                    <button type="button" @click="confirmDelete('{{ route('school.notices.destroy', $notice) }}', '{{ addslashes($notice->title) }}')" class="text-slate-400 hover:text-rose-500 transition-colors p-1 rounded-lg cursor-pointer" title="Delete Notice">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                </div>
                <h3 class="text-sm font-bold text-slate-800 leading-snug mb-2">{{ $notice->title }}</h3>
                <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-wrap mb-4">{{ $notice->content }}</p>
            </div>
            <div class="flex items-center gap-2 mt-2 pt-3 border-t border-slate-50">
                <span class="material-symbols-outlined text-[14px] text-slate-400">schedule</span>
                <span class="text-[9px] text-slate-450 font-mono">Published: {{ $notice->published_at ? \Carbon\Carbon::parse($notice->published_at)->format('d M Y, h:i A') : $notice->created_at->format('d M Y, h:i A') }}</span>
            </div>
        </div>
        @empty
        <div class="col-span-full py-16 text-center premium-card rounded-2xl bg-white">
            <span class="material-symbols-outlined text-indigo-500 text-5xl mb-3 animate-float-slow" data-icon="campaign">campaign</span>
            <h4 class="text-sm font-bold text-slate-800 mb-1">No Notices Published</h4>
            <p class="text-xs text-slate-500 max-w-sm mx-auto leading-normal">Announcements, exam reminders, and holiday updates will appear here when you create them.</p>
        </div>
        @endforelse
    </div>

    <!-- Create Notice Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 relative z-10 border border-slate-200/60 max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-base font-bold text-slate-900">Publish New Notice</h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" action="{{ route('school.notices.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Notice Title</label>
                        <input type="text" name="title" placeholder="e.g. Mid-Term Report Card Release" required
                               class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet placeholder-slate-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Category</label>
                        <select name="type" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet appearance-none cursor-pointer bg-white">
                            <option value="general">General announcement</option>
                            <option value="academic">Academic / Exam related</option>
                            <option value="event">School Event / Activities</option>
                            <option value="holiday">Holiday / School Closure</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Notice Content</label>
                        <textarea name="content" rows="4" placeholder="Enter notice details..." required
                                  class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet placeholder-slate-300 resize-none"></textarea>
                    </div>
                    <div x-data="{ publishNow: true }">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-semibold text-slate-700">Publish immediately</label>
                            <input type="checkbox" name="publish_now" value="1" x-model="publishNow" class="rounded text-violet-600 focus:ring-violet-500 h-4 w-4">
                        </div>
                        <div x-show="!publishNow" x-transition>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Scheduled Publish Date</label>
                            <input type="datetime-local" name="published_at" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet">
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">Publish Notice</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Global Delete Form -->
    <form id="global-delete-form" method="POST" :action="deleteUrl" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <!-- Custom Delete Confirmation Modal -->
    <div x-show="deleteModal" x-cloak class="fixed inset-0 z-[110] flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="deleteModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 relative z-10 border border-slate-200/60 text-center" @click.stop>
            <div class="w-12 h-12 rounded-full bg-rose-50 border border-rose-100 flex items-center justify-center mx-auto mb-4 text-rose-600">
                <span class="material-symbols-outlined text-[24px]">delete_forever</span>
            </div>
            <h3 class="text-sm font-bold text-slate-950 mb-1">Delete Notice</h3>
            <p class="text-xs text-slate-500 leading-relaxed px-2">
                Are you sure you want to delete <strong class="text-slate-800" x-text="deleteNoticeTitle"></strong>? This action is permanent.
            </p>
            <div class="mt-6 flex justify-center gap-3">
                <button type="button" @click="deleteModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="button" @click="document.getElementById('global-delete-form').submit()" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-md shadow-rose-600/10">
                    Confirm Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
