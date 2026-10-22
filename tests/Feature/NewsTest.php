<?php

namespace Tests\Feature;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_index_displays_published_news(): void
    {
        News::factory()->create([
            'title' => 'Pengumuman Lolos Tahap Satu',
            'is_published' => true,
            'published_at' => now()->subHours(2),
        ]);

        News::factory()->create([
            'title' => 'Draf Belum Terbit',
            'is_published' => false,
        ]);

        $response = $this->get('/berita');

        $response->assertStatus(200);
        $response->assertSee('Pengumuman Lolos Tahap Satu');
        $response->assertDontSee('Draf Belum Terbit');
    }

    public function test_news_detail_displays_content_for_published_article(): void
    {
        $news = News::factory()->create([
            'title' => 'Artikel Resmi Bebras',
            'slug' => 'artikel-resmi-bebras',
            'content' => '<p>Ini adalah isi konten resmi yang sangat penting.</p>',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/berita/' . $news->slug);

        $response->assertStatus(200);
        $response->assertSee('Artikel Resmi Bebras');
        $response->assertSee('Ini adalah isi konten resmi yang sangat penting.', false);
    }

    public function test_unpublished_news_returns_404(): void
    {
        $draft = News::factory()->create([
            'title' => 'Draf Rahasia Internal',
            'slug' => 'draf-rahasia-internal',
            'is_published' => false,
        ]);

        $future = News::factory()->create([
            'title' => 'Berita Terjadwal Masa Depan',
            'slug' => 'berita-terjadwal-masa-depan',
            'is_published' => true,
            'published_at' => now()->addDays(3),
        ]);

        $this->get('/berita/' . $draft->slug)->assertStatus(404);
        $this->get('/berita/' . $future->slug)->assertStatus(404);
    }
}
