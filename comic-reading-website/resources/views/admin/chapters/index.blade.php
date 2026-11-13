@extends('layouts.admin')

@section('title', 'Chương - ' . $comic->title)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="{{ route('admin.comics.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i>
            Danh sách truyện</a>
        <h4 class="fw-bold mb-0">Chương của: {{ $comic->title }}</h4>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <form method="POST" action="{{ route('admin.comics.organize', $comic) }}"
            onsubmit="return confirm('Chuyển toàn bộ ảnh bìa và ảnh các chương vào thư mục comics/{{ $comic->slug }} và đặt lại tên file theo quy ước? Việc này có thể mất ít phút nếu truyện có nhiều ảnh.')">
            @csrf
            <button class="btn btn-outline-secondary" title="Đổi tên file và thư mục ảnh theo tên truyện">
                <i class="bi bi-folder-symlink"></i> Chuẩn hóa tên ảnh &amp; thư mục
            </button>
        </form>
        <a href="{{ route('admin.comics.chapters.create', $comic) }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Thêm chương
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Chương</th>
                    <th>Tiêu đề</th>
                    <th class="text-center">Số ảnh</th>
                    <th class="text-end">Lượt xem</th>
                    <th>Ngày đăng</th>
                    <th class="text-end" style="width:200px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($chapters as $chapter)
                <tr>
                    <td class="fw-semibold">Chap {{ (float) $chapter->number }}</td>
                    <td>{{ $chapter->title ?: '—' }}</td>
                    <td class="text-center">{{ $chapter->images_count }}</td>
                    <td class="text-end">{{ number_format($chapter->views) }}</td>
                    <td>{{ $chapter->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-end">
                        <a href="{{ route('chapters.show', [$comic, $chapter]) }}" target="_blank"
                            class="btn btn-sm btn-outline-secondary" title="Xem">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('admin.chapters.edit', $chapter) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i> Sửa
                        </a>
                        <form method="POST" action="{{ route('admin.chapters.destroy', $chapter) }}" class="d-inline"
                            onsubmit="return confirm('Xóa chương này cùng toàn bộ ảnh?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Truyện chưa có chương nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-center mt-3">
    {{ $chapters->links('pagination::bootstrap-5') }}
</div>
@endsection