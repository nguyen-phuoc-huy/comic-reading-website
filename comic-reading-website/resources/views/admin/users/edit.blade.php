@extends('layouts.admin')

@section('title', 'Sửa người dùng')

@section('content')
<a href="{{ route('admin.users.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Danh sách người dùng</a>
<h4 class="fw-bold mb-3">Sửa người dùng: {{ $user->name }}</h4>

<div class="card shadow-sm" style="max-width:820px">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')
            @include('admin.users._form', ['user' => $user])

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Cập nhật</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection
