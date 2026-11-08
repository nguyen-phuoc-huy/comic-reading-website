@extends('layouts.site')

@section('title', config('app.name') . ' - Đọc truyện tranh online')

@section('content')

{{-- Đọc tiếp --}}
@if ($continueReading->isNotEmpty())
    <section class="mb-4">
        <h5 class="fw-bold mb-3"><i class="bi bi-bookmark-star text-warning"></i> Đọc tiếp</h5>
        <div class="row row-cols-1 row-cols-md-3 g-3">
            @foreach ($continueReading as $history)
                @continue(!$history->comic || !$history->chapter)
                <div class="col">
                    <div class="card shadow-sm h-100">
                        <div class="row g-0 h-100">
                            <div class="col-3">
                                <img src="{{ $history->comic->cover ? asset('storage/' . $history->comic->cover) : 'https://placehold.co/300x400?text=No+Cover' }}"
                                     class="img-fluid rounded-start h-100 w-100" style="object-fit:cover" alt="">
                            </div>
                            <div class="col-9">
                                <div class="card-body py-2">
                                    <a href="{{ route('comics.show', $history->comic) }}" class="text-decoration-none text-dark fw-semibold d-block text-truncate">
                                        {{ $history->comic->title }}
                                    </a>
                                    <small class="text-muted d-block mb-2">Đã đọc đến chap {{ (float) $history->chapter->number }}</small>
                                    <a href="{{ route('chapters.show', [$history->comic, $history->chapter]) }}" class="btn btn-sm btn-primary">
                                        Đọc tiếp <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif

<div class="row g-4">
    {{-- Truyện mới cập nhật --}}
    <div class="col-lg-9">
        <h5 class="fw-bold mb-3"><i class="bi bi-lightning-charge-fill text-warning"></i> Truyện mới cập nhật</h5>

        @if ($latestComics->isEmpty())
            <div class="alert alert-info">Chưa có truyện nào.</div>
        @else
            <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3">
                @foreach ($latestComics as $comic)
                    @include('partials.comic-card', ['comic' => $comic])
                @endforeach
            </div>
            <div class="d-flex justify-content-center mt-4">
                {{ $latestComics->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-3">
        <div class="card shadow-sm mb-4">
            <div class="card-header fw-semibold"><i class="bi bi-fire text-danger"></i> Xem nhiều nhất</div>
            <ol class="list-group list-group-numbered list-group-flush">
                @foreach ($topComics as $top)
                    <li class="list-group-item d-flex justify-content-between align-items-start gap-2">
                        <a href="{{ route('comics.show', $top) }}" class="text-decoration-none text-dark me-auto">
                            {{ $top->title }}
                        </a>
                        <span class="badge text-bg-secondary">{{ number_format($top->views) }}</span>
                    </li>
                @endforeach
            </ol>
        </div>

        <div class="card shadow-sm">
            <div class="card-header fw-semibold"><i class="bi bi-tags"></i> Thể loại</div>
            <div class="card-body">
                @foreach ($categories as $category)
                    <a href="{{ route('categories.show', $category) }}" class="badge text-bg-light border text-decoration-none mb-1">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Truyện hoàn thành --}}
@if ($completedComics->isNotEmpty())
    <section class="mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-check-circle-fill text-success"></i> Truyện đã hoàn thành</h5>
            <a href="{{ route('comics.index', ['status' => 'completed']) }}" class="small">Xem tất cả</a>
        </div>
        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-3">
            @foreach ($completedComics as $comic)
                @include('partials.comic-card', ['comic' => $comic])
            @endforeach
        </div>
    </section>
@endif

@endsection
