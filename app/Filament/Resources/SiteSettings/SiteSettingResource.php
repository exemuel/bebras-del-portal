<?php

namespace App\Filament\Resources\SiteSettings;

use App\Filament\Resources\SiteSettings\Pages\CreateSiteSetting;
use App\Filament\Resources\SiteSettings\Pages\EditSiteSetting;
use App\Filament\Resources\SiteSettings\Pages\ListSiteSettings;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static ?string $navigationLabel = 'Pengaturan Konten Situs';

    protected static ?string $modelLabel = 'Pengaturan Konten';

    protected static ?string $pluralModelLabel = 'Pengaturan Konten Situs';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label('Kunci Pengaturan (Key)')
                    ->required()
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->maxLength(100),

                Textarea::make('value')
                    ->label('Nilai / Teks Konten')
                    ->rows(6)
                    ->columnSpanFull()
                    ->helperText('Dapat berupa teks biasa atau potongan HTML terstruktur sesuai kebutuhan bagian situs.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Kunci')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                TextColumn::make('value')
                    ->label('Isi Konten')
                    ->limit(80)
                    ->searchable(),

                TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSiteSettings::route('/'),
            'create' => CreateSiteSetting::route('/create'),
            'edit' => EditSiteSetting::route('/{record}/edit'),
        ];
    }
}
