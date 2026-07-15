<?php

use App\Models\SeoSetting;

beforeEach(function () {
    if (SeoSetting::count() === 0) {
        $this->artisan('db:seed', ['--class' => 'SeoSettingSeeder']);
    }
});

// ══════════════════════════════════════════════════════════
// INTEGRIDADE DOS DADOS
// ══════════════════════════════════════════════════════════
test('existem configurações padrão de SEO cadastradas', function () {
    $count = SeoSetting::count();
    expect($count)->toBeGreaterThanOrEqual(3);
});

test('cada configuração possui key única e valor definido', function () {
    $settings = SeoSetting::all();
    $keys = [];

    foreach ($settings as $setting) {
        expect($setting->key)->not->toBeNull();
        expect($setting->key)->not->toBeEmpty();
        expect($setting->value)->not->toBeNull();
        expect($setting->group)->toBeIn(['general', 'social']);

        // Keys não devem se repetir
        expect(in_array($setting->key, $keys))->toBeFalse("Key duplicada: {$setting->key}");
        $keys[] = $setting->key;
    }
});

test('meta_title_default existe e contém o nome do marketplace', function () {
    $setting = SeoSetting::where('key', 'meta_title_default')->first();

    expect($setting)->not->toBeNull();
    expect($setting->value)->toContain('Demanda3D');
});

test('meta_description_default existe e tem pelo menos 50 caracteres', function () {
    $setting = SeoSetting::where('key', 'meta_description_default')->first();

    expect($setting)->not->toBeNull();
    expect(strlen($setting->value))->toBeGreaterThanOrEqual(50);
});

test('canonical_url_default aponta para o domínio da aplicação', function () {
    $setting = SeoSetting::where('key', 'canonical_url_default')->first();

    expect($setting)->not->toBeNull();
    expect($setting->value)->toContain(config('app.url'));
});

// ══════════════════════════════════════════════════════════
// IDEMPOTÊNCIA DO SEEDER
// ══════════════════════════════════════════════════════════
test('seeder de SEO é idempotente — não duplica registros', function () {
    $countBefore = SeoSetting::count();

    $this->artisan('db:seed', ['--class' => 'SeoSettingSeeder']);

    $countAfter = SeoSetting::count();
    expect($countAfter)->toBe($countBefore);
});