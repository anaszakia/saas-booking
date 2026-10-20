<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Booking Lapangan Olahraga Online')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tabler Icons (Both CDN & CSS Theme fallback for reliable icon rendering) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/3.28.1/tabler-icons.min.css" integrity="sha512-H/rYvF16o3kK8k2N3e6g5x46R/s7c7J3qY3rI3zV2u3hGz8T5s8V9kY3jX3bQ4z2=" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        :root {
            --font-main: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            --font-heading: 'Outfit', sans-serif;
            --color-primary: #0284c7;
            --color-primary-hover: #0369a1;
            --color-primary-subtle: #e0f2fe;
            --color-dark: #0f172a;
            --color-slate: #1e293b;
            --color-muted: #64748b;
            --color-border: #e2e8f0;
            --color-bg: #f8fafc;
            --card-radius: 1rem;
            --btn-radius: 0.65rem;
            --shadow-subtle: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05);
            --shadow-card: 0 10px 15px -3px rgba(15, 23, 42, 0.03), 0 4px 6px -4px rgba(15, 23, 42, 0.02);
            --shadow-elevated: 0 20px 25px -5px rgba(15, 23, 42, 0.06), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
        }

        body {
            font-family: var(--font-main);
            background-color: var(--color-bg);
            color: var(--color-slate);
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
        }

        h1, h2, h3, h4, h5, h6, .brand-font {
            font-family: var(--font-heading);
            color: var(--color-dark);
            letter-spacing: -0.02em;
        }

        /* Navbar Clean Modern */
        .portal-navbar {
            background-color: #ffffff;
            border-bottom: 1px solid var(--color-border);
            padding: 0.85rem 0;
            position: sticky;
            top: 0;
            z-index: 1020;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        /* Hero Header Minimalist Dark Navy */
        .portal-hero {
            background: linear-gradient(135deg, #091220 0%, #0f1f38 60%, #17325e 100%);
            border-radius: 1.25rem;
            color: #ffffff;
            padding: 2.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 12px 28px -10px rgba(15, 23, 42, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.08);
            position: relative;
            overflow: hidden;
        }

        .portal-hero .hero-badge {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.35rem 0.85rem;
            border-radius: 2rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .portal-hero .hero-timezone {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 0.85rem;
            padding: 0.75rem 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* Card Section Styling */
        .section-card {
            background: #ffffff;
            border: 1px solid var(--color-border);
            border-radius: var(--card-radius);
            box-shadow: var(--shadow-card);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .step-number {
            width: 28px;
            height: 28px;
            background: #0284c7;
            color: #ffffff;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 700;
        }

        /* Date Pills Slider */
        .date-slider {
            display: flex;
            gap: 0.6rem;
            overflow-x: auto;
            padding: 0.25rem 0.1rem 0.75rem 0.1rem;
            scrollbar-width: thin;
        }
        .date-slider::-webkit-scrollbar {
            height: 4px;
        }
        .date-slider::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .date-card {
            flex: 0 0 auto;
            width: 78px;
            padding: 0.75rem 0.4rem;
            text-align: center;
            background: #ffffff;
            border: 1.5px solid var(--color-border);
            border-radius: 0.85rem;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            user-select: none;
        }
        .date-card:hover {
            border-color: #38bdf8;
            background-color: #f0f9ff;
            transform: translateY(-2px);
        }
        .date-card.active {
            border-color: #0284c7;
            background-color: #0284c7;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px -3px rgba(2, 132, 199, 0.4);
        }
        .date-card.active .date-day, 
        .date-card.active .date-num, 
        .date-card.active .date-month {
            color: #ffffff !important;
        }

        /* Court / Field Selector Cards */
        .field-card {
            border: 1.5px solid var(--color-border);
            border-radius: 0.85rem;
            padding: 1rem;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            position: relative;
            user-select: none;
        }
        .field-card:hover {
            border-color: #0284c7;
            background-color: #f8fafc;
            transform: translateY(-2px);
        }
        .field-card.selected {
            border-color: #0284c7;
            background-color: #f0f9ff;
            box-shadow: 0 0 0 2px #0284c7;
        }

        /* Time Slot Matrix */
        .time-slot {
            border: 1.5px solid var(--color-border);
            border-radius: 0.65rem;
            padding: 0.75rem 0.35rem;
            text-align: center;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            user-select: none;
        }
        .time-slot:hover:not(.disabled) {
            border-color: #0284c7;
            background-color: #f0f9ff;
            transform: translateY(-2px);
        }
        .time-slot.active {
            border-color: #0284c7;
            background-color: #0284c7;
            color: #ffffff !important;
            box-shadow: 0 6px 14px -3px rgba(2, 132, 199, 0.4);
            transform: translateY(-2px);
        }
        .time-slot.active .slot-text {
            color: #ffffff !important;
        }
        .time-slot.disabled {
            background-color: #f1f5f9;
            border-color: #e2e8f0;
            color: #94a3b8;
            cursor: not-allowed;
            opacity: 0.65;
        }

        /* Sticky Summary Card */
        .summary-card {
            background: #ffffff;
            border: 1px solid var(--color-border);
            border-radius: var(--card-radius);
            box-shadow: var(--shadow-card);
            padding: 1.5rem;
        }

        /* Primary Button Style */
        .btn-brand {
            background-color: #0284c7;
            border-color: #0284c7;
            color: #ffffff;
            font-weight: 600;
            border-radius: var(--btn-radius);
            padding: 0.75rem 1.25rem;
            transition: all 0.2s ease;
        }
        .btn-brand:hover:not(:disabled) {
            background-color: #0369a1;
            border-color: #0369a1;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 8px 18px -4px rgba(2, 132, 199, 0.4);
        }
        .btn-brand:disabled {
            background-color: #94a3b8;
            border-color: #94a3b8;
            color: #ffffff;
            opacity: 0.6;
        }

        /* Safe SVG icon styling in case webfont is delayed */
        .icon-inline {
            display: inline-block;
            vertical-align: middle;
            line-height: 1;
        }
    </style>
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

    {{-- Clean Modern Navbar --}}
    <header class="portal-navbar">
        <div class="container d-flex align-items-center justify-content-between">
            <a class="d-flex align-items-center gap-2 text-decoration-none" href="#">
                @if(isset($tenant) && $tenant->logo)
                    <img src="{{ minio_url($tenant->logo) }}" alt="{{ $tenant->name }}" style="height: 38px;" class="rounded">
                @else
                    <span class="rounded-3 d-inline-flex align-items-center justify-content-center text-white" 
                          style="width: 38px; height: 38px; background: #0284c7;">
                        <i class="ti ti-trophy fs-4"></i>
                    </span>
                @endif
                <div>
                    <span class="fw-bold fs-5 text-dark brand-font d-block lh-1">{{ $tenant->name ?? 'Sport Arena' }}</span>
                    <small class="text-muted" style="font-size: 11px;">Portal Booking Resmi</small>
                </div>
            </a>

            <div>
                @if(isset($tenant) && $tenant->phone)
                    <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^\d]/', '', $tenant->phone)) }}"
                       target="_blank" class="btn btn-outline-success btn-sm rounded-pill d-inline-flex align-items-center gap-1 px-3 py-1 fw-medium">
                        <i class="ti ti-brand-whatsapp fs-5"></i>
                        <span class="d-none d-sm-inline">Hubungi Pengelola</span>
                    </a>
                @endif
            </div>
        </div>
    </header>

    {{-- Main Content Container --}}
    <main class="flex-grow-1 py-4">
        <div class="container">
            @yield('content')
        </div>
    </main>

    {{-- Footer --}}
    <footer class="bg-white border-top py-4 mt-auto">
        <div class="container d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 text-muted small">
            <div>
                &copy; {{ date('Y') }} <strong>{{ $tenant->name ?? 'Venue Partner' }}</strong>. Didukung oleh Sistem SaaS Booking.
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-success"><i class="ti ti-shield-check me-1"></i>Slot Terenkripsi Real-Time</span>
                <span>•</span>
                <span><i class="ti ti-clock-check me-1"></i>Garansi 15 Menit</span>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS & SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        // Override native window.alert with SweetAlert2
        window.alert = function (message) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: message,
                confirmButtonColor: '#0284c7',
                confirmButtonText: 'Mengerti'
            });
        };
    </script>
    @stack('scripts')
</body>
</html>
