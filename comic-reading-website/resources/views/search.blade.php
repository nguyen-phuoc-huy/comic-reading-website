@extends('layouts.site')

@section('title', 'Tìm kiếm - ' . config('app.name'))

@section('content')
<h4 class="fw-bold mb-3"><i class="bi bi-search"></i> Tìm kiếm truyện</h4>

<form method="GET" action="{{ route('search') }}" class="mb-4">
    <div class="input-group">
        <input type="search" name="q" value="{{ $keyword }}" maxlength="100" class="form-control"
               placeholder="Nhập tên truyện, tên khác hoặc tác giả..." autofocus>
        <button class="btn btn-primary"><i class="bi bi-search"></i> Tìm</button>
    </div>
</form>

@if (is_null($comics))
    <div class="alert alert-light border">Nhập từ khóa để bắt đầu tìm kiếm.</div>
@elseif ($comics->isEmpty())
    <div class="alert alert-warning">Không tìm thấy truyện nào với từ khóa "<strong>{{ $keyword }}</strong>".</div>
@else
    <p class="text-muted">Tìm thấy {{ $comics->total() }} kết quả cho "<strong>{{ $keyword }}</strong>"</p>
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
