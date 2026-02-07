<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Track Report</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700" rel="stylesheet" />
    <style>
        :root {
            --brand: #0a6fb3;
            --ink: #0f1a21;
            --muted: #5b6b74;
            --bg: #f5f7f8;
            --card: #ffffff;
            --line: #e3eaee;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Manrope", "Segoe UI", sans-serif;
            background: var(--bg);
            color: var(--ink);
        }

        .shell {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 24px;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 20px 40px rgba(15, 26, 33, 0.08);
        }

        h1 {
            margin: 0 0 10px;
            font-size: 28px;
        }

        p {
            margin: 0 0 18px;
            color: var(--muted);
        }

        .field {
            display: grid;
            gap: 6px;
            margin-bottom: 12px;
            font-size: 13px;
        }

        .field input {
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid var(--line);
            font-size: 13px;
        }

        .btn {
            border: none;
            border-radius: 8px;
            padding: 10px 16px;
            font-weight: 700;
            cursor: pointer;
            background: var(--brand);
            color: #fff;
        }

        .alert {
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 12px;
            margin-bottom: 12px;
        }

        .alert.error {
            background: #fff4f4;
            border: 1px solid #f2b6b6;
            color: #a01515;
        }

        .alert.success {
            background: #e7f6ee;
            border: 1px solid #b7e3cc;
            color: #0f7a47;
        }

        .report-list {
            border-top: 1px solid var(--line);
            margin-top: 16px;
            padding-top: 16px;
        }

        .report-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--line);
            font-size: 13px;
        }

        .report-item a {
            text-decoration: none;
            color: var(--brand);
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="shell">
        <div class="card">
            <h1>Track Your Report</h1>
            <p>Enter UHID and mobile number to receive an OTP and view validated reports.</p>

            @if (!empty($error))
                <div class="alert error">{{ $error }}</div>
            @endif
            @if (!empty($message))
                <div class="alert success">{{ $message }}</div>
            @endif

            @if (($step ?? 'request') === 'request')
                <form method="post" action="{{ route('reports.track.request') }}">
                    @csrf
                    <div class="field">
                        <label>UHID</label>
                        <input type="text" name="uhid" required>
                    </div>
                    <div class="field">
                        <label>Mobile Number</label>
                        <input type="text" name="phone" required>
                    </div>
                    <button class="btn" type="submit">Request OTP</button>
                </form>
            @elseif (($step ?? '') === 'verify')
                <form method="post" action="{{ route('reports.track.confirm') }}">
                    @csrf
                    <div class="field">
                        <label>OTP</label>
                        <input type="text" name="otp" required>
                    </div>
                    <button class="btn" type="submit">Verify</button>
                </form>
            @elseif (($step ?? '') === 'result')
                <p><strong>{{ $patient?->name ?? '' }}</strong> — reports are listed below.</p>
                <div class="report-list">
                    @forelse ($reportItems ?? [] as $item)
                        <div class="report-item">
                            <div>
                                {{ $item->specimen?->specimen_no ?? '-' }} • {{ $item->testMaster?->name ?? '-' }}
                            </div>
                            <a href="{{ route('reports.show', $item) }}">Open</a>
                        </div>
                    @empty
                        <p>Report not ready. Please check again later.</p>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
</body>
</html>
