<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Models;

use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnRequest extends Model
{
    protected $fillable = [
        'order_id', 'client_id', 'status',
        'reason_encrypted', 'reason_hash',
        'requested_at', 'shipped_back_at', 'approved_at', 'rejected_at',
        'rejection_reason_encrypted',
    ];

    protected $appends = ['reason', 'rejection_reason'];

    protected function casts(): array
    {
        return [
            'requested_at'     => 'datetime',
            'shipped_back_at'  => 'datetime',
            'approved_at'      => 'datetime',
            'rejected_at'      => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getReasonAttribute(): ?string
    {
        return EncryptionService::decrypt($this->reason_encrypted);
    }

    public function getRejectionReasonAttribute(): ?string
    {
        return EncryptionService::decrypt($this->rejection_reason_encrypted);
    }
}