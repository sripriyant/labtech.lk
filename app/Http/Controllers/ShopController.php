<?php

namespace App\Http\Controllers;

use App\Models\ShopCategory;
use App\Models\ShopOrder;
use App\Models\ShopProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $categorySlug = trim((string) $request->query('category', ''));
        $sort = trim((string) $request->query('sort', 'new'));
        $price = trim((string) $request->query('price', ''));

        $categories = ShopCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $productsQuery = ShopProduct::query()
            ->with('category')
            ->where('is_active', true)
            ->orderBy('sort_order');

        if ($search !== '') {
            $productsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%');
            });
        }

        if ($categorySlug !== '') {
            $category = $categories->firstWhere('slug', $categorySlug);
            if ($category) {
                $productsQuery->where('category_id', $category->id);
            }
        }

        if ($price !== '') {
            [$min, $max] = array_pad(explode('-', $price, 2), 2, '');
            $minVal = is_numeric($min) ? (float) $min : null;
            $maxVal = is_numeric($max) ? (float) $max : null;
            if ($minVal !== null) {
                $productsQuery->where('price', '>=', $minVal);
            }
            if ($maxVal !== null) {
                $productsQuery->where('price', '<=', $maxVal);
            }
        }

        switch ($sort) {
            case 'price_low':
                $productsQuery->orderBy('price');
                break;
            case 'price_high':
                $productsQuery->orderByDesc('price');
                break;
            case 'name':
                $productsQuery->orderBy('name');
                break;
            default:
                $productsQuery->orderByDesc('id');
                $sort = 'new';
        }

        $products = $productsQuery->get();
        $cart = $this->getCart($request);
        $cartItems = $this->cartItems($cart);

        return view('shop.index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'category' => $categorySlug,
                'sort' => $sort,
                'price' => $price,
            ],
            'cart' => $cartItems,
        ]);
    }

    public function addToCart(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:shop_products,id'],
        ]);

        $cart = $this->getCart($request);
        $productId = (int) $data['product_id'];
        $cart[$productId] = ($cart[$productId] ?? 0) + 1;
        $request->session()->put('shop_cart', $cart);

        return redirect()->route('shop.index');
    }

    public function updateCart(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:shop_products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $this->getCart($request);
        $productId = (int) $data['product_id'];
        $cart[$productId] = (int) $data['quantity'];
        $request->session()->put('shop_cart', $cart);

        return redirect()->route('shop.index');
    }

    public function removeFromCart(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
        ]);

        $cart = $this->getCart($request);
        $productId = (int) $data['product_id'];
        unset($cart[$productId]);
        $request->session()->put('shop_cart', $cart);

        return redirect()->route('shop.index');
    }

    public function checkout(Request $request): View
    {
        $cart = $this->getCart($request);
        $cartItems = $this->cartItems($cart);

        return view('shop.checkout', [
            'cart' => $cartItems,
        ]);
    }

    public function placeOrder(Request $request): RedirectResponse
    {
        $cart = $this->getCart($request);
        $cartItems = $this->cartItems($cart);

        if ($cartItems['items']->isEmpty()) {
            return redirect()->route('shop.index');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'lab_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $order = ShopOrder::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'lab_name' => $data['lab_name'] ?? null,
            'address' => $data['address'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
            'subtotal' => $cartItems['subtotal'],
            'total' => $cartItems['subtotal'],
        ]);

        foreach ($cartItems['items'] as $item) {
            $order->items()->create([
                'shop_product_id' => $item['product']->id,
                'name' => $item['product']->name,
                'price' => $item['product']->price,
                'quantity' => $item['quantity'],
                'total' => $item['total'],
            ]);
        }

        $request->session()->forget('shop_cart');

        return redirect()->route('shop.index')->with('status', 'Order placed successfully');
    }

    private function getCart(Request $request): array
    {
        return $request->session()->get('shop_cart', []);
    }

    private function cartItems(array $cart): array
    {
        $ids = array_keys($cart);
        $products = ShopProduct::query()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $items = collect();
        $subtotal = 0;

        foreach ($cart as $productId => $qty) {
            $product = $products->get((int) $productId);
            if (!$product) {
                continue;
            }
            $quantity = max(1, (int) $qty);
            $total = $product->price * $quantity;
            $subtotal += $total;
            $items->push([
                'product' => $product,
                'quantity' => $quantity,
                'total' => $total,
            ]);
        }

        return [
            'items' => $items,
            'subtotal' => $subtotal,
        ];
    }
}
