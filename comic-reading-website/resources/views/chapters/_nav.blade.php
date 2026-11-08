<div class="d-flex flex-wrap justify-content-center align-items-center gap-2 my-3">
    @if ($prevChapter)
        <a href="{{ route('chapters.show', [$comic, $prevChapter]) }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-chevron-left"></i> Chương trước
        </a>
    @else
        <button class="btn btn-secondary btn-sm" disabled><i class="bi bi-chevron-left"></i> Chương trước</button>
    @endif

    <select class="form-select form-select-sm w-auto" onchange="if (this.value) location.href = this.value">
        @foreach ($chapterList as $item)
            <option value="{{ route('chapters.show', [$comic, $item]) }}" @selected($item->id === $chapter->id)>
                Chap {{ (float) $item->number }}
            </option>
        @endforeach
    </select>

    @if ($nextChapter)
        <a href="{{ route('chapters.show', [$comic, $nextChapter]) }}" class="btn btn-primary btn-sm">
            Chương sau <i class="bi bi-chevron-right"></i>
        </a>
    @else
        <button class="btn btn-primary btn-sm" disabled>Chương sau <i class="bi bi-chevron-right"></i></button>
    @endif
</div>
