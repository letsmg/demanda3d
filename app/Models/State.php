<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class State extends Model
{
    protected $fillable = ['uf', 'name', 'cep_start', 'cep_end'];

    public function carriers(): BelongsToMany
    {
        return $this->belongsToMany(Carrier::class, 'carrier_state')
            ->withTimestamps();
    }

    /**
     * Encontra o estado correspondente a um CEP.
     */
    public static function findByCep(string $cep): ?self
    {
        $prefix = substr(preg_replace('/[^0-9]/', '', $cep), 0, 5);
        $prefixInt = (int) $prefix;

        return self::all()->first(function (self $state) use ($prefixInt) {
            $start = (int) substr(preg_replace('/[^0-9]/', '', $state->cep_start), 0, 5);
            $end   = (int) substr(preg_replace('/[^0-9]/', '', $state->cep_end), 0, 5);

            return $prefixInt >= $start && $prefixInt <= $end;
        });
    }
}