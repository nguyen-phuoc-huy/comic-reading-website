@extends('layouts.admin')

@section('title', 'Quản lý thể loại')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Quản lý thể loại</h4>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Thêm thể loại</a>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5">
        <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Tìm thể loại...">
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
                    <th>Mô tả</th>
                    <th class="text-center">Số truyện</th>
                    <th class="text-end" style="width:160px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td class="fw-semibold">{{ $category->name }}</td>
                        <td><code>{{ $category->slug }}</code></td>
                        <td class="text-muted">{{ \Illuminate\Support\Str::limit($category->description, 60) }}</td>
                        <td class="text-center">{{ $category->comics_count }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Sửa</a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="d-inline"
                                  onsubmit="return confirm('Xóa thể loại này?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Chưa có thể loại nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-center mt-3">
    {{ $categories->links('pagination::bootstrap-5') }}
</div>
@endsection
