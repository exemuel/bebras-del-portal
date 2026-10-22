<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Bebras Biro Institut Teknologi Del') - Tantangan Berpikir Komputasional</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Bebras, Institut Teknologi Del, IT Del, kompetisi Bebras, computational thinking, berpikir komputasional, sertifikat bebras" name="keywords">
    <meta content="@yield('meta_description', 'Portal resmi Bebras Biro Institut Teknologi Del untuk pengumuman kegiatan, berita, dan pencarian sertifikat peserta Tantangan Bebras.')" name="description">

    <!-- Favicon -->
    <link href="{{ asset('img/favicon.ico') }}" rel="icon">

    <!-- Theme tokens + fonts (shared with the admin panel) -->
    @include('partials.theme-head')

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    {{-- Bootstrap Icons removed: back-to-top now uses Font Awesome (fa-chevron-up) --}}

    <!-- Libraries Stylesheet -->
    <link href="{{ asset('lib/animate/animate.min.css') }}" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    @stack('meta')
    @stack('styles')
</head>

<body @if(request()->routeIs('home')) data-bs-spy="scroll" data-bs-target="#navbarCollapse" data-bs-offset="51" @endif>
    <div class="container-xxl bg-white p-0">
        <!-- Spinner Start -->
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-grow text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Memuat...</span>
            </div>
        </div>
        <!-- Spinner End -->

        <!-- Navbar Start -->
        <div class="container-xxl position-relative p-0" id="top-nav">
            <nav class="navbar navbar-expand-lg navbar-light px-4 px-lg-5 py-3 py-lg-0 sticky-top">
                <a href="{{ route('home') }}" class="navbar-brand p-0">
                    <div class="d-flex align-items-center">
                        <img src="{{ asset('img/itdel.png') }}" alt="Logo IT Del" class="me-2" style="max-height: 48px;">
                        <span class="navbar-brand-text fw-bold">Bebras Biro IT Del</span>
                    </div>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                    <span class="fa fa-bars"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <div class="navbar-nav mx-auto py-0">
                        <a href="{{ route('home') }}" class="nav-item nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a>
                        @php $homeBase = request()->routeIs('home') ? '' : route('home'); @endphp
                        <a href="{{ $homeBase }}#aboutus" class="nav-item nav-link">Tentang Kami</a>
                        <a href="{{ $homeBase }}#programs" class="nav-item nav-link">Kegiatan</a>
                        <a href="{{ $homeBase }}#bebraschallenge" class="nav-item nav-link">Tantangan</a>
                        <a href="{{ route('news.index') }}" class="nav-item nav-link {{ request()->routeIs('news.*') ? 'active' : '' }}">Berita</a>
                        <a href="{{ route('certificates.search') }}" class="nav-item nav-link {{ request()->routeIs('certificates.*') ? 'active' : '' }}">Cari Sertifikat</a>
                    </div>
                    <a href="{{ route('certificates.search') }}" class="btn btn-secondary-gradient rounded-pill py-2 px-4 ms-3 d-none d-lg-block">
                        <i class="fa fa-award me-1"></i> Unduh Sertifikat
                    </a>
                </div>
            </nav>
        </div>
        <!-- Navbar End -->

        <!-- Main Content -->
        <main>
            @yield('content')
        </main>

        <!-- Footer Start -->
        <div class="container-fluid bg-primary text-light footer wow fadeIn" data-wow-delay="0.1s">
            <div class="container py-5 px-lg-5">
                <div class="row g-5">
                    <div class="col-md-6 col-lg-5">
                        <h4 class="text-white mb-4">Bebras Biro IT Del</h4>
                        <p><i class="fa fa-map-marker-alt me-3"></i>{{ $globalSettings['contact_address'] ?? 'Institut Teknologi Del, Jl. Sisingamangaraja, Laguboti, Toba, Sumatera Utara' }}</p>
                        <p><i class="fa fa-phone-alt me-3"></i>{{ $globalSettings['contact_phone'] ?? '+62 632 331234' }}</p>
                        <p><i class="fa fa-envelope me-3"></i>{{ $globalSettings['contact_email'] ?? 'del@bebras.ac.id' }}</p>
                        <div class="d-flex pt-2">
                            @if(!empty($globalSettings['social_facebook']))
                            <a class="btn btn-outline-light btn-social" href="{{ $globalSettings['social_facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            @endif
                            @if(!empty($globalSettings['social_instagram']))
                            <a class="btn btn-outline-light btn-social" href="{{ $globalSettings['social_instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            @endif
                            @if(!empty($globalSettings['social_youtube']))
                            <a class="btn btn-outline-light btn-social" href="{{ $globalSettings['social_youtube'] }}" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <h4 class="text-white mb-4">Tautan Cepat</h4>
                        <a class="btn btn-link" href="https://www.del.ac.id/" target="_blank" rel="noopener">Institut Teknologi Del</a>
                        <a class="btn btn-link" href="https://bebras.or.id/" target="_blank" rel="noopener">Bebras Indonesia</a>
                        <a class="btn btn-link" href="https://www.bebras.org/" target="_blank" rel="noopener">Bebras Organization</a>
                        <a class="btn btn-link" href="{{ route('certificates.search') }}">Pencarian Sertifikat</a>
                        <a class="btn btn-link" href="{{ route('news.index') }}">Berita & Pengumuman</a>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <h4 class="text-white mb-4">Gerakan PANDAI</h4>
                        <p>Inisiatif pengenalan computational thinking untuk siswa dan pelatihan guru di kawasan Sumatera Utara oleh Biro Bebras IT Del.</p>
                        <div class="mt-3">
                            <a href="{{ route('certificates.search') }}" class="btn btn-outline-light rounded-pill py-2 px-4">
                                <i class="fa fa-search me-2"></i>Cari Sertifikat Saya
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container px-lg-5">
                <div class="copyright">
                    <div class="row">
                        <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                            &copy; {{ date('Y') }} <a class="border-bottom" href="{{ route('home') }}">bebras.del.ac.id</a>, Hak Cipta Dilindungi.
                        </div>
                        <div class="col-md-6 text-center text-md-end">
                            <span class="text-white-50 small">Bebras Indonesia &mdash; Biro IT Del</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Footer End -->

        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-lg-square back-to-top pt-2" aria-label="Kembali ke atas"><i class="fa fa-chevron-up text-white"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('lib/wow/wow.min.js') }}"></script>
    <script src="{{ asset('lib/easing/easing.min.js') }}"></script>
    <script src="{{ asset('lib/waypoints/waypoints.min.js') }}"></script>
    <script src="{{ asset('lib/counterup/counterup.min.js') }}"></script>

    <!-- Template Javascript -->
    <script src="{{ asset('js/main.js') }}"></script>
    @stack('scripts')
</body>

</html>
