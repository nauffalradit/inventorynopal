@extends('layouts.app')

@section('content')
<div class="actions" style="justify-content:space-between;margin-top:0">
    <h1 style="margin:0">Kelola User</h1>
    <span class="muted">ADMIN: ubah role & aktif/nonaktif staff</span>
</div>

<form method="get" action="{{ route('admin.users.index') }}" style="margin-bottom:14px">
    <div class="form-grid" style="grid-template-columns:200px 200px auto;align-items:end">
        <label>Role
            <select name="role">
                <option value="">Semua</option>
                <option value="admin" @selected(($role ?? '')==='admin')>admin</option>
                <option value="staff" @selected(($role ?? '')==='staff')>staff</option>
            </select>
        </label>
        <label>Status
            <select name="status">
                <option value="">Semua</option>
                <option value="aktif" @selected(($status ?? '')==='aktif')>aktif</option>
                <option value="nonaktif" @selected(($status ?? '')==='nonaktif')>nonaktif</option>
            </select>
        </label>
        <div class="actions" style="margin-top:0"><button class="btn" type="submit">Filter</button><a class="btn" href="{{ route('admin.users.index') }}">Reset</a></div>
    </div>
</form>

<table>
    <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
    @forelse ($users as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td><span class="badge">{{ $user->role }}{{ $user->isAdmin() && $user->role !== 'admin' ? ' (fallback admin)' : '' }}</span></td>
            <td><span class="badge" style="{{ $user->is_active ? '' : 'background:#fef2f2;color:#991b1b' }}">{{ $user->is_active ? 'aktif' : 'nonaktif' }}</span></td>
            <td>
                <form class="inline" method="post" action="{{ route('admin.users.role', $user) }}">
                    @csrf @method('PATCH')
                    <select name="role" style="width:auto;min-height:32px;display:inline-block">
                        <option value="staff" @selected($user->role==='staff')>staff</option>
                        <option value="admin" @selected($user->role==='admin')>admin</option>
                    </select>
                    <button class="btn" type="submit">Ubah Role</button>
                </form>
                <form class="inline" method="post" action="{{ route('admin.users.toggle', $user) }}">
                    @csrf @method('PATCH')
                    <button class="btn" type="submit">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="muted">Belum ada user.</td></tr>
    @endforelse
    </tbody>
</table>
<div style="margin-top:14px">{{ $users->links() }}</div>

<p class="muted" style="margin-top:14px">Fallback <code>ADMIN_EMAILS</code> di <code>.env</code> tetap sebagai super-admin emergency jika role DB terhapus.</p>
@endsection
