<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\View\View;

class NewsController extends Controller
{
    /**
     * Display a paginated listing of published news.
     */
    public function index(): View
    {
        $newsList = News::published()
            ->latest('published_at')
            ->paginate(9);

        return view('public.news.index', compact('newsList'));
    }

    /**
     * Display the specified news article.
     */
    public function show(string $slug): View
    {
        $news = News::published()
            ->where('slug', $slug)
            ->firstOrFail();

        $recentNews = News::published()
            ->where('id', '!=', $news->id)
            ->latest('published_at')
            ->take(4)
            ->get();

        return view('public.news.show', compact('news', 'recentNews'));
    }
}
