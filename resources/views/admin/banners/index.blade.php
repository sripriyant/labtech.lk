@extends('layouts.admin')

@php
    $pageTitle = 'Banner Manager';
@endphp

@section('content')
    <div class="card">
        <form method="post" action="{{ route('banners.store') }}" enctype="multipart/form-data">
            @csrf
            <div style="display:grid;gap:16px;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
                <div>
                    <label for="title">Title</label>
                    <input id="title" name="title" type="text" placeholder="Banner title">
                </div>
                <div>
                    <label for="subtitle">Subtitle</label>
                    <input id="subtitle" name="subtitle" type="text" placeholder="Banner subtitle">
                </div>
                <div>
                    <label for="cta_text">CTA Text</label>
                    <input id="cta_text" name="cta_text" type="text" placeholder="Read More">
                </div>
                <div>
                    <label for="cta_link">CTA Link</label>
                    <input id="cta_link" name="cta_link" type="text" placeholder="https://">
                </div>
                <div>
                    <label for="sort_order">Sort Order</label>
                    <input id="sort_order" name="sort_order" type="number" min="0" value="0">
                </div>
                <div>
                    <label for="image">Banner Image</label>
                    <input id="image" name="image" type="file" accept="image/*" required>
                </div>
            </div>
            <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-top:16px;">
                <label><input type="checkbox" name="is_active" value="1" checked> Active</label>
                <button class="btn" type="submit">Upload Banner</button>
            </div>
        </form>
    </div>

    <div class="card" style="margin-top:20px;">
        <table style="width:100%;border-collapse:collapse;font-size:14px;">
            <thead>
                <tr>
                    <th style="text-align:left;padding:12px 10px;border-bottom:1px solid var(--line);">Image</th>
                    <th style="text-align:left;padding:12px 10px;border-bottom:1px solid var(--line);">Title</th>
                    <th style="text-align:left;padding:12px 10px;border-bottom:1px solid var(--line);">CTA</th>
                    <th style="text-align:left;padding:12px 10px;border-bottom:1px solid var(--line);">Order</th>
                    <th style="text-align:left;padding:12px 10px;border-bottom:1px solid var(--line);">Status</th>
                    <th style="text-align:left;padding:12px 10px;border-bottom:1px solid var(--line);">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($banners as $banner)
                    <tr>
                        <td style="padding:12px 10px;border-bottom:1px solid var(--line);">
                            <img style="width:120px;height:60px;border-radius:8px;object-fit:cover;border:1px solid var(--line);" src="{{ asset('storage/' . $banner->image_path) }}" alt="Banner">
                        </td>
                        <td style="padding:12px 10px;border-bottom:1px solid var(--line);">{{ $banner->title ?? 'Untitled' }}</td>
                        <td style="padding:12px 10px;border-bottom:1px solid var(--line);">{{ $banner->cta_text ?? '-' }}</td>
                        <td style="padding:12px 10px;border-bottom:1px solid var(--line);">{{ $banner->sort_order }}</td>
                        <td style="padding:12px 10px;border-bottom:1px solid var(--line);">
                            @if ($banner->is_active)
                                <span style="display:inline-block;padding:4px 10px;border-radius:999px;background:#e8f6f1;color:#0b5a40;font-size:12px;font-weight:600;">Active</span>
                            @else
                                <span style="display:inline-block;padding:4px 10px;border-radius:999px;background:#f3f4f6;color:#475569;font-size:12px;font-weight:600;">Inactive</span>
                            @endif
                        </td>
                        <td style="padding:12px 10px;border-bottom:1px solid var(--line);">
                            <form method="post" action="{{ route('banners.destroy', $banner) }}">
                                @csrf
                                <button class="btn" type="submit" style="background:#ffffff;color:var(--muted);border:1px solid var(--line);">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:12px 10px;">No banners uploaded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
