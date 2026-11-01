<?php

namespace App\Filament\Widgets;

use App\Models\Certificate;
use App\Services\DashboardStatistics;
use Filament\Support\Colors\Color;
use Filament\Widgets\ChartWidget;

class ParticipantsByCategoryChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Peserta per Kategori setiap Tahun';

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '320px';

    public ?string $filter = 'all';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return [
            'all' => 'Semua peserta',
            'winners' => 'Hanya pemenang',
        ];
    }

    protected function getData(): array
    {
        $stats = app(DashboardStatistics::class)->certificates();
        $source = $this->filter === 'winners' ? $stats['winners'] : $stats['participants'];

        // Same hues as the category colors registered in AdminPanelProvider.
        $colors = [
            'SiKecil' => Color::Pink[500],
            'Siaga' => Color::Emerald[500],
            'Penggalang' => Color::Amber[500],
            'Penegak' => Color::Purple[500],
        ];

        $datasets = [];
        foreach (array_keys(Certificate::CATEGORIES) as $category) {
            $datasets[] = [
                'label' => $category,
                'data' => array_map(fn (int $year) => $source[$year][$category] ?? 0, $stats['years']),
                'backgroundColor' => $colors[$category],
                'borderRadius' => 6,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => array_map('strval', $stats['years']),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
            'plugins' => ['legend' => ['position' => 'bottom']],
        ];
    }
}
