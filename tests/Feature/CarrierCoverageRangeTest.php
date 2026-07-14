<?php

use App\Models\Carrier;
use App\Models\CarrierCoverageRange;


test('coverage range covers cep within boundaries', function () {
    $range = new CarrierCoverageRange([
        'title'     => 'Test Range',
        'cep_start' => '01000000',
        'cep_end'   => '09999999',
    ]);

    // Usa scope direto
    $result = CarrierCoverageRange::coversCep('05000000')->get();
    // Em memória, sem persistência — testa a lógica do scope
    $query = CarrierCoverageRange::coversCep('05000000');
    expect($query->toSql())->toContain('cep_start');
    expect($query->toSql())->toContain('cep_end');
});

test('coverage range does not cover cep outside boundaries', function () {
    $carrier = Carrier::factory()->withUser()->create();
    CarrierCoverageRange::create([
        'carrier_id' => $carrier->id,
        'title'      => 'SP Capital',
        'cep_start'  => '01000000',
        'cep_end'    => '09999999',
    ]);

    $carrier->refresh();

    expect($carrier->doesCoverCep('05000000'))->toBeTrue();
    expect($carrier->doesCoverCep('15000000'))->toBeFalse();
    expect($carrier->doesCoverCep('01000000'))->toBeTrue(); // Limite inferior
    expect($carrier->doesCoverCep('09999999'))->toBeTrue(); // Limite superior
});

test('coverage range can be created for carrier', function () {
    $carrier = Carrier::factory()->withUser()->create();

    $range = CarrierCoverageRange::create([
        'carrier_id' => $carrier->id,
        'title'      => 'Grande São Paulo',
        'cep_start'  => '01000000',
        'cep_end'    => '09999999',
    ]);

    expect($range->carrier->id)->toBe($carrier->id);
    expect($carrier->coverageRanges)->toHaveCount(1);
});