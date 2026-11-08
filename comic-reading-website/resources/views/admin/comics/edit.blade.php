@extends('layouts.admin')

@section('title', 'Sửa truyện')

@section('content')
<h4 class="fw-bold mb-3">Sửa truyện: {{ $comic->title }}</h4>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.comics.update', $comic) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.comics._form', ['comic' => $comic])

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Cập nhật</button>
                <a href="{{ route('admin.comics.index') }}" class="btn btn-outline-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection
