@extends('layouts.app')

@section('title', 'Pencarian Sertifikat Bebras Challenge')
@section('meta_description', 'Layanan pencarian dan pengunduhan sertifikat resmi peserta dan pemenang Bebras Challenge Biro Institut Teknologi Del.')

@if(request()->filled('username'))
@push('meta')
<meta name="robots" content="noindex, follow">
@endpush
@endif

@section('content')
    <x-page-header title="Pencarian Sertifikat Bebras Challenge"
                   subtitle="Unduh sertifikat resmi Anda dengan memasukkan username akun Bebras Challenge"
                   :breadcrumbs="[['Cari Sertifikat', null]]" />

    <div class="container-xxl page-section">
        <div class="container px-lg-5">

            {{-- Search Form --}}
            <div class="cert-search-form-wrap mb-4">
                <div class="surface surface--muted p-3 p-sm-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary-gradient rounded-circle mb-3" style="width: 70px; height: 70px;">
                            <i class="fa fa-award text-white fs-2"></i>
                        </div>
                        <h2 class="h4 fw-bold mb-1">Cek Sertifikat Anda</h2>
                        <p class="text-muted small">Masukkan <strong>Bebras Username</strong> yang Anda gunakan saat pelaksanaan Tantangan Bebras.</p>
                    </div>

                    {{-- GET form — URL: /sertifikat?username=... (bookmarkable & shareable) --}}
                    <form action="{{ route('certificates.search') }}" method="GET" id="cert-search-form">
                        <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden bg-white border @error('username') border-danger @enderror">
                            <span class="input-group-text bg-white border-0 ps-3 ps-md-4 pe-2 text-muted">
                                <i class="fa fa-user"></i>
                            </span>
                            <input type="text"
                                   name="username"
                                   id="username"
                                   class="form-control border-0 py-3 shadow-none @error('username') is-invalid @enderror"
                                   placeholder="Contoh: smp_budi_123"
                                   value="{{ old('username', $username) }}"
                                   required
                                   autocomplete="username"
                                   autofocus>
                            <button type="submit" class="btn btn-primary-gradient px-3 px-md-5 fw-semi-bold" id="btn-search-cert">
                                <i class="fa fa-search me-1" id="search-icon"></i><span class="spinner-border spinner-border-sm me-1 d-none" id="search-spinner" role="status" aria-hidden="true"></span> Cari
                            </button>
                        </div>
                        @error('username')
                            <div class="text-danger small mt-2 ps-3">
                                <i class="fa fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror

                        {{-- Year filter — visible after first search --}}
                        @if($certificateYears->isNotEmpty())
                            <div class="d-flex align-items-center gap-2 mt-3 flex-wrap">
                                <label for="year-filter" class="text-muted small fw-semibold mb-0">
                                    <i class="fa fa-calendar-alt me-1"></i>Filter Tahun:
                                </label>
                                <select name="year" id="year-filter"
                                        class="form-select form-select-sm w-auto shadow-none rounded-pill border"
                                        onchange="if (this.form.username.value) this.form.submit()">
                                    <option value="" {{ !$filterYear ? 'selected' : '' }}>Semua Tahun</option>
                                    @foreach($certificateYears as $yr)
                                        <option value="{{ $yr }}" {{ $filterYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                                    @endforeach
                                </select>
                                @if($filterYear)
                                    <a href="{{ route('certificates.search', ['username' => $username]) }}"
                                       class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                        <i class="fa fa-times me-1"></i>Hapus Filter
                                    </a>
                                @endif
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Search Results --}}
            @if($searched)
                @if($certificatesByYear->isEmpty())
                    {{-- Empty State --}}
                    <div class="cert-search-form-wrap mb-5">
                        <x-empty-state icon="fa-exclamation-triangle" tone="warning" title="Sertifikat Tidak Ditemukan">
                            <p class="mb-1" style="line-height: 1.8;">
                                Sertifikat tidak ditemukan untuk username <strong>"{{ $username }}"</strong>@if($filterYear) pada tahun <strong>{{ $filterYear }}</strong>@endif.
                            </p>
                            <p class="small mb-4" style="line-height: 1.7;">
                                Pastikan username ditulis <strong>persis sama</strong> (huruf kecil, tanpa spasi) seperti saat mendaftar Bebras Challenge.
                                @if($certificateYears->isNotEmpty())
                                    <br>Data sertifikat tersedia untuk tahun: <strong>{{ $certificateYears->join(', ') }}</strong>.
                                @endif
                            </p>
                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                <a href="{{ route('certificates.search') }}" class="btn btn-outline-dark rounded-pill px-4">
                                    <i class="fa fa-redo me-1"></i> Cari Username Lain
                                </a>
                                <a href="mailto:{{ $globalSettings['contact_email'] ?? 'del@bebras.ac.id' }}"
                                   class="btn btn-outline-primary rounded-pill px-4">
                                    <i class="fa fa-envelope me-1"></i> Hubungi Kami
                                </a>
                            </div>
                        </x-empty-state>
                    </div>
                @else
                    {{-- Results found — full-width, grouped by year in accordion --}}
                    <div class="mb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <i class="fa fa-check-circle text-success me-2"></i>
                            <strong class="text-dark">{{ $totalCount }} Sertifikat</strong>
                            @if($yearCount > 1) dari <strong>{{ $yearCount }} tahun</strong> @endif
                            ditemukan untuk <span class="font-monospace text-primary fw-bold">{{ $username }}</span>
                            @if($filterYear)
                                <span class="badge bg-secondary rounded-pill ms-1 year-summary-badge">Filter: {{ $filterYear }}</span>
                            @endif
                        </div>
                        <a href="{{ route('certificates.search') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                            <i class="fa fa-search me-1"></i> Cari Username Lain
                        </a>
                    </div>

                    <div class="accordion cert-year-accordion surface overflow-hidden mb-5" id="certAccordion">
                        @foreach($certificatesByYear as $year => $certs)
                            @php $isFirst = $loop->first; $collapseId = "year-{$year}"; @endphp
                            <div class="accordion-item border-0 border-bottom">
                                <h3 class="accordion-header">
                                    <button class="accordion-button {{ $isFirst ? '' : 'collapsed' }} py-3 px-4"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#{{ $collapseId }}"
                                            aria-expanded="{{ $isFirst ? 'true' : 'false' }}"
                                            aria-controls="{{ $collapseId }}">
                                        <i class="fa fa-calendar-alt me-2"></i>
                                        Bebras Challenge {{ $year }}
                                        <span class="ms-2 badge bg-white text-primary rounded-pill year-summary-badge">
                                            {{ $certs->count() }} sertifikat
                                        </span>
                                    </button>
                                </h3>
                                <div id="{{ $collapseId }}"
                                     class="accordion-collapse collapse {{ $isFirst ? 'show' : '' }}"
                                     data-bs-parent="#certAccordion">
                                    <div class="accordion-body p-0">
                                        <div class="table-responsive d-none d-md-block">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="ps-4">Nama Peserta</th>
                                                        <th>Sekolah</th>
                                                        <th>Kategori</th>
                                                        <th>Penghargaan</th>
                                                        <th class="text-end pe-4">Unduh</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($certs as $cert)
                                                        <tr>
                                                            <td class="ps-4 py-3">
                                                                <span class="fw-bold text-dark d-block">{{ $cert->participant_name }}</span>
                                                                <small class="text-muted font-monospace">{{ $cert->bebras_username }}</small>
                                                            </td>
                                                            <td>{{ $cert->school_name ?? '–' }}</td>
                                                            <td>
                                                                <span class="badge cat-badge cat-{{ \App\Models\Certificate::categoryKey($cert->category) }} px-3 py-2 rounded-pill">
                                                                    {{ $cert->category }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @if($cert->award_type === 'Winner')
                                                                    <span class="badge badge-winner px-3 py-2 rounded-pill">
                                                                        <i class="fa fa-trophy me-1"></i> {{ \App\Models\Certificate::awardLabel($cert->award_type) }}
                                                                    </span>
                                                                @else
                                                                    <span class="badge badge-participant px-3 py-2 rounded-pill">
                                                                        <i class="fa fa-award me-1"></i> {{ \App\Models\Certificate::awardLabel($cert->award_type) }}
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td class="text-end pe-4">
                                                                <a href="{{ route('certificates.download', $cert->id) }}"
                                                                   rel="nofollow"
                                                                   class="btn btn-sm btn-success rounded-pill px-3 py-2 shadow-sm"
                                                                   title="{{ $cert->category }} {{ $cert->year }} — {{ $cert->award_type }}">
                                                                    <i class="fa fa-download me-1"></i> PDF
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-md-none">
                                            @foreach($certs as $cert)
                                                <div class="cert-card">
                                                    <span class="fw-bold text-dark d-block">{{ $cert->participant_name }}</span>
                                                    <small class="text-muted font-monospace d-block">{{ $cert->bebras_username }}</small>
                                                    <small class="text-muted d-block mb-2">{{ $cert->school_name ?? '-' }}</small>
                                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                                        <span class="badge cat-badge cat-{{ \App\Models\Certificate::categoryKey($cert->category) }} px-3 py-2 rounded-pill">{{ $cert->category }}</span>
                                                        @if($cert->award_type === 'Winner')
                                                            <span class="badge badge-winner px-3 py-2 rounded-pill"><i class="fa fa-trophy me-1"></i> {{ \App\Models\Certificate::awardLabel($cert->award_type) }}</span>
                                                        @else
                                                            <span class="badge badge-participant px-3 py-2 rounded-pill"><i class="fa fa-award me-1"></i> {{ \App\Models\Certificate::awardLabel($cert->award_type) }}</span>
                                                        @endif
                                                        <a href="{{ route('certificates.download', $cert->id) }}" rel="nofollow"
                                                           class="btn btn-sm btn-success rounded-pill px-3 ms-auto">
                                                            <i class="fa fa-download me-1"></i> PDF
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif

            {{-- Info Banners — only shown before first search --}}
            @if(!$searched)
                <div class="row g-4 mt-4">
                    <div class="col-md-4">
                        <div class="surface surface--muted p-4 h-100 text-center">
                            <i class="fa fa-shield-alt text-primary-gradient fa-2x mb-3"></i>
                            <h5>Resmi &amp; Terverifikasi</h5>
                            <p class="text-muted small m-0">Sertifikat diterbitkan secara resmi oleh Biro Bebras Institut Teknologi Del &amp; Bebras Indonesia.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="surface surface--muted p-4 h-100 text-center">
                            <i class="fa fa-file-pdf text-primary-gradient fa-2x mb-3"></i>
                            <h5>Format Digital PDF</h5>
                            <p class="text-muted small m-0">Unduhan berkas PDF beresolusi tinggi yang siap dicetak untuk kebutuhan portofolio siswa.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="surface surface--muted p-4 h-100 text-center">
                            <i class="fa fa-question-circle text-primary-gradient fa-2x mb-3"></i>
                            <h5>Butuh Bantuan?</h5>
                            <p class="text-muted small m-0">Jika username lupa atau sertifikat terkendala, hubungi koordinator melalui email
                                <strong>{{ $globalSettings['contact_email'] ?? 'del@bebras.ac.id' }}</strong>.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('cert-search-form').addEventListener('submit', function () {
        document.getElementById('search-icon').classList.add('d-none');
        document.getElementById('search-spinner').classList.remove('d-none');
    });
</script>
@endpush
