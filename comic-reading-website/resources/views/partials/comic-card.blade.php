<div class="col">
    <div class="card h-100 comic-card shadow-sm">
        <a href="{{ route('comics.show', $comic) }}" class="position-relative d-block">
            <img src="{{ $comic->cover ? asset('storage/' . $comic->cover) : 'https://placehold.co/300x400?text=No+Cover' }}"
                 class="card-img-top" alt="{{ $comic->title }}" loading="lazy">
            @if (!is_null($comic->latest_chapter_number ?? null))
                <span class="badge text-bg-primary position-absolute bottom-0 start-0 m-2">
                    Chap {{ (float) $comic->latest_chapter_number }}
                </span>
            @endif
        </a>
        <div class="card-body p-2">
            <a href="{{ route('comics.show', $comic) }}" class="text-decoration-none text-dark">
                <h6 class="card-title mb-1">{{ $comic->title }}</h6>
            </a>
            <div class="small text-muted d-flex justify-content-between">
                <span><i class="bi bi-eye"></i> {{ number_format($comic->views) }}</span>
                @include('partials.status-badge', ['status' => $comic->status])
            </div>
        </div>
    </div>
</div>
