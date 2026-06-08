@extends('layouts.school')

@section('title', 'AuraCampus | Parents')

@section('content')
<div x-data="{ 
    showAddModal: {{ $errors->any() ? 'true' : 'false' }}, 
    showLinkModal: false,
    selectedParentId: '',
    selectedParentName: '',
    openLinkModal(parentId, parentName) {
        this.selectedParentId = parentId;
        this.selectedParentName = parentName;
        this.showLinkModal = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Parents Directory</h2>
            <p class="text-xs text-slate-500 mt-1">Manage parent accounts, link them to students, and oversee parent portal access.</p>
        </div>
        <button @click="showAddModal = true" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-[16px]">person_add</span>
            Add Parent
        </button>
    </div>

    <!-- Alert Message -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-700 flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    <!-- Parents Table -->
    <div class="premium-card rounded-2xl overflow-hidden bg-white">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <span class="text-xs font-semibold text-slate-500">{{ $parents->count() }} Parents Registered</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Parent Details</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Linked Children</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Created At</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($parents as $parent)
                    <tr class="hover:bg-slate-50/50 transition-all duration-150">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center text-teal-600 text-xs font-bold shadow-sm shrink-0">
                                    {{ strtoupper(substr($parent->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800">{{ $parent->name }}</p>
                                    <p class="text-[9px] text-slate-400 font-mono mt-0.5">{{ $parent->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-2">
                                @forelse($parent->students as $child)
                                <span class="px-2 py-0.5 bg-slate-100 border border-slate-200 text-slate-700 text-[10px] rounded-lg font-medium inline-flex items-center gap-1">
                                    <span class="font-bold text-slate-900">{{ $child->name }}</span>
                                    <span class="text-slate-400 font-mono text-[9px]">({{ $child->pivot->relationship }})</span>
                                    <span class="text-[9px] bg-indigo-50 text-indigo-600 px-1 rounded font-mono font-semibold">
                                        {{ $child->studentDetail?->class ? $child->studentDetail->class->name . '-' . $child->studentDetail->class->section : '—' }}
                                    </span>
                                </span>
                                @empty
                                <span class="text-xs text-slate-400 italic">No linked children</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-[10px] text-slate-450 font-mono">{{ $parent->created_at->format('d M Y') }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button @click="openLinkModal({{ $parent->id }}, '{{ addslashes($parent->name) }}')"
                                    class="px-2.5 py-1 text-[11px] font-bold text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200/50 rounded-lg transition-all cursor-pointer inline-flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px]">link</span>
                                Link Child
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <span class="material-symbols-outlined text-teal-600 text-4xl mb-3 animate-float-slow">family_restroom</span>
                            <h4 class="text-sm font-bold text-slate-800 mb-1">No Parents Registered</h4>
                            <p class="text-xs text-slate-500">Click "Add Parent" to register your first parent account.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Parent Modal -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="showAddModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg relative z-10 border border-slate-200/60 max-h-[90vh] flex flex-col overflow-hidden" @click.stop>
            <!-- Modal Header -->
            <div class="flex justify-between items-center p-6 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Add New Parent</h3>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer flex items-center justify-center" title="Close">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" action="{{ route('school.parents.store') }}" class="flex-1 flex flex-col min-h-0 m-0">
                @csrf
                <!-- Modal Body (Scrollable) -->
                <div class="p-6 space-y-4 overflow-y-auto flex-1 min-h-0">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Full Name</label>
                        <input type="text" name="name" placeholder="e.g. Rajesh Patel" required
                               class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald placeholder-slate-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email</label>
                        <input type="email" name="email" placeholder="e.g. rajesh@parent.com" required
                               class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald placeholder-slate-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Password</label>
                        <input type="password" name="password" placeholder="Min 6 characters" required minlength="6"
                               class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald placeholder-slate-300">
                    </div>

                    <div class="border-t border-slate-100 pt-4">
                        <p class="text-xs font-bold text-slate-850 mb-2">Link Student (Optional)</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Select Student</label>
                                <select name="student_id" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald appearance-none cursor-pointer bg-white">
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
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Relationship</label>
                                <select name="relationship" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald appearance-none cursor-pointer bg-white">
                                    <option value="">Choose Relationship</option>
                                    <option value="Father">Father</option>
                                    <option value="Mother">Mother</option>
                                    <option value="Guardian">Guardian</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Modal Footer -->
                <div class="p-6 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-3 rounded-b-2xl">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">Save Parent</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Link Student Modal -->
    <div x-show="showLinkModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="showLinkModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 border border-slate-200/60 max-h-[90vh] flex flex-col overflow-hidden" @click.stop>
            <!-- Modal Header -->
            <div class="flex justify-between items-center p-6 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Link Child to Parent</h3>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="'Parent: ' + selectedParentName"></p>
                </div>
                <button @click="showLinkModal = false" class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer flex items-center justify-center" title="Close">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" action="{{ route('school.parents.link') }}" class="flex-1 flex flex-col min-h-0 m-0">
                @csrf
                <input type="hidden" name="parent_id" :value="selectedParentId">
                <!-- Modal Body (Scrollable) -->
                <div class="p-6 space-y-4 overflow-y-auto flex-1 min-h-0">
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
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Relationship</label>
                        <select name="relationship" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald appearance-none cursor-pointer bg-white">
                            <option value="">Choose Relationship</option>
                            <option value="Father">Father</option>
                            <option value="Mother">Mother</option>
                            <option value="Guardian">Guardian</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <!-- Modal Footer -->
                <div class="p-6 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-3 rounded-b-2xl">
                    <button type="button" @click="showLinkModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">Link Student</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
