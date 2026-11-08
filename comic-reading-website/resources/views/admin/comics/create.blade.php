@extends('layouts.admin')

@section('title', 'Thêm truyện')

@section('content')
<h4 class="fw-bold mb-3">Thêm truyện mới</h4>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.comics.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.comics._form', ['comic' => null])

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Lưu</button>
                <a href="{{ route('admin.comics.index') }}" class="btn btn-outline-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection
