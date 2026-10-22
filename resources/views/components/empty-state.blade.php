@props(['icon' => 'fa-info-circle', 'title', 'tone' => 'muted'])

<div {{ $attributes->class(['surface surface--muted text-center p-4 p-md-5']) }}>
    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 {{ $tone === 'warning' ? 'bg-warning text-white' : 'bg-primary-gradient text-white' }}" style="width: 60px; height: 60px;">
        <i class="fa {{ $icon }} fs-3"></i>
    </div>
    <h4 class="fw-bold text-dark mb-2">{{ $title }}</h4>
    <div class="text-muted">{{ $slot }}</div>
</div>
