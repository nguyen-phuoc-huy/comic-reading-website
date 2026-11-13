@extends('layouts.site')

@section('title', 'Đăng nhập - ' . config('app.name'))

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="fw-bold text-center mb-4"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</h4>

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                               class="form-control @error('email') is-invalid @enderror">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mật khẩu</label>
                        <input type="password" name="password" required autocomplete="current-password"
                               class="form-control @error('password') is-invalid @enderror">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="small">Quên mật khẩu?</a>
                        @endif
                    </div>

                    <button class="btn btn-primary w-100">Đăng nhập</button>
                </form>
                @if (config('services.google.client_id'))
                    <div class="d-flex align-items-center my-3 text-muted small">
                        <hr class="flex-grow-1 m-0"><span class="px-2">hoặc</span><hr class="flex-grow-1 m-0">
                    </div>
                    <a href="{{ route('auth.google') }}" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-google text-danger"></i> Đăng nhập bằng Google
                    </a>
                @endif


                @if (Route::has('register'))
                    <p class="text-center small text-muted mt-3 mb-0">
                        Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký ngay</a>
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
