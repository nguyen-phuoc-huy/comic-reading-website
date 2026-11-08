<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comic;
use App\Models\ReadingHistory;
use Illuminate\Http\Request;

class ComicController extends Controller
{
    // GET /truyen
    public function index(Request $request)
    {
        $statuses = ['ongoing', 'completed', 'dropped'];

        $query = Comic::query()
            ->with('author')
            ->withMax('chapters as latest_chapter_number', 'number');

        if (in_array($request->status, $statuses, true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->whereHas('categories', fn ($q) => $q->where('slug', $request->category));
        }

        match ($request->input('sort')) {
            'views' => $query->orderByDesc('views'),
            'name'  => $query->orderBy('title'),
            default => $query->latest('updated_at'),
        };

        $comics     = $query->paginate(24)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('comics.index', compact('comics', 'categories'));
    }

    // GET /truyen/{comic:slug}
    public function show(Request $request, Comic $comic)
    {
        $this->countView($comic);

        $comic->load(['author', 'categories', 'chapters']);

        $chapters      = $comic->chapters;      // đã sắp xếp giảm dần theo số chương
        $latestChapter = $chapters->first();
        $firstChapter  = $chapters->last();

        $user        = $request->user();
        $isFollowing = false;
        $lastRead    = null;

        if ($user) {
            $isFollowing = $comic->followers()->where('users.id', $user->id)->exists();

            $lastRead = ReadingHistory::with('chapter')
                ->where('user_id', $user->id)
                ->where('comic_id', $comic->id)
                ->first();
        }

        // Bình luận gốc kèm các trả lời
        $comments = $comic->comments()
            ->whereNull('parent_id')
            ->with([
                'user',
                'replies' => fn ($q) => $q->with('user')->oldest(),
            ])
            ->latest()
            ->paginate(10, ['*'], 'comments_page');

        // Truyện cùng thể loại
        $categoryIds = $comic->categories->pluck('id');

        $relatedComics = Comic::query()
            ->where('id', '!=', $comic->id)
            ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds))
            ->inRandomOrder()
            ->take(6)
            ->get();

        return view('comics.show', compact(
            'comic',
            'chapters',
            'latestChapter',
            'firstChapter',
            'isFollowing',
            'lastRead',
            'comments',
            'relatedComics'
        ));
    }

    /**
     * Mỗi người chỉ tính 1 lượt xem / truyện / phiên làm việc.
     * Dùng query builder thô (toBase) để không làm đổi updated_at.
     */
    private function countView(Comic $comic): void
    {
        $key = "viewed_comic_{$comic->id}";

        if (! session()->has($key)) {
            Comic::whereKey($comic->id)->toBase()->increment('views');
            session()->put($key, true);
        }
    }
}