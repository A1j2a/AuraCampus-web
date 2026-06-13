@extends('layouts.superadmin')

@section('title', 'AuraCampus | School Admins')

@section('content')
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
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-405 italic">
                        <span class="material-symbols-outlined text-4xl mb-3 block">admin_panel_settings</span>
                        No school admins registered yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
