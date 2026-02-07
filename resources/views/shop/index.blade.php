<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lab Consumables Shop</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:300,400,500,600,700,800" rel="stylesheet" />
    <style>
        :root {
            --brand: #0b5a77;
            --brand-dark: #08364a;
            --accent: #f97316;
            --accent-dark: #ea580c;
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

        a { color: inherit; text-decoration: none; }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .topbar {
            background: var(--brand-dark);
            color: #fff;
            font-size: 13px;
            padding: 10px 0;
        }

        .topbar .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
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
            gap: 16px;
            padding: 14px 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
        }

        .brand-mark {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--brand), #2ec4b6);
            display: grid;
            place-items: center;
            color: #fff;
        }

        .nav-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn {
            border: none;
            padding: 9px 16px;
            border-radius: 999px;
            font-weight: 600;
            cursor: pointer;
            font-size: 13px;
        }

        .btn.primary {
            background: var(--brand);
            color: #fff;
            box-shadow: var(--shadow);
        }

        .btn.accent {
            background: linear-gradient(135deg, var(--accent), #fbbf24);
            color: #1f2937;
            box-shadow: var(--shadow);
        }

        .hero {
            padding: 60px 0 40px;
            background: radial-gradient(circle at top left, #dff2f4 0, #eef4f7 55%);
        }

        .hero-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 28px;
            align-items: center;
        }

        .hero h1 {
            margin: 0 0 12px;
            font-size: clamp(28px, 4vw, 40px);
        }

        .hero p {
            margin: 0 0 16px;
            color: var(--muted);
            line-height: 1.6;
        }

        .hero-points {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .hero-point {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 12px;
            color: var(--brand-dark);
            font-weight: 600;
        }

        .shop-shell {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr) 320px;
            gap: 20px;
            padding: 32px 0 70px;
            align-items: start;
        }

        .sidebar {
            display: grid;
            gap: 16px;
        }

        .sidebar-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 16px;
            box-shadow: var(--shadow);
        }

        .sidebar-title {
            font-weight: 700;
            margin-bottom: 12px;
        }

        .filter-form {
            display: grid;
            gap: 12px;
        }

        .filter-group {
            display: grid;
            gap: 6px;
            font-size: 12px;
            color: var(--muted);
        }

        .filter-group input,
        .filter-group select {
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid var(--line);
            font-size: 13px;
            background: #fff;
        }

        .category-list {
            display: grid;
            gap: 8px;
        }

        .category-link {
            padding: 8px 12px;
            border-radius: 10px;
            border: 1px solid var(--line);
            background: #f8fafc;
            font-size: 12px;
            font-weight: 600;
            color: var(--muted);
        }

        .category-link.active {
            background: var(--brand);
            color: #fff;
            border-color: transparent;
        }

        .products-area {
            min-width: 0;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
        }

        .product-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 14px;
            box-shadow: var(--shadow);
            display: grid;
            gap: 10px;
        }

        .product-media {
            height: 160px;
            border-radius: 14px;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(11, 90, 119, 0.1), rgba(46, 196, 182, 0.12));
            display: grid;
            place-items: center;
            color: var(--brand);
            font-weight: 700;
        }

        .product-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-card h3 {
            margin: 0;
            font-size: 15px;
        }

        .product-card p {
            margin: 0;
            color: var(--muted);
            font-size: 12px;
            min-height: 38px;
        }

        .product-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .price {
            font-weight: 700;
            color: var(--brand);
        }

        .cart-panel {
            background: #fff;
            border-radius: 18px;
            border: 1px solid var(--line);
            padding: 18px;
            box-shadow: var(--shadow);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .cart-panel h3 {
            margin-top: 0;
        }

        .cart-item {
            display: grid;
            gap: 6px;
            padding: 10px 0;
            border-bottom: 1px solid #edf2f7;
        }

        .cart-item strong {
            font-size: 13px;
        }

        .cart-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .cart-actions input {
            width: 60px;
            padding: 6px 8px;
            border-radius: 8px;
            border: 1px solid var(--line);
            font-size: 12px;
        }

        .cart-footer {
            display: grid;
            gap: 10px;
            margin-top: 16px;
        }

        .cart-total {
            font-weight: 700;
            color: var(--brand-dark);
        }

        .notice {
            background: #eaf6ff;
            border: 1px solid #cfe7ff;
            padding: 10px 12px;
            border-radius: 12px;
            font-size: 12px;
            color: #0b5a77;
        }

        @media (max-width: 1100px) {
            .shop-shell {
                grid-template-columns: 240px minmax(0, 1fr);
            }

            .cart-panel {
                grid-column: 1 / -1;
                position: static;
            }
        }

        @media (max-width: 900px) {
            .shop-shell {
                grid-template-columns: 1fr;
            }

            .cart-panel {
                position: static;
            }
        }

        @media (max-width: 720px) {
            .nav-inner {
                flex-direction: column;
            }

            .topbar .container {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    @php
        $labName = \App\Models\Setting::query()
            ->where('key', 'lab_name')
            ->whereNull('lab_id')
            ->value('value') ?: 'Labtech.lk';
        $cartItems = $cart['items'] ?? collect();
        $cartSubtotal = $cart['subtotal'] ?? 0;
    @endphp

    <div class="topbar">
        <div class="container">
            <div>Medical lab consumables marketplace</div>
            <div>Phone: +94 77 270 2303</div>
        </div>
    </div>

    <nav class="nav">
        <div class="container nav-inner">
            <div class="brand">
                <div class="brand-mark">LT</div>
                <div>{{ $labName }} Shop</div>
            </div>
            <div class="nav-actions">
                <a class="btn" href="/">Back to Home</a>
                <a class="btn primary" href="/login">LIS Login</a>
            </div>
        </div>
    </nav>

    @if (session('status'))
        <div class="container" style="margin-top:16px;">
            <div class="notice">{{ session('status') }}</div>
        </div>
    @endif

    <section class="hero">
        <div class="container hero-grid">
            <div>
                <h1>Laboratory consumables, ready to ship.</h1>
                <p>Order reagents, tubes, collection kits, and lab essentials with verified supply chains. Built for diagnostics teams who need reliability.</p>
                <div class="hero-points">
                    <span class="hero-point">Easy to start</span>
                    <span class="hero-point">Easy to run</span>
                    <span class="hero-point">Easy to grow</span>
                </div>
            </div>
            <div class="notice">
                Upload your product list in the admin panel to build the full catalog.
            </div>
        </div>
    </section>

    <section class="container">
        <div class="shop-shell">
            <aside class="sidebar">
                <div class="sidebar-card">
                    <div class="sidebar-title">Filters</div>
                    <form method="get" class="filter-form">
                        <div class="filter-group">
                            <label>Search</label>
                            <input type="text" name="search" placeholder="Search products" value="{{ $filters['search'] ?? '' }}">
                        </div>
                        <div class="filter-group">
                            <label>Sort by</label>
                            <select name="sort">
                                <option value="new" {{ ($filters['sort'] ?? '') === 'new' ? 'selected' : '' }}>Newest</option>
                                <option value="price_low" {{ ($filters['sort'] ?? '') === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_high" {{ ($filters['sort'] ?? '') === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                                <option value="name" {{ ($filters['sort'] ?? '') === 'name' ? 'selected' : '' }}>Name A-Z</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Price range</label>
                            <select name="price">
                                <option value="" {{ empty($filters['price']) ? 'selected' : '' }}>All prices</option>
                                <option value="0-1000" {{ ($filters['price'] ?? '') === '0-1000' ? 'selected' : '' }}>LKR 0 - 1,000</option>
                                <option value="1000-5000" {{ ($filters['price'] ?? '') === '1000-5000' ? 'selected' : '' }}>LKR 1,000 - 5,000</option>
                                <option value="5000-10000" {{ ($filters['price'] ?? '') === '5000-10000' ? 'selected' : '' }}>LKR 5,000 - 10,000</option>
                                <option value="10000-50000" {{ ($filters['price'] ?? '') === '10000-50000' ? 'selected' : '' }}>LKR 10,000 - 50,000</option>
                                <option value="50000-" {{ ($filters['price'] ?? '') === '50000-' ? 'selected' : '' }}>LKR 50,000+</option>
                            </select>
                        </div>
                        <button class="btn" type="submit">Apply Filters</button>
                    </form>
                </div>

                <div class="sidebar-card">
                    <div class="sidebar-title">Categories</div>
                    <div class="category-list">
@php
                        $baseFilters = [
                            'search' => $filters['search'] ?? '',
                            'sort' => $filters['sort'] ?? 'new',
                            'price' => $filters['price'] ?? '',
                        ];
                    @endphp
                        <a class="category-link {{ empty($filters['category']) ? 'active' : '' }}" href="{{ route('shop.index', $baseFilters) }}">All</a>
                        @foreach ($categories as $category)
                            <a class="category-link {{ ($filters['category'] ?? '') === $category->slug ? 'active' : '' }}" href="{{ route('shop.index', array_merge($baseFilters, ['category' => $category->slug])) }}">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </aside>

            <div class="products-area">
                <div class="product-grid">
                    @forelse ($products as $product)
                        <article class="product-card">
                            <div class="product-media">
                                @if (!empty($product->image_path))
                                    <img src="{{ $product->image_path }}" alt="{{ $product->name }}">
                                @else
                                    Product
                                @endif
                            </div>
                            <div>
                                <h3>{{ $product->name }}</h3>
                                <p>{{ $product->description ?? 'Medical-grade consumable for lab workflows.' }}</p>
                            </div>
                            <div class="product-meta">
                                <span class="price">LKR {{ number_format($product->price, 2) }}</span>
                                <form method="post" action="{{ route('shop.cart.add') }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button class="btn accent" type="submit">Buy</button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <article class="product-card">
                            <div class="product-media">Catalog</div>
                            <h3>No products yet</h3>
                            <p>Upload items from admin shop to showcase the catalog.</p>
                        </article>
                    @endforelse
                </div>
            </div>

            <aside class="cart-panel">
                <h3>Cart</h3>
                @if ($cartItems->isEmpty())
                    <p class="help-text">No items added yet.</p>
                @else
                    @foreach ($cartItems as $item)
                        <div class="cart-item">
                            <strong>{{ $item['product']->name }}</strong>
                            <div>LKR {{ number_format($item['product']->price, 2) }}</div>
                            <form class="cart-actions" method="post" action="{{ route('shop.cart.update') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item['product']->id }}">
                                <input type="number" name="quantity" min="1" value="{{ $item['quantity'] }}">
                                <button class="btn" type="submit">Update</button>
                            </form>
                            <form method="post" action="{{ route('shop.cart.remove') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item['product']->id }}">
                                <button class="btn secondary" type="submit">Remove</button>
                            </form>
                        </div>
                    @endforeach
                    <div class="cart-footer">
                        <div class="cart-total">Subtotal: LKR {{ number_format($cartSubtotal, 2) }}</div>
                        <a class="btn primary" href="{{ route('shop.checkout') }}">Checkout</a>
                    </div>
                @endif
            </aside>
        </div>
    </section>
</body>
</html>
