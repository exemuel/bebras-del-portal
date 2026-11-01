<?php

namespace App\Filament\Widgets;

use App\Services\DashboardStatistics;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class NewsActivityChart extends ChartWidget
{
    protected static ?int $sort = 5;

    protected ?string $heading = 'Berita Terbit per Bulan (12 bulan terakhir)';

    protected ?string $maxHeight = '260px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $monthly = app(DashboardStatistics::class)->news()['monthly'];

        return [
            'datasets' => [[
                'label' => 'Berita terbit',
                'data' => array_values($monthly),
                'fill' => true,
                'tension' => 0.3,
            ]],
            'labels' => array_map(
                fn (string $month) => Carbon::createFromFormat('Y-m', $month)->translatedFormat('M Y'),
                array_keys($monthly),
            ),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
            'plugins' => ['legend' => ['display' => false]],
        ];
    }
}
