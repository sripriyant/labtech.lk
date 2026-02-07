<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:300,400,500,600,700,800" rel="stylesheet" />
    <style>
        :root {
            --brand: #0b5a77;
            --ink: #0f172a;
            --muted: #5b6b74;
            --bg: #eef4f7;
            --card: #ffffff;
            --line: #d8e3ea;
            --shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            --font: "Poppins", "Segoe UI", sans-serif;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: var(--font);
            color: var(--ink);
            background: var(--bg);
        }

        .container {
            max-width: 980px;
            margin: 0 auto;
            padding: 40px 24px;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 20px;
        }

        .card {
            background: #fff;
            border-radius: 18px;
            border: 1px solid var(--line);
            padding: 20px;
            box-shadow: var(--shadow);
        }

        .field {
            display: grid;
            gap: 6px;
            font-size: 12px;
            margin-bottom: 12px;
        }

        .field input,
        .field textarea {
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid var(--line);
            font-size: 13px;
            font-family: inherit;
            background: #fff;
        }

        .btn {
            background: var(--brand);
            color: #fff;
            border: none;
            padding: 10px 16px;
            border-radius: 999px;
            font-weight: 600;
            cursor: pointer;
            font-size: 13px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            padding: 8px 0;
            border-bottom: 1px solid #edf2f7;
        }

        @media (max-width: 900px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div style="margin-bottom:16px;">
            <a href="{{ route('shop.index') }}"><- Back to shop</a>
        </div>

        <div class="checkout-grid">
            <div class="card">
                <h2 style="margin-top:0;">Checkout</h2>
                <form method="post" action="{{ route('shop.checkout.submit') }}">
                    @csrf
                    <div class="field">
                        <label>Name</label>
                        <input name="name" type="text" required>
                    </div>
                    <div class="field">
                        <label>Phone</label>
                        <input name="phone" type="text" required>
                    </div>
                    <div class="field">
                        <label>Email</label>
                        <input name="email" type="email">
                    </div>
                    <div class="field">
                        <label>Lab Name</label>
                        <input name="lab_name" type="text">
                    </div>
                    <div class="field">
                        <label>Delivery Address</label>
                        <input name="address" type="text">
                    </div>
                    <div class="field">
                        <label>Notes</label>
                        <textarea name="notes" rows="3"></textarea>
                    </div>
                    <button class="btn" type="submit">Place Order</button>
                </form>
            </div>
            <div class="card">
                <h3 style="margin-top:0;">Order Summary</h3>
                @foreach ($cart['items'] as $item)
                    <div class="summary-item">
                        <span>{{ $item['product']->name }} x {{ $item['quantity'] }}</span>
                        <span>LKR {{ number_format($item['total'], 2) }}</span>
                    </div>
                @endforeach
                <div class="summary-item" style="font-weight:700;">
                    <span>Total</span>
                    <span>LKR {{ number_format($cart['subtotal'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
