@extends('layouts.superadmin')

@section('title', 'AuraCampus | Schools Management')

@section('content')
<div x-data="{
    showModal: false,
    showEditModal: false,
    logoPreview: null,
    profilePreview: null,
    editSchool: {
        id: '',
        name: '',
        email: '',
        phone: '',
        address: '',
        status: 'active',
        font_family: '',
        logo_url: ''
    },
    openEditModal(school) {
        this.editSchool = {
            id: school.id,
            name: school.name || '',
            email: school.email || '',
            phone: school.phone || '',
            address: school.address || '',
            status: school.status || 'active',
            font_family: school.font_family || '',
            logo_url: school.logo_path ? '/storage/' + school.logo_path : null
        };
        this.logoPreview = this.editSchool.logo_url;
        this.showEditModal = true;
    },
    handleLogoUpload(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => this.logoPreview = e.target.result;
            reader.readAsDataURL(file);
        }
    },
    handleProfileUpload(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => this.profilePreview = e.target.result;
            reader.readAsDataURL(file);
        }
    }
}">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Schools Management</h2>
            <p class="text-xs text-slate-500 mt-1">Configure and deploy new institution campuses onto the AuraCampus network.</p>
        </div>
        <button @click="logoPreview = null; profilePreview = null; showModal = true" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-[16px]">add</span>
            Provision Campus
        </button>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-700 flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="premium-card p-5 rounded-2xl flex flex-col justify-between h-32 relative overflow-hidden group">
            <div class="absolute -right-10 -top-10 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex justify-between items-start">
                <span class="text-[9px] font-mono text-slate-400 uppercase tracking-widest font-bold">Active Campuses</span>
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_6px_#10b981]"></span>
            </div>
            <div>
                <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ $schools->where('status', 'active')->count() }} Nodes</h3>
                <p class="text-[10px] text-slate-500 mt-1 font-medium">{{ $schools->pluck('name')->take(3)->join(', ') }}</p>
            </div>
        </div>
        <div class="premium-card p-5 rounded-2xl flex flex-col justify-between h-32 relative overflow-hidden group">
            <div class="absolute -right-10 -top-10 w-24 h-24 bg-amber-500/5 rounded-full blur-2xl group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex justify-between items-start">
                <span class="text-[9px] font-mono text-slate-400 uppercase tracking-widest font-bold">Total Users</span>
            </div>
            <div>
                <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ $schools->sum('users_count') }}</h3>
                <p class="text-[10px] text-slate-500 mt-1 font-medium">Across all campuses</p>
            </div>
        </div>
        <div class="premium-card p-5 rounded-2xl flex flex-col justify-between h-32 relative overflow-hidden group">
            <div class="absolute -right-10 -top-10 w-24 h-24 bg-indigo-500/5 rounded-full blur-2xl group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex justify-between items-start">
                <span class="text-[9px] font-mono text-slate-400 uppercase tracking-widest font-bold">Server Uptime</span>
                <span class="text-[9px] font-mono text-indigo-600 font-bold bg-indigo-50 px-1 py-0.5 rounded">99.98%</span>
            </div>
            <div>
                <h3 class="text-xl font-bold text-slate-900 tracking-tight">Active</h3>
                <p class="text-[10px] text-slate-500 mt-1 font-medium">All systems operational</p>
            </div>
        </div>
    </div>

    <!-- Schools Table -->
    <div class="premium-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50">
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Campus</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Admin</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Users</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider">Provisioned</th>
                        <th class="px-6 py-4 font-mono text-[9px] text-slate-400 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($schools as $school)
                    <tr class="hover:bg-slate-50/50 transition-all duration-150">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($school->logo_path)
                                <img src="{{ asset('storage/' . $school->logo_path) }}" alt="{{ $school->name }}" class="w-9 h-9 rounded-xl border border-slate-100 object-cover shadow-sm shrink-0">
                                @else
                                <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-bold shadow-sm shrink-0">
                                    {{ strtoupper(substr($school->name, 0, 2)) }}
                                </div>
                                @endif
                                <div>
                                    <p class="text-xs font-bold text-slate-800">{{ $school->name }}</p>
                                    <p class="text-[9px] text-slate-400 font-mono mt-0.5">{{ $school->slug }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-xs text-slate-600">{{ $school->email ?? '—' }}</p>
                            <p class="text-[9px] text-slate-400 font-mono">{{ $school->phone ?? '' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if($school->admin)
                            <div class="flex items-center gap-2.5">
                                @if($school->admin->profile_image)
                                <img src="{{ asset('storage/' . $school->admin->profile_image) }}" alt="{{ $school->admin->name }}" class="w-7 h-7 rounded-lg object-cover border border-slate-100 shrink-0">
                                @else
                                <div class="w-7 h-7 rounded-lg bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600 text-[10px] font-bold shrink-0">
                                    {{ strtoupper(substr($school->admin->name, 0, 2)) }}
                                </div>
                                @endif
                                <div>
                                    <p class="text-xs font-semibold text-slate-700">{{ $school->admin->name }}</p>
                                    <p class="text-[9px] text-slate-400 font-mono">{{ $school->admin->getRoleNames()->first() ?? 'school-admin' }}</p>
                                </div>
                            </div>
                            @else
                            <span class="text-[10px] text-slate-400 italic">No admin assigned</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-mono font-bold text-slate-700">{{ $school->users_count }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 text-[9px] font-bold rounded-full uppercase
                                {{ $school->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : ($school->status === 'pending' ? 'bg-amber-50 text-amber-700 border border-amber-100 animate-pulse' : 'bg-rose-50 text-rose-700 border border-rose-100') }}">
                                {{ $school->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-[10px] text-slate-400 font-mono">{{ $school->created_at->format('d M Y') }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button @click="openEditModal({{ json_encode($school) }})" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 hover:text-indigo-700 text-[10px] font-bold rounded-lg cursor-pointer transition-all flex items-center gap-1 ml-auto">
                                <span class="material-symbols-outlined text-[14px]">edit</span>
                                Edit
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <span class="material-symbols-outlined text-indigo-600 text-4xl mb-3 animate-float-slow">database</span>
                            <h4 class="text-sm font-bold text-slate-800 mb-1">No Campuses Provisioned</h4>
                            <p class="text-xs text-slate-500">Click "Provision Campus" to onboard a school.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Provision Campus Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 relative z-10 border border-slate-200/60 max-h-[90vh] overflow-y-auto" @click.stop
             x-data="{
                 logoPreview: null,
                 profilePreview: null,
                 handleLogoUpload(event) {
                     const file = event.target.files[0];
                     if (file) {
                         const reader = new FileReader();
                         reader.onload = (e) => this.logoPreview = e.target.result;
                         reader.readAsDataURL(file);
                     }
                 },
                 handleProfileUpload(event) {
                     const file = event.target.files[0];
                     if (file) {
                         const reader = new FileReader();
                         reader.onload = (e) => this.profilePreview = e.target.result;
                         reader.readAsDataURL(file);
                     }
                 }
             }">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Provision New Campus</h3>
                    <p class="text-[10px] text-slate-400 mt-1">This will create a school and its administrator account.</p>
                </div>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" action="{{ route('superadmin.schools.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div class="p-3 bg-indigo-50/50 rounded-xl border border-indigo-100">
                        <h4 class="text-[10px] font-mono font-bold text-indigo-600 uppercase tracking-wider mb-3">School Details</h4>
                        <div class="space-y-3">
                            <!-- School Logo Upload -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">School Logo <span class="text-slate-400 font-normal">(Optional)</span></label>
                                <div class="flex items-center gap-3">
                                    <div class="w-14 h-14 rounded-xl border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden bg-slate-50 shrink-0 transition-all hover:border-indigo-300">
                                        <template x-if="logoPreview">
                                            <img :src="logoPreview" alt="Logo Preview" class="w-full h-full object-cover rounded-xl">
                                        </template>
                                        <template x-if="!logoPreview">
                                            <span class="material-symbols-outlined text-slate-300 text-[24px]">add_photo_alternate</span>
                                        </template>
                                    </div>
                                    <div class="flex-1">
                                        <input type="file" name="school_logo" accept="image/*" @change="handleLogoUpload($event)"
                                               class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 file:cursor-pointer cursor-pointer">
                                        <p class="text-[9px] text-slate-400 mt-1">JPG, PNG, SVG or WebP. Max 2MB.</p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">School Name</label>
                                <input type="text" name="name" placeholder="e.g. Emerald High School" required
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus placeholder-slate-300">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email</label>
                                    <input type="email" name="email" placeholder="info@school.com"
                                           class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus placeholder-slate-300">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Phone</label>
                                    <input type="text" name="phone" placeholder="+91-XXXXXXXXXX"
                                           class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus placeholder-slate-300">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Address</label>
                                <input type="text" name="address" placeholder="Full school address"
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus placeholder-slate-300">
                            </div>


                        </div>
                    </div>

                    <div class="p-3 bg-emerald-50/50 rounded-xl border border-emerald-100">
                        <h4 class="text-[10px] font-mono font-bold text-emerald-600 uppercase tracking-wider mb-3">Admin Account</h4>
                        <div class="space-y-3">
                            <!-- Admin Profile Image -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Profile Image <span class="text-slate-400 font-normal">(Optional)</span></label>
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-full border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden bg-slate-50 shrink-0 transition-all hover:border-emerald-300">
                                        <template x-if="profilePreview">
                                            <img :src="profilePreview" alt="Profile Preview" class="w-full h-full object-cover rounded-full">
                                        </template>
                                        <template x-if="!profilePreview">
                                            <span class="material-symbols-outlined text-slate-300 text-[20px]">person</span>
                                        </template>
                                    </div>
                                    <div class="flex-1">
                                        <input type="file" name="admin_profile_image" accept="image/*" @change="handleProfileUpload($event)"
                                               class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-600 hover:file:bg-emerald-100 file:cursor-pointer cursor-pointer">
                                        <p class="text-[9px] text-slate-400 mt-1">JPG, PNG, SVG or WebP. Max 2MB.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Administrator Name</label>
                                    <input type="text" name="admin_name" placeholder="e.g. Dr. John Smith" required
                                           class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald placeholder-slate-300">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Role / Designation</label>
                                    <input type="text" name="admin_role" placeholder="e.g. Principal"
                                           class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald placeholder-slate-300">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Admin Email</label>
                                <input type="email" name="admin_email" placeholder="admin@school.com" required
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald placeholder-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Password</label>
                                <input type="password" name="admin_password" placeholder="Min 6 characters" required minlength="6"
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-emerald placeholder-slate-300">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">Provision Campus</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Campus Modal -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="showEditModal = false"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 relative z-10 border border-slate-200/60 max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Edit Campus Details</h3>
                    <p class="text-[10px] text-slate-400 mt-1">Update general configurations and status for this campus.</p>
                </div>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" :action="'/super-admin/schools/' + editSchool.id" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="space-y-4">
                    <div class="p-3 bg-indigo-50/50 rounded-xl border border-indigo-100">
                        <h4 class="text-[10px] font-mono font-bold text-indigo-600 uppercase tracking-wider mb-3">Campus Details</h4>
                        <div class="space-y-3">
                            <!-- School Logo Upload -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">School Logo</label>
                                <div class="flex items-center gap-3">
                                    <div class="w-14 h-14 rounded-xl border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden bg-slate-50 shrink-0 transition-all hover:border-indigo-300">
                                        <template x-if="logoPreview">
                                            <img :src="logoPreview" alt="Logo Preview" class="w-full h-full object-cover rounded-xl">
                                        </template>
                                        <template x-if="!logoPreview">
                                            <span class="material-symbols-outlined text-slate-300 text-[24px]">school</span>
                                        </template>
                                    </div>
                                    <div class="flex-1">
                                        <input type="file" name="school_logo" accept="image/*" @change="handleLogoUpload($event)"
                                               class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 file:cursor-pointer cursor-pointer">
                                        <p class="text-[9px] text-slate-400 mt-1">JPG, PNG, SVG or WebP. Max 2MB.</p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">School Name</label>
                                <input type="text" name="name" x-model="editSchool.name" required
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus placeholder-slate-300">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email</label>
                                    <input type="email" name="email" x-model="editSchool.email"
                                           class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus placeholder-slate-300">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Phone</label>
                                    <input type="text" name="phone" x-model="editSchool.phone"
                                           class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus placeholder-slate-300">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Address</label>
                                <input type="text" name="address" x-model="editSchool.address"
                                       class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus placeholder-slate-300">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Status</label>
                                    <select name="status" x-model="editSchool.status" required
                                            class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus appearance-none cursor-pointer bg-white">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Font Family</label>
                                    <input type="text" name="font_family" x-model="editSchool.font_family" placeholder="e.g. Inter"
                                           class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus placeholder-slate-300">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">Update Campus</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
