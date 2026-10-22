@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    <!-- Hero Header Start -->
    <div class="container-xxl bg-primary hero-header mb-5" id="home">
        <div class="container px-lg-5">
            <div class="row g-4 align-items-center">
                <div class="col-lg-8 text-center text-lg-start">
                    <h1 class="text-white mb-4 animated slideInDown">
                        <span class="visually-hidden">Bebras Biro Institut Teknologi Del</span>
                        <img style="max-width: 60%; height: auto;" class="img-fluid wow fadeInUp" data-wow-delay="0.2s" src="{{ asset('img/bebras ind red.png') }}" alt="Bebras Indonesia">
                    </h1>
                    <p class="text-white pb-3 animated slideInDown fs-5" style="line-height: 1.8;">
                        {{ $settings['hero_tagline'] ?? 'Bebras bertujuan memperkenalkan informatika dan berpikir komputasional kepada guru, generasi muda, pengambil keputusan pendidikan, dan masyarakat, sebagai ilmu yang mendidik dan meningkatkan pemahaman dalam pemanfaatan teknologi.' }}
                    </p>
                    <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                        <a href="{{ route('certificates.search') }}" class="btn btn-secondary-gradient py-sm-3 px-sm-5 rounded-pill animated slideInLeft">
                            <i class="fa fa-search me-2"></i>Cari & Unduh Sertifikat
                        </a>
                        <a href="#aboutus" class="btn btn-outline-light py-sm-3 px-sm-5 rounded-pill animated slideInRight">
                            Tentang Kami
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 text-center">
                    <img class="img-fluid wow fadeInUp" data-wow-delay="0.4s" src="{{ asset('img/maskot-bebras-ulos.png') }}" alt="Maskot Bebras Ulos" style="max-height: 420px;">
                </div>
            </div>
        </div>
    </div>
    <!-- Hero Header End -->

    <!-- About Us Start -->
    <div class="container-xxl page-section--lg" id="aboutus">
        <div class="container px-lg-5">
            <div class="row g-5 align-items-center">
                <div class="col-lg-8 wow fadeInUp" data-wow-delay="0.1s">
                    <span class="eyebrow">Tentang Kami</span>
                    <h3 class="mb-4">#1 Apa Itu Bebras?</h3>
                    <div class="mb-4 text-muted" style="line-height: 1.8;">
                        {!! $settings['about_apa_itu_bebras'] ?? '<p>Bebras adalah inisiatif internasional untuk mempromosikan Computational Thinking di kalangan guru dan siswa.</p>' !!}
                    </div>

                    <h3 class="mb-4">#2 Sejarah Bebras Biro Institut Teknologi Del</h3>
                    <div class="mb-4 text-muted" style="line-height: 1.8;">
                        {!! $settings['about_sejarah_del'] ?? '<p>Institut Teknologi Del telah menjadi anggota Bebras Indonesia sebagai Biro Bebras sejak tahun 2017.</p>' !!}
                    </div>

                    <h3 class="mb-4">#3 Apa itu Berpikir Komputasional?</h3>
                    <div class="mb-4 text-muted" style="line-height: 1.8;">
                        {!! $settings['about_computational_thinking'] ?? '<p>Berpikir komputasional (Computational Thinking) adalah proses berpikir dalam merumuskan persoalan dan solusinya.</p>' !!}
                    </div>

                    <!-- Statistics Counter -->
                    <div class="row g-4 mb-4 pt-3">
                        <div class="col-sm-6 wow fadeIn" data-wow-delay="0.5s">
                            <div class="d-flex align-items-center p-3 bg-light rounded shadow-sm">
                                <i class="fa fa-building fa-2x text-primary-gradient flex-shrink-0"></i>
                                <div class="ms-3">
                                    <h2 class="mb-0 text-primary-gradient" data-toggle="counter-up">{{ $settings['stat_mitra_count'] ?? 59 }}</h2>
                                    <p class="mb-0 text-muted small fw-medium">{{ $settings['stat_mitra_label'] ?? 'Mitra Bebras Challenge 2024 (Biro IT Del)' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 wow fadeIn" data-wow-delay="0.7s">
                            <div class="d-flex align-items-center p-3 bg-light rounded shadow-sm">
                                <i class="fa fa-users fa-2x text-secondary-gradient flex-shrink-0"></i>
                                <div class="ms-3">
                                    <h2 class="mb-0 text-secondary-gradient" data-toggle="counter-up">{{ $settings['stat_peserta_count'] ?? 3151 }}</h2>
                                    <p class="mb-0 text-muted small fw-medium">{{ $settings['stat_peserta_label'] ?? 'Peserta Bebras Challenge 2024 (Biro IT Del)' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 text-center">
                    <img class="img-fluid wow fadeInUp rounded shadow-sm mb-4" data-wow-delay="0.3s" src="{{ asset('img/Bebras-Indonesia-Book-2020-SD-OK-Okt2024_Page26.bmp') }}" alt="Materi Bebras Halaman 26">
                    <img class="img-fluid wow fadeInUp rounded shadow-sm" data-wow-delay="0.5s" src="{{ asset('img/Bebras-Indonesia-Book-2020-SD-OK-Okt2024_Page27.bmp') }}" alt="Materi Bebras Halaman 27">
                </div>
            </div>
        </div>
    </div>
    <!-- About Us End -->

    <!-- Programs / Activities Start -->
    <div class="container-xxl page-section--lg page-section--tint" id="programs">
        <div class="container px-lg-5">
            <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                <span class="eyebrow">Kegiatan</span>
                <h2 class="display-title">Kegiatan Bebras Biro IT Del</h2>
                <span class="rainbow-rule mb-5" aria-hidden="true"></span>
            </div>
            <div class="row g-4">
                <div class="col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="feature-item feature-item--sun surface p-4 h-100">
                        <div class="icon-bubble icon-bubble--sun mb-4">
                            <i class="fa fa-chalkboard-teacher text-white fs-4"></i>
                        </div>
                        <h4 class="mb-3">{{ $settings['activity_workshop_title'] ?? 'Lokakarya' }}</h4>
                        <p class="m-0 text-muted">{{ $settings['activity_workshop_desc'] ?? 'Memperkenalkan konsep komputasi atau informatika dan tantangan Bebras kepada guru dan siswa.' }}</p>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="feature-item feature-item--mint surface p-4 h-100">
                        <div class="icon-bubble icon-bubble--mint mb-4">
                            <i class="fa fa-laptop-code text-white fs-4"></i>
                        </div>
                        <h4 class="mb-3">{{ $settings['activity_challenge_title'] ?? 'Tantangan Bebras' }}</h4>
                        <p class="m-0 text-muted">{{ $settings['activity_challenge_desc'] ?? 'Diselenggarakan sesuai jadwal yang ditetapkan komite internasional, biasanya minggu kedua atau ketiga November (disebut Bebras Week).' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Programs End -->

    <!-- Bebras Challenge Categories Start -->
    <div class="container-xxl page-section--lg" id="bebraschallenge">
        <div class="container px-lg-5">
            <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                <span class="eyebrow">Bebras Challenge</span>
                <h2 class="display-title">Kategori Tantangan</h2>
                <span class="rainbow-rule mb-5" aria-hidden="true"></span>
            </div>
            <div class="row g-4">
                <!-- SiKecil -->
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="surface cat-card cat-sikecil h-100 overflow-hidden text-center">
                        <div class="border-bottom p-4">
                            <h4 class="mb-1">SiKecil</h4>
                            <span class="text-muted small">SD/MI (Setara kelas 1 - 3)</span>
                            <div class="mt-3">
                                <img class="img-fluid rounded" src="{{ asset('img/sikecil.jpg') }}" alt="Kategori SiKecil">
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="d-flex justify-content-between mb-3"><span>Durasi: 30 menit</span><i class="fa fa-clock text-primary-gradient"></i></div>
                            <div class="d-flex justify-content-between"><span>Jumlah: 8 soal</span><i class="fa fa-tasks text-primary-gradient"></i></div>
                        </div>
                    </div>
                </div>

                <!-- Siaga -->
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.2s">
                    <div class="surface cat-card cat-siaga h-100 overflow-hidden text-center">
                        <div class="border-bottom p-4">
                            <h4 class="mb-1">Siaga</h4>
                            <span class="text-muted small">SD/MI (Setara kelas 4 – 6)</span>
                            <div class="mt-3">
                                <img class="img-fluid rounded" src="{{ asset('img/siaga.jpg') }}" alt="Kategori Siaga">
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="d-flex justify-content-between mb-3"><span>Durasi: 40 menit</span><i class="fa fa-clock text-primary-gradient"></i></div>
                            <div class="d-flex justify-content-between"><span>Jumlah: 12 soal</span><i class="fa fa-tasks text-primary-gradient"></i></div>
                        </div>
                    </div>
                </div>

                <!-- Penggalang -->
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="surface cat-card cat-penggalang h-100 overflow-hidden text-center">
                        <div class="border-bottom p-4">
                            <h4 class="mb-1">Penggalang</h4>
                            <span class="text-muted small">SMP/MTs (Setara kelas 7 – 9)</span>
                            <div class="mt-3">
                                <img class="img-fluid rounded" src="{{ asset('img/penggalang.jpg') }}" alt="Kategori Penggalang">
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="d-flex justify-content-between mb-3"><span>Durasi: 45 menit</span><i class="fa fa-clock text-primary-gradient"></i></div>
                            <div class="d-flex justify-content-between"><span>Jumlah: 15 soal</span><i class="fa fa-tasks text-primary-gradient"></i></div>
                        </div>
                    </div>
                </div>

                <!-- Penegak -->
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.4s">
                    <div class="surface cat-card cat-penegak h-100 overflow-hidden text-center">
                        <div class="border-bottom p-4">
                            <h4 class="mb-1">Penegak</h4>
                            <span class="text-muted small">SMA/SMK/MA (Setara kelas 10 – 12)</span>
                            <div class="mt-3">
                                <img class="img-fluid rounded" src="{{ asset('img/penegak.jpg') }}" alt="Kategori Penegak">
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="d-flex justify-content-between mb-3"><span>Durasi: 45 menit</span><i class="fa fa-clock text-primary-gradient"></i></div>
                            <div class="d-flex justify-content-between"><span>Jumlah: 15 soal</span><i class="fa fa-tasks text-primary-gradient"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bebras Challenge Categories End -->

    <!-- Latest News Start -->
    <div class="container-xxl page-section--lg page-section--sky" id="news">
        <div class="container px-lg-5">
            <div class="d-flex flex-wrap justify-content-between align-items-end mb-5 wow fadeInUp" data-wow-delay="0.1s">
                <div>
                    <span class="eyebrow">Warta & Kabar</span>
                    <h2 class="display-title mb-0">Berita Terbaru</h2>
                </div>
                <div>
                    <a href="{{ route('news.index') }}" class="btn btn-outline-primary rounded-pill px-4 py-2 mt-3 mt-md-0">
                        Lihat Semua Berita <i class="fa fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            @if($latestNews->isEmpty())
                <x-empty-state icon="fa-newspaper" title="Belum ada berita">Belum ada berita yang dipublikasikan saat ini.</x-empty-state>
            @else
                <div class="row g-4">
                    @foreach($latestNews as $item)
                        <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="{{ 0.1 * ($loop->index + 1) }}s">
                            <div class="news-card surface surface--hover">
                                @if($item->image_path)
                                    <img src="{{ Storage::disk('public')->url($item->image_path) }}" alt="{{ $item->title }}">
                                @else
                                    <div class="d-flex align-items-center justify-content-center bg-primary-gradient text-white" style="height: 180px;">
                                        <i class="fa fa-newspaper fa-4x opacity-50"></i>
                                    </div>
                                @endif
                                <div class="news-card-body">
                                    <div class="news-date">
                                        <i class="fa fa-calendar-alt me-1"></i>
                                        {{ $item->published_at ? $item->published_at->translatedFormat('d F Y') : $item->created_at->translatedFormat('d F Y') }}
                                    </div>
                                    <h5 class="card-title mb-3">
                                        <a href="{{ route('news.show', $item->slug) }}" class="text-dark text-decoration-none">
                                            {{ $item->title }}
                                        </a>
                                    </h5>
                                    <p class="text-muted small mb-4 flex-grow-1">
                                        {{ $item->display_excerpt }}
                                    </p>
                                    <div>
                                        <a href="{{ route('news.show', $item->slug) }}" class="read-more">
                                            Baca Selengkapnya <i class="fa fa-chevron-right ms-1 small"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    <!-- Latest News End -->
@endsection
