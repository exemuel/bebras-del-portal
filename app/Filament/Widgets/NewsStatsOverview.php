<?php

namespace App\Filament\Widgets;

use App\Services\DashboardStatistics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class NewsStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Berita & Pengumuman';

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $news = app(DashboardStatistics::class)->news();

        return [
            Stat::make('Total berita', number_format($news['total'], 0, ',', '.'))
                ->chart(array_values($news['monthly']))
                ->color('primary'),
            Stat::make('Terbit', number_format($news['published'], 0, ',', '.'))
                ->description($news['last30'] . ' dalam 30 hari terakhir')
                ->color('success'),
            Stat::make('Draf', number_format($news['draft'], 0, ',', '.'))
                ->color('gray'),
            Stat::make('Terjadwal', number_format($news['scheduled'], 0, ',', '.'))
                ->description('Terbit pada waktu publikasi')
                ->color('warning'),
        ];
    }
}
