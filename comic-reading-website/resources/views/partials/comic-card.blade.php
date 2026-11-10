{{-- Khung truyện nằm ngang: ảnh bìa bên trái, thông tin bên phải.
Thứ tự bên phải: tên truyện > 3 chương mới nhất > tác giả và các thông tin khác (dưới cùng).
Tự quy định số cột (điện thoại 1, máy tính bảng 2, màn hình lớn 3) nên không cần sửa lưới ở các trang. --}}
<div class="col-12 col-md-6 col-lg-6 col-xl-4">
    <div class="card h-100 comic-card shadow-sm overflow-hidden">
        <div class="d-flex h-100" style="min-height:165px">

            {{-- Ảnh bìa (bên trái) --}}
            <a href="{{ route('comics.show', $comic) }}" class="position-relative flex-shrink-0 d-block"
                style="width:110px">
                <img src="{{ $comic->cover ? asset('storage/' . $comic->cover) : 'https://placehold.co/300x400?text=No+Cover' }}"
                    alt="{{ $comic->title }}" loading="lazy" class="w-100 h-100"
                    style="object-fit:cover;aspect-ratio:auto">
                @if (!is_null($comic->latest_chapter_number ?? null))
                {{-- <span class="badge text-bg-primary position-absolute bottom-0 start-0 m-1">
                    Chap {{ (float) $comic->latest_chapter_number }}
                </span> --}}
                @endif
            </a>

            {{-- Thông tin (bên phải) --}}
            <div class="flex-grow-1 p-2 d-flex flex-column" style="min-width:0">

                {{-- Tên truyện --}}
                <a href="{{ route('comics.show', $comic) }}" class="text-decoration-none text-dark">
                    <h6 class="card-title mb-1" style="min-height:0">{{ $comic->title }}</h6>
                </a>

                {{-- 3 chương mới nhất kèm thời gian đăng; bấm vào tên chương để đọc ngay --}}
                @if ($comic->latestChapters->isNotEmpty())
                <ul class="list-unstyled small mb-0">
                    @foreach ($comic->latestChapters as $chapter)
                    <li class="d-flex justify-content-between align-items-start gap-2 py-1">
                        <a href="{{ route('chapters.show', [$comic, $chapter]) }}"
                            class="text-decoration-none text-truncate"
                            title="{{ $chapter->title ?: 'Chap ' . (float) $chapter->number }}">
                            Chap {{ (float) $chapter->number }}
                        </a>
                        <span class="text-muted text-nowrap" style="font-size:.75rem">
                            {{ \App\Support\TimeAgo::vi($chapter->created_at) }}
                        </span>
                    </li>
                    @endforeach
                </ul>
                @endif

                {{-- Tác giả và các thông tin khác (dưới cùng) --}}
                <div class="mt-auto pt-2 small text-muted">
                    @if ($comic->relationLoaded('author') && $comic->author)
                    <div class="text-truncate mb-1">
                        <i class="bi bi-person"></i> {{ $comic->author->name }}
                    </div>
                    @endif
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span><i class="bi bi-eye"></i> {{ number_format($comic->views) }}</span>
                        @include('partials.status-badge', ['status' => $comic->status])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>