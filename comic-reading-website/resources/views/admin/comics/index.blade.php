@extends('layouts.admin')

@section('title', 'Quản lý truyện')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Quản lý truyện</h4>
    <a href="{{ route('admin.comics.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Thêm truyện</a>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5">
        <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Tìm theo tên truyện...">
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">Tất cả trạng thái</option>
            <option value="ongoing" @selected(request('status') === 'ongoing')>Đang tiến hành</option>
            <option value="completed" @selected(request('status') === 'completed')>Hoàn thành</option>
            <option value="dropped" @selected(request('status') === 'dropped')>Tạm ngưng</option>
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-outline-primary w-100">Lọc</button></div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:70px">Bìa</th>
                    <th>Tên truyện</th>
                    <th>Tác giả</th>
                    <th>Trạng thái</th>
                    <th class="text-center">Chương</th>
                    <th class="text-end">Lượt xem</th>
                    <th class="text-end" style="width:230px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($comics as $comic)
                    <tr>
                        <td>
                            <img src="{{ $comic->cover ? asset('storage/' . $comic->cover) : 'https://placehold.co/300x400?text=No' }}"
                                 style="width:48px;height:64px;object-fit:cover" class="rounded" alt="">
                        </td>
                        <td>
                            <a href="{{ route('comics.show', $comic) }}" target="_blank" class="fw-semibold text-decoration-none">{{ $comic->title }}</a>
                        </td>
                        <td>{{ $comic->author->name ?? '—' }}</td>
                        <td>@include('partials.status-badge', ['status' => $comic->status])</td>
                        <td class="text-center">{{ $comic->chapters_count }}</td>
                        <td class="text-end">{{ number_format($comic->views) }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.comics.chapters.index', $comic) }}" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-files"></i> Chương
                            </a>
                            <a href="{{ route('admin.comics.edit', $comic) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i> Sửa
                            </a>
                            <form method="POST" action="{{ route('admin.comics.destroy', $comic) }}" class="d-inline"
                                  onsubmit="return confirm('Xóa truyện này?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Chưa có truyện nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-center mt-3">
    {{ $comics->links('pagination::bootstrap-5') }}
</div>
@endsection
