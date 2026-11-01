<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\News;
use Illuminate\Support\Facades\Cache;

/**
 * Aggregates for the admin dashboard widgets.
 *
 * "Participants" are distinct Bebras usernames, so someone holding both a
 * Participant and a Winner certificate for the same edition is counted once.
 * Figures are derived from the certificates uploaded to the portal.
 */
class DashboardStatistics
{
    public const CACHE_KEY = 'dashboard_statistics';

    private const TTL_SECONDS = 300;

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array{certificates: array, news: array}
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::TTL_SECONDS, fn () => [
            'certificates' => $this->computeCertificates(),
            'news' => $this->computeNews(),
        ]);
    }

    /**
     * @return array{
     *     years: list<int>,
     *     participants: array<int, array<string, int>>,
     *     winners: array<int, array<string, int>>,
     *     totals: array<int, array{participants: int, winners: int, schools: int}>
     * }
     */
    public function certificates(): array
    {
        return $this->all()['certificates'];
    }

    /**
     * @return array{total: int, published: int, draft: int, scheduled: int, last30: int, monthly: array<string, int>}
     */
    public function news(): array
    {
        return $this->all()['news'];
    }

    private function computeCertificates(): array
    {
        $participants = $this->countByYearAndCategory(Certificate::query());
        $winners = $this->countByYearAndCategory(Certificate::query()->where('award_type', 'Winner'));

        $yearTotals = Certificate::query()
            ->selectRaw('year, COUNT(DISTINCT bebras_username) as participants')
            ->groupBy('year')
            ->pluck('participants', 'year');

        $winnerTotals = Certificate::query()
            ->where('award_type', 'Winner')
            ->selectRaw('year, COUNT(DISTINCT bebras_username) as winners')
            ->groupBy('year')
            ->pluck('winners', 'year');

        $schools = Certificate::query()
            ->whereNotNull('school_name')
            ->where('school_name', '!=', '')
            ->selectRaw('year, COUNT(DISTINCT LOWER(TRIM(school_name))) as schools')
            ->groupBy('year')
            ->pluck('schools', 'year');

        $years = $yearTotals->keys()->map(fn ($year) => (int) $year)->sort()->values()->all();

        $totals = [];
        foreach ($years as $year) {
            $totals[$year] = [
                'participants' => (int) ($yearTotals[$year] ?? 0),
                'winners' => (int) ($winnerTotals[$year] ?? 0),
                'schools' => (int) ($schools[$year] ?? 0),
            ];
        }

        return [
            'years' => $years,
            'participants' => $participants,
            'winners' => $winners,
            'totals' => $totals,
        ];
    }

    /**
     * @return array<int, array<string, int>> [year => [category => distinct usernames]]
     */
    private function countByYearAndCategory($query): array
    {
        $result = [];

        $rows = $query
            ->selectRaw('year, category, COUNT(DISTINCT bebras_username) as total')
            ->groupBy('year', 'category')
            ->get();

        foreach ($rows as $row) {
            $result[(int) $row->year][$row->category] = (int) $row->total;
        }

        return $result;
    }

    private function computeNews(): array
    {
        $now = now();

        $monthly = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthly[$now->copy()->startOfMonth()->subMonthsNoOverflow($i)->format('Y-m')] = 0;
        }

        // Grouped in PHP to stay portable between SQLite and MySQL.
        News::published()
            ->where('published_at', '>=', $now->copy()->startOfMonth()->subMonthsNoOverflow(11))
            ->pluck('published_at')
            ->each(function ($publishedAt) use (&$monthly) {
                $key = $publishedAt->format('Y-m');
                if (array_key_exists($key, $monthly)) {
                    $monthly[$key]++;
                }
            });

        return [
            'total' => News::count(),
            'published' => News::published()->count(),
            'draft' => News::where('is_published', false)->count(),
            'scheduled' => News::where('is_published', true)->where('published_at', '>', $now)->count(),
            'last30' => News::published()->where('published_at', '>=', $now->copy()->subDays(30))->count(),
            'monthly' => $monthly,
        ];
    }
}
