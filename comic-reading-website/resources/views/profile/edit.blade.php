@extends('layouts.site')

@section('title', 'Hồ sơ của tôi - ' . config('app.name'))

@section('content')
<h4 class="fw-bold mb-4"><i class="bi bi-person-gear"></i> Hồ sơ của tôi</h4>

<div class="row g-4">
    {{-- Thẻ thông tin tổng quan --}}
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body text-center p-4">
                @if ($user->avatar)
                    <img src="{{ $user->avatar }}" alt="" width="96" height="96" referrerpolicy="no-referrer"
                         class="rounded-circle mb-3 object-fit-cover">
                @else
                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fs-1 fw-bold mb-3"
                         style="width:96px;height:96px">
                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                <div class="text-muted small mb-2">{{ $user->email }}</div>
                @if ($user->role === 'admin')
                    <span class="badge text-bg-danger">Quản trị viên</span>
                @else
                    <span class="badge text-bg-secondary">Thành viên</span>
                @endif
                <div class="text-muted small mt-2">
                    <i class="bi bi-calendar-check"></i> Tham gia {{ $user->created_at?->format('d/m/Y') }}
                </div>
            </div>

            <div class="list-group list-group-flush">
                <a href="{{ route('follows.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-heart text-danger"></i> Truyện đang theo dõi</span>
                    <span class="badge text-bg-light border">{{ $stats['follows'] }}</span>
                </a>
                <a href="{{ route('history.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-clock-history text-primary"></i> Truyện đã đọc</span>
                    <span class="badge text-bg-light border">{{ $stats['histories'] }}</span>
                </a>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-chat-dots text-success"></i> Bình luận đã đăng</span>
                    <span class="badge text-bg-light border">{{ $stats['comments'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        {{-- Thông tin cá nhân --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header fw-semibold">Thông tin tài khoản</div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Tên hiển thị</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="255"
                               class="form-control @error('name') is-invalid @enderror">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255"
                               class="form-control @error('email') is-invalid @enderror">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Lưu thay đổi</button>
                </form>
            </div>
        </div>

        {{-- Đổi mật khẩu --}}
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">{{ $user->password ? 'Đổi mật khẩu' : 'Đặt mật khẩu' }}</div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf
                    @method('PUT')

                    @if ($user->password)
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu hiện tại</label>
                        <input type="password" name="current_password" autocomplete="current-password" required
                               class="form-control @error('current_password', 'updatePassword') is-invalid @enderror">
                        @error('current_password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    @else
                        <div class="alert alert-info small">
                            Tài khoản của bạn đăng nhập bằng Google nên chưa có mật khẩu. Bạn có thể đặt mật khẩu để đăng nhập thêm bằng email.
                        </div>
                    @endif

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Mật khẩu mới</label>
                            <input type="password" name="password" autocomplete="new-password" required minlength="8"
                                   class="form-control @error('password', 'updatePassword') is-invalid @enderror">
                            @error('password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Tối thiểu 8 ký tự.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nhập lại mật khẩu mới</label>
                            <input type="password" name="password_confirmation" autocomplete="new-password" required class="form-control">
                        </div>
                    </div>

                    <button class="btn btn-primary"><i class="bi bi-key"></i> {{ $user->password ? 'Đổi mật khẩu' : 'Đặt mật khẩu' }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
