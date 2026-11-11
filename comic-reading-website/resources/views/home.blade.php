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
                            <a href="{{ route('comics.show', $history->comic) }}"
                                class="text-decoration-none text-dark fw-semibold d-block text-truncate">
                                {{ $history->comic->title }}
                            </a>
                            <small class="text-muted d-block mb-2">Đã đọc đến chap {{ (float) $history->chapter->number
                                }}</small>
                            <a href="{{ route('chapters.show', [$history->comic, $history->chapter]) }}"
                                class="btn btn-sm btn-primary">
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

{{-- Truyện phổ biến (nhiều người theo dõi nhất)
@if ($popularComics->isNotEmpty())
<section class="mb-4">
    <h5 class="fw-bold mb-3">
        <i class="bi bi-stars text-warning"></i> Truyện phổ biến
        <small class="text-muted fw-normal fs-6">- được theo dõi nhiều nhất</small>
    </h5>
    <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-3">
        @foreach ($popularComics as $comic)
        @include('partials.comic-card', ['comic' => $comic])
        @endforeach
    </div>
</section>
@endif --}}

{{-- Xem nhiều nhất: slide show 10 truyện, mũi tên trái/phải dịch từng truyện --}}
@if ($topComics->isNotEmpty())
<section class="mb-4">
    <h5 class="fw-bold mb-3"><i class="bi bi-fire text-danger"></i> Xem nhiều nhất</h5>

    <div class="top-slider position-relative" id="topSlider">
        <button type="button" class="top-slider-btn top-slider-prev" aria-label="Truyện trước" disabled>
            <i class="bi bi-chevron-left"></i>
        </button>
        <button type="button" class="top-slider-btn top-slider-next" aria-label="Truyện tiếp theo">
            <i class="bi bi-chevron-right"></i>
        </button>

        <div class="top-track">
            @foreach ($topComics as $top)
            <div class="top-slide">
                <a href="{{ route('comics.show', $top) }}"
                    class="top-slide-link position-relative d-block rounded overflow-hidden shadow-sm">
                    <img src="{{ $top->cover ? asset('storage/' . $top->cover) : 'https://placehold.co/300x400?text=No+Cover' }}"
                        alt="{{ $top->title }}" loading="lazy" draggable="false" class="w-100 d-block"
                        style="aspect-ratio:3/4;object-fit:cover">

                    <span
                        class="badge position-absolute top-0 start-0 m-1 {{ $loop->iteration <= 3 ? 'text-bg-danger' : 'text-bg-dark' }}">
                        #{{ $loop->iteration }}
                    </span>

                    {{-- Tên truyện + chương mới nhất: nền đen mờ đè lên cạnh dưới ảnh bìa --}}
                    <div class="top-slide-caption">
                        <div class="top-slide-title">{{ $top->title }}</div>
                        <div class="top-slide-chap">
                            {{ !is_null($top->latest_chapter_number) ? 'Chap ' . (float) $top->latest_chapter_number :
                            'Chưa có chương' }}
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Truyện mới cập nhật (full chiều ngang) --}}
<section>
    <h5 class="fw-bold mb-3"><i class="bi bi-lightning-charge-fill text-warning"></i> Truyện mới cập nhật</h5>

    @if ($latestComics->isEmpty())
    <div class="alert alert-info">Chưa có truyện nào.</div>
    @else
    <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-3">
        @foreach ($latestComics as $comic)
        @include('partials.comic-card', ['comic' => $comic])
        @endforeach
    </div>
    <div class="d-flex justify-content-center mt-4">
        {{ $latestComics->links('pagination::bootstrap-5') }}
    </div>
    @endif
</section>

{{-- Truyện hoàn thành --}}
{{-- @if ($completedComics->isNotEmpty())
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
@endif --}}

@endsection

@push('styles')
<style>
    /* Số truyện hiển thị cùng lúc: điện thoại 3, máy tính bảng 5, laptop 6, màn hình lớn 8 */
    .top-slider {
        --per-view: 3;
    }

    @media (min-width: 768px) {
        .top-slider {
            --per-view: 5;
        }
    }

    @media (min-width: 992px) {
        .top-slider {
            --per-view: 6;
        }
    }

    @media (min-width: 1200px) {
        .top-slider {
            --per-view: 8;
        }
    }

    .top-track {
        display: flex;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }

    .top-track::-webkit-scrollbar {
        display: none;
    }

    .top-slide {
        flex: 0 0 calc(100% / var(--per-view));
        padding: 0 .25rem;
        scroll-snap-align: start;
    }

    /* Nền đen mờ đè lên cạnh dưới ảnh bìa */
    .top-slide-caption {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        padding: .35rem .5rem;
        color: #fff;
        background: rgba(0, 0, 0, .65);
    }

    .top-slide-title {
        font-size: .85rem;
        font-weight: 600;
        line-height: 1.25;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .top-slide-chap {
        font-size: .75rem;
        color: #ffc107;
    }

    .top-slide-link {
        transition: transform .15s;
    }

    .top-slide-link:hover {
        transform: translateY(-3px);
    }

    /* Hai mũi tên trái / phải */
    .top-slider-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 2;
        width: 38px;
        height: 38px;
        border: 0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: rgba(0, 0, 0, .6);
        transition: opacity .2s, background .2s;
    }

    .top-slider-btn:hover:not(:disabled) {
        background: rgba(0, 0, 0, .85);
    }

    .top-slider-btn:disabled {
        opacity: .25;
        cursor: default;
    }

    .top-slider-prev {
        left: .35rem;
    }

    .top-slider-next {
        right: .35rem;
    }

    .top-slider-btn[hidden] {
        display: none;
    }

    @media (max-width: 575.98px) {
        .top-slider-btn {
            width: 30px;
            height: 30px;
            font-size: .8rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Slide show "Xem nhiều nhất": mỗi lần bấm mũi tên dịch đúng 1 truyện
    (function () {
        var slider = document.getElementById('topSlider');
        if (!slider) return;

        var track = slider.querySelector('.top-track');
        var prev = slider.querySelector('.top-slider-prev');
        var next = slider.querySelector('.top-slider-next');
        var target = null, timer = null;

        function slideWidth() { return track.firstElementChild ? track.firstElementChild.offsetWidth : 0; }

        function maxIndex() {
            var w = slideWidth();
            return w ? Math.max(0, Math.round((track.scrollWidth - track.clientWidth) / w)) : 0;
        }

        function updateButtons() {
            var max = track.scrollWidth - track.clientWidth;
            var needed = max > 2; // ít truyện hơn số ô hiển thị thì không cần mũi tên
            prev.hidden = next.hidden = !needed;
            prev.disabled = track.scrollLeft <= 2;
            next.disabled = track.scrollLeft >= max - 2;
        }

        function step(direction) {
            var w = slideWidth();
            if (!w) return;
            var base = target !== null ? target : Math.round(track.scrollLeft / w);
            target = Math.min(maxIndex(), Math.max(0, base + direction));
            track.scrollTo({ left: target * w, behavior: 'smooth' });
            clearTimeout(timer);
            timer = setTimeout(function () { target = null; }, 450);
        }

        prev.addEventListener('click', function () { step(-1); });
        next.addEventListener('click', function () { step(1); });
        track.addEventListener('scroll', updateButtons, { passive: true });
        window.addEventListener('resize', updateButtons);
        updateButtons();
    })();
</script>
@endpush