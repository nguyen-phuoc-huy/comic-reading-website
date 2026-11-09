@extends('layouts.admin')

@section('title', 'Quản lý thể loại')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h4 class="fw-bold mb-0">Quản lý thể loại</h4>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importCategoriesModal">
            <i class="bi bi-file-earmark-spreadsheet"></i> Nhập từ Excel
        </button>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Thêm thể
            loại</a>
    </div>
</div>

{{-- Kết quả sau khi nhập Excel --}}
@include('admin.imports._result')

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
                        <a href="{{ route('admin.categories.edit', $category) }}"
                            class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Sửa</a>
                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="d-inline"
                            onsubmit="return confirm('Xóa thể loại này?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">Chưa có thể loại nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-center mt-3">
    {{ $categories->links('pagination::bootstrap-5') }}
</div>

{{-- Cửa sổ nhập thể loại từ Excel --}}
<div class="modal fade" id="importCategoriesModal" tabindex="-1" aria-labelledby="importCategoriesLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.imports.store', 'categories') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="return_to" value="categories">

                <div class="modal-header">
                    <h5 class="modal-title" id="importCategoriesLabel">
                        <i class="bi bi-file-earmark-spreadsheet text-success"></i> Nhập thể loại từ Excel
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>

                <div class="modal-body">
                    <p class="small text-muted mb-2">
                        File Excel (.xlsx, .xls hoặc .csv), dòng 1 là tên cột, hệ thống chỉ đọc sheet đầu tiên.
                    </p>
                    <table class="table table-sm table-bordered small">
                        <thead class="table-light">
                            <tr>
                                <th>Cột</th>
                                <th>Bắt buộc</th>
                                <th>Ý nghĩa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>name</code></td>
                                <td class="text-danger fw-semibold">Có</td>
                                <td>Tên thể loại</td>
                            </tr>
                            <tr>
                                <td><code>description</code></td>
                                <td>Không</td>
                                <td>Mô tả ngắn</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="small text-muted">
                        Thể loại trùng tên đã có sẽ được cập nhật mô tả (hoặc bỏ qua nếu không đổi), không bị nhân đôi.
                    </p>

                    <a href="{{ route('admin.imports.template', 'categories') }}"
                        class="btn btn-outline-secondary btn-sm mb-3">
                        <i class="bi bi-download"></i> Tải file mẫu
                    </a>

                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button class="btn btn-success"><i class="bi bi-upload"></i> Nhập</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection