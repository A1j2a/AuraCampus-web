@extends('layouts.school')

@section('title', 'AuraCampus | Teachers')

@section('content')
<div x-data="{ showModal: false, editModal: false, editTeacher: { class_ids: [], subject_ids: [], class_teacher_of: '' } }">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Teachers Directory</h2>
            <p class="text-xs text-slate-500 mt-1">Onboard staff, assign classes, and credentials are auto-generated.</p>
        </div>
        <button @click="showModal = true" class="px-4 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-[16px]">person_add</span>
            Onboard Teacher
        </button>
    </div>

    @if($errors->any())
    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-xl text-xs font-semibold text-rose-700">
        <ul class="list-disc list-inside space-y-1">
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

    <!-- Teachers Table -->
    <div class="premium-card rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <span class="text-xs font-semibold text-slate-500">{{ $teachers->count() }} Teachers Onboarded</span>
            <button onclick="exportTableToCSV('teachers-list.csv')" class="px-3 py-1 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-[10px] font-bold rounded-lg cursor-pointer transition-all flex items-center gap-1">
                <span class="material-symbols-outlined text-[13px]">download</span>
                Export CSV
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50">
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Teacher</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Employee ID</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Designation</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Experience</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Credentials</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($teachers as $teacher)
                    <tr class="hover:bg-slate-50/50 transition-all duration-150">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-violet-50 border border-violet-100 flex items-center justify-center text-violet-600 text-xs font-bold shadow-sm shrink-0">
                                    {{ strtoupper(substr($teacher->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800">{{ $teacher->name }}</p>
                                    <p class="text-[9px] text-slate-400 font-mono mt-0.5">{{ $teacher->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-mono font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded">{{ $teacher->teacherDetail?->employee_id ?? '—' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs text-slate-700 font-medium">{{ $teacher->teacherDetail?->designation ?? '—' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs text-slate-500">{{ $teacher->teacherDetail?->experience ?? '—' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($teacher->credential)
                            <div class="text-[10px] font-mono text-slate-600">
                                <p><span class="text-slate-400">User:</span> {{ $teacher->credential->username }}</p>
                                <p><span class="text-slate-400">Pass:</span> {{ $teacher->credential->plain_password }}</p>
                            </div>
                            @else
                            <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-[10px] text-slate-400 font-mono">{{ $teacher->teacherDetail?->joining_date?->format('d M Y') ?? '—' }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button @click='editModal = true; editTeacher = {{ json_encode(["id" => $teacher->id, "name" => $teacher->name, "email" => $teacher->email, "mobile" => $teacher->phone ?? "", "employee_id" => $teacher->teacherDetail?->employee_id ?? "", "designation" => $teacher->teacherDetail?->designation ?? "", "qualification" => $teacher->teacherDetail?->qualification ?? "", "experience" => $teacher->teacherDetail?->experience ?? "", "joining_date" => $teacher->teacherDetail?->joining_date?->format("Y-m-d") ?? "", "class_ids" => $teacher->teacherClassSections->pluck("class_id")->toArray(), "class_teacher_of" => $teacher->teacherClassSections->where("is_class_teacher", true)->first()?->class_id ?? "", "subject_ids" => $teacher->subjects->pluck("id")->toArray()]) }}' class="text-slate-400 hover:text-violet-600 transition-colors cursor-pointer">
                                <span class="material-symbols-outlined text-[16px]">edit</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <span class="material-symbols-outlined text-violet-600 text-4xl mb-3">badge</span>
                            <h4 class="text-sm font-bold text-slate-800 mb-1">No Teachers Onboarded</h4>
                            <p class="text-xs text-slate-500">Click "Onboard Teacher" to add your first faculty member.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Onboard Teacher Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl relative z-10 border border-slate-200/60 max-h-[90vh] flex flex-col overflow-hidden" @click.stop>
            <div class="flex justify-between items-center p-6 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Onboard New Teacher</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Credentials will be auto-generated</p>
                </div>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <form method="POST" action="{{ route('school.teachers.store') }}" class="flex-1 flex flex-col min-h-0">
                @csrf
                <div class="p-6 space-y-5 overflow-y-auto flex-1 min-h-0">

                    <!-- Personal Info -->
                    <div>
                        <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest mb-3 font-bold">Personal Information</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Full Name</label>
                                <input type="text" name="name" placeholder="e.g. Priya Sharma" required
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none placeholder-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email</label>
                                <input type="email" name="email" placeholder="email@school.com" required
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none placeholder-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Mobile</label>
                                <input type="text" name="mobile" placeholder="9876543210" required
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none placeholder-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Employee ID</label>
                                <input type="text" name="employee_id" placeholder="e.g. EMP-001" required
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none placeholder-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Designation</label>
                                <input type="text" name="designation" placeholder="e.g. Senior Teacher" required
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none placeholder-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Qualification</label>
                                <input type="text" name="qualification" placeholder="e.g. M.Sc. Physics" required
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none placeholder-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Experience</label>
                                <input type="text" name="experience" placeholder="e.g. 5 years"
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none placeholder-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Joining Date</label>
                                <input type="date" name="joining_date"
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- Class Assignment -->
                    @if($classes->count())
                    <div class="border-t border-slate-100 pt-5">
                        <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest mb-3 font-bold">Assign Classes</p>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($classes as $class)
                            <label class="flex items-center gap-2 p-2.5 border border-slate-200 rounded-lg hover:border-violet-400 cursor-pointer transition-all">
                                <input type="checkbox" name="class_ids[]" value="{{ $class->id }}" class="rounded text-violet-600">
                                <span class="text-xs font-medium text-slate-700">{{ $class->name }} - {{ $class->section }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Class Teacher Of -->
                    <div class="border-t border-slate-100 pt-5">
                        <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest mb-3 font-bold">Class Teacher Of</p>
                        <select name="class_teacher_of" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none appearance-none bg-white">
                            <option value="">None</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }} - {{ $class->section }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Subject Qualification -->
                    @if($subjects->count())
                    <div class="border-t border-slate-100 pt-5">
                        <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest mb-3 font-bold">Subjects Qualified to Teach</p>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($subjects as $subject)
                            <label class="flex items-center gap-2 p-2.5 border border-slate-200 rounded-lg hover:border-violet-400 cursor-pointer transition-all">
                                <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" class="rounded text-violet-600">
                                <span class="text-xs font-medium text-slate-700">{{ $subject->name }} ({{ $subject->code }})</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>

                <div class="p-6 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-3 rounded-b-2xl">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">Onboard Teacher</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Teacher Modal -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="editModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl relative z-10 border border-slate-200/60 max-h-[90vh] flex flex-col overflow-hidden" @click.stop>
            <div class="flex justify-between items-center p-6 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Edit Teacher</h3>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="editTeacher.name"></p>
                </div>
                <button @click="editModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <form method="POST" :action="'/school/teachers/' + editTeacher.id" class="flex-1 flex flex-col min-h-0">
                @csrf
                @method('PATCH')
                <div class="p-6 space-y-5 overflow-y-auto flex-1 min-h-0">
                    <div>
                        <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest mb-3 font-bold">Personal Information</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Full Name</label>
                                <input type="text" name="name" :value="editTeacher.name" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none placeholder-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email</label>
                                <input type="email" name="email" :value="editTeacher.email" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none placeholder-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Mobile</label>
                                <input type="text" name="mobile" :value="editTeacher.mobile" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none placeholder-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Employee ID</label>
                                <input type="text" name="employee_id" :value="editTeacher.employee_id" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none placeholder-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Designation</label>
                                <input type="text" name="designation" :value="editTeacher.designation" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none placeholder-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Qualification</label>
                                <input type="text" name="qualification" :value="editTeacher.qualification" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none placeholder-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Experience</label>
                                <input type="text" name="experience" :value="editTeacher.experience" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none placeholder-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Joining Date</label>
                                <input type="date" name="joining_date" :value="editTeacher.joining_date" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none">
                            </div>
                        </div>
                    </div>

                    @if($classes->count())
                    <div class="border-t border-slate-100 pt-5">
                        <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest mb-3 font-bold">Assign Classes</p>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($classes as $class)
                            <label class="flex items-center gap-2 p-2.5 border border-slate-200 rounded-lg hover:border-violet-400 cursor-pointer transition-all">
                                <input type="checkbox" name="class_ids[]" value="{{ $class->id }}" class="rounded text-violet-600"
                                       :checked="editTeacher.class_ids && editTeacher.class_ids.includes({{ $class->id }})">
                                <span class="text-xs font-medium text-slate-700">{{ $class->name }} - {{ $class->section }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-5">
                        <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest mb-3 font-bold">Class Teacher Of</p>
                        <select name="class_teacher_of" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none appearance-none bg-white">
                            <option value="">None</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}" :selected="editTeacher.class_teacher_of == {{ $class->id }}">{{ $class->name }} - {{ $class->section }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Subject Qualification -->
                    @if($subjects->count())
                    <div class="border-t border-slate-100 pt-5">
                        <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest mb-3 font-bold">Subjects Qualified to Teach</p>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($subjects as $subject)
                            <label class="flex items-center gap-2 p-2.5 border border-slate-200 rounded-lg hover:border-violet-400 cursor-pointer transition-all">
                                <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" class="rounded text-violet-600"
                                       :checked="editTeacher.subject_ids && editTeacher.subject_ids.includes({{ $subject->id }})">
                                <span class="text-xs font-medium text-slate-700">{{ $subject->name }} ({{ $subject->code }})</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif
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
