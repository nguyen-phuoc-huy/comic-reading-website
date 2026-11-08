@extends('layouts.admin')

@section('title', 'Tổng quan')

@section('content')
<h4 class="fw-bold mb-4">Tổng quan</h4>

@php
    $cards = [
        ['Truyện', $stats['comics'], 'bi-journal-richtext', 'primary'],
        ['Chương', $stats['chapters'], 'bi-file-earmark-image', 'success'],
        ['Người dùng', $stats['users'], 'bi-people', 'info'],
        ['Bình luận', $stats['comments'], 'bi-chat-dots', 'warning'],
        ['Lượt xem', $stats['views'], 'bi-eye', 'danger'],
    ];
@endphp

<div class="row row-cols-2 row-cols-lg-5 g-3 mb-4">
    @foreach ($cards as [$label, $value, $icon, $color])
        <div class="col">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-{{ $color }}-subtle text-{{ $color }} p-3 fs-4">
                        <i class="bi {{ $icon }}"></i>
                    </div>
                    <div>
                        <div class="text-muted small">{{ $label }}</div>
                        <div class="fs-4 fw-bold">{{ number_format($value) }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold">Top truyện xem nhiều</div>
            <ul class="list-group list-group-flush">
                @forelse ($topComics as $comic)
                    <li class="list-group-item d-flex justify-content-between">
                        <a href="{{ route('admin.comics.edit', $comic) }}" class="text-decoration-none">{{ $comic->title }}</a>
                        <span class="badge text-bg-secondary">{{ number_format($comic->views) }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Chưa có dữ liệu.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold">Chương mới đăng</div>
            <ul class="list-group list-group-flush">
                @forelse ($latestChapters as $chapter)
                    <li class="list-group-item">
                        <div class="fw-semibold">{{ $chapter->comic->title ?? '(đã xóa)' }}</div>
                        <small class="text-muted">
                            Chap {{ (float) $chapter->number }} &middot; {{ $chapter->created_at->diffForHumans() }}
                        </small>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Chưa có chương nào.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold d-flex justify-content-between">
                Bình luận mới
                <a href="{{ route('admin.comments.index') }}" class="small">Xem tất cả</a>
            </div>
            <ul class="list-group list-group-flush">
                @forelse ($latestComments as $comment)
                    <li class="list-group-item">
                        <div class="small">
                            <strong>{{ $comment->user->name ?? 'Ẩn danh' }}</strong>
                            <span class="text-muted">· {{ $comment->comic->title ?? '' }}</span>
                        </div>
                        <div class="text-truncate">{{ $comment->content }}</div>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Chưa có bình luận.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
