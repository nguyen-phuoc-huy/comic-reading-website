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
        .comic-card img {
            aspect-ratio: 3 / 4;
            object-fit: cover;
        }

        .comic-card {
            transition: transform .15s;
        }

        .comic-card:hover {
            transform: translateY(-3px);
        }

        .comic-card .card-title {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.6em;
            font-size: .9rem;
        }

        /* ===== Thanh menu thứ hai (nằm dưới thanh chính) ===== */
        .subnav .container {
            position: relative;
        }

        .subnav .nav-link {
            color: var(--bs-body-color);
            font-weight: 500;
            padding: .65rem .9rem;
            border-bottom: 3px solid transparent;
        }

        .subnav .nav-link:hover {
            color: var(--bs-primary);
        }

        .subnav .nav-link.active {
            color: var(--bs-primary);
            border-bottom-color: var(--bs-primary);
        }

        /* Menu thể loại: xổ ra full chiều rộng, chia nhóm theo chữ cái, trình bày dạng cột */
        .subnav .dropdown {
            position: static;
        }

        .subnav .dropdown-menu.nav-categories {
            left: 0;
            right: 0;
            top: 100%;
            min-width: 0;
            margin-top: 0;
            padding: .75rem 1rem;
            border-radius: 0 0 .5rem .5rem;
            column-count: 2;
            column-gap: 1.25rem;
        }

        .subnav .dropdown-menu.nav-categories.show {
            display: block;
        }

        .nav-categories .cat-all {
            column-span: all;
            margin-bottom: .5rem;
        }

        .nav-categories .cat-group {
            break-inside: avoid;
            margin-bottom: .75rem;
        }

        .nav-categories .cat-letter {
            font-weight: 700;
            font-size: 1.05rem;
            color: var(--bs-primary);
            border-bottom: 2px solid var(--bs-primary);
            margin: 0 .5rem .25rem;
            padding-bottom: .1rem;
        }

        @media (min-width: 768px) {
            .subnav .dropdown-menu.nav-categories {
                column-count: 4;
            }
        }

        @media (min-width: 992px) {
            .subnav .dropdown-menu.nav-categories {
                column-count: 5;
            }

            /* Rê chuột là hiện (desktop) */
            .subnav .nav-item.dropdown:hover>.nav-categories {
                display: block;
            }
        }

        /* ===== Sidebar menu (điện thoại) ===== */
        #siteSidebar {
            max-width: 85vw;
        }

        .sidebar-cat-letter {
            font-weight: 700;
            color: var(--bs-primary);
            border-bottom: 1px solid var(--bs-border-color);
            margin: .6rem 0 .2rem;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-body-tertiary d-flex flex-column min-vh-100">

    @php
    $navCategories = \App\Models\Category::orderBy('name')->get(['id', 'name', 'slug']);

    // Xác định mục đang được chọn trên thanh menu dưới
    $onIndex = request()->routeIs('comics.index');
    $qStatus = request('status');
    $qSort = request('sort');

    $isAll = $onIndex && !$qStatus && !$qSort;
    $isViews = $onIndex && $qSort === 'views' && !$qStatus;
    $isOngoing = $onIndex && $qStatus === 'ongoing';
    $isCompleted = $onIndex && $qStatus === 'completed';
    @endphp

    <header class="sticky-top shadow-sm">

        {{-- ===== Thanh 1: tên website (trang chủ) + tìm kiếm + tài khoản ===== --}}
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <div class="d-flex align-items-center">
                    {{-- Nút mở menu bên trái (chỉ hiện khi màn hình nhỏ) --}}
                    <button class="btn btn-outline-light btn-sm d-lg-none me-2" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#siteSidebar" aria-controls="siteSidebar" aria-label="Mở menu">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <a class="navbar-brand fw-bold" href="{{ route('home') }}">
                        <i class="bi bi-book-half text-warning"></i> {{ config('app.name') }}
                    </a>
                </div>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="mainNav">
                    <form class="d-flex flex-grow-1 mx-lg-4 my-2 my-lg-0" method="GET" action="{{ route('search') }}">
                        <input class="form-control form-control-sm me-2" type="search" name="q"
                            value="{{ request('q') }}" placeholder="Tìm truyện, tên khác, tác giả..." maxlength="100">
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
                                @if (Route::has('profile.edit'))
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i
                                            class="bi bi-gear"></i> Hồ sơ</a></li>
                                @endif
                                @if (auth()->user()->role === 'admin')
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i
                                            class="bi bi-speedometer2"></i> Trang quản trị</a></li>
                                @endif
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item"><i
                                                class="bi bi-box-arrow-right"></i> Đăng xuất</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        {{-- ===== Thanh 2: các mục điều hướng ===== --}}
        <nav class="subnav bg-body border-bottom d-none d-lg-block" aria-label="Menu chính">
            <div class="container">
                <ul class="nav flex-wrap">
                    <li class="nav-item">
                        <a class="nav-link {{ $onIndex && !$qStatus && !$qSort ? 'active' : '' }}"
                            href="{{ route('comics.index') }}">
                            <i class="bi bi-collection"></i> Tất cả truyện
                        </a>
                    </li>

                    {{-- Thể loại: rê chuột (máy tính) hoặc bấm (điện thoại) để xem danh sách --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('categories.*') ? 'active' : '' }}"
                            href="#" role="button" data-bs-toggle="dropdown" data-bs-display="static"
                            aria-expanded="false">
                            <i class="bi bi-tags"></i> Thể loại
                        </a>
                        <div class="dropdown-menu nav-categories shadow">
                            <a class="dropdown-item fw-semibold text-primary cat-all"
                                href="{{ route('categories.index') }}">
                                <i class="bi bi-grid"></i> Tất cả thể loại
                            </a>
                            @forelse (\App\Support\VietnameseAlphabet::group($navCategories) as $letter => $items)
                            <div class="cat-group">
                                <div class="cat-letter">{{ $letter }}</div>
                                @foreach ($items as $navCategory)
                                <a class="dropdown-item rounded" href="{{ route('categories.show', $navCategory) }}">
                                    {{ $navCategory->name }}
                                </a>
                                @endforeach
                            </div>
                            @empty
                            <span class="dropdown-item-text text-muted">Chưa có thể loại nào.</span>
                            @endforelse
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ $onIndex && $qSort === 'views' && !$qStatus ? 'active' : '' }}"
                            href="{{ route('comics.index', ['sort' => 'views']) }}">
                            <i class="bi bi-fire"></i> Xem nhiều
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $onIndex && $qStatus === 'ongoing' ? 'active' : '' }}"
                            href="{{ route('comics.index', ['status' => 'ongoing']) }}">
                            <i class="bi bi-arrow-repeat"></i> Đang tiến hành
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $onIndex && $qStatus === 'completed' ? 'active' : '' }}"
                            href="{{ route('comics.index', ['status' => 'completed']) }}">
                            <i class="bi bi-check-circle"></i> Hoàn thành
                        </a>
                    </li>

                    @auth
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('follows.*') ? 'active' : '' }}"
                            href="{{ route('follows.index') }}">
                            <i class="bi bi-heart"></i> Theo dõi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('history.*') ? 'active' : '' }}"
                            href="{{ route('history.index') }}">
                            <i class="bi bi-clock-history"></i> Lịch sử đọc
                        </a>
                    </li>
                    @endauth
                </ul>
            </div>
        </nav>
    </header>

    {{-- ===== Sidebar menu bên trái (điện thoại / màn hình nhỏ) ===== --}}
    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="siteSidebar" aria-labelledby="siteSidebarLabel">
        <div class="offcanvas-header bg-dark text-white">
            <h5 class="offcanvas-title" id="siteSidebarLabel">
                <i class="bi bi-book-half text-warning"></i> {{ config('app.name') }}
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                aria-label="Đóng"></button>
        </div>

        <div class="offcanvas-body p-0">
            <div class="list-group list-group-flush">
                <a class="list-group-item list-group-item-action {{ $isAll ? 'active' : '' }}"
                    href="{{ route('comics.index') }}">
                    <i class="bi bi-collection me-2"></i> Tất cả truyện
                </a>

                {{-- Thể loại: bấm để mở danh sách, nhóm theo chữ cái A-Z --}}
                <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center
                      {{ request()->routeIs('categories.*') ? 'active' : '' }}" data-bs-toggle="collapse"
                    href="#sidebarCategories" role="button" aria-expanded="false" aria-controls="sidebarCategories">
                    <span><i class="bi bi-tags me-2"></i> Thể loại</span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse" id="sidebarCategories">
                    <div class="bg-body-tertiary px-3 py-2">
                        <a class="d-block fw-semibold text-primary text-decoration-none py-1"
                            href="{{ route('categories.index') }}">
                            <i class="bi bi-grid"></i> Tất cả thể loại
                        </a>
                        @forelse (\App\Support\VietnameseAlphabet::group($navCategories) as $letter => $items)
                        <div class="sidebar-cat-letter">{{ $letter }}</div>
                        @foreach ($items as $navCategory)
                        <a class="d-block py-1 ps-2 text-decoration-none text-body"
                            href="{{ route('categories.show', $navCategory) }}">{{ $navCategory->name }}</a>
                        @endforeach
                        @empty
                        <span class="text-muted small">Chưa có thể loại nào.</span>
                        @endforelse
                    </div>
                </div>

                <a class="list-group-item list-group-item-action {{ $isViews ? 'active' : '' }}"
                    href="{{ route('comics.index', ['sort' => 'views']) }}">
                    <i class="bi bi-fire me-2"></i> Xem nhiều
                </a>
                <a class="list-group-item list-group-item-action {{ $isOngoing ? 'active' : '' }}"
                    href="{{ route('comics.index', ['status' => 'ongoing']) }}">
                    <i class="bi bi-arrow-repeat me-2"></i> Đang tiến hành
                </a>
                <a class="list-group-item list-group-item-action {{ $isCompleted ? 'active' : '' }}"
                    href="{{ route('comics.index', ['status' => 'completed']) }}">
                    <i class="bi bi-check-circle me-2"></i> Hoàn thành
                </a>

                @auth
                <a class="list-group-item list-group-item-action {{ request()->routeIs('follows.*') ? 'active' : '' }}"
                    href="{{ route('follows.index') }}">
                    <i class="bi bi-heart me-2"></i> Theo dõi
                </a>
                <a class="list-group-item list-group-item-action {{ request()->routeIs('history.*') ? 'active' : '' }}"
                    href="{{ route('history.index') }}">
                    <i class="bi bi-clock-history me-2"></i> Lịch sử đọc
                </a>
                @endauth
            </div>
        </div>
    </div>

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
    <script>
        // Khi màn hình rộng ra (>= 992px) thì tự đóng sidebar nếu đang mở
    window.matchMedia('(min-width: 992px)').addEventListener('change', function (e) {
        if (!e.matches) return;
        var el = document.getElementById('siteSidebar');
        var instance = el ? bootstrap.Offcanvas.getInstance(el) : null;
        if (instance) instance.hide();
    });
    </script>
    @stack('scripts')
</body>

</html>