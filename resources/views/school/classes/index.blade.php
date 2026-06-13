@extends('layouts.school')

@section('title', 'AuraCampus | Classes & Sections')

@section('content')
<div x-data="{ showModal: false, editModal: false, editClass: {} }">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Classes & Sections</h2>
            <p class="text-xs text-slate-500 mt-1">Manage classrooms, assign class teachers, and organize sections.</p>
        </div>
        <button @click="showModal = true" class="px-4 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-[16px]">add</span>
            Add Class
        </button>
    </div>

    <!-- Flash Messages & Errors -->
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

    <!-- Classes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($classes as $class)
        <div class="premium-card p-5 rounded-2xl hover:shadow-md transition-all duration-300 group relative overflow-hidden">
            <div class="absolute -right-8 -top-8 w-20 h-20 bg-violet-500/5 rounded-full blur-2xl group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-violet-50 border border-violet-100 flex items-center justify-center text-violet-600 font-bold text-sm shadow-sm">
                        {{ $class->section }}
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">{{ $class->name }}</h3>
                        <p class="text-[10px] text-slate-400 font-mono mt-0.5">Section {{ $class->section }}</p>
                    </div>
                </div>
                <div class="flex gap-1">
                    <button @click='editModal = true; editClass = {{ json_encode(["id" => $class->id, "name" => $class->name, "section" => $class->section, "room_number" => $class->room_number, "capacity" => $class->capacity, "teacher_id" => $class->teacher_id]) }}' class="text-slate-400 hover:text-violet-600 transition-colors p-1">
                        <span class="material-symbols-outlined text-[16px]">edit</span>
                    </button>
                    <form method="POST" action="{{ route('school.classes.destroy', $class) }}" onsubmit="return confirm('Delete this class? WARNING: This will cascade delete all students, timetables, and exams associated with this class.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-slate-300 hover:text-rose-500 transition-colors cursor-pointer p-1">
                            <span class="material-symbols-outlined text-[16px]">close</span>
                        </button>
                    </form>
                </div>
            </div>
            <div class="flex items-center gap-4 text-xs text-slate-500 border-t border-slate-100 pt-3">
                <div class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[14px] text-slate-400">groups</span>
                    <span class="font-semibold">{{ $class->students_count }} Students</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[14px] text-slate-400">meeting_room</span>
                    <span>{{ $class->room_number ?? '—' }}</span>
                </div>
            </div>
            @if($class->teacher)
            <div class="flex items-center gap-2 mt-3 p-2 bg-slate-50 rounded-lg">
                <div class="w-6 h-6 rounded-md bg-violet-50 border border-violet-100 flex items-center justify-center text-violet-600 text-[9px] font-bold">
                    {{ strtoupper(substr($class->teacher->name, 0, 2)) }}
                </div>
                <span class="text-[10px] text-slate-600 font-medium">{{ $class->teacher->name }}</span>
                <span class="text-[9px] text-slate-400 font-mono ml-auto">Class Teacher</span>
            </div>
            @endif
        </div>
        @empty
        <div class="col-span-full premium-card p-12 rounded-2xl text-center">
            <span class="material-symbols-outlined text-violet-600 text-4xl mb-3 animate-float-slow">class</span>
            <h4 class="text-sm font-bold text-slate-800 mb-1">No Classes Created Yet</h4>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">Click "Add Class" to create your first classroom section.</p>
        </div>
        @endforelse
    </div>

    <!-- Add Class Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 relative z-10 border border-slate-200/60" @click.stop>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-base font-bold text-slate-900">Add New Class</h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" action="{{ route('school.classes.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Class Name</label>
                        <input type="text" name="name" placeholder="e.g. Class 10" required
                               class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet placeholder-slate-300">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Section</label>
                            <input type="text" name="section" placeholder="e.g. A" required
                                   class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet placeholder-slate-300">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Room Number</label>
                            <input type="text" name="room_number" placeholder="e.g. Room 201"
                                   class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet placeholder-slate-300">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Class Teacher</label>
                        <select name="teacher_id" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none appearance-none bg-white">
                            <option value="">Select Teacher</option>
                            @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">Create Class</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Class Modal -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="editModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 relative z-10 border border-slate-200/60" @click.stop>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-base font-bold text-slate-900">Edit Class</h3>
                <button @click="editModal = false" class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" :action="'/school/classes/' + editClass.id">
                @csrf
                @method('PATCH')
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Class Name</label>
                        <input type="text" name="name" :value="editClass.name" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Section</label>
                            <input type="text" name="section" :value="editClass.section" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Capacity</label>
                            <input type="number" name="capacity" :value="editClass.capacity" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Room Number</label>
                        <input type="text" name="room_number" :value="editClass.room_number" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-violet">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Class Teacher</label>
                        <select name="teacher_id" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none appearance-none bg-white">
                            <option value="">Select Teacher</option>
                            @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" :selected="editClass.teacher_id == {{ $teacher->id }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="editModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
