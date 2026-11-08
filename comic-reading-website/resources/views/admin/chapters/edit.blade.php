@extends('layouts.admin')

@section('title', 'Sửa chương')

@section('content')
<a href="{{ route('admin.comics.chapters.index', $chapter->comic) }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Danh sách chương</a>
<h4 class="fw-bold mb-3">Sửa chương {{ (float) $chapter->number }} - {{ $chapter->comic->title }}</h4>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.chapters.update', $chapter) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Số chương <span class="text-danger">*</span></label>
                    <input type="number" step="0.1" min="0" name="number" value="{{ old('number', (float) $chapter->number) }}" required
                           class="form-control @error('number') is-invalid @enderror">
                    @error('number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-9">
                    <label class="form-label">Tiêu đề</label>
                    <input type="text" name="title" value="{{ old('title', $chapter->title) }}" maxlength="255"
                           class="form-control @error('title') is-invalid @enderror">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Thêm ảnh mới (nối tiếp vào cuối chương)</label>
                    <input type="file" name="images[]" multiple accept="image/*"
                           class="form-control @error('images.*') is-invalid @enderror">
                    @error('images.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Cập nhật</button>
                <a href="{{ route('admin.comics.chapters.index', $chapter->comic) }}" class="btn btn-outline-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>

{{-- Ảnh hiện có (nằm ngoài form chính để tránh lồng form) --}}
<div class="card shadow-sm">
    <div class="card-header fw-semibold">Các trang hiện có ({{ $chapter->images->count() }})</div>
    <div class="card-body">
        @if ($chapter->images->isEmpty())
            <p class="text-muted mb-0">Chương chưa có ảnh.</p>
        @else
            <div class="row row-cols-3 row-cols-md-5 row-cols-lg-6 g-2">
                @foreach ($chapter->images as $image)
                    <div class="col">
                        <div class="card h-100">
                            <img src="{{ asset('storage/' . $image->image_path) }}" class="card-img-top"
                                 style="height:140px;object-fit:cover" loading="lazy" alt="Trang {{ $loop->iteration }}">
                            <div class="card-body p-1 d-flex justify-content-between align-items-center">
                                <small class="text-muted">#{{ $loop->iteration }}</small>
                                <form method="POST" action="{{ route('admin.chapters.images.destroy', [$chapter, $image]) }}"
                                      onsubmit="return confirm('Xóa ảnh này?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger py-0"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
