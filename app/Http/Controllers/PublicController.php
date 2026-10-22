<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\SiteSetting;
use Illuminate\View\View;

class PublicController extends Controller
{
    /**
     * Display the portal landing page.
     */
    public function index(): View
    {
        $latestNews = News::published()
            ->latest('published_at')
            ->take(3)
            ->get();

        $settings = SiteSetting::all()->pluck('value', 'key')->toArray();

        return view('public.index', compact('latestNews', 'settings'));
    }
}
