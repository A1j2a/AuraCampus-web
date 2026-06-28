@extends('layouts.superadmin')

@section('title', 'AuraCampus | School Admins')

@section('content')
<div x-data="{
    showEditModal: false,
    profilePreview: null,
    editAdmin: {
        id: '',
        name: '',
        email: '',
        profile_image_url: ''
    },
    openEditModal(admin) {
        this.editAdmin = {
            id: admin.id,
            name: admin.name || '',
            email: admin.email || '',
            profile_image_url: admin.profile_image ? '/storage/' + admin.profile_image : null
        };
        this.profilePreview = this.editAdmin.profile_image_url;
        this.showEditModal = true;
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
    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-700 flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    <div class="premium-card p-8 rounded-2xl relative overflow-hidden bg-white border border-slate-200/60 shadow-sm">
    <div class="absolute -right-20 -top-20 w-48 h-48 bg-purple-500/5 rounded-full blur-2xl"></div>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">School Admins Directory</h2>
            <p class="text-xs text-slate-500 mt-1">Manage global credentials and administrative roles assigned to campus administrators.</p>
        </div>
    </div>

    <!-- Admins Table -->
    <div class="overflow-x-auto border border-slate-100 rounded-xl">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100 font-mono text-[9px] text-slate-400 uppercase tracking-wider">
                    <th class="px-6 py-4">Admin</th>
                    <th class="px-6 py-4">Email Address</th>
                    <th class="px-6 py-4">Role</th>
                    <th class="px-6 py-4">Assigned Campus</th>
                    <th class="px-6 py-4">Registered Date</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($admins as $admin)
                <tr class="hover:bg-slate-50/50 transition-all duration-150">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            @if($admin->profile_image)
                            <img src="{{ asset('storage/' . $admin->profile_image) }}" alt="{{ $admin->name }}" class="w-8 h-8 rounded-lg object-cover border border-slate-100 shrink-0">
                            @else
                            <div class="w-8 h-8 rounded-lg bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600 font-bold shrink-0">
                                {{ strtoupper(substr($admin->name, 0, 2)) }}
                            </div>
                            @endif
                            <span class="font-bold text-slate-800">{{ $admin->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 font-mono text-slate-600">
                        {{ $admin->email }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-indigo-50 text-indigo-700 border border-indigo-100 text-[9px] font-bold rounded-full uppercase">
                            <span class="material-symbols-outlined text-[12px]">shield_person</span>
                            {{ $admin->getRoleNames()->first() ?? 'school-admin' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($admin->school)
                        <div class="flex items-center gap-2">
                            @if($admin->school->logo_path)
                            <img src="{{ asset('storage/' . $admin->school->logo_path) }}" alt="{{ $admin->school->name }}" class="w-5 h-5 rounded object-cover shrink-0 border border-slate-100">
                            @endif
                            <div>
                                <span class="font-bold text-slate-800">{{ $admin->school->name }}</span>
                                <span class="block text-[9px] text-slate-400 font-mono mt-0.5">slug: {{ $admin->school->slug }}</span>
                            </div>
                        </div>
                        @else
                        <span class="text-slate-400 italic">No Campus Assigned</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 font-mono text-slate-450 text-[10px]">
                        {{ $admin->created_at->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $status = $admin->school?->status ?? 'active';
                            $badge = $status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-150' : 'bg-amber-50 text-amber-700 border-amber-150';
                        @endphp
                        <span class="px-2 py-0.5 border text-[9px] font-mono rounded font-bold uppercase {{ $badge }}">
                            {{ $status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button @click="openEditModal({{ json_encode($admin) }})" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 hover:text-indigo-700 text-[10px] font-bold rounded-lg cursor-pointer transition-all flex items-center gap-1 ml-auto">
                            <span class="material-symbols-outlined text-[14px]">edit</span>
                            Edit
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-405 italic">
                        <span class="material-symbols-outlined text-4xl mb-3 block">admin_panel_settings</span>
                        No school admins registered yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Admin Modal -->
<div x-show="showEditModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="fixed inset-0 bg-slate-900/30 backdrop-blur-sm" @click="showEditModal = false"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 relative z-10 border border-slate-200/60 max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-base font-bold text-slate-900">Edit Admin Credentials</h3>
                <p class="text-[10px] text-slate-400 mt-1">Update profile information and password for this campus administrator.</p>
            </div>
            <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        <form method="POST" :action="'/super-admin/admins/' + editAdmin.id" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <div class="space-y-4">
                <div class="p-3 bg-purple-50/50 rounded-xl border border-purple-100">
                    <h4 class="text-[10px] font-mono font-bold text-purple-600 uppercase tracking-wider mb-3">Admin Details</h4>
                    <div class="space-y-3">
                        <!-- Profile Image -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Profile Picture</label>
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden bg-slate-50 shrink-0 transition-all hover:border-purple-300">
                                    <template x-if="profilePreview">
                                        <img :src="profilePreview" alt="Profile Preview" class="w-full h-full object-cover rounded-full">
                                    </template>
                                    <template x-if="!profilePreview">
                                        <span class="material-symbols-outlined text-slate-300 text-[20px]">person</span>
                                    </template>
                                </div>
                                <div class="flex-1">
                                    <input type="file" name="profile_image" accept="image/*" @change="handleProfileUpload($event)"
                                           class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-600 hover:file:bg-purple-100 file:cursor-pointer cursor-pointer">
                                    <p class="text-[9px] text-slate-400 mt-1">JPG, PNG, SVG or WebP. Max 2MB.</p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Administrator Name</label>
                            <input type="text" name="name" x-model="editAdmin.name" required
                                   class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-purple">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Admin Email</label>
                            <input type="email" name="email" x-model="editAdmin.email" required
                                   class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-purple">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Reset Password <span class="text-slate-400 font-normal">(Leave blank to keep current)</span></label>
                            <input type="password" name="password" placeholder="Min 6 characters" minlength="6"
                                   class="w-full px-4 py-2.5 premium-input rounded-xl text-xs font-medium focus:outline-none focus:premium-input-focus-purple">
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="showEditModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors cursor-pointer">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm">Update Credentials</button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
