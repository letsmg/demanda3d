<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property string $key
 * @property string|null $value
 * @property string $group
 */
class SeoSetting extends Model
{
    protected $table = 'seo_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * Retorna o valor de uma configuração SEO pelo key.
     * Utiliza cache de 1 hora para evitar queries repetidas.
     */
    public static function getValue(string $key, ?string $default = null): ?string
    {
        return Cache::remember("seo_setting:{$key}", 3600, function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    /**
     * Retorna todas as configurações SEO como array associativo [key => value].
     */
    public static function getAllAsArray(): array
    {
        return Cache::remember('seo_settings:all', 3600, function () {
            return static::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Invalida o cache quando qualquer configuração for alterada.
     */
    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('seo_settings:all');
        });

        static::deleted(function () {
            Cache::forget('seo_settings:all');
        });
    }
}