<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Comic;
use App\Models\Comment;
use App\Models\ReadingHistory;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
    // GET /truyen/{comic:slug}/chap-{chapter:number}
    public function show(Request $request, Comic $comic, Chapter $chapter)
    {
        $chapter->load('images');

        $this->countView($comic, $chapter);
        $this->saveHistory($request, $comic, $chapter);

        // Chương trước / sau (chapters() mặc định sắp giảm dần nên cần reorder)
        $prevChapter = $comic->chapters()
            ->where('number', '<', $chapter->number)
            ->reorder('number', 'desc')
            ->first();

        $nextChapter = $comic->chapters()
            ->where('number', '>', $chapter->number)
            ->reorder('number', 'asc')
            ->first();

        // Danh sách chương cho ô chọn nhanh
        $chapterList = $comic->chapters()->get(['id', 'comic_id', 'number']);

        // Bình luận của chương
        $comments = Comment::query()
            ->where('chapter_id', $chapter->id)
            ->whereNull('parent_id')
            ->with([
                'user',
                'replies' => fn ($q) => $q->with('user')->oldest(),
            ])
            ->latest()
            ->paginate(10, ['*'], 'comments_page');

        return view('chapters.show', compact(
            'comic',
            'chapter',
            'prevChapter',
            'nextChapter',
            'chapterList',
            'comments'
        ));
    }

    /**
     * Tăng lượt xem chương và truyện, mỗi phiên chỉ tính 1 lần cho mỗi chương.
     */
    private function countView(Comic $comic, Chapter $chapter): void
    {
        $key = "viewed_chapter_{$chapter->id}";

        if (! session()->has($key)) {
            Chapter::whereKey($chapter->id)->toBase()->increment('views');
            Comic::whereKey($comic->id)->toBase()->increment('views');
            session()->put($key, true);
        }
    }

    /**
     * Mỗi truyện chỉ lưu 1 dòng: chương đọc gần nhất.
     */
    private function saveHistory(Request $request, Comic $comic, Chapter $chapter): void
    {
        $user = $request->user();

        if (! $user) {
            return;
        }

        $history = ReadingHistory::firstOrNew([
            'user_id'  => $user->id,
            'comic_id' => $comic->id,
        ]);

        $history->chapter_id = $chapter->id;
        $history->updated_at = now(); // cập nhật thời gian dù vẫn là chương cũ
        $history->save();
    }
}