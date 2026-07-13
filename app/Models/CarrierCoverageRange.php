<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarrierCoverageRange extends Model
{
    protected $table = 'carrier_coverage_ranges';

    protected $fillable = ['carrier_id', 'title', 'cep_start', 'cep_end'];

    // ── Relacionamentos ──────────────────────────────────────

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    // ── Scopes ───────────────────────────────────────────────

    /**
     * Scope para buscar faixas que cobrem um determinado CEP.
     */
    public function scopeCoversCep($query, string $cep)
    {
        return $query->where('cep_start', '<=', $cep)
                     ->where('cep_end', '>=', $cep);
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.