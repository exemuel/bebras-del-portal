<?php

namespace App\Filament\Resources\News;

use App\Filament\Resources\News\Pages\CreateNews;
use App\Filament\Resources\News\Pages\EditNews;
use App\Filament\Resources\News\Pages\ListNews;
use App\Models\News;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static ?string $navigationLabel = 'Berita & Pengumuman';

    protected static ?string $modelLabel = 'Berita';

    protected static ?string $pluralModelLabel = 'Berita & Pengumuman';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Judul Berita')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, ?string $state, callable $set) {
                        if ($operation === 'create' && filled($state)) {
                            $set('slug', Str::slug($state));
                        }
                    }),

                TextInput::make('slug')
                    ->label('Slug URL')
                    ->required()
                    ->unique(News::class, 'slug', ignoreRecord: true)
                    ->maxLength(255),

                Textarea::make('excerpt')
                    ->label('Ringkasan / Excerpt')
                    ->rows(3)
                    ->helperText('Ringkasan singkat yang ditampilkan pada kartu berita. Kosongkan untuk generate otomatis dari konten.')
                    ->maxLength(500)
                    ->columnSpanFull(),

                RichEditor::make('content')
                    ->label('Isi Konten Berita')
                    ->required()
                    ->columnSpanFull(),

                FileUpload::make('image_path')
                    ->label('Gambar Utama / Sampul')
                    ->image()
                    ->disk('public')
                    ->directory('news')
                    ->imageEditor(),

                Toggle::make('is_published')
                    ->label('Publikasikan?')
                    ->default(true),

                DateTimePicker::make('published_at')
                    ->label('Waktu Publikasi')
                    ->default(now())
                    ->helperText('Berita hanya tampil jika waktu publikasi telah tercapai.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Sampul')
                    ->disk('public'),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_published')
                    ->label('Status')
                    ->boolean(),

                TextColumn::make('published_at')
                    ->label('Dipublikasi Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                Filter::make('published')
                    ->label('Hanya yang Terbit')
                    ->query(fn (Builder $query): Builder => $query->where('is_published', true)->where('published_at', '<=', now())),

                Filter::make('draft')
                    ->label('Hanya Draf')
                    ->query(fn (Builder $query): Builder => $query->where('is_published', false)),

                Filter::make('scheduled')
                    ->label('Terjadwal (Masa Depan)')
                    ->query(fn (Builder $query): Builder => $query->where('is_published', true)->where('published_at', '>', now())),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNews::route('/'),
            'create' => CreateNews::route('/create'),
            'edit' => EditNews::route('/{record}/edit'),
        ];
    }
}
