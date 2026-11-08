@php
    $chapter = $chapter ?? null;
    $me = auth()->user();
@endphp

<div class="card shadow-sm mt-4" id="comments">
    <div class="card-header fw-semibold">
        <i class="bi bi-chat-dots"></i> Bình luận ({{ $comments->total() }})
    </div>
    <div class="card-body">

        @auth
            <form method="POST" action="{{ route('comments.store') }}" class="mb-4">
                @csrf
                <input type="hidden" name="comic_id" value="{{ $comic->id }}">
                @if ($chapter)
                    <input type="hidden" name="chapter_id" value="{{ $chapter->id }}">
                @endif
                <textarea name="content" rows="3" maxlength="1000" required
                          class="form-control mb-2" placeholder="Viết bình luận của bạn..."></textarea>
                <button class="btn btn-primary btn-sm"><i class="bi bi-send"></i> Gửi bình luận</button>
            </form>
        @else
            <div class="alert alert-light border">
                Vui lòng <a href="{{ Route::has('login') ? route('login') : '#' }}">đăng nhập</a> để bình luận.
            </div>
        @endauth

        @forelse ($comments as $comment)
            <div class="d-flex mb-3">
                <div class="flex-shrink-0 rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-3"
                     style="width:40px;height:40px">
                    {{ mb_strtoupper(mb_substr($comment->user->name, 0, 1)) }}
                </div>
                <div class="flex-grow-1">
                    <div>
                        <strong>{{ $comment->user->name }}</strong>
                        <small class="text-muted ms-2">{{ $comment->created_at->diffForHumans() }}</small>
                    </div>
                    <div class="mb-1">{!! nl2br(e($comment->content)) !!}</div>

                    <div class="small">
                        @auth
                            <a class="text-decoration-none me-3" data-bs-toggle="collapse" href="#reply-{{ $comment->id }}">
                                <i class="bi bi-reply"></i> Trả lời
                            </a>
                        @endauth
                        @if ($me && ($me->id === $comment->user_id || $me->role === 'admin'))
                            <form method="POST" action="{{ route('comments.destroy', $comment) }}" class="d-inline"
                                  onsubmit="return confirm('Xóa bình luận này?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-link btn-sm p-0 text-danger text-decoration-none">
                                    <i class="bi bi-trash"></i> Xóa
                                </button>
                            </form>
                        @endif
                    </div>

                    @auth
                        <div class="collapse mt-2" id="reply-{{ $comment->id }}">
                            <form method="POST" action="{{ route('comments.store') }}">
                                @csrf
                                <input type="hidden" name="comic_id" value="{{ $comic->id }}">
                                @if ($chapter)
                                    <input type="hidden" name="chapter_id" value="{{ $chapter->id }}">
                                @endif
                                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                <textarea name="content" rows="2" maxlength="1000" required
                                          class="form-control form-control-sm mb-2"
                                          placeholder="Trả lời {{ $comment->user->name }}..."></textarea>
                                <button class="btn btn-outline-primary btn-sm">Gửi trả lời</button>
                            </form>
                        </div>
                    @endauth

                    {{-- Các trả lời --}}
                    @foreach ($comment->replies as $reply)
                        <div class="d-flex mt-3">
                            <div class="flex-shrink-0 rounded-circle bg-info text-white d-flex align-items-center justify-content-center me-2"
                                 style="width:32px;height:32px;font-size:.85rem">
                                {{ mb_strtoupper(mb_substr($reply->user->name, 0, 1)) }}
                            </div>
                            <div class="flex-grow-1">
                                <div>
                                    <strong>{{ $reply->user->name }}</strong>
                                    <small class="text-muted ms-2">{{ $reply->created_at->diffForHumans() }}</small>
                                </div>
                                <div>{!! nl2br(e($reply->content)) !!}</div>
                                @if ($me && ($me->id === $reply->user_id || $me->role === 'admin'))
                                    <form method="POST" action="{{ route('comments.destroy', $reply) }}" class="small"
                                          onsubmit="return confirm('Xóa trả lời này?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-link btn-sm p-0 text-danger text-decoration-none">
                                            <i class="bi bi-trash"></i> Xóa
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @if (!$loop->last)<hr>@endif
        @empty
            <p class="text-muted mb-0">Chưa có bình luận nào. Hãy là người đầu tiên!</p>
        @endforelse

        <div class="d-flex justify-content-center mt-3">
            {{ $comments->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
