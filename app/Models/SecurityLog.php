<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'attempted_at',
        'violation_type',
        'raw_response',
    ];

    protected function casts(): array
    {
        return [
            'attempted_at' => 'datetime',
            'raw_response' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}