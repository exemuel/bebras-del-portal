<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\News\NewsResource;
use App\Models\News;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestNewsTable extends TableWidget
{
    protected static ?int $sort = 6;

    protected static ?string $heading = 'Berita Terbaru';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => News::query()->latest('published_at')->latest('id')->limit(5))
            ->columns([
                TextColumn::make('title')->label('Judul')->limit(40),
                IconColumn::make('is_published')->label('Status')->boolean(),
                TextColumn::make('published_at')->label('Dipublikasi')->dateTime('d M Y'),
            ])
            ->recordUrl(fn (News $record) => NewsResource::getUrl('edit', ['record' => $record]))
            ->paginated(false)
            ->emptyStateHeading('Belum ada berita');
    }
}
