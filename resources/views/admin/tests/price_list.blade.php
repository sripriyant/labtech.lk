<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Price List</title>
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            color: #111827;
            background: #fff;
        }

        .price-list-print {
            position: fixed;
            top: 16px;
            right: 16px;
            border: 1px solid #0a6fb3;
            background: #0a6fb3;
            color: #fff;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            z-index: 100;
        }

        @media print {
            .price-list-print {
                display: none;
            }
        }

        .page {
            padding: 32px;
        }

        h1 {
            margin: 0 0 16px;
            text-align: center;
            letter-spacing: 0.08em;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th, td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        thead th {
            background: #f3f4f6;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 11px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <button class="price-list-print" onclick="window.print()">Print Price List</button>
    <div class="page">
        <h1>PRICE LIST</h1>
        <table>
            <thead>
                <tr>
                    <th>Test Name</th>
                    <th>Code</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tests as $test)
                    <tr>
                        <td>{{ $test->name }}</td>
                        <td>{{ $test->code }}</td>
                        <td>{{ number_format((float) $test->price, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No tests found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
