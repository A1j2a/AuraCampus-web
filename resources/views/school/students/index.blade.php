@extends('layouts.school')

@section('title', 'AuraCampus | Students')

@section('content')
<div x-data="{ transferModal: false, statusModal: false, editModal: false, selectedStudent: null, selectedStudentName: '', editStudent: {} }">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Students Registry</h2>
            <p class="text-xs text-slate-500 mt-1">Students are registered via Parent module. Manage status and transfers here.</p>
        </div>
        <a href="{{ route('school.parents') }}" class="px-4 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-[16px]">family_restroom</span>
            Add via Parents
        </a>
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

    <!-- Filters -->
    <form method="GET" action="{{ route('school.students') }}" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or admission no."
               class="px-4 py-2 premium-input rounded-xl text-xs font-medium focus:outline-none placeholder-slate-300 w-64">

        <select name="class_id" class="px-4 py-2 premium-input rounded-xl text-xs font-medium focus:outline-none appearance-none bg-white">
            <option value="">All Classes</option>
            @foreach($classes as $class)
            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                {{ $class->name }} - {{ $class->section }}
            </option>
            @endforeach
        </select>

        <select name="status" class="px-4 py-2 premium-input rounded-xl text-xs font-medium focus:outline-none appearance-none bg-white">
            <option value="">All Status</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            <option value="transferred" {{ request('status') == 'transferred' ? 'selected' : '' }}>Transferred</option>
            <option value="graduated" {{ request('status') == 'graduated' ? 'selected' : '' }}>Graduated</option>
        </select>

        <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-xs font-bold rounded-xl cursor-pointer">Filter</button>
        @if(request()->hasAny(['search','class_id','status']))
        <a href="{{ route('school.students') }}" class="px-4 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl cursor-pointer">Clear</a>
        @endif
    </form>

    <!-- Students Table -->
    <div class="premium-card rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <span class="text-xs font-semibold text-slate-500">{{ $students->count() }} Students Found</span>
            <button onclick="exportTableToCSV('students-list.csv')" class="px-3 py-1 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-[10px] font-bold rounded-lg cursor-pointer flex items-center gap-1">
                <span class="material-symbols-outlined text-[13px]">download</span>
                Export CSV
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50">
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Admission #</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Class</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Roll</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Blood Group</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $student)
                    <tr class="hover:bg-slate-50/50 transition-all duration-150">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($student->profile_image)
                                    <img src="{{ asset('storage/' . $student->profile_image) }}" class="w-9 h-9 rounded-xl object-cover border border-indigo-100 shadow-sm shrink-0" alt="{{ $student->name }}">
                                @else
                                    <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-bold shadow-sm shrink-0">
                                        {{ strtoupper(substr($student->name, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="text-xs font-bold text-slate-800">{{ $student->name }}</p>
                                    <p class="text-[9px] text-slate-400 font-mono mt-0.5">
                                        {{ $student->studentDetail?->date_of_birth?->format('d M Y') ?? '—' }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-mono font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded">
                                {{ $student->studentDetail?->admission_number ?? '—' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-semibold text-slate-700">
                                {{ $student->studentDetail?->class ? $student->studentDetail->class->name . ' - ' . $student->studentDetail->class->section : '—' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-mono text-slate-600">{{ $student->studentDetail?->roll_number ?? '—' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs text-slate-600">{{ $student->studentDetail?->blood_group ?? '—' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @php $status = $student->studentDetail?->status ?? 'active'; @endphp
                            <span class="px-2 py-0.5 text-[9px] font-bold rounded-full uppercase
                                {{ $status === 'active' ? 'bg-violet-50 text-violet-700 border border-violet-100' :
                                  ($status === 'inactive' ? 'bg-slate-100 text-slate-500 border border-slate-200' :
                                  ($status === 'transferred' ? 'bg-blue-50 text-blue-700 border border-blue-100' :
                                  'bg-purple-50 text-purple-700 border border-purple-100')) }}">
                                {{ $status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button @click='editModal = true; editStudent = {{ json_encode(["id" => $student->id, "name" => $student->name, "admission_number" => $student->studentDetail?->admission_number ?? "", "roll_number" => $student->studentDetail?->roll_number ?? "", "dob" => $student->studentDetail?->date_of_birth?->format("Y-m-d") ?? "", "gender" => $student->studentDetail?->gender ?? "", "blood_group" => $student->studentDetail?->blood_group ?? "", "class_id" => $student->studentDetail?->class_id ?? ""]) }}'
                                        class="px-2 py-1 text-[10px] font-bold text-violet-600 bg-violet-50 hover:bg-violet-100 border border-violet-200/50 rounded-lg cursor-pointer flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[12px]">edit</span>
                                    Edit
                                </button>
                                <button @click="selectedStudent = {{ $student->id }}; selectedStudentName = '{{ addslashes($student->name) }}'; transferModal = true"
                                        class="px-2 py-1 text-[10px] font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-200/50 rounded-lg cursor-pointer flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[12px]">swap_horiz</span>
                                    Transfer
                                </button>
                                <button @click="selectedStudent = {{ $student->id }}; selectedStudentName = '{{ addslashes($student->name) }}'; statusModal = true"
                                        class="px-2 py-1 text-[10px] font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-lg cursor-pointer flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[12px]">edit</span>
                                    Status
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <span class="material-symbols-outlined text-violet-600 text-4xl mb-3">groups</span>
                            <h4 class="text-sm font-bold text-slate-800 mb-1">No Students Found</h4>
                            <p class="text-xs text-slate-500">Students are added through the <a href="{{ route('school.parents') }}" class="text-violet-600 font-bold">Parents</a> module.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Transfer Modal -->
    <div x-show="transferModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="transferModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 border border-slate-200/60 p-6" @click.stop>
            <div class="flex justify-between items-center mb-5">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Transfer Student</h3>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="selectedStudentName"></p>
                </div>
                <button @click="transferModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" :action="'/school/students/' + selectedStudent + '/transfer'">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Transfer to Class</label>
                    <select name="class_id" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none appearance-none bg-white">
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }} - {{ $class->section }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-3 mt-5">
                    <button type="button" @click="transferModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl cursor-pointer shadow-sm">Transfer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Status Modal -->
    <div x-show="statusModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="statusModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 border border-slate-200/60 p-6" @click.stop>
            <div class="flex justify-between items-center mb-5">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Update Status</h3>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="selectedStudentName"></p>
                </div>
                <button @click="statusModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" :action="'/school/students/' + selectedStudent + '/status'">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Status</label>
                    <select name="status" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none appearance-none bg-white">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="transferred">Transferred</option>
                        <option value="graduated">Graduated</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 mt-5">
                    <button type="button" @click="statusModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl cursor-pointer shadow-sm">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="editModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg relative z-10 border border-slate-200/60 max-h-[90vh] flex flex-col overflow-hidden" @click.stop>
            <div class="flex justify-between items-center p-6 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Edit Student</h3>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="editStudent.name"></p>
                </div>
                <button @click="editModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" :action="'/school/students/' + editStudent.id" enctype="multipart/form-data" class="flex-1 flex flex-col min-h-0">
                @csrf
                @method('PATCH')
                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Full Name</label>
                            <input type="text" name="name" :value="editStudent.name" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Admission Number</label>
                            <input type="text" name="admission_number" :value="editStudent.admission_number" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Roll Number</label>
                            <input type="text" name="roll_number" :value="editStudent.roll_number" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Date of Birth</label>
                            <input type="date" name="dob" :value="editStudent.dob" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Gender</label>
                            <select name="gender" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none appearance-none bg-white">
                                <option value="">Select</option>
                                <option value="male" :selected="editStudent.gender === 'male'">Male</option>
                                <option value="female" :selected="editStudent.gender === 'female'">Female</option>
                                <option value="other" :selected="editStudent.gender === 'other'">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Blood Group</label>
                            <select name="blood_group" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none appearance-none bg-white">
                                <option value="">Select</option>
                                @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                                <option value="{{ $bg }}" :selected="editStudent.blood_group === '{{ $bg }}'">{{ $bg }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Class</label>
                            <select name="class_id" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none appearance-none bg-white">
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}" :selected="editStudent.class_id == {{ $class->id }}">{{ $class->name }} - {{ $class->section }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Profile Image (Leave blank to keep current)</label>
                            <input type="file" name="profile_image" accept="image/*"
                                   class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none file:mr-4 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                        </div>
                    </div>
                </div>
                <div class="p-6 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-3 rounded-b-2xl">
                    <button type="button" @click="editModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl cursor-pointer shadow-sm">Update</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
