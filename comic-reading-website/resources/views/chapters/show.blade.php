@extends('layouts.site')

@section('title', $comic->title . ' - Chap ' . (float) $chapter->number)
@section('main_class', 'container-fluid px-0 pb-4')

@push('styles')
<style>
    body { background-color: #111 !important; }
    .reader-wrap { max-width: 900px; margin: 0 auto; }
    .reader-wrap img { width: 100%; height: auto; display: block; }
</style>
@endpush

@section('content')
<div class="container">
    <nav aria-label="breadcrumb" class="pt-3">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('comics.show', $comic) }}" class="text-decoration-none">{{ $comic->title }}</a></li>
            <li class="breadcrumb-item active text-white-50">Chap {{ (float) $chapter->number }}</li>
        </ol>
    </nav>
    <h5 class="text-white text-center mb-0">
        {{ $comic->title }} - Chap {{ (float) $chapter->number }}@if ($chapter->title): {{ $chapter->title }}@endif
    </h5>

    @include('chapters._nav')
</div>

<div class="reader-wrap">
    @forelse ($chapter->images as $image)
        <img src="{{ asset('storage/' . $image->image_path) }}" alt="Trang {{ $loop->iteration }}" loading="lazy">
    @empty
        <div class="alert alert-warning m-3">Chương này chưa có ảnh.</div>
    @endforelse
</div>

<div class="container">
    @include('chapters._nav')

    <div class="mt-4">
        @include('partials.comments', ['comments' => $comments, 'comic' => $comic, 'chapter' => $chapter])
    </div>
</div>
@endsection

@push('scripts')
<script>
    const prevUrl = @json($prevChapter ? route('chapters.show', [$comic, $prevChapter]) : null);
    const nextUrl = @json($nextChapter ? route('chapters.show', [$comic, $nextChapter]) : null);

    document.addEventListener('keydown', function (e) {
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;
        if (e.key === 'ArrowLeft' && prevUrl) location.href = prevUrl;
        if (e.key === 'ArrowRight' && nextUrl) location.href = nextUrl;
    });
</script>
@endpush
