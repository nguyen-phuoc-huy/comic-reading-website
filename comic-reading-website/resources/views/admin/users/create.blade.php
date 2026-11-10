@extends('layouts.admin')

@section('title', 'Thêm người dùng')

@section('content')
<a href="{{ route('admin.users.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Danh sách người dùng</a>
<h4 class="fw-bold mb-3">Thêm người dùng mới</h4>

<div class="card shadow-sm" style="max-width:820px">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            @include('admin.users._form', ['user' => null])

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Lưu</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection
