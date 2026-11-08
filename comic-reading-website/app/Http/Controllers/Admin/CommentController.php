<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // GET admin/comments
    public function index(Request $request)
    {
        $comments = Comment::with(['user', 'comic', 'chapter'])
            ->when($request->q, fn ($q, $kw) => $q->where('content', 'like', "%{$kw}%"))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.comments.index', compact('comments'));
    }

    // DELETE admin/comments/{comment}
    public function destroy(Comment $comment)
    {
        // Các trả lời (replies) sẽ tự xóa theo nhờ cascadeOnDelete trên parent_id
        $comment->delete();

        return back()->with('success', 'Đã xóa bình luận.');
    }
}