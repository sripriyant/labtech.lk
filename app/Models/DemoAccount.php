<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Lab;
use App\Models\User;

class DemoAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_id',
        'created_by',
        'name',
        'email',
        'phone',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function lab()
    {
        return $this->belongsTo(Lab::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
