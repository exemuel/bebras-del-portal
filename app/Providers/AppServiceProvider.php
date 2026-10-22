<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private const SETTINGS_CACHE_KEY = 'site_settings';

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Share site settings (footer contact + social links) with the public layout.
        View::composer('layouts.app', function ($view) {
            $view->with('globalSettings', $this->siteSettings());
        });

        // Keep the cache in sync when admins edit settings.
        SiteSetting::saved(fn () => Cache::forget(self::SETTINGS_CACHE_KEY));
        SiteSetting::deleted(fn () => Cache::forget(self::SETTINGS_CACHE_KEY));
    }

    private function siteSettings(): array
    {
        try {
            return Cache::remember(self::SETTINGS_CACHE_KEY, 600, fn () => SiteSetting::pluck('value', 'key')->toArray());
        } catch (\Throwable $e) {
            Log::warning('Unable to load site settings: ' . $e->getMessage());

            return [];
        }
    }
}
