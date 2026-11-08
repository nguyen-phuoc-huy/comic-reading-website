@extends('layouts.site')

@section('title', 'Hồ sơ - ' . config('app.name'))

@section('content')
@php $user = auth()->user(); @endphp

<div class="row justify-content-center">
    <div class="col-lg-7">
        <h4 class="fw-bold mb-3"><i class="bi bi-person-gear"></i> Hồ sơ của tôi</h4>

        {{-- Thông tin cá nhân --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header fw-semibold">Thông tin cá nhân</div>
            <div class="card-body">
                @if (session('status') === 'profile-updated')
                    <div class="alert alert-success">Đã lưu thông tin.</div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label">Tên hiển thị</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="form-control @error('name') is-invalid @enderror">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="form-control @error('email') is-invalid @enderror">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button class="btn btn-primary">Lưu thay đổi</button>
                </form>
            </div>
        </div>

        {{-- Đổi mật khẩu --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header fw-semibold">Đổi mật khẩu</div>
            <div class="card-body">
                @if (session('status') === 'password-updated')
                    <div class="alert alert-success">Đã đổi mật khẩu.</div>
                @endif

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu hiện tại</label>
                        <input type="password" name="current_password" autocomplete="current-password"
                               class="form-control @error('current_password', 'updatePassword') is-invalid @enderror">
                        @error('current_password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu mới</label>
                        <input type="password" name="password" autocomplete="new-password"
                               class="form-control @error('password', 'updatePassword') is-invalid @enderror">
                        @error('password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nhập lại mật khẩu mới</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password" class="form-control">
                    </div>
                    <button class="btn btn-primary">Đổi mật khẩu</button>
                </form>
            </div>
        </div>

        {{-- Xóa tài khoản --}}
        <div class="card border-danger shadow-sm">
            <div class="card-header text-danger fw-semibold">Xóa tài khoản</div>
            <div class="card-body">
                <p class="text-muted small">Khi xóa, toàn bộ dữ liệu của bạn (theo dõi, lịch sử, bình luận) sẽ bị xóa vĩnh viễn.</p>

                <button class="btn btn-outline-danger" type="button" data-bs-toggle="collapse" data-bs-target="#deleteAccount"
                        aria-expanded="{{ $errors->userDeletion->isNotEmpty() ? 'true' : 'false' }}">
                    Xóa tài khoản của tôi
                </button>

                <div class="collapse mt-3 {{ $errors->userDeletion->isNotEmpty() ? 'show' : '' }}" id="deleteAccount">
                    <form method="POST" action="{{ route('profile.destroy') }}">
                        @csrf
                        @method('DELETE')
                        <label class="form-label">Nhập mật khẩu để xác nhận</label>
                        <input type="password" name="password"
                               class="form-control mb-2 @error('password', 'userDeletion') is-invalid @enderror">
                        @error('password', 'userDeletion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <button class="btn btn-danger">Xác nhận xóa</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
