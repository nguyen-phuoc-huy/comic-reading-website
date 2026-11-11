<style>
    .btn-download-pdf {
        border-radius: 8px;
        padding: 7px 14px;
        transition: all 0.25s ease;
        box-shadow: 0 3px 10px rgba(220, 53, 69, 0.3) !important;
    }

    .btn-download-pdf:hover {
        background-color: #b02a37;
        border-color: #b02a37;
        transform: translateY(-2px);
        box-shadow: 0 5px 16px rgba(220, 53, 69, 0.5) !important;
    }

    .btn-download-pdf:active {
        transform: scale(0.96);
    }
</style>

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

    {{-- <a href="{{ route('chapters.download', [$comic, $chapter]) }}" class="btn btn-outline-light btn-sm"
        title="Tải chương này về (PDF)">
        <i class="bi bi-file-earmark-pdf"></i> <span class="d-none d-sm-inline">Tải PDF</span>
    </a> --}}

    @if ($nextChapter)
    <a href="{{ route('chapters.show', [$comic, $nextChapter]) }}" class="btn btn-primary btn-sm">
        Chương sau <i class="bi bi-chevron-right"></i>
    </a>
    @else
    <button class="btn btn-primary btn-sm" disabled>Chương sau <i class="bi bi-chevron-right"></i></button>
    @endif


    <a href="{{ route('chapters.download', [$comic, $chapter]) }}"
        class="btn btn-danger btn-sm fw-bold shadow-sm btn-download-pdf" title="Tải chương này về (PDF)">
        <i class="bi bi-file-earmark-pdf-fill me-1"></i>
        <span>Tải PDF</span>
        <i class="bi bi-download ms-1"></i>
    </a>

</div>