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

        /* ===== Thanh trên cùng: tên website + (tìm kiếm) + tài khoản ===== */
        /* Điện thoại: ô tìm kiếm tự xuống dòng, nằm ngay dưới hàng tên website */
        .top-bar .top-search {
            flex: 1 0 100%;
        }

        @media (min-width: 992px) {
            .top-bar .top-search {
                flex: 1 1 auto;
            }
        }

        /* Nút mũi tên mở/đóng menu (chỉ hiện trên điện thoại), xoay lên khi menu đang mở */
        .menu-toggle i {
            display: inline-block;
            transition: transform .25s;
        }

        .menu-toggle[aria-expanded="true"] i {
            transform: rotate(180deg);
        }

        /* ===== Thanh menu thứ hai ===== */
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

        /* Menu thể loại: một cột dọc trong ô giới hạn kích thước, cuộn được, chia nhóm theo chữ cái */
        .subnav .dropdown {
            position: relative;
        }

        .subnav .dropdown-menu.nav-categories {
            left: 0;
            right: auto;
            top: 100%;
            margin-top: 0;
            width: 260px;
            min-width: 0;
            max-height: 60vh;
            overflow-y: auto;
            padding: .25rem .5rem .5rem;
            border-radius: 0 0 .5rem .5rem;
        }

        .subnav .dropdown-menu.nav-categories.show {
            display: block;
        }

        .nav-categories .cat-all {
            margin: .25rem 0 .25rem;
        }

        .nav-categories .cat-letter {
            position: sticky;
            top: 0;
            z-index: 1;
            background: var(--bs-body-bg);
            font-weight: 700;
            font-size: 1.05rem;
            color: var(--bs-primary);
            border-bottom: 2px solid var(--bs-primary);
            margin: .5rem .5rem .25rem;
            padding-bottom: .1rem;
        }

        @media (min-width: 992px) {

            /* Rê chuột là hiện (desktop) */
            .subnav .nav-item.dropdown:hover>.nav-categories {
                display: block;
            }
        }

        /* Điện thoại: menu là danh sách dọc trượt xuống, thể loại bung ngay dưới mục "Thể loại" */
        @media (max-width: 991.98px) {
            #subMenu {
                max-height: calc(100vh - 6.5rem);
                overflow-y: auto;
            }

            .subnav .nav-link {
                padding: .6rem .5rem;
                border-bottom: 0;
                border-left: 3px solid transparent;
            }

            .subnav .nav-link.active {
                border-left-color: var(--bs-primary);
                background: var(--bs-primary-bg-subtle);
            }

            .subnav .dropdown-menu.nav-categories {
                position: static;
                float: none;
                width: 100%;
                max-height: 45vh;
                border: 1px solid var(--bs-border-color);
                box-shadow: none !important;
                padding: .25rem .5rem .5rem;
                border-radius: .5rem;
                margin-bottom: .5rem;
            }
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
    @endphp

    <header class="sticky-top shadow-sm">

        {{-- ===== Thanh 1: tên website (trang chủ) + tìm kiếm + tài khoản ===== --}}
        <nav class="top-bar navbar navbar-dark bg-dark py-2">
            <div class="container d-flex flex-wrap align-items-center">

                <a class="navbar-brand fw-bold order-1 me-2" href="{{ route('home') }}">
                    <i class="bi bi-book-half text-warning"></i> {{ config('app.name') }}
                </a>

                {{-- Ô tìm kiếm: điện thoại nằm dưới hàng đầu, máy tính nằm giữa --}}
                <form class="top-search order-3 order-lg-2 d-flex mt-2 mt-lg-0 mx-lg-4" method="GET"
                    action="{{ route('search') }}">
                    <input class="form-control form-control-sm me-2" type="search" name="q" value="{{ request('q') }}"
                        placeholder="Tìm truyện, tên khác, tác giả..." maxlength="100">
                    <button class="btn btn-warning btn-sm" type="submit" aria-label="Tìm kiếm"><i
                            class="bi bi-search"></i></button>
                </form>

                {{-- Tài khoản + nút mũi tên mở menu (điện thoại) --}}
                <div class="order-2 order-lg-3 ms-auto d-flex align-items-center gap-2">
                    @guest
                    @if (Route::has('login'))
                    <a class="btn btn-sm btn-outline-light" href="{{ route('login') }}">
                        <i class="bi bi-box-arrow-in-right"></i> <span class="d-none d-sm-inline">Đăng nhập</span>
                    </a>
                    @endif
                    @if (Route::has('register'))
                    <a class="btn btn-sm btn-warning d-none d-sm-inline-block" href="{{ route('register') }}">Đăng
                        ký</a>
                    @endif
                    @else
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> <span class="d-none d-sm-inline">{{ auth()->user()->name
                                }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if (Route::has('profile.edit'))
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-gear"></i>
                                    Hồ sơ</a></li>
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
                                    <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i>
                                        Đăng xuất</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    @endguest

                    <button class="menu-toggle btn btn-sm btn-outline-light d-lg-none" type="button"
                        data-bs-toggle="collapse" data-bs-target="#subMenu" aria-controls="subMenu"
                        aria-expanded="false" aria-label="Mở menu">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                </div>
            </div>
        </nav>

        {{-- ===== Thanh 2: các mục điều hướng (điện thoại: ẩn, bấm mũi tên để trượt xuống) ===== --}}
        <div class="collapse d-lg-block" id="subMenu">
            <nav class="subnav bg-body border-bottom" aria-label="Menu chính">
                <div class="container">
                    <ul class="nav flex-column flex-lg-row flex-lg-wrap">
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
                                    <a class="dropdown-item rounded"
                                        href="{{ route('categories.show', $navCategory) }}">
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

                        {{-- Điện thoại nhỏ: nút Đăng ký ở thanh trên bị ẩn nên đưa vào menu --}}
                        @guest
                        @if (Route::has('register'))
                        <li class="nav-item d-sm-none">
                            <a class="nav-link" href="{{ route('register') }}"><i class="bi bi-person-plus"></i> Đăng
                                ký</a>
                        </li>
                        @endif
                        @endguest
                    </ul>
                </div>
            </nav>
        </div>
    </header>

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