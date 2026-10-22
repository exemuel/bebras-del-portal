@extends('layouts.app')

@section('title', $news->title)
@section('meta_description', Str::limit(strip_tags($news->display_excerpt), 160))

@section('content')
    <x-page-header :title="$news->title" :breadcrumbs="[['Berita', route('news.index')], ['Detail', null]]" />

    <!-- Article Content Start -->
    <div class="container-xxl page-section">
        <div class="container px-lg-5">
            <div class="row g-5">
                <!-- Main Article Column -->
                <div class="col-lg-8">
                    <div class="surface p-4">
                        <div class="d-flex align-items-center text-muted mb-4 pb-3 border-bottom">
                            <span class="me-4"><i class="fa fa-calendar-alt text-primary me-2"></i>{{ $news->published_at ? $news->published_at->translatedFormat('d F Y') : $news->created_at->translatedFormat('d F Y') }}</span>
                            <span><i class="fa fa-user-edit text-primary me-2"></i>Biro Bebras IT Del</span>
                        </div>

                        @if($news->image_path)
                            <div class="mb-4 text-center">
                                <img src="{{ Storage::disk('public')->url($news->image_path) }}" alt="{{ $news->title }}" class="img-fluid rounded shadow-sm" style="max-height: 450px; width: 100%; object-fit: cover;">
                            </div>
                        @endif

                        <div class="article-body fs-6" style="line-height: 1.9; color: #2c3e50;">
                            {!! $news->content !!}
                        </div>

                        <hr class="my-5">

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('news.index') }}" class="btn btn-outline-primary rounded-pill px-4">
                                <i class="fa fa-arrow-left me-2"></i>Kembali ke Berita
                            </a>
                            <a href="{{ route('certificates.search') }}" class="btn btn-secondary-gradient rounded-pill px-4">
                                <i class="fa fa-award me-2"></i>Cari Sertifikat
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Column -->
                <div class="col-lg-4">
                    <div class="surface surface--muted p-4 mb-4">
                        <h5 class="mb-4 pb-2 border-bottom">Berita Terkait</h5>
                        @forelse($recentNews as $recent)
                            <div class="mb-3 pb-3 border-bottom">
                                <small class="text-muted d-block mb-1">
                                    <i class="fa fa-calendar-alt me-1"></i>
                                    {{ $recent->published_at ? $recent->published_at->translatedFormat('d F Y') : $recent->created_at->translatedFormat('d F Y') }}
                                </small>
                                <a href="{{ route('news.show', $recent->slug) }}" class="text-dark text-decoration-none fw-medium" style="font-size: 0.95rem;">
                                    {{ $recent->title }}
                                </a>
                            </div>
                        @empty
                            <p class="text-muted small m-0">Tidak ada berita lainnya.</p>
                        @endforelse
                    </div>

                    <div class="surface bg-primary-gradient text-white p-4 text-center">
                        <i class="fa fa-certificate fa-3x mb-3 opacity-75"></i>
                        <h5 class="text-white mb-2">Ikut Bebras Challenge?</h5>
                        <p class="small text-white-50 mb-3">Unduh sertifikat peserta dan pemenang Anda dengan mudah menggunakan Bebras Username.</p>
                        <a href="{{ route('certificates.search') }}" class="btn btn-light text-primary rounded-pill px-4 fw-semi-bold">
                            Unduh Sertifikat
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Article Content End -->
@endsection
