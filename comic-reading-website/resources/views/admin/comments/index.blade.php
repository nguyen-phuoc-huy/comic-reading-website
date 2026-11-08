@extends('layouts.admin')

@section('title', 'Quản lý bình luận')

@section('content')
<h4 class="fw-bold mb-3">Quản lý bình luận</h4>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5">
        <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Tìm theo nội dung bình luận...">
    </div>
    <div class="col-md-2"><button class="btn btn-outline-primary w-100">Tìm</button></div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Người dùng</th>
                    <th>Truyện / Chương</th>
                    <th>Nội dung</th>
                    <th>Thời gian</th>
                    <th class="text-end" style="width:80px">Xóa</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($comments as $comment)
                    <tr>
                        <td>
                            {{ $comment->user->name ?? 'Ẩn danh' }}
                            @if ($comment->parent_id)<span class="badge text-bg-light border">trả lời</span>@endif
                        </td>
                        <td>
                            @if ($comment->comic)
                                <a href="{{ route('comics.show', $comment->comic) }}" target="_blank" class="text-decoration-none">
                                    {{ $comment->comic->title }}
                                </a>
                            @else
                                <span class="text-muted">(đã xóa)</span>
                            @endif
                            @if ($comment->chapter)
                                <div class="small text-muted">Chap {{ (float) $comment->chapter->number }}</div>
                            @endif
                        </td>
                        <td style="max-width:360px">{{ \Illuminate\Support\Str::limit($comment->content, 120) }}</td>
                        <td class="small text-muted">{{ $comment->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}"
                                  onsubmit="return confirm('Xóa bình luận này (kèm các trả lời)?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Chưa có bình luận nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-center mt-3">
    {{ $comments->links('pagination::bootstrap-5') }}
</div>
@endsection
