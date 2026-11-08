<?php

namespace App\Http\Controllers;

use App\Models\Comic;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    // GET /tim-kiem?q=...
    public function index(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $keyword = trim((string) $request->input('q', ''));
        $comics  = null;

        if ($keyword !== '') {
            // Escape các ký tự đặc biệt của LIKE
            $like = '%' . addcslashes($keyword, '%_\\') . '%';

            $comics = Comic::query()
                ->with('author')
                ->withMax('chapters as latest_chapter_number', 'number')
                ->where(function ($q) use ($like) {
                    $q->where('title', 'like', $like)
                        ->orWhere('other_names', 'like', $like)
                        ->orWhereHas('author', fn ($a) => $a->where('name', 'like', $like));
                })
                ->orderByDesc('views')
                ->paginate(24)
                ->withQueryString();
        }

        return view('search', compact('keyword', 'comics'));
    }
}