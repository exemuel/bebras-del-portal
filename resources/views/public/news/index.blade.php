@extends('layouts.app')

@section('title', 'Berita & Pengumuman')
@section('meta_description', 'Kumpulan berita, pengumuman, dan informasi terkini dari Bebras Biro Institut Teknologi Del.')

@section('content')
    <x-page-header title="Berita & Pengumuman" :breadcrumbs="[['Berita', null]]" />

    <!-- News List Start -->
    <div class="container-xxl page-section">
        <div class="container px-lg-5">
            @if($newsList->isEmpty())
                <x-empty-state icon="fa-newspaper" title="Belum ada berita yang tersedia">
                    <p>Silakan kembali lagi nanti untuk melihat informasi terbaru.</p>
                    <a href="{{ route('home') }}" class="btn btn-primary-gradient rounded-pill px-4 mt-2">Kembali ke Beranda</a>
                </x-empty-state>
            @else
                <div class="row g-4">
                    @foreach($newsList as $item)
                        <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="{{ 0.1 * ($loop->index % 3 + 1) }}s">
                            <div class="news-card surface surface--hover">
                                @if($item->image_path)
                                    <img src="{{ Storage::disk('public')->url($item->image_path) }}" alt="{{ $item->title }}">
                                @else
                                    <div class="d-flex align-items-center justify-content-center bg-primary-gradient text-white" style="height: 190px;">
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

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-5">
                    {{ $newsList->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
    <!-- News List End -->
@endsection
