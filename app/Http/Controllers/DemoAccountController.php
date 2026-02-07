<?php

namespace App\Http\Controllers;

use App\Models\DemoAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DemoAccountController extends Controller
{
    public function store(Request ): RedirectResponse
    {
        ->requirePermission('admin.dashboard');

         = ->user();
        if (! || !->isSuperAdmin()) {
            abort(403);
        }

         = ->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'expires_at' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string'],
        ]);

        DemoAccount::create([
            'name' => ['name'],
            'email' => ['email'] ?? null,
            'phone' => ['phone'] ?? null,
            'expires_at' => ['expires_at'],
            'notes' => ['notes'] ?? null,
            'created_by' => ->id,
        ]);

        return back()->with('demoAccountSuccess', 'Demo account created.');
    }
}
