<?php

use App\Models\Category;

beforeEach(function () {
    if (Category::count() === 0) {
        $this->artisan('db:seed', ['--class' => 'CategorySeeder']);
    }
});

// ══════════════════════════════════════════════════════════
// INTEGRIDADE DOS DADOS
// ══════════════════════════════════════════════════════════
test('existem pelo menos 5 categorias cadastradas', function () {
    $count = Category::count();
    expect($count)->toBeGreaterThanOrEqual(5);
});

test('cada categoria possui slug único e url-safe', function () {
    $categories = Category::all();
    $slugs = [];

    foreach ($categories as $category) {
        expect($category->slug)->not->toBeNull();
        expect($category->slug)->not->toBeEmpty();
        expect($category->slug)->toMatch('/^[a-z0-9]+(-[a-z0-9]+)*$/');

        expect(in_array($category->slug, $slugs))->toBeFalse("Slug duplicado: {$category->slug}");
        $slugs[] = $category->slug;
    }
});

test('categoria adulta possui flag is_adult = true', function () {
    $adultCategory = Category::where('slug', 'adulto')->first();

    expect($adultCategory)->not->toBeNull();
    expect($adultCategory->is_adult)->toBeTrue();
});

test('categorias normais possuem is_adult = false', function () {
    $normalCategories = Category::where('is_adult', false)->get();

    expect($normalCategories)->not->toBeEmpty();
    foreach ($normalCategories as $cat) {
        expect($cat->is_adult)->toBeFalse();
        expect($cat->slug)->not->toBe('adulto');
    }
});

// ══════════════════════════════════════════════════════════
// IDEMPOTÊNCIA DO SEEDER
// ══════════════════════════════════════════════════════════
test('seeder de categorias é idempotente — não duplica registros', function () {
    $countBefore = Category::count();

    $this->artisan('db:seed', ['--class' => 'CategorySeeder']);

    $countAfter = Category::count();
    expect($countAfter)->toBe($countBefore);
});

// ══════════════════════════════════════════════════════════
// BUSCA
// ══════════════════════════════════════════════════════════
test('consegue buscar categoria por slug', function () {
    $found = Category::where('slug', 'escritorio')->first();
    expect($found)->not->toBeNull();
    expect($found->name)->toBe('Escritório');
});

test('consegue listar todas as categorias não adultas', function () {
    $safeCategories = Category::where('is_adult', false)->get();

    expect($safeCategories->count())->toBeGreaterThanOrEqual(4);
    foreach ($safeCategories as $cat) {
        expect($cat->is_adult)->toBeFalse();
    }
});