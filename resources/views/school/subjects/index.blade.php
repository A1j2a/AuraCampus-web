@extends('layouts.school')

@section('title', 'AuraCampus | Subjects')

@section('content')
<div x-data="{ 
    showAddModal: false, 
    showAssignModal: false, 
    editModal: false, 
    editSubject: {}, 
    selectedClassId: '', 
    selectedSubjectIds: [],
    deleteModal: false,
    deleteUrl: '',
    deleteItemName: '',
    deleteType: '',
    confirmDelete(url, name, type) {
        this.deleteUrl = url;
        this.deleteItemName = name;
        this.deleteType = type;
        this.deleteModal = true;
    }
}">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Subjects & Class Assignment</h2>
            <p class="text-xs text-slate-500 mt-1">Create subjects and assign them to classes.</p>
        </div>
        <div class="flex gap-3">
            <button @click="selectedClassId = ''; selectedSubjectIds = []; showAssignModal = true" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-[16px]">assignment</span>
                Assign to Class
            </button>
            <button @click="showAddModal = true" class="px-4 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-[16px]">add</span>
                Add Subject
            </button>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-xl text-xs font-semibold text-rose-700">
        <div class="flex items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-[18px]">error</span>
            <span>Please correct the errors:</span>
        </div>
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(session('success'))
    <div class="mb-6 p-4 bg-violet-50 border border-violet-200 rounded-xl text-xs font-semibold text-violet-700 flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Subjects List -->
        <div class="premium-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50">
                <span class="text-xs font-semibold text-slate-500">{{ $subjects->count() }} Subjects</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50">
                            <th class="px-5 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Subject</th>
                            <th class="px-5 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Code</th>
                            <th class="px-5 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Type</th>
                            <th class="px-5 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Classes</th>
                            <th class="px-5 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($subjects as $subject)
                        <tr class="hover:bg-slate-50/50 transition-all duration-150">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                                        <span class="material-symbols-outlined text-[14px]">book</span>
                                    </div>
                                    <span class="text-xs font-bold text-slate-800">{{ $subject->name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-xs font-mono font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded">{{ $subject->code }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2 py-0.5 text-[9px] font-bold rounded-full uppercase
                                    {{ $subject->type === 'theory' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : ($subject->type === 'practical' ? 'bg-amber-50 text-amber-700 border border-amber-100' : 'bg-violet-50 text-violet-700 border border-violet-100') }}">
                                    {{ $subject->type }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-xs font-semibold text-slate-600">{{ $subject->classes_count }} classes</span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click='editModal = true; editSubject = {{ json_encode(["id" => $subject->id, "name" => $subject->name, "code" => $subject->code, "type" => $subject->type]) }}'
                                            class="text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer">
                                        <span class="material-symbols-outlined text-[16px]">edit</span>
                                    </button>
                                    <button type="button" @click="confirmDelete('{{ route('school.subjects.destroy', $subject) }}', '{{ addslashes($subject->name) }} ({{ $subject->code }})', 'delete')" class="text-slate-300 hover:text-rose-500 transition-colors cursor-pointer">
                                        <span class="material-symbols-outlined text-[16px]">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center">
                                <span class="material-symbols-outlined text-violet-600 text-3xl mb-2">book</span>
                                <p class="text-xs font-bold text-slate-800">No Subjects Yet</p>
                                <p class="text-xs text-slate-500 mt-1">Click "Add Subject" to start.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Class wise subjects view -->
        <div class="space-y-4">
            <p class="text-xs font-mono text-slate-400 uppercase tracking-widest font-bold">Subjects per Class</p>
            @forelse($classes as $class)
            <div class="premium-card rounded-xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-violet-50 border border-violet-100 flex items-center justify-center text-violet-600 text-xs font-bold">
                            {{ $class->section }}
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-800">{{ $class->name }}</p>
                            <p class="text-[9px] text-slate-400 font-mono">Section {{ $class->section }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click='selectedClassId = "{{ $class->id }}"; selectedSubjectIds = {{ json_encode($class->subjects->pluck("id")->map(fn($id) => (string)$id)->toArray()) }}; showAssignModal = true'
                                class="text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]">edit</span>
                        </button>
                        <span class="text-[10px] text-slate-500 font-semibold">{{ $class->subjects->count() }} subjects</span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    @forelse($class->subjects as $subject)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-indigo-50 border border-indigo-100 text-indigo-700 text-[10px] rounded-lg font-medium">
                        {{ $subject->name }}
                        <button type="button" @click="confirmDelete('{{ route('school.classes.subjects.detach', [$class->id, $subject->id]) }}', '{{ addslashes($subject->name) }} from {{ addslashes($class->name) }}', 'detach')" class="text-slate-400 hover:text-rose-600 inline-flex items-center align-middle focus:outline-none cursor-pointer ml-0.5">
                            <span class="material-symbols-outlined text-[10px]">close</span>
                        </button>
                    </span>
                    @empty
                    <span class="text-[10px] text-slate-400 italic">No subjects assigned</span>
                    @endforelse
                </div>
            </div>
            @empty
            <div class="premium-card rounded-xl p-6 text-center">
                <p class="text-xs text-slate-400">No classes created yet.</p>
            </div>
            @endforelse
        </div>

    </div>

    <!-- Add Subject Modal -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="showAddModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 relative z-10 border border-slate-200/60" @click.stop>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-base font-bold text-slate-900">Add Subject</h3>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" action="{{ route('school.subjects.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Subject Name</label>
                        <input type="text" name="name" placeholder="e.g. Mathematics" required
                               class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none placeholder-slate-300">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Subject Code</label>
                            <input type="text" name="code" placeholder="e.g. MATH101" required
                                   class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none placeholder-slate-300">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Type</label>
                            <select name="type" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none appearance-none bg-white">
                                <option value="theory">Theory</option>
                                <option value="practical">Practical</option>
                                <option value="both">Both</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl cursor-pointer shadow-sm">Add Subject</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Assign Subjects to Class Modal -->
    <div x-show="showAssignModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="showAssignModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg relative z-10 border border-slate-200/60 max-h-[90vh] flex flex-col overflow-hidden" @click.stop>
            <div class="flex justify-between items-center p-6 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Assign Subjects to Class</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Select a class then pick subjects for it</p>
                </div>
                <button @click="showAssignModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" action="{{ route('school.subjects.assign') }}" class="flex-1 flex flex-col min-h-0">
                @csrf
                <div class="p-6 space-y-5 overflow-y-auto flex-1">
                    <!-- Class Selector -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Select Class</label>
                        <select name="class_id" x-model="selectedClassId" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none appearance-none bg-white">
                            <option value="">Choose Class</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }} - Section {{ $class->section }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Subjects Checkboxes -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-xs font-semibold text-slate-700">Select Subjects</label>
                            @if($subjects->count())
                            <button type="button" @click="if (selectedSubjectIds.length === {{ $subjects->count() }}) { selectedSubjectIds = []; } else { selectedSubjectIds = {{ json_encode($subjects->pluck('id')->map(fn($id) => (string)$id)->toArray()) }}; }" class="text-[10px] font-bold text-indigo-650 hover:text-indigo-800 transition-colors focus:outline-none cursor-pointer">
                                <span x-text="selectedSubjectIds.length === {{ $subjects->count() }} ? 'Deselect All' : 'Select All'"></span>
                            </button>
                            @endif
                        </div>
                        @if($subjects->count())
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($subjects as $subject)
                            <label class="flex items-center gap-2 p-2.5 border border-slate-200 rounded-lg hover:border-indigo-400 cursor-pointer transition-all">
                                <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" x-model="selectedSubjectIds" class="rounded text-indigo-600">
                                <div>
                                    <p class="text-xs font-semibold text-slate-700">{{ $subject->name }}</p>
                                    <p class="text-[9px] text-slate-400 font-mono">{{ $subject->code }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        @else
                        <p class="text-xs text-slate-400 italic">No subjects created yet. Add subjects first.</p>
                        @endif
                    </div>
                </div>
                <div class="p-6 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-3 rounded-b-2xl">
                    <button type="button" @click="showAssignModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl cursor-pointer shadow-sm">Assign Subjects</button>
                </div>
            </form>
        </div>
    </div>


    <!-- Edit Subject Modal -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="editModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 border border-slate-200/60" @click.stop>
            <div class="flex justify-between items-center p-6 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Edit Subject</h3>
                <button @click="editModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" :action="'/school/subjects/' + editSubject.id">
                @csrf
                @method('PATCH')
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Subject Name</label>
                        <input type="text" name="name" :value="editSubject.name" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Subject Code</label>
                            <input type="text" name="code" :value="editSubject.code" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Type</label>
                            <select name="type" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none appearance-none bg-white">
                                <option value="theory" :selected="editSubject.type === 'theory'">Theory</option>
                                <option value="practical" :selected="editSubject.type === 'practical'">Practical</option>
                                <option value="both" :selected="editSubject.type === 'both'">Both</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="p-6 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-3 rounded-b-2xl">
                    <button type="button" @click="editModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl cursor-pointer shadow-sm">Update</button>
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
            <h3 class="text-sm font-bold text-slate-950 mb-1" x-text="deleteType === 'detach' ? 'Remove Subject' : 'Delete Subject'"></h3>
            <p class="text-xs text-slate-500 leading-relaxed px-2" x-text="deleteType === 'detach' ? 'Are you sure you want to remove ' + deleteItemName + '?' : 'Are you sure you want to delete ' + deleteItemName + '? This action is permanent and will delete the subject from the school database.'">
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
