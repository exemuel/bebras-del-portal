<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Administrator account
        User::firstOrCreate(
            ['email' => 'admin@bebras.del.ac.id'],
            [
                'name' => 'Biro Bebras IT Del',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Authoritative Site Settings (from legacy site)
        $settings = [
            'hero_tagline' => 'Bebras bertujuan memperkenalkan informatika dan berpikir komputasional kepada guru, generasi muda, pengambil keputusan pendidikan, dan masyarakat, sebagai ilmu yang mendidik dan meningkatkan pemahaman dalam pemanfaatan teknologi.',
            
            'about_apa_itu_bebras' => "<p>Secara harfiah, “Bebras” adalah kata dalam bahasa Lithuania, yang berarti “berang-berang” dalam bahasa Indonesia. Prof. Valentina Dagiene dari Universitas Vilnius, Lithuania adalah yang mencetuskan gagasan <strong>Bebras Computational Thinking Challenge</strong>, yang saat ini diikuti oleh lebih dari 55 negara di dunia.</p><p>Bebras adalah sebuah inisiatif internasional yang tujuannya adalah untuk mempromosikan <em>Computational Thinking</em> (Berpikir dengan landasan Komputasi atau Informatika), di kalangan guru dan murid mulai tingkat SD, serta untuk masyarakat luas.</p><p>Cara untuk promosi adalah dengan menyelenggarakan kegiatan kompetisi secara daring (<em>online</em>), yang disebut sebagai “Tantangan Bebras”. Tantangan Bebras bukan hanya sekedar untuk menang. Selain untuk berlomba, tantangan Bebras juga bertujuan agar siswa belajar <em>Computational Thinking</em> selama maupun setelah lomba.</p><p>Di Indonesia, kompetisi dapat dilaksanakan di sekolah yang mempunyai cukup komputer, atau di universitas pembina.</p><p>Selama Kompetisi, siswa harus memberikan solusi untuk persoalan yang disebut “Soal Bebras”. Soal-soal yang bertema komputasi/informatika ini dirancang semenarik mungkin, dan seharusnya dapat dijawab oleh siswa tanpa pengetahuan sebelumnya tentang komputasi atau informatika.</p><p>Setiap soal Bebras mengandung aspek komputasi atau informatika dan dimaksudkan untuk menguji bakat peserta untuk berpikir komputasi atau informatika. Untuk menjawab soal-soal Bebras, secara alamiah, siswa dituntut untuk berpikir terkait dengan informasi, struktur diskrit, komputasi, pengolahan data, serta harus menggunakan konsep algoritmik.</p>",
            
            'about_sejarah_del' => '<p>Institut Teknologi Del telah menjadi anggota Bebras Indonesia sebagai Biro Bebras sejak tahun 2017. Sebagai bagian dari Bebras Indonesia, IT Del telah secara aktif turut dalam berbagai kegiatan termasuk di antaranya menjadi penyelenggara <em>Workshop</em> dan Tantangan Bebras. Saat ini tercatat 4 dosen dari berbagai program studi telah bergabung dan menjadi volunteer. Bebras Biro IT Del dipimpin oleh seorang koordinator yang berperan sebagai perpanjangan tangan Bebras Indonesia.</p>',
            
            'about_computational_thinking' => '<p>Berpikir komputasional (<em>Computational Thinking</em>) adalah proses berpikir dalam memformulasikan persoalan dan berstrategi dalam menentukan/memilih solusi yang efektif, efisien, optimal untuk dikerjakan oleh agen pemroses informasi atau solusi tersebut. Agen pemroses informasi bisa manusia atau komputer [Computational Thinking - PPG Kemendikdasmen].</p>',
            
            'stat_mitra_count' => '59',
            'stat_mitra_label' => 'Mitra Bebras Challenge 2024 (Biro IT Del)',
            'stat_peserta_count' => '3151',
            'stat_peserta_label' => 'Peserta Bebras Challenge 2024 (Biro IT Del)',
            
            'activity_workshop_title' => 'Lokakarya',
            'activity_workshop_desc' => 'Memperkenalkan konsep komputasi atau informatika dan tantangan Bebras kepada guru dan siswa.',
            'activity_challenge_title' => 'Tantangan Bebras',
            'activity_challenge_desc' => 'Diselenggarakan sesuai jadwal yang ditetapkan komite internasional, biasanya minggu kedua atau ketiga November (disebut Bebras Week).',
            
            'contact_address' => 'Institut Teknologi Del, Jl. Sisingamangaraja, Sitoluama, Laguboti, Toba Samosir, Sumatera Utara, Indonesia, 22381',
            'contact_phone' => '+62 632 331234',
            'contact_email' => 'del@bebras.ac.id',

            // Social media links
            'social_facebook' => 'https://www.facebook.com/bebras.itdel',
            'social_instagram' => 'https://www.instagram.com/bebras.itdel',
            'social_youtube'  => 'https://www.youtube.com/@bebrasindonesia',
        ];

        foreach ($settings as $key => $val) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $val]);
        }

        // 3. Initial Published News (from actual Bebras IT Del activities)
        News::firstOrCreate(
            ['slug' => 'pembukaan-pendaftaran-bebras-challenge-2024-biro-it-del'],
            [
                'title' => 'Pembukaan Pendaftaran Bebras Challenge 2024 Biro IT Del',
                'excerpt' => 'Biro Bebras Institut Teknologi Del secara resmi membuka pendaftaran Tantangan Bebras 2024 untuk jenjang SD, SMP, dan SMA/SMK sederajat.',
                'content' => '<p>Biro Bebras Institut Teknologi Del secara resmi membuka pendaftaran Tantangan Bebras 2024. Kegiatan ini bertujuan mengasah kemampuan berpikir komputasional generasi muda di kawasan Toba dan sekitarnya.</p><p>Kategori yang dibuka mencakup SiKecil (SD kelas 1-3), Siaga (SD kelas 4-6), Penggalang (SMP kelas 7-9), dan Penegak (SMA/SMK kelas 10-12). Pendaftaran dapat dikoordinasikan langsung melalui guru pembina di masing-masing sekolah mitra.</p>',
                'image_path' => null,
                'is_published' => true,
                'published_at' => now()->subDays(10),
            ]
        );

        News::firstOrCreate(
            ['slug' => 'workshop-computational-thinking-guru-sekolah-mitra'],
            [
                'title' => 'Workshop Computational Thinking bagi Guru Sekolah Mitra',
                'excerpt' => 'Pelatihan intensif pemecahan masalah dan pengenalan materi komputasi bagi guru-guru di Kabupaten Toba dan sekitarnya.',
                'content' => '<p>Sebagai bagian dari komitmen pengabdian kepada masyarakat, Biro Bebras IT Del menyelenggarakan pelatihan komputasi dan pemecahan masalah logika bagi puluhan guru dari berbagai sekolah mitra.</p><p>Diharapkan melalui kegiatan ini para guru dapat mengintegrasikan computational thinking ke dalam kegiatan belajar mengajar sehari-hari.</p>',
                'image_path' => null,
                'is_published' => true,
                'published_at' => now()->subDays(5),
            ]
        );

        News::firstOrCreate(
            ['slug' => 'pengumuman-hasil-dan-sertifikat-bebras-challenge-2024'],
            [
                'title' => 'Pengumuman Hasil dan Sertifikat Bebras Challenge 2024',
                'excerpt' => 'Sertifikat keikutsertaan dan penghargaan pemenang kini dapat diunduh secara mandiri melalui portal resmi dengan memasukkan username Bebras.',
                'content' => '<p>Selamat kepada seluruh 3.151 peserta Bebras Challenge 2024 yang telah menunjukkan dedikasi dan antusiasme tinggi!</p><p>Sertifikat resmi peserta dan pemenang kini telah diterbitkan dan dapat diunduh langsung melalui menu Sertifikat di portal ini dengan memasukkan username masing-masing.</p>',
                'image_path' => null,
                'is_published' => true,
                'published_at' => now()->subDay(),
            ]
        );

        // 4. Sample Certificates for Demonstration
        $dummyPdf = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000052 00000 n \n0000000101 00000 n \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n178\n%%EOF";

        $sampleCerts = [
            [
                'bebras_username' => 'smp_budi_123',
                'participant_name' => 'Budi Santoso',
                'school_name' => 'SMP Negeri 1 Laguboti',
                'category' => 'Penggalang',
                'award_type' => 'Winner',
                'year' => 2024,
                'file_subpath' => 'certificates/2024/smp_budi_123_Winner.pdf',
            ],
            [
                'bebras_username' => 'siti_nur_456',
                'participant_name' => 'Siti Nurhaliza',
                'school_name' => 'SMA Unggul Del',
                'category' => 'Penegak',
                'award_type' => 'Participant',
                'year' => 2024,
                'file_subpath' => 'certificates/2024/siti_nur_456_Participant.pdf',
            ],
            [
                'bebras_username' => 'siti_nur_456',
                'participant_name' => 'Siti Nurhaliza',
                'school_name' => 'SMA Unggul Del',
                'category' => 'Penggalang',
                'award_type' => 'Winner',
                'year' => 2023,
                'file_subpath' => 'certificates/2023/siti_nur_456_Winner.pdf',
            ],
        ];

        foreach ($sampleCerts as $c) {
            \Illuminate\Support\Facades\Storage::disk('local')->put($c['file_subpath'], $dummyPdf);

            \App\Models\Certificate::firstOrCreate(
                [
                    'bebras_username' => $c['bebras_username'],
                    'year' => $c['year'],
                    'category' => $c['category'],
                    'award_type' => $c['award_type'],
                ],
                [
                    'participant_name' => $c['participant_name'],
                    'school_name' => $c['school_name'],
                    'file_path' => $c['file_subpath'],
                    'original_filename' => basename($c['file_subpath']),
                    'file_size' => strlen($dummyPdf),
                    'file_sha256' => hash('sha256', $dummyPdf),
                ]
            );
        }
    }
}
