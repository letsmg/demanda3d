<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    /**
     * A tabela não usa updated_at — os logs são imutáveis.
     */
    public const UPDATED_AT = null;

    /**
     * Mass-assignment permitido.
     */
    protected $fillable = [
        'tenant_id',
        'causer_type',
        'causer_id',
        'event',
        'subject_type',
        'subject_id',
        'description',
        'properties',
    ];

    /**
     * Casts para tipos nativos.
     */
    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    // ───────────────────────────────────────────────
    // Relationships
    // ───────────────────────────────────────────────

    /**
     * Tenant ao qual o log pertence (nulo para ações globais de admin).
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Quem executou a ação (User admin/seller/carrier ou Client).
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Recurso afetado pela ação (Product, Order, User, etc.).
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    // ───────────────────────────────────────────────
    // Scopes
    // ───────────────────────────────────────────────

    /**
     * Scope para filtrar por tenant — usado quando o usuário é Seller.
     * Admins ignoram este filtro no Controller.
     */
    public function scopeForTenant($query, ?int $tenantId)
    {
        if ($tenantId !== null) {
            return $query->where('tenant_id', $tenantId);
        }

        return $query;
    }

    /**
     * Scope para filtrar por causer (quem executou a ação).
     */
    public function scopeFilterByCauser($query, ?int $causerId, ?string $causerType = null)
    {
        if ($causerId) {
            $query->where('causer_id', $causerId);

            if ($causerType) {
                $query->where('causer_type', $causerType);
            }
        }

        return $query;
    }

    /**
     * Scope para filtrar por evento (tipo da ação).
     */
    public function scopeFilterByEvent($query, ?string $event)
    {
        if ($event) {
            return $query->where('event', $event);
        }

        return $query;
    }

    /**
     * Scope para filtrar por subject_type (recurso afetado).
     */
    public function scopeFilterBySubjectType($query, ?string $subjectType)
    {
        if ($subjectType) {
            return $query->where('subject_type', $subjectType);
        }

        return $query;
    }

    /**
     * Scope para filtrar por range de data.
     */
    public function scopeFilterByDateRange($query, ?string $from, ?string $to)
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }

    /**
     * Scope para ordenação padrão: mais recentes primeiro.
     */
    public function scopeLatestFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}