@extends('layouts.site')

@section('title', 'Lịch sử đọc - ' . config('app.name'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-clock-history"></i> Lịch sử đọc</h4>

    @if ($histories->isNotEmpty())
        <form method="POST" action="{{ route('history.clear') }}"
              onsubmit="return confirm('Xóa toàn bộ lịch sử đọc?')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Xóa tất cả</button>
        </form>
    @endif
</div>

@if ($histories->isEmpty())
    <div class="alert alert-info">Bạn chưa đọc truyện nào.</div>
@else
    <div class="list-group shadow-sm">
        @foreach ($histories as $history)
            @continue(!$history->comic || !$history->chapter)
            <div class="list-group-item d-flex align-items-center gap-3">
                <img src="{{ $history->comic->cover ? asset('storage/' . $history->comic->cover) : 'https://placehold.co/300x400?text=No+Cover' }}"
                     alt="" style="width:56px;height:75px;object-fit:cover" class="rounded">

                <div class="flex-grow-1">
                    <a href="{{ route('comics.show', $history->comic) }}" class="fw-semibold text-decoration-none text-dark">
                        {{ $history->comic->title }}
                    </a>
                    <div class="small text-muted">
                        Đã đọc đến chap {{ (float) $history->chapter->number }}
                        &middot; {{ $history->updated_at->diffForHumans() }}
                    </div>
                </div>

                <a href="{{ route('chapters.show', [$history->comic, $history->chapter]) }}" class="btn btn-primary btn-sm">
                    Đọc tiếp
                </a>
                <form method="POST" action="{{ route('history.destroy', $history) }}">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-secondary btn-sm" title="Xóa khỏi lịch sử"><i class="bi bi-x-lg"></i></button>
                </form>
            </div>
        @endforeach
    </div>
    <div class="d-flex justify-content-center mt-4">
        {{ $histories->links('pagination::bootstrap-5') }}
    </div>
@endif
@endsection
