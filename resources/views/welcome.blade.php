<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>labtech.lk | Laboratory Information System</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css->family=outfit:300,400,500,600,700,800|dm-serif-display:400" rel="stylesheet" />

    <style>
        :root {
            --brand: #0b5a77;
            --brand-deep: #063647;
            --accent: #2ec4b6;
            --ink: #0f1e26;
            --muted: #5a6b76;
            --bg: #ffffff;
            --card: #ffffff;
            --line: #d7e1e8;
            --shadow: 0 18px 40px rgba(6, 22, 34, 0.08);
            --font: "Outfit", "Segoe UI", sans-serif;
            --display: "DM Serif Display", "Times New Roman", serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: var(--font);
            color: var(--ink);
            background: var(--bg);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .topbar {
            background: #f8fafc;
            color: var(--muted);
            padding: 10px 0;
            font-size: 13px;
            border-bottom: 1px solid var(--line);
        }

        .topbar .container {
            display: flex;
            align-items: center;
            gap: 18px;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .nav {
            background: #fff;
            border-bottom: 1px solid var(--line);
        }

        .nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 14px 0;
            flex-wrap: wrap;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .logo-img {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            background: #fff;
            padding: 8px;
            box-shadow: 0 18px 32px rgba(6, 54, 71, 0.15);
            object-fit: contain;
        }

        .logo-copy {
            display: flex;
            flex-direction: column;
            gap: 2px;
            font-weight: 600;
        }

        .logo-subtitle {
            font-size: 11px;
            letter-spacing: 0.5em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .logo-text {
            font-size: 20px;
            letter-spacing: 0.04em;
            color: var(--ink);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 18px;
            font-size: 14px;
            color: var(--muted);
        }

        .nav-links .shop-link {
            background: linear-gradient(135deg, #f97316, #facc15);
            color: #1f2937;
            padding: 8px 14px;
            border-radius: 999px;
            font-weight: 700;
            box-shadow: 0 10px 24px rgba(249, 115, 22, 0.25);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn {
            padding: 10px 16px;
            border-radius: 999px;
            border: 1px solid transparent;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn.primary {
            background: var(--brand);
            color: #fff;
            box-shadow: var(--shadow);
        }

        .btn.accent {
            background: linear-gradient(135deg, #1d4ed8, #38bdf8);
            color: #fff;
            box-shadow: var(--shadow);
            border-color: transparent;
        }

        .btn.shop {
            background: linear-gradient(135deg, #f97316, #facc15);
            color: #1f2937;
            box-shadow: var(--shadow);
            border-color: transparent;
        }

        .btn.outline {
            border-color: var(--line);
            background: #fff;
            color: var(--ink);
        }

        .hero {
            padding: 70px 0 60px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .hero-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 28px;
            align-items: center;
        }

        .eyebrow {
            text-transform: uppercase;
            letter-spacing: 0.25em;
            color: var(--brand);
            font-weight: 700;
            font-size: 12px;
        }

        .hero h1 {
            font-family: var(--display);
            font-weight: 400;
            font-size: clamp(2.4rem, 4vw, 3.6rem);
            margin: 12px 0;
        }

        .hero p {
            color: var(--muted);
            line-height: 1.6;
            font-size: 16px;
        }

        .hero-actions {
            margin-top: 18px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .hero-card {
            background: #fff;
            border-radius: 20px;
            padding: 24px;
            box-shadow: var(--shadow);
            border: 1px solid #f0f4f7;
            position: relative;
            overflow: hidden;
        }

        .hero-card h3 {
            margin-top: 0;
        }

        .hero-card::after {
            content: "";
            position: absolute;
            inset: -40% -20% auto auto;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(46, 196, 182, 0.35), rgba(46, 196, 182, 0));
            pointer-events: none;
        }

        .preview-card {
            border-radius: 16px;
            padding: 14px;
            border: 1px solid #dbe6ed;
            background: linear-gradient(135deg, #f6fbff, #eef6fb);
            margin: 12px 0 16px;
        }

        .preview-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--brand-deep);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .preview-shell {
            margin-top: 10px;
            border-radius: 14px;
            border: 1px solid #c9d6df;
            background: #fff;
            padding: 14px;
            box-shadow: inset 0 0 0 1px #f1f5f8;
        }

        .mini-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .mini-card {
            border-radius: 10px;
            padding: 10px;
            background: linear-gradient(140deg, #0b5a77, #2ec4b6);
            color: #fff;
            font-size: 11px;
            font-weight: 600;
        }

        .mini-card:nth-child(2) {
            background: linear-gradient(140deg, #1d4ed8, #38bdf8);
        }

        .mini-card:nth-child(3) {
            background: linear-gradient(140deg, #f97316, #facc15);
        }

        .pill-list.compact span {
            background: #ffffff;
            border: 1px solid #dbe6ed;
            box-shadow: 0 6px 12px rgba(6, 22, 34, 0.06);
        }

        .hero-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 18px;
        }

        .badge {
            background: #f2f8fa;
            border: 1px solid #d8e4eb;
            color: var(--brand-deep);
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .stats {
            padding: 50px 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 16px;
        }

        .stat-card {
            background: var(--card);
            border-radius: 16px;
            padding: 18px;
            border: 1px solid var(--line);
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .stat-card strong {
            display: block;
            font-size: 26px;
            color: var(--brand-deep);
        }

        .stat-card::before {
            content: "";
            position: absolute;
            inset: 0;
            opacity: 0.12;
            background: linear-gradient(135deg, #2ec4b6, #1d4ed8);
        }

        .stat-card:nth-child(2)::before {
            background: linear-gradient(135deg, #f97316, #facc15);
        }

        .stat-card:nth-child(3)::before {
            background: linear-gradient(135deg, #22c55e, #16a34a);
        }

        .stat-card:nth-child(4)::before {
            background: linear-gradient(135deg, #8b5cf6, #ec4899);
        }

        .stat-card > * {
            position: relative;
        }

        .stat-card strong {
            font-size: 28px;
            letter-spacing: 0.01em;
        }

        .stat-card div {
            color: #0f2c3a;
            font-weight: 600;
        }

        .section {
            padding: 60px 0;
        }

        .section h2 {
            font-family: var(--display);
            font-size: clamp(2rem, 3vw, 2.6rem);
            margin: 0 0 12px;
        }

        .section p.lead {
            color: var(--muted);
            max-width: 640px;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 18px;
            margin-top: 26px;
        }

        .feature-card {
            background: var(--card);
            border-radius: 18px;
            padding: 20px;
            border: 1px solid var(--line);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
            position: relative;
            overflow: hidden;
        }

        .feature-card::after {
            content: "";
            position: absolute;
            inset: auto -40% -40% auto;
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, rgba(46, 196, 182, 0.18), rgba(46, 196, 182, 0));
            pointer-events: none;
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: 20px;
            color: #fff;
            margin-bottom: 12px;
            background: linear-gradient(135deg, #1d4ed8, #38bdf8);
        }

        .feature-card h4 {
            margin: 0 0 8px;
        }

        .feature-card:nth-child(2) .feature-icon {
            background: linear-gradient(135deg, #16a34a, #22c55e);
        }

        .feature-card:nth-child(3) .feature-icon {
            background: linear-gradient(135deg, #f97316, #facc15);
        }

        .feature-card:nth-child(4) .feature-icon {
            background: linear-gradient(135deg, #8b5cf6, #ec4899);
        }

        .why-section {
            background: #ffffff;
            border-top: 1px solid var(--line);
            border-bottom: 1px solid var(--line);
        }

        .why-header {
            text-align: center;
            max-width: 760px;
            margin: 0 auto 28px;
        }

        .why-header h2 {
            margin-bottom: 10px;
        }

        .why-header a {
            color: var(--brand);
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .why-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
        }

        .why-card {
            background: #fff;
            border-radius: 18px;
            padding: 20px;
            border: 1px solid var(--line);
            text-align: center;
        }

        .why-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            margin: 0 auto 14px;
            display: grid;
            place-items: center;
            font-size: 24px;
            color: #fff;
            background: linear-gradient(135deg, #1d4ed8, #38bdf8);
        }

        .why-card:nth-child(2) .why-icon {
            background: linear-gradient(135deg, #16a34a, #22c55e);
        }

        .why-card:nth-child(3) .why-icon {
            background: linear-gradient(135deg, #f97316, #facc15);
        }

        .why-card:nth-child(4) .why-icon {
            background: linear-gradient(135deg, #8b5cf6, #ec4899);
        }

        .why-card:nth-child(5) .why-icon {
            background: linear-gradient(135deg, #0ea5e9, #14b8a6);
        }

        .why-card:nth-child(6) .why-icon {
            background: linear-gradient(135deg, #ef4444, #f97316);
        }

        .why-card:nth-child(7) .why-icon {
            background: linear-gradient(135deg, #6366f1, #a855f7);
        }

        .why-card:nth-child(8) .why-icon {
            background: linear-gradient(135deg, #0f766e, #2dd4bf);
        }

        .why-card h4 {
            margin: 0 0 8px;
        }
        .split {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 26px;
            align-items: center;
        }

        .panel {
            background: var(--card);
            border-radius: 20px;
            padding: 24px;
            border: 1px solid var(--line);
            box-shadow: var(--shadow);
        }

        .demo-form {
            display: grid;
            gap: 14px;
        }

        .demo-field {
            display: grid;
            gap: 6px;
            font-size: 13px;
            color: var(--muted);
        }

        .demo-input {
            height: 46px;
            border-radius: 12px;
            border: 1px solid #d6e3ea;
            padding: 0 14px;
            background: #f7fbfd;
            font-size: 14px;
        }

        .demo-inline {
            display: grid;
            grid-template-columns: 90px 1fr;
            gap: 10px;
        }

        .demo-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: #e8f2ff;
            color: #1d4ed8;
            font-weight: 700;
            font-size: 11px;
        }

        .demo-actions {
            display: grid;
            gap: 12px;
            margin-top: 10px;
        }

        .demo-check {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 12px;
            color: var(--muted);
        }

        .demo-primary {
            height: 48px;
            border-radius: 14px;
            border: none;
            background: #1d4ed8;
            color: #fff;
            font-weight: 700;
            font-size: 14px;
        }

        .demo-secondary {
            border: none;
            background: transparent;
            color: #1d4ed8;
            font-weight: 700;
            font-size: 13px;
            text-align: left;
            padding: 0;
        }

        .pill-list {
            display: grid;
            gap: 10px;
            font-size: 14px;
            color: var(--muted);
        }

        .pill-list span {
            background: #f3f7f9;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid #e1e9ef;
        }

        .footer {
            background: #0b1526;
            color: #dbe7f4;
            padding: 60px 0 30px;
        }

        .footer-cta {
            background: radial-gradient(circle at top, rgba(16, 185, 129, 0.2), rgba(11, 21, 38, 0.85) 60%),
                        linear-gradient(135deg, #0f172a, #14233d);
            border-radius: 24px;
            padding: 48px 28px;
            text-align: center;
            box-shadow: 0 24px 60px rgba(2, 8, 23, 0.45);
            margin-bottom: 40px;
        }

        .footer-cta h2 {
            margin: 0 0 10px;
            font-size: clamp(24px, 3.2vw, 36px);
            color: #ffffff;
        }

        .footer-cta p {
            margin: 0 0 22px;
            color: #cbd5e1;
        }

        .footer-cta .cta-btn {
            background: #22c55e;
            color: #0b1526;
            padding: 14px 28px;
            border-radius: 14px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .footer-cta .cta-meta {
            margin-top: 16px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 18px;
            font-size: 13px;
            color: #94a3b8;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 22px;
        }

        .footer h4 {
            margin: 0 0 12px;
            color: #ffffff;
        }

        .footer a {
            color: #cbd5e1;
            font-size: 13px;
        }

        .footer-bottom {
            margin-top: 28px;
            padding-top: 18px;
            border-top: 1px solid rgba(148, 163, 184, 0.2);
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            font-size: 12px;
            color: #94a3b8;
        }

        .social-links {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .social-links img {
            width: 22px;
            height: 22px;
            filter: brightness(1.6) grayscale(40%);
        }

        .cta-strip {
            background: linear-gradient(120deg, var(--brand), var(--accent));
            color: #fff;
            padding: 24px;
            border-radius: 20px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .cta-strip strong {
            font-size: 18px;
        }

        .mockup {
            background: #f7fafc;
            border-radius: 18px;
            padding: 16px;
            border: 1px dashed #c9d6df;
            text-align: center;
            color: var(--muted);
        }

        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 18px;
            margin-top: 24px;
        }

        .image-card {
            background: #fff;
            border-radius: 18px;
            border: 1px solid var(--line);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .image-card img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-card .caption {
            padding: 12px 16px;
            font-weight: 600;
            color: var(--brand-deep);
            background: #f7fafc;
        }

        .app-section {
            background: radial-gradient(circle at 70% 25%, rgba(16, 185, 129, 0.22), rgba(11, 21, 38, 0.9) 60%),
                        linear-gradient(135deg, #0b1526, #111827);
            color: #e2e8f0;
        }

        .app-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 32px;
            align-items: center;
        }

        .app-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(45, 212, 191, 0.18);
            color: #2dd4bf;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .app-title {
            margin: 16px 0 12px;
            font-size: clamp(26px, 3.2vw, 38px);
            color: #ffffff;
        }

        .app-title span {
            color: #2dd4bf;
        }

        .app-copy {
            color: #cbd5e1;
            line-height: 1.6;
        }

        .app-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px 18px;
            margin: 18px 0 24px;
            padding: 0;
            list-style: none;
            font-size: 13px;
        }

        .app-list li::before {
            content: "✓";
            margin-right: 8px;
            color: #22c55e;
            font-weight: 700;
        }

        .app-cta {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .app-button {
            background: #1fbf84;
            color: #0b1526;
            border: none;
            border-radius: 14px;
            padding: 12px 18px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-width: 220px;
            box-shadow: 0 14px 30px rgba(31, 191, 132, 0.35);
        }

        .app-button span {
            display: block;
            font-weight: 600;
            font-size: 14px;
        }

        .app-meta {
            font-size: 12px;
            color: #94a3b8;
        }

        .app-note {
            margin-top: 14px;
            font-size: 11px;
            color: #64748b;
        }

        .app-device {
            position: relative;
            display: grid;
            place-items: center;
        }

        .laptop-frame {
            width: min(520px, 100%);
            border-radius: 22px;
            padding: 18px;
            background: linear-gradient(180deg, #0f172a, #1f2937);
            border: 2px solid rgba(148, 163, 184, 0.25);
            box-shadow: 0 30px 60px rgba(15, 23, 42, 0.6);
        }

        .laptop-screen {
            background: linear-gradient(180deg, #111827, #1f2937);
            border-radius: 16px;
            padding: 16px;
            color: #f8fafc;
            display: grid;
            gap: 14px;
            min-height: 260px;
        }

        .laptop-top {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #cbd5e1;
        }

        .laptop-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .laptop-logo {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: #fff;
            color: #0f172a;
            display: grid;
            place-items: center;
            font-weight: 800;
        }

        .laptop-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .laptop-card {
            background: rgba(148, 163, 184, 0.15);
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 12px;
            display: grid;
            gap: 6px;
        }

        .laptop-card strong {
            font-size: 18px;
        }

        .laptop-card.success {
            background: rgba(16, 185, 129, 0.25);
        }

        .laptop-base {
            height: 14px;
            margin: 12px auto 0;
            width: 80%;
            background: #0b1220;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 12px 24px rgba(2, 8, 23, 0.45);
        }

        .device-glow {
            position: absolute;
            inset: 10%;
            border-radius: 26px;
            border: 1px solid rgba(45, 212, 191, 0.3);
            filter: blur(10px);
            pointer-events: none;
        }

        @media (max-width: 960px) {
            .container {
                padding: 0 18px;
            }

            .nav-inner {
                flex-direction: column;
            }

            .nav-links {
                justify-content: center;
                flex-wrap: wrap;
            }

            .nav-actions {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
            }

            .hero-grid {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero-actions {
                justify-content: center;
            }

            .hero-card {
                text-align: left;
            }

            .split {
                grid-template-columns: 1fr;
            }

            .app-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            .nav-actions {
                width: 100%;
                justify-content: center;
            }
            .cta-strip {
                text-align: center;
            }

            .feature-grid,
            .why-grid,
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .demo-inline {
                grid-template-columns: 1fr;
            }

            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }

            .app-list {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 520px) {
            .hero {
                padding: 50px 0 40px;
            }

            .hero h1 {
                font-size: 2.1rem;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="container">
            <div>Fast diagnostics. Trusted results. Connected care.</div>
            <div>Call: +94 77 270 2303 | Email: support@labtech.lk</div>
        </div>
    </div>

    @php
        $isSuperAdmin = auth()->check() && auth()->user()->isSuperAdmin();
    @endphp

    <nav class="nav">
        <div class="container nav-inner">
            <div class="logo">
                <img class="logo-img" src="{{ asset('images/logo.png') }}" alt="labtech.lk logo">
                <div class="logo-copy">
                    <span class="logo-subtitle">Labtech LIS</span>
                    <span class="logo-text">labtech.lk</span>
                </div>
            </div>
            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="#workflow">Workflow</a>
                <a href="#modules">Modules</a>
                <a href="#pricing">Plans</a>
                <a href="#contact">Contact</a>
                <a class="shop-link" href="/shop">Shop</a>
            </div>
            <div class="nav-actions">
                <a class="btn outline" href="/login">LIS System Login</a>
                <a class="btn primary" href="#demo">Request Demo</a>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container hero-grid">
            <div>
                <div class="eyebrow">Labtech.lk LIS</div>
                <h1>Run a modern lab with a single connected system.</h1>
                <p>labtech.lk helps laboratories manage patient onboarding, sample tracking, testing workflows, reporting, billing, and referrals with speed and clarity.</p>
                <div class="hero-badges">
                    <span class="badge">Smart sample tracking</span>
                    <span class="badge">Multi-branch ready</span>
                    <span class="badge">PDF / SMS / WhatsApp reports</span>
                </div>
                <div class="hero-actions">
                <a class="btn accent" href="/login">Login to LIS</a>
                    <a class="btn shop" href="/shop">Buy Consumable</a>
                </div>
            </div>
            <div class="hero-card">
                <h3>Lab Operations Dashboard</h3>
                <p>Monitor pending samples, validation queues, and revenue in real-time.</p>
                <div class="preview-card">
                    <div class="preview-title">Dashboard Preview</div>
                <div class="preview-shell" style="padding:0; overflow:hidden;">
                    <img src="{{ asset('images/admin.jpeg') }}" alt="labtech.lk LIS dashboard preview" style="width:100%; height:auto; display:block;">
                </div>
                </div>
                <div class="pill-list compact">
                    <span>Specimen chain-of-custody</span>
                    <span>Auto flag & critical alerts</span>
                    <span>Role-based approvals</span>
                </div>
            </div>
        </div>
    </section>

    <section class="stats">
        <div class="container stats-grid">
            <div class="stat-card">
                <strong>99.9%</strong>
                <div>Report accuracy pipeline</div>
            </div>
            <div class="stat-card">
                <strong>24/7</strong>
                <div>Smart notifications</div>
            </div>
            <div class="stat-card">
                <strong>100+</strong>
                <div>Supported test panels</div>
            </div>
            <div class="stat-card">
                <strong>50+</strong>
                <div>Automation triggers</div>
            </div>
        </div>
    </section>

    <section id="features" class="section">
        <div class="container">
            <h2>Everything your lab team needs.</h2>
            <p class="lead">From front desk to final report, labtech.lk keeps every step connected with secure audit trails.</p>
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon">💳</div>
                    <h4>Patient + Billing</h4>
                    <p>Quick billing, discounts, package handling, and multi-payment support.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🧪</div>
                    <h4>Sample Tracking</h4>
                    <p>Specimen status timelines, barcode labels, and center wise queues.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">✅</div>
                    <h4>Results & Validation</h4>
                    <p>Structured test parameters, flags, and approval workflows.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔔</div>
                    <h4>Reporting & Alerts</h4>
                    <p>PDF reports, SMS/Email/WhatsApp messaging, and QR verification.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="workflow" class="section">
        <div class="container split">
            <div>
                <h2>Built for labs, not spreadsheets.</h2>
                <p class="lead">Align staff, reduce turnaround time, and maintain compliance with built-in audit logs and approvals.</p>
                <div class="pill-list">
                    <span>Specimen intake + triage</span>
                    <span>Analyzer integration ready</span>
                    <span>Centre & doctor wise reports</span>
                    <span>Inventory-aware workflows</span>
                </div>
            </div>
            <div class="panel">
                <h4>Workflow highlights</h4>
                <ul style="margin:0; padding-left:18px; color: var(--muted);">
                    <li>Sample collected -> lab received -> tested -> validated -> approved.</li>
                    <li>Automatic TAT tracking and SLA alerts.</li>
                    <li>Audit trail for every result update.</li>
                </ul>
            </div>
        </div>
    </section>

    <section id="modules" class="section">
        <div class="container">
            <h2>Core modules</h2>
            <p class="lead">Pick the modules you need and scale for multi-centre operations.</p>
            <div class="feature-grid">
                <div class="feature-card">
                    <h4>Test Master</h4>
                    <p>Departments, panels, reference ranges, and billing visibility.</p>
                </div>
                <div class="feature-card">
                    <h4>Inventory</h4>
                    <p>Reagents, consumption tracking, reorder alerts, and suppliers.</p>
                </div>
                <div class="feature-card">
                    <h4>Analytics</h4>
                    <p>Operational dashboards, KPI summaries, and financials.</p>
                </div>
                <div class="feature-card">
                    <h4>Patient Portal</h4>
                    <p>Secure report delivery with download links and QR checks.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="pricing" class="section">
        <div class="container">
            <div class="cta-strip">
                <div>
                    <strong>Need a custom LIS package-></strong>
                    <div>Talk to our team for onboarding, training, and migration.</div>
                </div>
                <a class="btn outline" href="#demo">Request Demo</a>
            </div>
        </div>
    </section>

    <section id="demo" class="section">
        <div class="container split">
            <div>
                <h2>Request Demo</h2>
                <p class="lead">Demo booking placeholder. We will activate online demo requests here soon.</p>
            </div>
            <div class="panel">
                <h4>Demo Request Form (Coming Soon)</h4>
                <form class="demo-form">
                    <label class="demo-field">
                        Your name
                        <input class="demo-input" type="text" placeholder="Full name" disabled>
                    </label>
                    <label class="demo-field">
                        Lab name
                        <input class="demo-input" type="text" placeholder="Lab / center name" disabled>
                    </label>
                    <label class="demo-field">
                        Mobile number <span class="demo-badge">OTP Required</span>
                        <div class="demo-inline">
                            <input class="demo-input" type="text" value="+94" disabled>
                            <input class="demo-input" type="text" placeholder="Mobile no." disabled>
                        </div>
                    </label>
                    <div class="demo-actions">
                        <button class="demo-secondary" type="button" disabled>+ Add referral code</button>
                        <label class="demo-check">
                            <input type="checkbox" disabled>
                            <span>I agree to Terms &amp; Conditions and Privacy Policy.</span>
                        </label>
                        <button class="demo-primary" type="button" disabled>Continue</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="section why-section">
        <div class="container">
            <div class="why-header">
                <h2>Why labtech.lk</h2>
                <p class="lead">A cloud-first LIS that is easy to use, secure, and built for fast-growing diagnostic labs. Here are the benefits our customers love.</p>
                <a href="/register">Start free trial -></a>
            </div>
            <div class="why-grid">
                <div class="why-card">
                    <div class="why-icon">PC</div>
                    <h4>Run on multiple computers</h4>
                    <p>Access your lab from any branch with secure user roles.</p>
                </div>
                <div class="why-card">
                    <div class="why-icon">MOB</div>
                    <h4>Access anywhere</h4>
                    <p>Works on desktop, tablet, or mobile with a clean UI.</p>
                </div>
                <div class="why-card">
                    <div class="why-icon">USD</div>
                    <h4>Affordable</h4>
                    <p>Flexible plans that scale with your lab volume.</p>
                </div>
                <div class="why-card">
                    <div class="why-icon">DL</div>
                    <h4>No installation hassles</h4>
                    <p>Launch quickly with cloud hosting and guided setup.</p>
                </div>
                <div class="why-card">
                    <div class="why-icon">UPD</div>
                    <h4>Regular updates</h4>
                    <p>Continuous improvements based on lab feedback.</p>
                </div>
                <div class="why-card">
                    <div class="why-icon">FAST</div>
                    <h4>Fast setup</h4>
                    <p>Start billing and reporting the same day.</p>
                </div>
                <div class="why-card">
                    <div class="why-icon">SUP</div>
                    <h4>Online support</h4>
                    <p>Dedicated LIS support with quick response.</p>
                </div>
                <div class="why-card">
                    <div class="why-icon">EASY</div>
                    <h4>Easy to operate</h4>
                    <p>Designed for front desk, lab techs, and doctors.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section app-section" id="app">
        <div class="container app-grid">
            <div>
                <span class="app-badge">Mobile App Available</span>
                <h2 class="app-title">Download Our <span>IOS / Android App</span></h2>
                <p class="app-copy">
                    Take Labtech.lk with you everywhere. Manage your Medical Laboratory & Clinicon the go with a feature-rich Android application.
                </p>
                <ul class="app-list">
                    <li>Web Based Application </li>
                    <li>QR Scanner</li>
                    <li>SMS Alerts&amp; Test Master </li>
                    <li>Stock management</li>
                      <li>Shop  </li>
                    <li>Laboratory & Clinic Billing</li>
                    <li>Pre loaded Report formats &amp; Test Master </li>
                    <li>Marketting & Accounts</li>
                </ul>
                <div class="app-cta">
                    <button class="app-button" type="button">
                        <span>Book a Demo</span>
                    </button>
                    <div class="app-meta">
                        <br>

                    </div>
                </div>
                <div class="app-note">
                </div>
            </div>
            <div class="app-device">
                <div class="laptop-frame">
                    <div class="laptop-screen">
                        <div class="laptop-top">
                            <span>10:10</span>
                            <span>Online</span>
                        </div>
                        <div class="laptop-brand">
                            <div class="laptop-logo">L</div>
                            <div>
                                <div style="font-weight:700;">Labtech.lk</div>
                                <div style="font-size:12px;color:#cbd5e1;">Medical Laboratory &amp; Clinic Management</div>
                            </div>
                        </div>
                        <div class="laptop-grid">
                            <div class="laptop-card success">
                                Today's Patients
                                <strong>38</strong>
                            </div>
                            <div class="laptop-card">
                                Validations
                                <strong>62</strong>
                            </div>
                            <div class="laptop-card">
                                Pending Reports
                                <strong>19</strong>
                            </div>
                            <div class="laptop-card">
                                Revenue
                                <strong>Rs. 45,000</strong>
                            </div>
                        </div>
                    </div>
                    <div class="laptop-base"></div>
                </div>
                <div class="device-glow"></div>
            </div>
            </div>
        </div>
    </section>

    <footer id="contact" class="footer">
        <div class="container">
            <div class="footer-cta">
                <h2>Medical Laboratory System</h2>
                <p>Join hundreds of clinics already using Labtech.lk. Start your free trial today.</p>
                <a class="cta-btn" href="/login">Start Free Trial -></a>
                <div class="cta-meta">
                    <span>WhatsApp: {{ $settings['contact_phone'] ?? '+94 77 270 2303' }}</span>
                    <span>Email: {{ $settings['support_email'] ?? 'support@labtech.lk' }}</span>
                </div>
            </div>

            <div class="footer-grid">
                <div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <img src="{{ asset('images/logo.png') }}" alt="labtech.lk logo" style="width:38px;height:38px;border-radius:10px;background:#fff;padding:6px;">
                        <strong style="color:#fff;">labtech.lk</strong>
                    </div>
                    <p style="margin-top:10px; color:#94a3b8;">Modern Medical Laboratory & Clinical Laboratory Management System</p>
                    <div class="social-links" style="margin-top:12px;">
                        <a href="#" aria-label="Facebook">
                            <img src="{{ asset('images/facebook.png') }}" alt="Facebook">
                        </a>
                        <a href="#" aria-label="LinkedIn">
                            <img src="{{ asset('images/linkedin.png') }}" alt="LinkedIn">
                        </a>
                        <a href="#" aria-label="YouTube">
                            <img src="{{ asset('images/youtube.png') }}" alt="YouTube">
                        </a>
                        <a href="#" aria-label="Instagram">
                            <img src="{{ asset('images/instagram.png') }}" alt="Instagram">
                        </a>
                    </div>
                </div>
                <div>
                </div>
                <div>
                </div>
                <div>
                    <h4>Contact</h4>
                    <div>+94 77 270 2303</div>
                    <div>support@labtech.lk</div>
                    <div>Sri Lanka</div>
                </div>
            </div>

            <div class="footer-bottom">
                <div>Copyright (c) {{ date('Y') }} Labtech.lk. All rights reserved.</div>
                <div>Powered by <a href="https://byocloud.lk" style="color:#e2e8f0; font-weight:600;">Byocloud.lk</a></div>
            </div>
        </div>
    </footer>
</body>
</html>
