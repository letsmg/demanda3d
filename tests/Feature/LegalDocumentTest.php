<?php

use App\Models\LegalDocument;

beforeEach(function () {
    if (LegalDocument::count() === 0) {
        $this->artisan('db:seed', ['--class' => 'LegalDocumentSeeder']);
    }
});

// ══════════════════════════════════════════════════════════
// INTEGRIDADE DOS DADOS
// ══════════════════════════════════════════════════════════
test('existem pelo menos 2 documentos legais (Termos + Privacidade)', function () {
    $count = LegalDocument::count();
    expect($count)->toBeGreaterThanOrEqual(2);
});

test('documentos possuem type válido', function () {
    $docs = LegalDocument::all();

    foreach ($docs as $doc) {
        expect($doc->type)->toBeIn([
            LegalDocument::TYPE_TERMS_OF_SERVICE,
            LegalDocument::TYPE_PRIVACY_POLICY,
        ]);
    }
});

test('cada documento possui título, conteúdo HTML e versão', function () {
    $docs = LegalDocument::all();

    foreach ($docs as $doc) {
        expect($doc->title)->not->toBeNull();
        expect($doc->title)->not->toBeEmpty();
        expect($doc->content_html)->not->toBeNull();
        expect($doc->content_html)->not->toBeEmpty();
        expect($doc->version)->toBeGreaterThanOrEqual(1);
        expect($doc->grace_period_days)->toBeGreaterThanOrEqual(0);
    }
});

test('termos de uso existem e contêm conteúdo HTML válido', function () {
    $terms = LegalDocument::where('type', LegalDocument::TYPE_TERMS_OF_SERVICE)->first();

    expect($terms)->not->toBeNull();
    expect($terms->content_html)->toContain('<');
    expect($terms->content_html)->toContain('>');
    expect(strlen($terms->content_html))->toBeGreaterThan(100);
});

test('política de privacidade existe e é distinta dos termos', function () {
    $privacy = LegalDocument::where('type', LegalDocument::TYPE_PRIVACY_POLICY)->first();

    expect($privacy)->not->toBeNull();
    expect($privacy->content_html)->not->toBe(
        LegalDocument::where('type', LegalDocument::TYPE_TERMS_OF_SERVICE)->first()->content_html
    );
});

// ══════════════════════════════════════════════════════════
// IDEMPOTÊNCIA DO SEEDER
// ══════════════════════════════════════════════════════════
test('seeder de documentos legais é idempotente — não duplica registros', function () {
    $countBefore = LegalDocument::count();

    $this->artisan('db:seed', ['--class' => 'LegalDocumentSeeder']);

    $countAfter = LegalDocument::count();
    expect($countAfter)->toBe($countBefore);
});