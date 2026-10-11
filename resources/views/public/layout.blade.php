<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Booking Lapangan Olahraga Online')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .navbar-brand img {
            max-height: 40px;
        }
        .hero-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            color: #ffffff;
            border-radius: 1.5rem;
        }
        .card-custom {
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }
        .slot-btn {
            border: 1.5px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.75rem 0.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            background-color: #ffffff;
        }
        .slot-btn:hover:not(.disabled) {
            border-color: #2563eb;
            background-color: #eff6ff;
            transform: translateY(-2px);
        }
        .slot-btn.active {
            border-color: #2563eb;
            background-color: #2563eb;
            color: #ffffff;
        }
        .slot-btn.disabled {
            background-color: #f1f5f9;
            color: #94a3b8;
            cursor: not-allowed;
            border-color: #e2e8f0;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    {{-- Public Navbar --}}
    <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold fs-4 text-dark" href="#">
                @if(isset($tenant) && $tenant->logo)
                    <img src="{{ minio_url($tenant->logo) }}" alt="{{ $tenant->name }}" class="rounded">
                @else
                    <span class="p-2 bg-primary text-white rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="ti ti-trophy fs-5"></i>
                    </span>
                @endif
                <span>{{ $tenant->name ?? 'Sport Arena' }}</span>
            </a>

            <div class="ms-auto d-flex align-items-center gap-3">
                @if(isset($tenant) && $tenant->phone)
                    <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^\d]/', '', $tenant->phone)) }}"
                       target="_blank" class="btn btn-outline-success btn-sm rounded-pill d-none d-sm-inline-flex align-items-center gap-1">
                        <i class="ti ti-brand-whatsapp fs-5"></i> Hubungi Pengelola
                    </a>
                @endif
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main class="flex-grow-1 py-4">
        <div class="container">
            @yield('content')
        </div>
    </main>

    {{-- Footer --}}
    <footer class="bg-white border-top py-4 mt-auto">
        <div class="container text-center text-muted small">
            <p class="mb-1">&copy; {{ date('Y') }} {{ $tenant->name ?? 'SaaS Sport Booking' }}. All rights reserved.</p>
            <p class="mb-0 text-secondary">Powered by Modern Multi-Tenant Booking Engine.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
