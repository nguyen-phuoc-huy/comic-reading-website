<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comic;
use App\Models\ReadingHistory;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Truyện mới cập nhật chương (có ít nhất 1 chương)
        $latestComics = Comic::query()
            ->with('author')
            ->withMax('chapters as latest_chapter_number', 'number')
            ->withMax('chapters as latest_chapter_at', 'created_at')
            ->has('chapters')
            ->orderByDesc('latest_chapter_at')
            ->paginate(24);

        // Truyện xem nhiều nhất
        $topComics = Comic::query()
            ->withMax('chapters as latest_chapter_number', 'number')
            ->orderByDesc('views')
            ->take(10)
            ->get();

        // Truyện đã hoàn thành
        $completedComics = Comic::query()
            ->withMax('chapters as latest_chapter_number', 'number')
            ->where('status', 'completed')
            ->latest('updated_at')
            ->take(12)
            ->get();

        $categories = Category::orderBy('name')->get();

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
            'topComics',
            'completedComics',
            'categories',
            'continueReading'
        ));
    }
}