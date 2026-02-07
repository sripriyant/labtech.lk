<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>labtech.lk Login Panel</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,600,700" rel="stylesheet" />

    <style>
        :root {
            --ink: #0b1b2b;
            --muted: #5c6d7e;
            --panel: #0d3b66;
            --accent: #19c2b6;
            --accent-2: #2f8cff;
            --card: #ffffff;
            --shadow: 0 30px 80px rgba(8, 22, 39, 0.25);
            --ring: rgba(25, 194, 182, 0.35);
            --font: "Space Grotesk", "Segoe UI", sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: var(--font);
            color: var(--ink);
            background:
                radial-gradient(circle at 15% 20%, rgba(47, 140, 255, 0.12), transparent 45%),
                radial-gradient(circle at 80% 0%, rgba(25, 194, 182, 0.18), transparent 40%),
                linear-gradient(135deg, #f1f5fb 0%, #e8f6f4 35%, #f9f3ea 100%);
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .login-shell {
            width: min(460px, 100%);
            background: var(--card);
            border-radius: 32px;
            padding: 48px;
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            gap: 24px;
            position: relative;
            animation: floatIn 0.6s ease both;
        }

        .login-shell::before {
            content: "";
            position: absolute;
            inset: 16px;
            border-radius: 26px;
            border: 1px solid rgba(15, 42, 74, 0.08);
            pointer-events: none;
        }

        .logo-row {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .logo-img {
            width: 108px;
            height: 108px;
            border-radius: 22px;
            object-fit: contain;
            background: #fff;
            padding: 12px;
            box-shadow: 0 20px 40px rgba(13, 59, 102, 0.15);
        }

        .brand-title {
            font-size: clamp(28px, 3vw, 36px);
            line-height: 1.1;
            font-weight: 700;
            letter-spacing: 0.02em;
            margin: 0;
        }

        .brand-subtitle {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.4em;
            color: rgba(11, 27, 43, 0.6);
            margin-bottom: 6px;
        }

        .brand-copy {
            margin: 0;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.6;
        }

        .login-header h2 {
            font-size: 24px;
            margin: 0 0 4px;
            font-weight: 700;
        }

        .login-header p {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
        }

        .form-grid {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .field {
            display: grid;
            gap: 6px;
        }

        .field label {
            font-size: 13px;
            color: var(--muted);
            font-weight: 600;
        }

        .field input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid rgba(12, 33, 59, 0.15);
            background: #fbfcff;
            font-size: 15px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .field input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px var(--ring);
        }

        .login-actions {
            display: flex;
            justify-content: flex-end;
        }

        .btn {
            border: none;
            padding: 12px 24px;
            border-radius: 999px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            letter-spacing: 0.04em;
        }

        .btn-primary {
            background: linear-gradient(120deg, var(--accent), var(--accent-2));
            color: #ffffff;
            box-shadow: 0 12px 28px rgba(47, 140, 255, 0.35);
        }

        .credentials-note {
            padding: 14px 16px;
            border-radius: 14px;
            background: rgba(25, 194, 182, 0.08);
            color: var(--muted);
            font-size: 14px;
            border-left: 4px solid var(--accent);
        }

        .footer-note {
            font-size: 11px;
            color: rgba(11, 27, 43, 0.6);
            text-align: center;
            margin: 0;
        }

        @keyframes floatIn {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 520px) {
            body {
                padding: 12px;
            }

            .login-shell {
                padding: 36px 28px;
            }

            .login-actions {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <div class="logo-row">
            <img class="logo-img" src="{{ asset('images/logo.png') }}" alt="labtech.lk logo">
            <div>
                <div class="brand-subtitle">Labtech.lk</div>
                <h1 class="brand-title">Login Panel</h1>
            </div>
        </div>

        <div class="brand-copy">
            Secure access to the labtech.lk console. Use the admin credentials provided below to continue.
        </div>

        <div class="login-header">
            <h2>Access Portal</h2>
            <p>Enter your username or email and password.</p>
        </div>

        <form class="form-grid" method="post" action="{{ route('login.submit') }}" id="loginForm">
            @csrf

            <div class="field">
                <label for="username">Username or Email</label>
                <input id="username" name="username" type="text" placeholder="Enter username or email" autocomplete="username" required>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="Enter password" autocomplete="current-password" required>
            </div>

            <div class="login-actions">
                <button class="btn btn-primary" type="submit">Log In</button>
            </div>
        </form>

        <div class="credentials-note">
        </div>

        <p class="footer-note">Designed for labtech.lk | 2026</p>
    </div>
</body>
</html>
