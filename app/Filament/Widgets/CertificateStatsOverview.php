<?php

namespace App\Filament\Widgets;

use App\Services\DashboardStatistics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CertificateStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Peserta Bebras Challenge';

    protected ?string $description = 'Dihitung dari sertifikat yang sudah diunggah ke portal (username unik).';

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $stats = app(DashboardStatistics::class)->certificates();
        $years = $stats['years'];

        if ($years === []) {
            return [
                Stat::make('Peserta', '0')->description('Belum ada sertifikat diunggah'),
            ];
        }

        $latest = end($years);
        $previous = count($years) > 1 ? $years[count($years) - 2] : null;
        $totals = $stats['totals'];

        $participants = $totals[$latest]['participants'];

        $participantStat = Stat::make("Peserta {$latest}", number_format($participants, 0, ',', '.'))
            ->chart(array_map(fn (int $year) => $totals[$year]['participants'], $years))
            ->color('primary');

        if ($previous !== null) {
            $delta = $participants - $totals[$previous]['participants'];
            $participantStat
                ->description(($delta >= 0 ? '+' : '') . number_format($delta, 0, ',', '.') . " dari {$previous}")
                ->descriptionIcon($delta >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->descriptionColor($delta >= 0 ? 'success' : 'danger');
        }

        return [
            $participantStat,
            Stat::make("Pemenang {$latest}", number_format($totals[$latest]['winners'], 0, ',', '.'))
                ->chart(array_map(fn (int $year) => $totals[$year]['winners'], $years))
                ->color('warning'),
            Stat::make("Sekolah {$latest}", number_format($totals[$latest]['schools'], 0, ',', '.'))
                ->chart(array_map(fn (int $year) => $totals[$year]['schools'], $years))
                ->color('success'),
            Stat::make('Edisi tercatat', (string) count($years))
                ->description($years[0] . ' – ' . $latest),
        ];
    }
}
