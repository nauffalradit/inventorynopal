@extends('layouts.app')

@section('content')
<div class="actions" style="justify-content:space-between;margin-top:0">
    <h1 style="margin:0">Kelola User</h1>
    <span class="muted">ADMIN: ubah role & aktif/nonaktif staff</span>
</div>

<form method="get" action="{{ route('admin.users.index') }}" style="margin-bottom:14px">
    <div class="form-grid" style="grid-template-columns:200px 200px auto;align-items:end">
        <x-field label="Role">
            <select name="role">
                <option value="">Semua</option>
                <option value="admin" @selected(($role ?? '')==='admin')>admin</option>
                <option value="staff" @selected(($role ?? '')==='staff')>staff</option>
            </select>
        </x-field>
        <x-field label="Status">
            <select name="status">
                <option value="">Semua</option>
                <option value="aktif" @selected(($status ?? '')==='aktif')>aktif</option>
                <option value="nonaktif" @selected(($status ?? '')==='nonaktif')>nonaktif</option>
            </select>
        </x-field>
        <div class="actions" style="margin-top:0"><x-btn type="submit">Filter</x-btn><x-btn href="{{ route('admin.users.index') }}">Reset</x-btn></div>
    </div>
</form>

<x-table :headers="['Nama', 'Email', 'Role', 'Status', 'Aksi']">
    @forelse ($users as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td><x-badge>{{ $user->role }}{{ $user->isAdmin() && $user->role !== 'admin' ? ' (fallback admin)' : '' }}</x-badge></td>
            <td><x-badge style="{{ $user->is_active ? '' : 'background:#fef2f2;color:#991b1b' }}">{{ $user->is_active ? 'aktif' : 'nonaktif' }}</x-badge></td>
            <td>
                <form class="inline" method="post" action="{{ route('admin.users.role', $user) }}">
                    @csrf @method('PATCH')
                    <select name="role" style="width:auto;min-height:32px;display:inline-block">
                        <option value="staff" @selected($user->role==='staff')>staff</option>
                        <option value="admin" @selected($user->role==='admin')>admin</option>
                    </select>
                    <x-btn type="submit">Ubah Role</x-btn>
                </form>
                <form class="inline" method="post" action="{{ route('admin.users.toggle', $user) }}">
                    @csrf @method('PATCH')
                    <x-btn type="submit">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</x-btn>
                </form>
            </td>
        </tr>
    @empty
        <x-empty-state :colspan="5" message="Belum ada user." />
    @endforelse
</x-table>
<div style="margin-top:14px">{{ $users->links() }}</div>

<p class="muted" style="margin-top:14px">Fallback <code>ADMIN_EMAILS</code> di <code>.env</code> tetap sebagai super-admin emergency jika role DB terhapus.</p>
@endsection
