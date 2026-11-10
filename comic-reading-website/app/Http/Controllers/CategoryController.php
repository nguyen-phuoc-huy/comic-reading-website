<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // GET /the-loai
    public function index()
    {
        $categories = Category::withCount('comics')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    // GET /the-loai/{category:slug}
    public function show(Request $request, Category $category)
    {
        $statuses = ['ongoing', 'completed', 'dropped'];

        $query = $category->comics()
            ->with(['author', 'latestChapters'])
            ->withMax('chapters as latest_chapter_number', 'number');

        if (in_array($request->status, $statuses, true)) {
            $query->where('status', $request->status);
        }

        match ($request->input('sort')) {
            'views' => $query->orderByDesc('views'),
            'name'  => $query->orderBy('title'),
            default => $query->latest('comics.updated_at'),
        };

        $comics     = $query->paginate(24)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('categories.show', compact('category', 'comics', 'categories'));
    }
}
