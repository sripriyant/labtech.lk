<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        $this->requirePermission('banners.manage');
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403);
        }

        $banners = Banner::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.banners.index', [
            'banners' => $banners,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('banners.manage');
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'cta_text' => ['nullable', 'string', 'max:255'],
            'cta_link' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['required', 'image', 'max:4096'],
        ]);

        $path = $request->file('image')->store('banners', 'public');

        Banner::create([
            'title' => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'cta_text' => $data['cta_text'] ?? null,
            'cta_link' => $data['cta_link'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'image_path' => $path,
        ]);

        return redirect()->route('banners.index');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $this->requirePermission('banners.manage');
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403);
        }

        $banner->delete();

        return redirect()->route('banners.index');
    }
}
