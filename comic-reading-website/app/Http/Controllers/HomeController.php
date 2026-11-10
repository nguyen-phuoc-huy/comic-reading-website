<?php

namespace App\Http\Controllers;

use App\Models\Comic;
use App\Models\ReadingHistory;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Truyện mới cập nhật chương (có ít nhất 1 chương)
        $latestComics = Comic::query()
            ->with(['author', 'latestChapters'])
            ->withMax('chapters as latest_chapter_number', 'number')
            ->withMax('chapters as latest_chapter_at', 'created_at')
            ->has('chapters')
            ->orderByDesc('latest_chapter_at')
            ->paginate(24);

        // Truyện phổ biến: nhiều người theo dõi nhất (bằng nhau thì xếp theo lượt xem)
        $popularComics = Comic::query()
            ->with('latestChapters')
            ->withCount('followers')
            ->withMax('chapters as latest_chapter_number', 'number')
            ->orderByDesc('followers_count')
            ->orderByDesc('views')
            ->take(12)
            ->get();

        // Truyện xem nhiều nhất
        $topComics = Comic::query()
            ->withMax('chapters as latest_chapter_number', 'number')
            ->orderByDesc('views')
            ->take(10)
            ->get();

        // Truyện đã hoàn thành
        $completedComics = Comic::query()
            ->with('latestChapters')
            ->withMax('chapters as latest_chapter_number', 'number')
            ->where('status', 'completed')
            ->latest('updated_at')
            ->take(12)
            ->get();

        // Đọc tiếp (chỉ khi đã đăng nhập)
        $continueReading = $request->user()
            ? ReadingHistory::with(['comic', 'chapter'])
                ->where('user_id', $request->user()->id)
                ->latest('updated_at')
                ->take(6)
                ->get()
            : collect();

        return view('home', compact(
            'latestComics',
            'popularComics',
            'topComics',
            'completedComics',
            'continueReading'
        ));
    }
}
