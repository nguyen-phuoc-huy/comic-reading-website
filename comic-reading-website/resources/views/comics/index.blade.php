@extends('layouts.site')

@section('title', 'Danh sách truyện - ' . config('app.name'))

@section('content')
<h4 class="fw-bold mb-3"><i class="bi bi-collection"></i> Danh sách truyện</h4>

<form method="GET" action="{{ route('comics.index') }}" class="card card-body shadow-sm mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small mb-1">Thể loại</label>
            <select name="category" class="form-select form-select-sm">
                <option value="">Tất cả</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Trạng thái</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Tất cả</option>
                <option value="ongoing" @selected(request('status') === 'ongoing')>Đang tiến hành</option>
                <option value="completed" @selected(request('status') === 'completed')>Hoàn thành</option>
                <option value="dropped" @selected(request('status') === 'dropped')>Tạm ngưng</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Sắp xếp</label>
            <select name="sort" class="form-select form-select-sm">
                <option value="latest" @selected(request('sort', 'latest') === 'latest')>Mới cập nhật</option>
                <option value="views" @selected(request('sort') === 'views')>Xem nhiều nhất</option>
                <option value="name" @selected(request('sort') === 'name')>Tên A-Z</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary btn-sm flex-fill"><i class="bi bi-funnel"></i> Lọc</button>
            <a href="{{ route('comics.index') }}" class="btn btn-outline-secondary btn-sm">Xóa lọc</a>
        </div>
    </div>
</form>

@if ($comics->isEmpty())
    <div class="alert alert-info">Không có truyện nào phù hợp.</div>
@else
    <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-3">
        @foreach ($comics as $comic)
            @include('partials.comic-card', ['comic' => $comic])
        @endforeach
    </div>
    <div class="d-flex justify-content-center mt-4">
        {{ $comics->links('pagination::bootstrap-4') }}
    </div>
@endif
@endsection
