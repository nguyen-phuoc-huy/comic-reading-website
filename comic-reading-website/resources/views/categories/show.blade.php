@extends('layouts.site')

@section('title', $category->name . ' - ' . config('app.name'))

@section('content')
<div class="mb-3">
    <h4 class="fw-bold mb-1"><i class="bi bi-tag"></i> Thể loại: {{ $category->name }}</h4>
    @if ($category->description)
        <p class="text-muted mb-0">{{ $category->description }}</p>
    @endif
</div>

<form method="GET" action="{{ route('categories.show', $category) }}" class="card card-body shadow-sm mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small mb-1">Trạng thái</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Tất cả</option>
                <option value="ongoing" @selected(request('status') === 'ongoing')>Đang tiến hành</option>
                <option value="completed" @selected(request('status') === 'completed')>Hoàn thành</option>
                <option value="dropped" @selected(request('status') === 'dropped')>Tạm ngưng</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small mb-1">Sắp xếp</label>
            <select name="sort" class="form-select form-select-sm">
                <option value="latest" @selected(request('sort', 'latest') === 'latest')>Mới cập nhật</option>
                <option value="views" @selected(request('sort') === 'views')>Xem nhiều nhất</option>
                <option value="name" @selected(request('sort') === 'name')>Tên A-Z</option>
            </select>
        </div>
        <div class="col-md-4">
            <button class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i> Lọc</button>
        </div>
    </div>
</form>

@if ($comics->isEmpty())
    <div class="alert alert-info">Chưa có truyện nào thuộc thể loại này.</div>
@else
    <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-3">
        @foreach ($comics as $comic)
            @include('partials.comic-card', ['comic' => $comic])
        @endforeach
    </div>
    <div class="d-flex justify-content-center mt-4">
        {{ $comics->links('pagination::bootstrap-5') }}
    </div>
@endif
@endsection
