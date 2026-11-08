@extends('layouts.site')

@section('title', $comic->title . ' - ' . config('app.name'))

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-3 text-center">
                <img src="{{ $comic->cover ? asset('storage/' . $comic->cover) : 'https://placehold.co/300x400?text=No+Cover' }}"
                     class="img-fluid rounded shadow-sm" alt="{{ $comic->title }}">
            </div>

            <div class="col-md-9">
                <h3 class="fw-bold">{{ $comic->title }}</h3>
                @if ($comic->other_names)
                    <p class="text-muted mb-2"><i class="bi bi-tag"></i> Tên khác: {{ $comic->other_names }}</p>
                @endif

                <table class="table table-sm table-borderless mb-3 w-auto">
                    <tr>
                        <th class="pe-4">Tác giả</th>
                        <td>{{ $comic->author->name ?? 'Đang cập nhật' }}</td>
                    </tr>
                    <tr>
                        <th>Tình trạng</th>
                        <td>@include('partials.status-badge', ['status' => $comic->status])</td>
                    </tr>
                    <tr>
                        <th>Lượt xem</th>
                        <td>{{ number_format($comic->views) }}</td>
                    </tr>
                    <tr>
                        <th>Thể loại</th>
                        <td>
                            @forelse ($comic->categories as $category)
                                <a href="{{ route('categories.show', $category) }}" class="badge text-bg-light border text-decoration-none">{{ $category->name }}</a>
                            @empty
                                <span class="text-muted">Chưa có</span>
                            @endforelse
                        </td>
                    </tr>
                </table>

                <div class="d-flex flex-wrap gap-2 mb-3">
                    @if ($lastRead && $lastRead->chapter)
                        <a href="{{ route('chapters.show', [$comic, $lastRead->chapter]) }}" class="btn btn-success">
                            <i class="bi bi-bookmark-check"></i> Đọc tiếp (chap {{ (float) $lastRead->chapter->number }})
                        </a>
                    @endif
                    @if ($firstChapter)
                        <a href="{{ route('chapters.show', [$comic, $firstChapter]) }}" class="btn btn-primary">
                            <i class="bi bi-play-fill"></i> Đọc từ đầu
                        </a>
                    @endif
                    @if ($latestChapter && $latestChapter->isNot($firstChapter))
                        <a href="{{ route('chapters.show', [$comic, $latestChapter]) }}" class="btn btn-outline-primary">
                            Chương mới nhất ({{ (float) $latestChapter->number }})
                        </a>
                    @endif

                    @auth
                        <form method="POST" action="{{ route('follow.toggle', $comic) }}">
                            @csrf
                            <button class="btn {{ $isFollowing ? 'btn-danger' : 'btn-outline-danger' }}">
                                <i class="bi bi-heart{{ $isFollowing ? '-fill' : '' }}"></i>
                                {{ $isFollowing ? 'Bỏ theo dõi' : 'Theo dõi' }}
                            </button>
                        </form>
                    @else
                        <a href="{{ Route::has('login') ? route('login') : '#' }}" class="btn btn-outline-danger">
                            <i class="bi bi-heart"></i> Theo dõi
                        </a>
                    @endauth
                </div>

                <h6 class="fw-bold">Nội dung</h6>
                <p class="mb-0">{!! nl2br(e($comic->description ?: 'Đang cập nhật...')) !!}</p>
            </div>
        </div>
    </div>
</div>

{{-- Danh sách chương --}}
<div class="card shadow-sm mt-4">
    <div class="card-header fw-semibold">
        <i class="bi bi-list-ol"></i> Danh sách chương ({{ $chapters->count() }})
    </div>
    @if ($chapters->isEmpty())
        <div class="card-body text-muted">Truyện chưa có chương nào.</div>
    @else
        <div class="list-group list-group-flush" style="max-height:420px;overflow-y:auto">
            @foreach ($chapters as $chapter)
                <a href="{{ route('chapters.show', [$comic, $chapter]) }}"
                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center
                          {{ $lastRead && $lastRead->chapter_id === $chapter->id ? 'active' : '' }}">
                    <span>
                        Chap {{ (float) $chapter->number }}
                        @if ($chapter->title)<span class="opacity-75">- {{ $chapter->title }}</span>@endif
                    </span>
                    <small class="opacity-75">
                        {{ $chapter->created_at->diffForHumans() }}
                        &middot; <i class="bi bi-eye"></i> {{ number_format($chapter->views) }}
                    </small>
                </a>
            @endforeach
        </div>
    @endif
</div>

{{-- Bình luận --}}
@include('partials.comments', ['comments' => $comments, 'comic' => $comic])

{{-- Truyện cùng thể loại --}}
@if ($relatedComics->isNotEmpty())
    <section class="mt-5">
        <h5 class="fw-bold mb-3">Có thể bạn cũng thích</h5>
        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-3">
            @foreach ($relatedComics as $related)
                @include('partials.comic-card', ['comic' => $related])
            @endforeach
        </div>
    </section>
@endif
@endsection
