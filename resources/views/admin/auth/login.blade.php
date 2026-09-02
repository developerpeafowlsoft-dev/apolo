<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Language" content="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no" />
    <meta name="description" content="{{ $generaleSetting?->name ?? config('app.name', 'Apolo') }} Super Admin Login">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- App favicon -->
    <link rel="shortcut icon" type="image/png" href="{{ $generaleSetting?->favicon ?? asset('assets/favicon.png') }}" />

    <!-- App title -->
    <title>{{ $generaleSetting?->title ?? config('app.name', 'Apolo') }} - Super Admin Login</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <style>
        :root {
            --theme_color: {{ $generaleSetting?->primary_color ?? '#A82783' }};
            --theme_color_rgb: 168, 39, 131;
            --theme_hover: #8e1f6e;
            --theme_light: #fdf2f9;
            --theme_glow: rgba(168, 39, 131, 0.25);
            --bg_gradient_start: #f8fafc;
            --bg_gradient_end: #f1f5f9;
            --card_bg: rgba(255, 255, 255, 0.92);
            --card_border: rgba(255, 255, 255, 0.85);
            --text_main: #0f172a;
            --text_muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f8fafc;
            color: var(--text_main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            padding: 30px 15px;
        }

        /* Ambient Background Mesh & Glow Orbs */
        .ambient-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .ambient-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.45;
            animation: floatOrb 20s ease-in-out infinite alternate;
        }

        .orb-1 {
            width: 550px;
            height: 550px;
            background: radial-gradient(circle, var(--theme_color) 0%, rgba(238, 69, 107, 0.4) 100%);
            top: -15%;
            left: -10%;
        }

        .orb-2 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, #6366f1 0%, rgba(168, 85, 247, 0.3) 100%);
            bottom: -15%;
            right: -10%;
            animation-delay: -5s;
        }

        .orb-3 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, #38bdf8 0%, rgba(244, 114, 182, 0.2) 100%);
            top: 40%;
            left: 30%;
            animation-delay: -10s;
            opacity: 0.25;
        }

        .bg-grid-pattern {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-image: radial-gradient(rgba(100, 116, 139, 0.12) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
            z-index: 0;
        }

        @keyframes floatOrb {
            0% {
                transform: translate(0, 0) scale(1);
            }
            50% {
                transform: translate(40px, 30px) scale(1.08);
            }
            100% {
                transform: translate(-30px, 50px) scale(0.95);
            }
        }

        /* Main Container Layout */
        .auth-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1220px;
            margin: auto;
        }

        .auth-card-shell {
            background: rgba(255, 255, 255, 0.78);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.08), 0 0 0 1px rgba(255, 255, 255, 0.6);
            border-radius: 28px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        /* Left Hero Visual Branding Column */
        .hero-column {
            background: linear-gradient(145deg, rgba(248, 250, 252, 0.85) 0%, rgba(241, 245, 249, 0.7) 100%);
            border-right: 1px solid rgba(226, 232, 240, 0.7);
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(226, 232, 240, 0.8);
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text_main);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            width: fit-content;
            margin-bottom: 24px;
        }

        .hero-badge .badge-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
            animation: pulseDot 2s infinite;
        }

        @keyframes pulseDot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.7; }
        }

        .hero-title {
            font-size: 28px;
            font-weight: 800;
            line-height: 1.3;
            letter-spacing: -0.02em;
            color: #0f172a;
            margin-bottom: 12px;
        }

        .text-gradient {
            background: linear-gradient(135deg, var(--theme_color) 0%, #ee456b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-description {
            font-size: 14.5px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        /* Illustration Stage */
        .hero-illustration-box {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 10px;
            margin: auto 0;
        }

        .hero-illustration-box img {
            max-width: 100%;
            height: auto;
            max-height: 320px;
            filter: drop-shadow(0 15px 30px rgba(0, 0, 0, 0.07));
            transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .hero-illustration-box:hover img {
            transform: translateY(-4px) scale(1.02);
        }

        /* Floating Feature Tags */
        .floating-feature-tag {
            position: absolute;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            padding: 8px 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 700;
            color: #1e293b;
            animation: floatTag 6s ease-in-out infinite alternate;
        }

        .tag-1 {
            top: 15%;
            left: -5px;
        }

        .tag-2 {
            bottom: 18%;
            right: 0px;
            animation-delay: -3s;
        }

        @keyframes floatTag {
            0% { transform: translateY(0px); }
            100% { transform: translateY(-8px); }
        }

        /* Hero Feature List */
        .hero-features {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-top: 24px;
            flex-wrap: wrap;
        }

        .feature-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            background: rgba(255, 255, 255, 0.8);
            padding: 6px 12px;
            border-radius: 8px;
            border: 1px solid rgba(226, 232, 240, 0.6);
        }

        .feature-chip i {
            color: var(--theme_color);
            font-size: 13px;
        }

        /* Right Form Column */
        .form-column {
            padding: 48px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        /* Brand Logo Area */
        .auth-brand-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .auth-logo-wrap {
            display: inline-block;
            margin-bottom: 16px;
            transition: transform 0.3s ease;
        }

        .auth-logo-wrap:hover {
            transform: scale(1.03);
        }

        .auth-logo-wrap img {
            max-height: 68px;
            max-width: 220px;
            object-fit: contain;
        }

        .auth-headline {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }

        .auth-subheadline {
            font-size: 14px;
            color: #64748b;
            font-weight: 400;
        }

        /* Form Inputs */
        .form-group-modern {
            margin-bottom: 20px;
        }

        .form-label-modern {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13.5px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        .input-wrapper-modern {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-left {
            position: absolute;
            left: 16px;
            font-size: 16px;
            color: #94a3b8;
            pointer-events: none;
            transition: color 0.25s ease;
            z-index: 2;
        }

        .form-control-modern {
            width: 100%;
            height: 50px;
            padding: 12px 16px 12px 46px !important;
            font-size: 14.5px;
            font-weight: 500;
            color: #0f172a;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0 !important;
            border-radius: 12px !important;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-control-modern:hover {
            border-color: #cbd5e1 !important;
            background: #ffffff;
        }

        .form-control-modern:focus {
            background: #ffffff;
            border-color: var(--theme_color) !important;
            box-shadow: 0 0 0 4px var(--theme_glow) !important;
            outline: none;
        }

        .form-control-modern:focus + .input-icon-left,
        .input-wrapper-modern:focus-within .input-icon-left {
            color: var(--theme_color);
        }

        .input-wrapper-modern .btn-eye-toggle {
            position: absolute;
            right: 14px;
            background: transparent;
            border: none;
            padding: 6px;
            font-size: 16px;
            color: #94a3b8;
            cursor: pointer;
            border-radius: 6px;
            transition: color 0.2s ease, transform 0.2s ease;
            z-index: 2;
        }

        .input-wrapper-modern .btn-eye-toggle:hover {
            color: var(--theme_color);
            transform: scale(1.1);
        }

        .error-feedback-modern {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
            font-weight: 500;
            color: #ef4444;
            margin-top: 6px;
        }

        /* Checkbox & Extras */
        .form-options-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 13px;
        }

        .custom-checkbox-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
            color: #475569;
            font-weight: 500;
        }

        .custom-checkbox-wrap input {
            cursor: pointer;
            accent-color: var(--theme_color);
            width: 16px;
            height: 16px;
        }

        .ssl-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            color: #10b981;
            font-weight: 600;
        }

        /* Modern Submit Button */
        .btn-submit-modern {
            width: 100%;
            height: 52px;
            background: linear-gradient(135deg, var(--theme_color) 0%, var(--theme_hover) 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 15.5px;
            font-weight: 700;
            letter-spacing: 0.01em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            box-shadow: 0 8px 20px -4px var(--theme_glow), 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-submit-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }

        .btn-submit-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px -4px var(--theme_glow), 0 4px 8px rgba(0, 0, 0, 0.08);
            color: #ffffff;
        }

        .btn-submit-modern:hover::before {
            left: 100%;
        }

        .btn-submit-modern:active {
            transform: translateY(0);
        }

        /* Demo Credentials Accordion (Local Dev) */
        .demo-credentials-card {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
            padding: 16px;
            margin-top: 24px;
        }

        .demo-card-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12.5px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
        }

        .demo-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .demo-pill {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .demo-pill:hover {
            border-color: var(--theme_color);
            background: var(--theme_light);
        }

        .demo-info {
            font-size: 11.5px;
            line-height: 1.35;
        }

        .demo-role {
            font-weight: 700;
            color: var(--text_main);
        }

        .demo-sub {
            color: #64748b;
        }

        .demo-copy-btn {
            background: rgba(168, 39, 131, 0.1);
            color: var(--theme_color);
            border: none;
            border-radius: 6px;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .demo-copy-btn:hover {
            background: var(--theme_color);
            color: #ffffff;
            transform: scale(1.05);
        }

        /* Footer & Powered By */
        .auth-footer {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: #94a3b8;
            flex-wrap: wrap;
            gap: 10px;
        }

        .auth-footer a {
            color: var(--theme_color);
            text-decoration: none;
            font-weight: 600;
        }

        .version-badge {
            background: #f1f5f9;
            color: #475569;
            padding: 3px 8px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 11px;
        }

        /* Responsive Breakpoints */
        @media (max-width: 991px) {
            .hero-column {
                display: none;
            }

            .form-column {
                padding: 40px 28px;
            }

            .auth-card-shell {
                max-width: 520px;
                margin: auto;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 15px 10px;
            }

            .form-column {
                padding: 32px 20px;
            }

            .demo-grid {
                grid-template-columns: 1fr;
            }

            .auth-headline {
                font-size: 21px;
            }
        }
    </style>
</head>

<body>

    <!-- Ambient Glowing Background Elements -->
    <div class="ambient-bg">
        <div class="ambient-orb orb-1"></div>
        <div class="ambient-orb orb-2"></div>
        <div class="ambient-orb orb-3"></div>
    </div>
    <div class="bg-grid-pattern"></div>

    <!-- Main Authentication Container -->
    <main class="auth-wrapper">
        <div class="auth-card-shell">
            <div class="row g-0">

                <!-- Left Column: Hero & Visual Branding Showcase -->
                <div class="col-lg-6 hero-column">
                    <div>
                        <div class="hero-badge">
                            <span class="badge-dot"></span>
                            <span>Super Admin Portal</span>
                        </div>

                        <h1 class="hero-title">
                            Manage Your Enterprise With
                            <span class="text-gradient">{{ $generaleSetting?->name ?? config('app.name', 'Apolo') }}</span>
                        </h1>

                        <p class="hero-description">
                            Unified eCommerce, multi-channel retail inventory, and lightning-fast POS management from a single secure workspace.
                        </p>
                    </div>

                    <!-- Centered Illustration Stage -->
                    <div class="hero-illustration-box">
                        <div class="floating-feature-tag tag-1">
                            <i class="bi bi-graph-up-arrow text-success"></i>
                            <span>Live POS & Sales Sync</span>
                        </div>

                        <img src="{{ asset('assets/images/login.svg') }}" alt="Retail Management Illustration" loading="lazy" />

                        <div class="floating-feature-tag tag-2">
                            <i class="bi bi-shield-lock-fill" style="color: var(--theme_color);"></i>
                            <span>Enterprise Security</span>
                        </div>
                    </div>

                    <!-- Bottom Feature Badges -->
                    <div class="hero-features">
                        <div class="feature-chip">
                            <i class="bi bi-check2-circle"></i>
                            <span>Real-Time Inventory</span>
                        </div>
                        <div class="feature-chip">
                            <i class="bi bi-lightning-charge-fill"></i>
                            <span>High-Speed Invoicing</span>
                        </div>
                        <div class="feature-chip">
                            <i class="bi bi-box-seam-fill"></i>
                            <span>Master Ledger ERP</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Login Form -->
                <div class="col-lg-6 form-column">

                    <!-- Brand Logo & Header -->
                    <div class="auth-brand-header">
                        <div class="auth-logo-wrap">
                            <img src="{{ $generaleSetting?->logo ?? asset('assets/logo.png') }}" alt="{{ $generaleSetting?->name ?? 'Apolo' }} Logo" loading="lazy" />
                        </div>
                        <h2 class="auth-headline">Login To Admin</h2>
                        <p class="auth-subheadline">
                            Welcome back to <span class="fw-bold" style="color: var(--theme_color);">{{ $generaleSetting?->name ?? config('app.name', 'Apolo') }}</span>
                        </p>
                    </div>

                    <!-- Login Form -->
                    <form action="{{ route('admin.login.submit') }}" method="POST" autocomplete="on">
                        @csrf

                        <!-- Email Address Input -->
                        <div class="form-group-modern">
                            <label for="email" class="form-label-modern">
                                <span>Email Address</span>
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-wrapper-modern">
                                <i class="bi bi-envelope input-icon-left"></i>
                                <input type="email"
                                       name="email"
                                       id="email"
                                       value="{{ old('email') }}"
                                       class="form-control-modern @error('email') is-invalid @enderror"
                                       placeholder="admin@apolo.com"
                                       required
                                       autofocus>
                            </div>
                            @error('email')
                                <div class="error-feedback-modern" role="alert">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>

                        <!-- Password Input -->
                        <div class="form-group-modern">
                            <label for="password" class="form-label-modern">
                                <span>Password</span>
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-wrapper-modern">
                                <i class="bi bi-lock input-icon-left"></i>
                                <input type="password"
                                       name="password"
                                       id="password"
                                       class="form-control-modern @error('password') is-invalid @enderror"
                                       placeholder="••••••••••••"
                                       required>
                                <button type="button" class="btn-eye-toggle" onclick="showHidePassword()" title="Toggle password visibility" tabindex="-1">
                                    <i class="fa fa-eye-slash" id="togglePassword"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="error-feedback-modern" role="alert">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>

                        <!-- Options Row: Remember Me & Security Status -->
                        <div class="form-options-row">
                            <label class="custom-checkbox-wrap">
                                <input type="checkbox" name="remember" id="remember" checked>
                                <span>Keep me logged in</span>
                            </label>

                            <span class="ssl-badge">
                                <i class="bi bi-shield-fill-check"></i>
                                <span>Encrypted Session</span>
                            </span>
                        </div>

                        <!-- Google ReCaptcha (if active) -->
                        @if ($GoogleReCaptcha?->is_active)
                            <div class="mb-3 d-flex justify-content-center">
                                <div class="g-recaptcha" data-sitekey="{{ $GoogleReCaptcha?->site_key }}"></div>
                            </div>
                            @error('g-recaptcha-response')
                                <div class="error-feedback-modern mb-3" role="alert">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        @endif

                        <!-- Submit Button -->
                        <button type="submit" class="btn-submit-modern" id="loginSubmitBtn">
                            <span>Sign In to Dashboard</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>

                        <!-- Local Development Quick Credentials -->
                        @if (app()->isLocal())
                            <div class="demo-credentials-card">
                                <div class="demo-card-title">
                                    <span><i class="bi bi-key-fill me-1"></i> Quick Demo Access</span>
                                    <span class="badge bg-secondary-subtle text-secondary" style="font-size: 10px;">DEV</span>
                                </div>
                                <div class="demo-grid">
                                    <div class="demo-pill">
                                        <div class="demo-info">
                                            <div class="demo-role">Super Admin</div>
                                            <div class="demo-sub font-monospace" style="font-size: 11px;">root@readyecommerce.com</div>
                                        </div>
                                        <button type="button" class="demo-copy-btn" onclick="loginAdmin()" title="Auto-fill Admin">
                                            <i class="fa-regular fa-copy"></i>
                                        </button>
                                    </div>

                                    <div class="demo-pill">
                                        <div class="demo-info">
                                            <div class="demo-role">Shop Portal</div>
                                            <div class="demo-sub font-monospace" style="font-size: 11px;">shop@readyecommerce.com</div>
                                        </div>
                                        <button type="button" class="demo-copy-btn" onclick="gotoShopLogin()" title="Open Shop Login">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </form>

                    <!-- Footer Details -->
                    <div class="auth-footer">
                        <span>
                            Powered by <a href="https://peafowlweb.com" target="_blank" rel="noopener" style="color: #10b981; font-weight: 700;">Peafowlweb AI</a> &copy; {{ date('Y') }}
                        </span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="version-badge">v{{ config('app.version', '1.0') }}</span>
                            <span class="badge bg-success-subtle text-success" style="font-size: 11px; border-radius: 6px;">
                                <i class="bi bi-circle-fill me-1" style="font-size: 7px;"></i>System Ready
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- SweetAlert2 Scripts -->
    <script src="{{ asset('assets/scripts/sweetalert2.min.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var themeColor = "{{ $generaleSetting?->primary_color ?? '#A82783' }}";
            document.documentElement.style.setProperty('--theme_color', themeColor);
        });

        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        function showHidePassword() {
            const toggle = document.getElementById("togglePassword");
            const password = document.getElementById("password");

            // toggle the type attribute
            const type = password.getAttribute("type") === "password" ? "text" : "password";
            password.setAttribute("type", type);

            // toggle icon
            if (type === "text") {
                toggle.classList.remove("fa-eye-slash");
                toggle.classList.add("fa-eye");
            } else {
                toggle.classList.remove("fa-eye");
                toggle.classList.add("fa-eye-slash");
            }
        }

        var email = document.getElementById("email");
        var password = document.getElementById("password");

        function loginAdmin() {
            email.value = 'root@readyecommerce.com';
            password.value = 'secret';

            if (!sessionStorage.getItem('fromShop')) {
                Toast.fire({
                    icon: 'success',
                    title: 'Admin Credentials Auto-Filled'
                });
            }
        }

        const checkSession = () => {
            if (sessionStorage.getItem('fromShop')) {
                loginAdmin();
                sessionStorage.removeItem('fromShop');
            }
        }

        const gotoShopLogin = () => {
            sessionStorage.setItem('fromAdmin', true);
            window.open("{{ route('shop.login') }}", '_blank');
        }

        checkSession();
    </script>
</body>

</html>
