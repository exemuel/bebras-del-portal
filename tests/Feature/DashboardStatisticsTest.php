<?php

namespace Tests\Feature;

use App\Filament\Widgets\CertificateStatsOverview;
use App\Filament\Widgets\LatestNewsTable;
use App\Filament\Widgets\NewsActivityChart;
use App\Filament\Widgets\NewsStatsOverview;
use App\Filament\Widgets\ParticipantsByCategoryChart;
use App\Filament\Widgets\ParticipantsByYearTable;
use App\Models\Certificate;
use App\Models\News;
use App\Models\User;
use App\Services\DashboardStatistics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardStatisticsTest extends TestCase
{
    use RefreshDatabase;

    private function certificate(string $username, string $category, string $award, int $year, ?string $school = 'SMP 1'): Certificate
    {
        return Certificate::factory()->create([
            'bebras_username' => $username,
            'category' => $category,
            'award_type' => $award,
            'year' => $year,
            'school_name' => $school,
        ]);
    }

    public function test_participants_are_distinct_usernames_per_year_and_category(): void
    {
        $this->certificate('budi', 'Penggalang', 'Participant', 2024);
        $this->certificate('budi', 'Penggalang', 'Winner', 2024); // same person, second certificate
        $this->certificate('siti', 'Penegak', 'Participant', 2024, 'SMA 2');
        $this->certificate('budi', 'Penggalang', 'Participant', 2023);

        $stats = app(DashboardStatistics::class)->certificates();

        $this->assertSame([2023, 2024], $stats['years']);
        $this->assertSame(1, $stats['participants'][2024]['Penggalang']);
        $this->assertSame(1, $stats['participants'][2024]['Penegak']);
        $this->assertSame(2, $stats['totals'][2024]['participants']);
        $this->assertSame(1, $stats['totals'][2024]['winners']);
        $this->assertSame(1, $stats['winners'][2024]['Penggalang']);
        $this->assertSame(2, $stats['totals'][2024]['schools']);
        $this->assertSame(1, $stats['totals'][2023]['participants']);
    }

    public function test_news_counts_separate_published_draft_and_scheduled(): void
    {
        News::factory()->count(2)->create(['is_published' => true, 'published_at' => now()->subDays(3)]);
        News::factory()->draft()->create();
        News::factory()->scheduled()->create();

        $news = app(DashboardStatistics::class)->news();

        $this->assertSame(4, $news['total']);
        $this->assertSame(2, $news['published']);
        $this->assertSame(1, $news['draft']);
        $this->assertSame(1, $news['scheduled']);
        $this->assertSame(2, $news['last30']);
        $this->assertCount(12, $news['monthly']);
        $this->assertSame(2, array_sum($news['monthly']));
    }

    public function test_statistics_refresh_when_data_changes(): void
    {
        $service = app(DashboardStatistics::class);
        $this->assertSame([], $service->certificates()['years']);

        $this->certificate('budi', 'Siaga', 'Participant', 2024);

        $this->assertSame([2024], $service->certificates()['years']);
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_dashboard_renders_with_no_data(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertOk();
    }

    public function test_dashboard_renders_with_data(): void
    {
        $this->certificate('budi', 'Siaga', 'Participant', 2024);
        News::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertOk()
            ->assertSeeLivewire(CertificateStatsOverview::class)
            ->assertSeeLivewire(ParticipantsByCategoryChart::class)
            ->assertSeeLivewire(ParticipantsByYearTable::class)
            ->assertSeeLivewire(NewsStatsOverview::class)
            ->assertSeeLivewire(NewsActivityChart::class)
            ->assertSeeLivewire(LatestNewsTable::class);
    }

    public function test_every_widget_renders(): void
    {
        $this->certificate('budi', 'Siaga', 'Participant', 2024);
        $this->certificate('siti', 'SiKecil', 'Winner', 2023);
        News::factory()->create(['title' => 'Berita Uji Dashboard']);

        $this->actingAs(User::factory()->create());

        foreach ([
            CertificateStatsOverview::class,
            ParticipantsByCategoryChart::class,
            ParticipantsByYearTable::class,
            NewsStatsOverview::class,
            NewsActivityChart::class,
            LatestNewsTable::class,
        ] as $widget) {
            Livewire::test($widget)->assertSuccessful();
        }

        Livewire::test(ParticipantsByCategoryChart::class)->set('filter', 'winners')->assertSuccessful();
        Livewire::test(LatestNewsTable::class)->assertSee('Berita Uji Dashboard');
        Livewire::test(ParticipantsByYearTable::class)->assertSee('2024');
    }

    public function test_widgets_render_with_no_data(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CertificateStatsOverview::class)->assertSee('Belum ada sertifikat diunggah');
        Livewire::test(ParticipantsByYearTable::class)->assertSee('Belum ada data sertifikat');
        Livewire::test(ParticipantsByCategoryChart::class)->assertSuccessful();
    }
}
