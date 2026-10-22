<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'SiKecil' => 'SiKecil (SD Kelas 1–3)',
        'Siaga' => 'Siaga (SD Kelas 4–6)',
        'Penggalang' => 'Penggalang (SMP Kelas 7–9)',
        'Penegak' => 'Penegak (SMA/SMK Kelas 10–12)',
    ];

    public const AWARD_TYPES = [
        'Participant' => 'Peserta',
        'Winner' => 'Pemenang',
    ];

    public static function availableYears(): \Illuminate\Support\Collection
    {
        return static::query()->distinct()->orderBy('year', 'desc')->pluck('year');
    }

    /**
     * Color key for a category. Matches the `.cat-*` CSS classes on the public
     * site and the custom Filament colors of the same name in the admin panel.
     */
    public static function categoryKey(?string $category): string
    {
        return in_array($category, array_keys(self::CATEGORIES), true) ? strtolower($category) : 'gray';
    }

    public static function awardLabel(?string $type): string
    {
        return self::AWARD_TYPES[$type] ?? (string) $type;
    }

    protected $fillable = [
        'bebras_username',
        'participant_name',
        'school_name',
        'category',
        'award_type',
        'year',
        'file_path',
        'original_filename',
        'file_size',
        'file_sha256',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'file_size' => 'integer',
        ];
    }

    /**
     * Contract for normalizing Bebras usernames.
     */
    public static function normalizeUsername(string $username): string
    {
        return strtolower(trim($username));
    }

    /**
     * Mutator to ensure username is always saved canonicalized.
     */
    public function setBebrasUsernameAttribute(string $value): void
    {
        $this->attributes['bebras_username'] = static::normalizeUsername($value);
    }

    /**
     * Get safe download filename.
     */
    public function getDownloadFilenameAttribute(): string
    {
        $cleanUser     = preg_replace('/[^a-zA-Z0-9_-]/', '_', $this->bebras_username);
        $cleanCategory = preg_replace('/[^a-zA-Z0-9_-]/', '_', $this->category);
        $cleanAward    = preg_replace('/[^a-zA-Z0-9_-]/', '_', $this->award_type);
        return "{$cleanUser}_{$this->year}_{$cleanCategory}_{$cleanAward}_sertifikat.pdf";
    }
}
