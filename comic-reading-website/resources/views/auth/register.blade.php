@extends('layouts.site')

@section('title', 'Đăng ký - ' . config('app.name'))

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="fw-bold text-center mb-4"><i class="bi bi-person-plus"></i> Đăng ký tài khoản</h4>

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Tên hiển thị</label>
                        <input type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                               class="form-control @error('name') is-invalid @enderror">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                               class="form-control @error('email') is-invalid @enderror">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mật khẩu</label>
                        <input type="password" name="password" required autocomplete="new-password"
                               class="form-control @error('password') is-invalid @enderror">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nhập lại mật khẩu</label>
                        <input type="password" name="password_confirmation" required autocomplete="new-password"
                               class="form-control">
                    </div>

                    <button class="btn btn-primary w-100">Đăng ký</button>
                </form>
                @if (config('services.google.client_id'))
                    <div class="d-flex align-items-center my-3 text-muted small">
                        <hr class="flex-grow-1 m-0"><span class="px-2">hoặc</span><hr class="flex-grow-1 m-0">
                    </div>
                    <a href="{{ route('auth.google') }}" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-google text-danger"></i> Đăng ký bằng Google
                    </a>
                @endif


                <p class="text-center small text-muted mt-3 mb-0">
                    Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
