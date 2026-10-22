<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_successfully(): void
    {
        SiteSetting::set('hero_tagline', 'Tagline Khusus Bebras IT Del');

        News::factory()->create([
            'title' => 'Berita Utama Hari Ini',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Bebras Biro IT Del');
        $response->assertSee('Tagline Khusus Bebras IT Del');
        $response->assertSee('Berita Utama Hari Ini');
        $response->assertSee('Apa Itu Bebras?');
        $response->assertSee('Kategori Tantangan');
    }

    public function test_draft_and_scheduled_news_are_not_shown_on_landing_page(): void
    {
        News::factory()->create([
            'title' => 'Berita Draf Rahasia',
            'is_published' => false,
            'published_at' => null,
        ]);

        News::factory()->create([
            'title' => 'Berita Masa Depan Belum Terbit',
            'is_published' => true,
            'published_at' => now()->addDays(5),
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertDontSee('Berita Draf Rahasia');
        $response->assertDontSee('Berita Masa Depan Belum Terbit');
    }
}
