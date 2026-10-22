<?php

namespace Tests\Feature;

use App\Models\Certificate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CertificateSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_certificate_search_page_renders_successfully(): void
    {
        $response = $this->get('/sertifikat');

        $response->assertStatus(200);
        $response->assertSee('Pencarian Sertifikat Bebras Challenge');
        $response->assertSee('Bebras Username');
    }

    public function test_certificate_search_finds_record_and_normalizes_username(): void
    {
        // Stored certificate with lowercase normalized username
        Certificate::create([
            'bebras_username' => 'budi_santoso_2024',
            'participant_name' => 'Budi Santoso',
            'school_name' => 'SMP Negeri 1 Laguboti',
            'category' => 'Penggalang',
            'award_type' => 'Winner',
            'year' => 2024,
            'file_path' => 'certificates/2024/budi_sample.pdf',
        ]);

        // Search via GET with uppercase + spaces — controller normalizes to lowercase
        $response = $this->get('/sertifikat?username=+BUDI_SANTOSO_2024+');

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertSee('SMP Negeri 1 Laguboti');
        $response->assertSee('Penggalang');
        $response->assertSee('Pemenang');  // Indonesian-only badge
        $response->assertSee('PDF');
    }

    public function test_multiple_certificates_are_returned_for_same_username(): void
    {
        Certificate::create([
            'bebras_username' => 'siti_juara',
            'participant_name' => 'Siti Nurhaliza',
            'category' => 'Penggalang',
            'award_type' => 'Participant',
            'year' => 2023,
            'file_path' => 'certificates/2023/siti_2023.pdf',
        ]);

        Certificate::create([
            'bebras_username' => 'siti_juara',
            'participant_name' => 'Siti Nurhaliza',
            'category' => 'Penegak',
            'award_type' => 'Winner',
            'year' => 2024,
            'file_path' => 'certificates/2024/siti_2024.pdf',
        ]);

        // GET request — returns grouped results for both years
        $response = $this->get('/sertifikat?username=siti_juara');

        $response->assertStatus(200);
        $response->assertSee('Siti Nurhaliza');
        $response->assertSee('2023');
        $response->assertSee('2024');
        $response->assertSee('Penggalang');
        $response->assertSee('Penegak');
    }

    public function test_unknown_username_displays_polite_not_found_message(): void
    {
        $response = $this->get('/sertifikat?username=username_tidak_terdaftar_999');

        $response->assertStatus(200);
        $response->assertSee('Sertifikat Tidak Ditemukan');
        $response->assertSee('Sertifikat tidak ditemukan untuk username', false);
    }

    public function test_secure_download_streams_pdf_with_clean_filename(): void
    {
        $dummyPdfContent = '%PDF-1.4 dummy certificate content';
        $path = 'certificates/2024/test_cert.pdf';
        Storage::disk('local')->put($path, $dummyPdfContent);

        $cert = Certificate::create([
            'bebras_username' => 'andi_wijaya',
            'participant_name' => 'Andi Wijaya',
            'category' => 'Siaga',
            'award_type' => 'Participant',
            'year' => 2024,
            'file_path' => $path,
        ]);

        $response = $this->get('/sertifikat/unduh/' . $cert->id);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        // Filename now includes category and award_type to avoid collisions
        $this->assertStringContainsString('andi_wijaya_2024_Siaga_Participant_sertifikat.pdf', $response->headers->get('Content-Disposition') ?? '');
    }

    public function test_downloading_nonexistent_certificate_returns_404(): void
    {
        $this->get('/sertifikat/unduh/99999')->assertStatus(404);
    }
}
