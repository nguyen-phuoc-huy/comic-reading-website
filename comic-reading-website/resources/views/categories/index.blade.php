@extends('layouts.site')

@section('title', 'Thể loại - ' . config('app.name'))

@section('content')
<h4 class="fw-bold mb-3"><i class="bi bi-tags"></i> Tất cả thể loại</h4>

@if ($categories->isEmpty())
    <div class="alert alert-info">Chưa có thể loại nào.</div>
@else
    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
        @foreach ($categories as $category)
            <div class="col">
                <a href="{{ route('categories.show', $category) }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm comic-card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <span class="fw-semibold text-dark">{{ $category->name }}</span>
                            <span class="badge text-bg-primary rounded-pill">{{ $category->comics_count }}</span>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endif
@endsection
