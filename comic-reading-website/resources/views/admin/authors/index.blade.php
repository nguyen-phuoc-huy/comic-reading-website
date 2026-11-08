@extends('layouts.admin')

@section('title', 'Quản lý tác giả')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Quản lý tác giả</h4>
    <a href="{{ route('admin.authors.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Thêm tác giả</a>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5">
        <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Tìm tác giả...">
    </div>
    <div class="col-md-2"><button class="btn btn-outline-primary w-100">Tìm</button></div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Tên</th>
                    <th>Slug</th>
                    <th class="text-center">Số truyện</th>
                    <th class="text-end" style="width:160px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($authors as $author)
                    <tr>
                        <td class="fw-semibold">{{ $author->name }}</td>
                        <td><code>{{ $author->slug }}</code></td>
                        <td class="text-center">{{ $author->comics_count }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.authors.edit', $author) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Sửa</a>
                            <form method="POST" action="{{ route('admin.authors.destroy', $author) }}" class="d-inline"
                                  onsubmit="return confirm('Xóa tác giả này? Các truyện của họ sẽ chuyển thành chưa rõ tác giả.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Chưa có tác giả nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-center mt-3">
    {{ $authors->links('pagination::bootstrap-5') }}
</div>
@endsection
