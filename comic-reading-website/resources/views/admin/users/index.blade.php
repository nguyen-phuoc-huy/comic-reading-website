@extends('layouts.admin')

@section('title', 'Quản lý người dùng')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h4 class="fw-bold mb-0">Quản lý người dùng</h4>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="bi bi-person-plus"></i> Thêm người dùng</a>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5">
        <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Tìm theo tên hoặc email...">
    </div>
    <div class="col-md-3">
        <select name="role" class="form-select">
            <option value="">Tất cả vai trò</option>
            <option value="admin" @selected(request('role') === 'admin')>Quản trị viên</option>
            <option value="user" @selected(request('role') === 'user')>Người dùng</option>
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-outline-primary w-100">Lọc</button></div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:60px">#</th>
                    <th>Tên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Ngày tạo</th>
                    <th class="text-end" style="width:160px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    @php $isMe = $user->id === auth()->id(); @endphp
                    <tr>
                        <td class="text-muted">{{ $user->id }}</td>
                        <td class="fw-semibold">
                            {{ $user->name }}
                            @if ($isMe)<span class="badge text-bg-info ms-1">Bạn</span>@endif
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if ($user->role === 'admin')
                                <span class="badge text-bg-danger">Quản trị viên</span>
                            @else
                                <span class="badge text-bg-secondary">Người dùng</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $user->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i> Sửa
                            </a>
                            @if ($isMe)
                                <button class="btn btn-sm btn-outline-danger" disabled title="Không thể tự xóa chính mình">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @else
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline"
                                      onsubmit="return confirm('Xóa người dùng này? Bình luận, lịch sử đọc và danh sách theo dõi của họ cũng sẽ bị xóa.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Không có người dùng nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-center mt-3">
    {{ $users->links('pagination::bootstrap-5') }}
</div>
@endsection
