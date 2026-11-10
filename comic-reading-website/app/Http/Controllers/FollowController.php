<?php

namespace App\Http\Controllers;

use App\Models\Comic;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    // GET /theo-doi
    public function index(Request $request)
    {
        $comics = $request->user()
            ->followedComics()
            ->with(['author', 'latestChapters'])
            ->withMax('chapters as latest_chapter_number', 'number')
            ->withMax('chapters as latest_chapter_at', 'created_at')
            ->orderByPivot('created_at', 'desc')
            ->paginate(24);

        return view('follows.index', compact('comics'));
    }

    // POST /truyen/{comic}/follow
    public function toggle(Request $request, Comic $comic)
    {
        $result    = $request->user()->followedComics()->toggle($comic->id);
        $following = count($result['attached']) > 0;

        if ($request->expectsJson()) {
            return response()->json(['following' => $following]);
        }

        return back()->with(
            'success',
            $following ? 'Đã theo dõi truyện.' : 'Đã bỏ theo dõi truyện.'
        );
    }
}
