<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // POST /comments
    public function store(Request $request)
    {
        $data = $request->validate([
            'comic_id'   => ['required', 'exists:comics,id'],
            'chapter_id' => ['nullable', 'exists:chapters,id'],
            'parent_id'  => ['nullable', 'exists:comments,id'],
            'content'    => ['required', 'string', 'max:1000'],
        ]);

        // Chương phải thuộc đúng truyện
        if (! empty($data['chapter_id'])) {
            $belongs = Chapter::where('id', $data['chapter_id'])
                ->where('comic_id', $data['comic_id'])
                ->exists();

            abort_unless($belongs, 422, 'Chương không thuộc truyện này.');
        }

        // Chỉ cho trả lời 1 cấp: nếu trả lời vào một reply thì gắn vào bình luận gốc
        if (! empty($data['parent_id'])) {
            $parent = Comment::findOrFail($data['parent_id']);

            abort_unless((int) $parent->comic_id === (int) $data['comic_id'], 422, 'Bình luận gốc không hợp lệ.');

            $data['parent_id'] = $parent->parent_id ?? $parent->id;
        }

        Comment::create([
            'user_id'    => $request->user()->id,
            'comic_id'   => $data['comic_id'],
            'chapter_id' => $data['chapter_id'] ?? null,
            'parent_id'  => $data['parent_id'] ?? null,
            'content'    => $data['content'],
        ]);

        return back()->with('success', 'Đã đăng bình luận.');
    }

    // DELETE /comments/{comment}
    public function destroy(Request $request, Comment $comment)
    {
        $user = $request->user();

        abort_unless(
            $comment->user_id === $user->id || $user->isAdmin(),
            403
        );

        $comment->delete(); // các reply tự xóa nhờ cascadeOnDelete

        return back()->with('success', 'Đã xóa bình luận.');
    }
}