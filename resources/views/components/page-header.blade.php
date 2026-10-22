@props(['title', 'subtitle' => null, 'breadcrumbs' => []])

<div class="page-header text-center text-white">
    <div class="container py-4">
        <h1 class="text-white animated slideInDown mb-0">{{ $title }}</h1>
        <span class="rainbow-rule" aria-hidden="true"></span>
        @if($subtitle)
            <p class="text-white-50 fs-5 mb-0 page-header-subtitle">{{ $subtitle }}</p>
        @endif
        @if(count($breadcrumbs))
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center mb-0">
                    <li class="breadcrumb-item"><a class="text-white-50" href="{{ route('home') }}">Beranda</a></li>
                    @foreach($breadcrumbs as [$label, $url])
                        @if($url)
                            <li class="breadcrumb-item"><a class="text-white-50" href="{{ $url }}">{{ $label }}</a></li>
                        @else
                            <li class="breadcrumb-item text-white active" aria-current="page">{{ $label }}</li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif
    </div>
    <img class="page-header-mascot" src="{{ asset('img/maskot-bebras-ulos.png') }}" alt="" aria-hidden="true">
</div>
