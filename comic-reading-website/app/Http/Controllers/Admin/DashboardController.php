<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Comic;
use App\Models\Comment;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'comics'   => Comic::count(),
            'chapters' => Chapter::count(),
            'users'    => User::count(),
            'comments' => Comment::count(),
            'views'    => Comic::sum('views'),
        ];

        $topComics      = Comic::orderByDesc('views')->take(5)->get();
        $latestChapters = Chapter::with('comic')->latest()->take(8)->get();
        $latestComments = Comment::with(['user', 'comic'])->latest()->take(8)->get();

        return view('admin.dashboard', compact(
            'stats',
            'topComics',
            'latestChapters',
            'latestComments'
        ));
    }
}