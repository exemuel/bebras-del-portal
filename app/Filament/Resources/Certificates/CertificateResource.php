<?php

namespace App\Filament\Resources\Certificates;

use App\Filament\Resources\Certificates\Pages\CreateCertificate;
use App\Filament\Resources\Certificates\Pages\EditCertificate;
use App\Filament\Resources\Certificates\Pages\ListCertificates;
use App\Models\Certificate;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class CertificateResource extends Resource
{
    protected static ?string $model = Certificate::class;

    protected static ?string $navigationLabel = 'Kelola Sertifikat';

    protected static ?string $modelLabel = 'Sertifikat';

    protected static ?string $pluralModelLabel = 'Kelola Sertifikat';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('bebras_username')
                    ->label('Bebras Username')
                    ->required()
                    ->helperText('Username peserta Bebras Challenge (akan otomatis dinormalisasi ke huruf kecil).')
                    ->maxLength(100),

                TextInput::make('participant_name')
                    ->label('Nama Lengkap Peserta')
                    ->required()
                    ->maxLength(255),

                TextInput::make('school_name')
                    ->label('Nama Asal Sekolah / Institusi')
                    ->maxLength(255),

                Select::make('category')
                    ->label('Kategori Tantangan')
                    ->options(Certificate::CATEGORIES)
                    ->required(),

                Select::make('award_type')
                    ->label('Tipe Penghargaan')
                    ->options(Certificate::AWARD_TYPES)
                    ->required(),

                TextInput::make('year')
                    ->label('Tahun Kompetisi')
                    ->required()
                    ->numeric()
                    ->default(date('Y'))
                    ->minValue(2015)
                    ->maxValue(2050),

                FileUpload::make('file_path')
                    ->label('Berkas Sertifikat (PDF)')
                    ->disk('local')
                    ->directory('certificates')
                    ->acceptedFileTypes(['application/pdf'])
                    ->required()
                    ->columnSpanFull()
                    ->helperText('Berkas disimpan di storage privat yang aman dan tidak dapat diakses langsung via URL publik.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bebras_username')
                    ->label('Username')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('participant_name')
                    ->label('Nama Peserta')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('school_name')
                    ->label('Sekolah')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => Certificate::categoryKey($state)),

                TextColumn::make('award_type')
                    ->label('Penghargaan')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Winner' ? 'warning' : 'primary'),

                TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(Certificate::CATEGORIES),

                SelectFilter::make('award_type')
                    ->label('Penghargaan')
                    ->options(Certificate::AWARD_TYPES),

                SelectFilter::make('year')
                    ->label('Tahun')
                    ->options(fn () => Certificate::distinct()->pluck('year', 'year')->toArray()),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Unduh PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Certificate $record) {
                        if (!Storage::disk('local')->exists($record->file_path)) {
                            \Filament\Notifications\Notification::make()
                                ->title('Berkas PDF tidak ditemukan di server.')
                                ->danger()
                                ->send();
                            return null;
                        }
                        return Storage::disk('local')->download($record->file_path, $record->download_filename);
                    }),
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
            'index' => ListCertificates::route('/'),
            'create' => CreateCertificate::route('/create'),
            'edit' => EditCertificate::route('/{record}/edit'),
        ];
    }
}
