@extends('layouts.school')

@section('title', 'AuraCampus | Parents')

@section('content')
<div x-data="{
    showAddModal: {{ $errors->any() ? 'true' : 'false' }},
    showLinkModal: false,
    editModal: false,
    editParent: {},
    selectedParentId: '',
    selectedParentName: '',
    childrenCount: 1,
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
            <p class="text-xs text-slate-500 mt-1">Register parents and their children together in one flow.</p>
        </div>
        <button @click="showAddModal = true" class="px-4 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-[16px]">person_add</span>
            Add Parent
        </button>
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

    <!-- Parents Table -->
    <div class="premium-card rounded-2xl overflow-hidden bg-white">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <span class="text-xs font-semibold text-slate-500">{{ $parents->count() }} Parents Registered</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Parent</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Relation</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Children</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Credentials</th>
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
                            <span class="text-xs text-slate-600">{{ $parent->parentDetail?->relation ?? '—' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($parent->students as $child)
                                <span class="px-2 py-0.5 bg-slate-100 border border-slate-200 text-slate-700 text-[10px] rounded-lg font-medium inline-flex items-center gap-1">
                                    <span class="font-bold text-slate-900">{{ $child->name }}</span>
                                    <span class="text-[9px] bg-indigo-50 text-indigo-600 px-1 rounded font-mono font-semibold">
                                        {{ $child->studentDetail?->class ? $child->studentDetail->class->name . '-' . $child->studentDetail->class->section : '—' }}
                                    </span>
                                </span>
                                @empty
                                <span class="text-xs text-slate-400 italic">No children</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($parent->credential)
                            <div class="text-[10px] font-mono text-slate-600">
                                <p><span class="text-slate-400">User:</span> {{ $parent->credential->username }}</p>
                                <p><span class="text-slate-400">Pass:</span> {{ $parent->credential->plain_password }}</p>
                            </div>
                            @else
                            <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button @click='editModal = true; editParent = {{ json_encode(["id" => $parent->id, "name" => $parent->name, "email" => $parent->email, "mobile" => $parent->phone ?? "", "relation" => $parent->parentDetail?->relation ?? "", "occupation" => $parent->parentDetail?->occupation ?? "", "emergency_contact" => $parent->parentDetail?->emergency_contact ?? ""]) }}'
                                        class="px-2 py-1 text-[10px] font-bold text-violet-600 bg-violet-50 hover:bg-violet-100 border border-violet-200/50 rounded-lg cursor-pointer flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[12px]">edit</span>
                                    Edit
                                </button>
                                <button @click="openLinkModal({{ $parent->id }}, '{{ addslashes($parent->name) }}')"
                                        class="px-2.5 py-1 text-[11px] font-bold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200/50 rounded-lg transition-all cursor-pointer inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[13px]">link</span>
                                    Add Child
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <span class="material-symbols-outlined text-teal-600 text-4xl mb-3">family_restroom</span>
                            <h4 class="text-sm font-bold text-slate-800 mb-1">No Parents Registered</h4>
                            <p class="text-xs text-slate-500">Click "Add Parent" to register your first parent.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Parent Modal -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="showAddModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl relative z-10 border border-slate-200/60 max-h-[90vh] flex flex-col overflow-hidden" @click.stop>
            <div class="flex justify-between items-center p-6 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Register Parent & Children</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Credentials will be auto-generated</p>
                </div>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <form method="POST" action="{{ route('school.parents.store') }}" class="flex-1 flex flex-col min-h-0">
                @csrf
                <div class="p-6 space-y-5 overflow-y-auto flex-1 min-h-0">

                    <!-- Parent Info -->
                    <div>
                        <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest mb-3 font-bold">Parent Information</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Full Name</label>
                                <input type="text" name="name" placeholder="e.g. Rajesh Patel" required
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none placeholder-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email</label>
                                <input type="email" name="email" placeholder="email@example.com" required
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none placeholder-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Mobile</label>
                                <input type="text" name="mobile" placeholder="9876543210" required
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none placeholder-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Relation</label>
                                <select name="relation" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none appearance-none bg-white">
                                    <option value="">Select</option>
                                    <option value="Father">Father</option>
                                    <option value="Mother">Mother</option>
                                    <option value="Guardian">Guardian</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Occupation</label>
                                <input type="text" name="occupation" placeholder="e.g. Business"
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none placeholder-slate-300">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Emergency Contact</label>
                                <input type="text" name="emergency_contact" placeholder="Emergency number"
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none placeholder-slate-300">
                            </div>
                        </div>
                    </div>

                    <!-- Children Count -->
                    <div class="border-t border-slate-100 pt-5">
                        <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest mb-3 font-bold">Number of Children</p>
                        <div class="flex gap-3">
                            @foreach([1,2,3,4] as $num)
                            <button type="button" @click="childrenCount = {{ $num }}"
                                    :class="childrenCount === {{ $num }} ? 'bg-violet-600 text-white border-violet-600' : 'bg-white text-slate-600 border-slate-200 hover:border-violet-400'"
                                    class="w-10 h-10 rounded-xl border-2 text-xs font-bold transition-all cursor-pointer">
                                {{ $num }}
                            </button>
                            @endforeach
                        </div>
                        <input type="hidden" name="children_count" :value="childrenCount">
                    </div>

                    <!-- Dynamic Children Forms -->
                    @foreach([0,1,2,3] as $i)
                    <div x-show="childrenCount > {{ $i }}" class="border border-slate-200 rounded-xl p-4 bg-slate-50/50">
                        <p class="text-[10px] font-mono text-violet-600 uppercase tracking-widest mb-3 font-bold">Child {{ $i + 1 }}</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Student Name</label>
                                <input type="text" name="children[{{ $i }}][name]" :required="childrenCount > {{ $i }}" placeholder="Full name"
                                       class="w-full px-3 py-2 premium-input rounded-lg text-xs font-medium focus:outline-none placeholder-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Admission No.</label>
                                <input type="text" name="children[{{ $i }}][admission_number]" :required="childrenCount > {{ $i }}" placeholder="e.g. AC/2025/001"
                                       class="w-full px-3 py-2 premium-input rounded-lg text-xs font-medium focus:outline-none placeholder-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Roll Number</label>
                                <input type="text" name="children[{{ $i }}][roll_number]" placeholder="e.g. 01"
                                       class="w-full px-3 py-2 premium-input rounded-lg text-xs font-medium focus:outline-none placeholder-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Class</label>
                                <select name="children[{{ $i }}][class_id]" :required="childrenCount > {{ $i }}" class="w-full px-3 py-2 premium-input rounded-lg text-xs font-medium focus:outline-none appearance-none bg-white">
                                    <option value="">Select Class</option>
                                    @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }} - {{ $class->section }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Gender</label>
                                <select name="children[{{ $i }}][gender]" class="w-full px-3 py-2 premium-input rounded-lg text-xs font-medium focus:outline-none appearance-none bg-white">
                                    <option value="">Select</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Date of Birth</label>
                                <input type="date" name="children[{{ $i }}][dob]"
                                       class="w-full px-3 py-2 premium-input rounded-lg text-xs font-medium focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Blood Group</label>
                                <select name="children[{{ $i }}][blood_group]" class="w-full px-3 py-2 premium-input rounded-lg text-xs font-medium focus:outline-none appearance-none bg-white">
                                    <option value="">Select</option>
                                    @foreach(['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg)
                                    <option value="{{ $bg }}">{{ $bg }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    @endforeach

                </div>

                <div class="p-6 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-3 rounded-b-2xl">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">
                        Register Parent & Children
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Link Child Modal -->
    <div x-show="showLinkModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="showLinkModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 border border-slate-200/60" @click.stop>
            <div class="flex justify-between items-center p-6 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Link Existing Student</h3>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="'Parent: ' + selectedParentName"></p>
                </div>
                <button @click="showLinkModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" action="{{ route('school.parents.link') }}" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="parent_id" :value="selectedParentId">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Student</label>
                    <select name="student_id" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none appearance-none bg-white">
                        <option value="">Select Student</option>
                        @foreach(\App\Models\User::where('school_id', auth()->user()->school_id)->role('student')->with('studentDetail.class')->get() as $s)
                        <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->studentDetail?->class?->name }} - {{ $s->studentDetail?->class?->section }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Relationship</label>
                    <select name="relationship" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none appearance-none bg-white">
                        <option value="">Select</option>
                        <option value="Father">Father</option>
                        <option value="Mother">Mother</option>
                        <option value="Guardian">Guardian</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showLinkModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl cursor-pointer shadow-sm">Link Student</button>
                </div>
            </form>
        </div>
    </div>


    <!-- Edit Parent Modal -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="editModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg relative z-10 border border-slate-200/60 max-h-[90vh] flex flex-col overflow-hidden" @click.stop>
            <div class="flex justify-between items-center p-6 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Edit Parent</h3>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="editParent.name"></p>
                </div>
                <button @click="editModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" :action="'/school/parents/' + editParent.id" class="flex-1 flex flex-col min-h-0">
                @csrf
                @method('PATCH')
                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Full Name</label>
                            <input type="text" name="name" :value="editParent.name" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email</label>
                            <input type="email" name="email" :value="editParent.email" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Mobile</label>
                            <input type="text" name="mobile" :value="editParent.mobile" required class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Relation</label>
                            <select name="relation" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none appearance-none bg-white">
                                <option value="Father" :selected="editParent.relation === 'Father'">Father</option>
                                <option value="Mother" :selected="editParent.relation === 'Mother'">Mother</option>
                                <option value="Guardian" :selected="editParent.relation === 'Guardian'">Guardian</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Occupation</label>
                            <input type="text" name="occupation" :value="editParent.occupation" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Emergency Contact</label>
                            <input type="text" name="emergency_contact" :value="editParent.emergency_contact" class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none">
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
