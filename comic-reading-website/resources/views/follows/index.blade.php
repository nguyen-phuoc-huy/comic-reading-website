@extends('layouts.site')

@section('title', 'Truyện theo dõi - ' . config('app.name'))

@section('content')
<h4 class="fw-bold mb-3"><i class="bi bi-heart-fill text-danger"></i> Truyện đang theo dõi</h4>

@if ($comics->isEmpty())
    <div class="alert alert-info">
        Bạn chưa theo dõi truyện nào. <a href="{{ route('comics.index') }}">Khám phá truyện ngay</a>.
    </div>
@else
    <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-3">
        @foreach ($comics as $comic)
            @include('partials.comic-card', ['comic' => $comic])
        @endforeach
    </div>
    <div class="d-flex justify-content-center mt-4">
        {{ $comics->links('pagination::bootstrap-5') }}
    </div>
@endif
@endsection
