<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\CertificateStatsOverview;
use App\Filament\Widgets\LatestNewsTable;
use App\Filament\Widgets\NewsActivityChart;
use App\Filament\Widgets\NewsStatsOverview;
use App\Filament\Widgets\ParticipantsByCategoryChart;
use App\Filament\Widgets\ParticipantsByYearTable;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile(isSimple: false)
            // Google Authenticator (TOTP) as a second factor beside the password. Optional
            // until the admin has enrolled; then flip `isRequired` to true to force it.
            ->multiFactorAuthentication([
                AppAuthentication::make()
                    ->recoverable()
                    ->brandName('Bebras IT Del'),
            ], isRequired: false)
            ->brandName('Bebras IT Del')
            ->brandLogo(asset('img/itdel.png'))
            ->brandLogoHeight('2.75rem')
            ->favicon(asset('img/favicon.ico'))
            ->darkMode(false)
            ->font('Nunito', provider: GoogleFontProvider::class)
            // Baseline palettes only: public/css/admin.css re-points every ramp at the
            // shared tokens in public/css/theme.css (the single source of truth).
            ->colors([
                'primary' => Color::Blue,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
                'sikecil' => Color::Pink,
                'siaga' => Color::Emerald,
                'penggalang' => Color::Amber,
                'penegak' => Color::Purple,
            ])
            ->renderHook(PanelsRenderHook::HEAD_END, fn () => view('partials.theme-head')->render()
                . '<link href="' . asset('css/admin.css') . '" rel="stylesheet">')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                CertificateStatsOverview::class,
                ParticipantsByCategoryChart::class,
                ParticipantsByYearTable::class,
                NewsStatsOverview::class,
                NewsActivityChart::class,
                LatestNewsTable::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
