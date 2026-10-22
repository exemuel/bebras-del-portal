<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\News;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PublicUiConsistencyTest extends TestCase
{
    use RefreshDatabase;

    private function cert(array $overrides = []): Certificate
    {
        return Certificate::create($overrides + [
            'bebras_username' => 'siti_juara',
            'participant_name' => 'Siti Nurhaliza',
            'school_name' => null,
            'category' => 'Penggalang',
            'award_type' => 'Participant',
            'year' => 2023,
            'file_path' => 'certificates/x.pdf',
        ]);
    }

    public function test_every_public_page_has_exactly_one_h1_and_no_mojibake(): void
    {
        $this->cert();
        $news = News::create([
            'title' => 'Judul Berita',
            'slug' => 'judul-berita',
            'content' => '<p>Isi</p>',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        foreach (['/', '/berita', '/berita/' . $news->slug, '/sertifikat', '/sertifikat?username=siti_juara', '/sertifikat?username=tidak_ada'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertSame(1, substr_count($html, '<h1'), "Expected one <h1> on {$url}");
            $this->assertStringNotContainsString('â€', $html, "Mojibake on {$url}");
        }
    }

    public function test_year_filter_returns_only_selected_year(): void
    {
        $this->cert(['year' => 2023, 'participant_name' => 'Peserta Dua Tiga']);
        $this->cert(['year' => 2024, 'category' => 'Penegak', 'participant_name' => 'Peserta Dua Empat']);

        $this->get('/sertifikat?username=siti_juara&year=2023')
            ->assertSee('Peserta Dua Tiga')
            ->assertDontSee('Peserta Dua Empat');
    }

    public function test_year_dropdown_is_available_before_first_search(): void
    {
        $this->cert();

        $this->get('/sertifikat')->assertSee('id="year-filter"', false);
    }

    public function test_same_user_same_year_has_distinct_download_filenames(): void
    {
        $a = $this->cert(['award_type' => 'Participant']);
        $b = $this->cert(['award_type' => 'Winner']);

        $this->assertNotSame($a->download_filename, $b->download_filename);
    }

    public function test_footer_reads_contact_info_from_site_settings(): void
    {
        Cache::flush();
        SiteSetting::create(['key' => 'contact_email', 'value' => 'halo@contoh.test']);

        foreach (['/', '/berita', '/sertifikat'] as $url) {
            $this->get($url)->assertSee('halo@contoh.test');
        }
    }

    public function test_public_footer_has_no_admin_link(): void
    {
        $this->get('/')->assertDontSee('/admin', false);
    }

    public function test_nav_anchor_links_are_hash_only_on_home_and_absolute_elsewhere(): void
    {
        $this->get('/')->assertSee('href="#aboutus"', false);
        $this->get('/berita')->assertSee(url('/') . '#aboutus', false);
    }

    public function test_search_results_are_noindex_but_blank_search_is_not(): void
    {
        $this->cert();

        $this->get('/sertifikat?username=siti_juara')->assertSee('noindex', false);
        $this->get('/sertifikat')->assertDontSee('noindex', false);
    }

    public function test_award_badges_are_indonesian_only(): void
    {
        $this->cert(['award_type' => 'Winner']);

        $this->get('/sertifikat?username=siti_juara')
            ->assertSee('Pemenang')
            ->assertDontSee('(Winner)');
    }
}
