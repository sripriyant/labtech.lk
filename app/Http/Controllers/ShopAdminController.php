<?php

namespace App\Http\Controllers;

use App\Models\ShopCategory;
use App\Models\ShopProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ShopAdminController extends Controller
{
    public function index(): View
    {
        $this->requirePermission('admin.dashboard');
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403);
        }

        $products = ShopProduct::query()
            ->with('category')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        $categories = ShopCategory::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.shop.index', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('admin.dashboard');
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:shop_categories,id'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('uploads/shop', 'public');
            $data['image_path'] = '/storage/' . $path;
        }

        if (!empty($data['category_id']) && empty($data['category'])) {
            $data['category'] = ShopCategory::query()
                ->whereKey($data['category_id'])
                ->value('name');
        }

        $data['is_active'] = !empty($data['is_active']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        ShopProduct::create($data);

        return redirect()
            ->route('admin.shop.index')
            ->with('status', 'Saved successfully');
    }

    public function update(Request $request, ShopProduct $product): RedirectResponse
    {
        $this->requirePermission('admin.dashboard');
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:shop_categories,id'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:5120'],
            'image_clear' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        if (!empty($data['image_clear']) && $product->image_path) {
            $storagePath = ltrim(str_replace('/storage/', '', $product->image_path), '/');
            Storage::disk('public')->delete($storagePath);
            $data['image_path'] = null;
        }

        if ($request->hasFile('image_file')) {
            if ($product->image_path) {
                $storagePath = ltrim(str_replace('/storage/', '', $product->image_path), '/');
                Storage::disk('public')->delete($storagePath);
            }
            $path = $request->file('image_file')->store('uploads/shop', 'public');
            $data['image_path'] = '/storage/' . $path;
        }

        if (!empty($data['category_id']) && empty($data['category'])) {
            $data['category'] = ShopCategory::query()
                ->whereKey($data['category_id'])
                ->value('name');
        }

        $data['is_active'] = !empty($data['is_active']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $product->update($data);

        return redirect()
            ->route('admin.shop.index')
            ->with('status', 'Saved successfully');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $this->requirePermission('admin.dashboard');
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = Str::slug($data['name']);
        if ($slug === '') {
            $slug = Str::random(8);
        }

        ShopCategory::create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => !empty($data['is_active']),
        ]);

        return redirect()
            ->route('admin.shop.index')
            ->with('status', 'Category saved');
    }

    public function updateCategory(Request $request, ShopCategory $category): RedirectResponse
    {
        $this->requirePermission('admin.dashboard');
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']) ?: $category->slug,
            'description' => $data['description'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => !empty($data['is_active']),
        ]);

        return redirect()
            ->route('admin.shop.index')
            ->with('status', 'Category updated');
    }

    public function destroyCategory(ShopCategory $category): RedirectResponse
    {
        $this->requirePermission('admin.dashboard');
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403);
        }

        $category->delete();

        return redirect()
            ->route('admin.shop.index')
            ->with('status', 'Category deleted');
    }
}
