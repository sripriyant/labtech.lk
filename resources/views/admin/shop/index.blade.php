@extends('layouts.admin')

@php
    $pageTitle = 'Shop Management';
@endphp

@section('content')
    <style>
        .shop-page {
            display: grid;
            gap: 18px;
        }

        .shop-top {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
        }

        .shop-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
        }

        .shop-card h3 {
            margin-top: 0;
        }

        .field {
            display: grid;
            gap: 6px;
            font-size: 12px;
            margin-bottom: 12px;
        }

        .field input,
        .field textarea,
        .field select {
            width: 100%;
            padding: 9px 10px;
            border-radius: 10px;
            border: 1px solid var(--line);
            font-size: 12px;
            font-family: inherit;
            background: #fff;
        }

        .btn {
            background: #0a6fb3;
            color: #fff;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 12px;
        }

        .btn.secondary {
            background: #f1f5f8;
            color: var(--muted);
            border: 1px solid var(--line);
        }

        .toast {
            position: fixed;
            top: 24px;
            right: 24px;
            background: #0b6b43;
            color: #fff;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 12px;
            box-shadow: 0 12px 30px rgba(15, 26, 33, 0.2);
            z-index: 999;
        }

        .table-wrap {
            border: 1px solid var(--line);
            border-radius: 14px;
            overflow: hidden;
            background: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th, td {
            padding: 10px 8px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: middle;
        }

        thead th {
            background: #f5f9fc;
            color: var(--muted);
        }

        .thumb {
            width: 56px;
            height: 40px;
            border-radius: 8px;
            background: #f1f5f8;
            display: grid;
            place-items: center;
            overflow: hidden;
        }

        .thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge.active {
            background: #e8f6f1;
            color: #0b5a40;
        }

        .badge.inactive {
            background: #f3f4f6;
            color: #475569;
        }

        .table-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .help-text {
            font-size: 12px;
            color: var(--muted);
        }
    </style>

    @if (session('status'))
        <div class="toast is-visible" role="status">{{ session('status') }}</div>
    @endif

    <div class="shop-page">
        <div class="shop-top">
            <div class="shop-card">
                <h3>Add Category</h3>
                <form method="post" action="{{ route('admin.shop.categories.store') }}">
                    @csrf
                    <div class="field">
                        <label>Name</label>
                        <input name="name" type="text" required>
                    </div>
                    <div class="field">
                        <label>Description</label>
                        <textarea name="description" rows="3"></textarea>
                    </div>
                    <div class="field">
                        <label>Sort Order</label>
                        <input name="sort_order" type="number" min="0" value="0">
                    </div>
                    <div class="field">
                        <label><input type="checkbox" name="is_active" value="1" checked> Active</label>
                    </div>
                    <button class="btn" type="submit">Save Category</button>
                </form>
            </div>

            <div class="shop-card">
                <h3>Add Product</h3>
                <form method="post" action="{{ route('admin.shop.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="field">
                        <label>Name</label>
                        <input name="name" type="text" required>
                    </div>
                    <div class="field">
                        <label>Category</label>
                        <select name="category_id">
                            <option value="">Select category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <div class="help-text">Optional: use category text for legacy items.</div>
                    </div>
                    <div class="field">
                        <label>Category (Text)</label>
                        <input name="category" type="text" placeholder="Consumables, Reagents">
                    </div>
                    <div class="field">
                        <label>Description</label>
                        <textarea name="description" rows="4"></textarea>
                    </div>
                    <div class="field">
                        <label>Price</label>
                        <input name="price" type="number" step="0.01" min="0" value="0.00">
                    </div>
                    <div class="field">
                        <label>Image</label>
                        <input name="image_file" type="file" accept=".png,.jpg,.jpeg">
                    </div>
                    <div class="field">
                        <label>Sort Order</label>
                        <input name="sort_order" type="number" min="0" value="0">
                    </div>
                    <div class="field">
                        <label><input type="checkbox" name="is_active" value="1" checked> Active</label>
                    </div>
                    <button class="btn" type="submit">Save Product</button>
                </form>
            </div>
        </div>

        <div class="shop-card">
            <h3>Categories</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Sort</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <form method="post" action="{{ route('admin.shop.categories.update', $category) }}">
                                    @csrf
                                    <td><input name="name" value="{{ $category->name }}" class="row-input" style="width:100%; padding:6px 8px; border-radius:8px; border:1px solid var(--line); font-size:12px;"></td>
                                    <td><input name="description" value="{{ $category->description }}" class="row-input" style="width:100%; padding:6px 8px; border-radius:8px; border:1px solid var(--line); font-size:12px;"></td>
                                    <td><input name="sort_order" type="number" min="0" value="{{ $category->sort_order }}" class="row-input" style="width:100%; padding:6px 8px; border-radius:8px; border:1px solid var(--line); font-size:12px;"></td>
                                    <td>
                                        <label style="display:flex;align-items:center;gap:6px;">
                                            <input type="checkbox" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }}>
                                            <span class="badge {{ $category->is_active ? 'active' : 'inactive' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="btn secondary" type="submit">Save</button>
                                            <button class="btn" type="submit" formaction="{{ route('admin.shop.categories.destroy', $category) }}" onclick="return confirm('Delete this category?');">Delete</button>
                                        </div>
                                    </td>
                                </form>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No categories yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="shop-card">
            <h3>Products</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td>
                                    <div class="thumb">
                                        @if (!empty($product->image_path))
                                            <img src="{{ $product->image_path }}" alt="{{ $product->name }}">
                                        @else
                                            -
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <form method="post" action="{{ route('admin.shop.update', $product) }}" enctype="multipart/form-data">
                                        @csrf
                                        <div class="field">
                                            <input name="name" type="text" value="{{ $product->name }}" required>
                                        </div>
                                        <div class="field">
                                            <select name="category_id">
                                                <option value="">Select category</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}" @selected($product->category_id === $category->id)>{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="field">
                                            <input name="category" type="text" value="{{ $product->category }}" placeholder="Category text">
                                        </div>
                                        <div class="field">
                                            <textarea name="description" rows="3">{{ $product->description }}</textarea>
                                        </div>
                                        <div class="field">
                                            <input name="price" type="number" step="0.01" min="0" value="{{ $product->price }}">
                                        </div>
                                        <div class="field">
                                            <input name="sort_order" type="number" min="0" value="{{ $product->sort_order }}">
                                        </div>
                                        <div class="field">
                                            <input name="image_file" type="file" accept=".png,.jpg,.jpeg">
                                            <label style="display:flex;align-items:center;gap:6px; font-size:12px;"><input type="checkbox" name="image_clear" value="1"> Clear image</label>
                                        </div>
                                        <label style="display:flex;align-items:center;gap:6px; font-size:12px;">
                                            <input type="checkbox" name="is_active" value="1" {{ $product->is_active ? 'checked' : '' }}> Active
                                        </label>
                                        <div class="table-actions" style="margin-top:10px;">
                                            <button class="btn secondary" type="submit">Save</button>
                                        </div>
                                    </form>
                                </td>
                                <td>{{ $product->category?->name ?? $product->category ?? '-' }}</td>
                                <td>LKR {{ number_format($product->price, 2) }}</td>
                                <td>
                                    <span class="badge {{ $product->is_active ? 'active' : 'inactive' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">No products yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
