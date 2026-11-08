<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .comic-card img { aspect-ratio: 3 / 4; object-fit: cover; }
        .comic-card { transition: transform .15s; }
        .comic-card:hover { transform: translateY(-3px); }
        .comic-card .card-title {
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
            overflow: hidden; min-height: 2.6em; font-size: .9rem;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-body-tertiary d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('home') }}">
            <i class="bi bi-book-half text-warning"></i> {{ config('app.name') }}
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Trang chủ</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('comics.index') ? 'active' : '' }}" href="{{ route('comics.index') }}">Truyện</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">Thể loại</a></li>
            </ul>

            <form class="d-flex me-lg-3 my-2 my-lg-0" method="GET" action="{{ route('search') }}">
                <input class="form-control form-control-sm me-2" type="search" name="q" value="{{ request('q') }}" placeholder="Tìm truyện, tác giả..." maxlength="100">
                <button class="btn btn-warning btn-sm" type="submit"><i class="bi bi-search"></i></button>
            </form>

            <ul class="navbar-nav">
                @guest
                    @if (Route::has('login'))
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Đăng nhập</a></li>
                    @endif
                    @if (Route::has('register'))
                        <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Đăng ký</a></li>
                    @endif
                @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('follows.index') }}"><i class="bi bi-heart"></i> Truyện theo dõi</a></li>
                            <li><a class="dropdown-item" href="{{ route('history.index') }}"><i class="bi bi-clock-history"></i> Lịch sử đọc</a></li>
                            @if (Route::has('profile.edit'))
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-gear"></i> Hồ sơ</a></li>
                            @endif
                            @if (auth()->user()->role === 'admin')
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> Trang quản trị</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Đăng xuất</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>

<main class="@yield('main_class', 'container py-4') flex-grow-1">
    @include('partials.flash')
    @yield('content')
</main>

<footer class="bg-dark text-secondary py-3 mt-auto">
    <div class="container text-center small">
        &copy; {{ date('Y') }} {{ config('app.name') }}. Đọc truyện tranh online.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
