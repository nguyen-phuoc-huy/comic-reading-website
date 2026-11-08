<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị') - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-body-tertiary">

<nav class="navbar navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
            <i class="bi bi-speedometer2 text-warning"></i> Quản trị
        </a>
        <div class="d-flex align-items-center gap-3">
            <a class="text-white-50 text-decoration-none small" href="{{ route('home') }}" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i> Xem website
            </a>
            <span class="text-white small">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-outline-light btn-sm">Đăng xuất</button>
            </form>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <aside class="col-md-3 col-lg-2 bg-white border-end min-vh-100 py-3">
            <ul class="nav nav-pills flex-column gap-1">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : 'text-dark' }}" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-house"></i> Tổng quan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.comics.*', 'admin.chapters.*') ? 'active' : 'text-dark' }}" href="{{ route('admin.comics.index') }}">
                        <i class="bi bi-journal-richtext"></i> Truyện &amp; chương
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : 'text-dark' }}" href="{{ route('admin.categories.index') }}">
                        <i class="bi bi-tags"></i> Thể loại
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.authors.*') ? 'active' : 'text-dark' }}" href="{{ route('admin.authors.index') }}">
                        <i class="bi bi-person-lines-fill"></i> Tác giả
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.comments.*') ? 'active' : 'text-dark' }}" href="{{ route('admin.comments.index') }}">
                        <i class="bi bi-chat-dots"></i> Bình luận
                    </a>
                </li>
            </ul>
        </aside>

        <main class="col-md-9 col-lg-10 py-4 px-4">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
