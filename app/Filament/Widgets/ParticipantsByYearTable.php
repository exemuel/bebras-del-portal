<?php

namespace App\Filament\Widgets;

use App\Models\Certificate;
use App\Services\DashboardStatistics;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ParticipantsByYearTable extends TableWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Rekap Peserta per Tahun dan Kategori';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $stats = app(DashboardStatistics::class)->certificates();
        $categories = array_keys(Certificate::CATEGORIES);

        // One row per edition (newest first), built from the cached aggregates.
        $rows = [];
        foreach (array_reverse($stats['years']) as $year) {
            $row = ['year' => $year];
            foreach ($categories as $category) {
                $row[$category] = $stats['participants'][$year][$category] ?? 0;
            }
            $rows[$year] = $row + [
                'total' => $stats['totals'][$year]['participants'],
                'winners' => $stats['totals'][$year]['winners'],
                'schools' => $stats['totals'][$year]['schools'],
            ];
        }

        $columns = [
            TextColumn::make('year')->label('Tahun')->weight('bold'),
        ];

        foreach ($categories as $category) {
            $columns[] = TextColumn::make($category)
                ->label($category)
                ->badge()
                ->color(Certificate::categoryKey($category))
                ->alignEnd();
        }

        $columns[] = TextColumn::make('total')->label('Total Peserta')->weight('bold')->alignEnd();
        $columns[] = TextColumn::make('winners')->label('Pemenang')->alignEnd();
        $columns[] = TextColumn::make('schools')->label('Sekolah')->alignEnd();

        return $table
            ->records(fn (): array => $rows)
            ->columns($columns)
            ->paginated(false)
            ->emptyStateHeading('Belum ada data sertifikat')
            ->emptyStateDescription('Statistik muncul setelah sertifikat diunggah.');
    }
}
