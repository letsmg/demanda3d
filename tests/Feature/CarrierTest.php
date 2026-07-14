<?php

use App\Enums\UserAccessLevel;
use App\Models\Carrier;
use App\Models\CarrierCoverageRange;
use App\Models\CarrierTenantAgreement;
use App\Models\User;
use App\Services\EncryptionService;


beforeEach(function () {
    $this->seller = User::factory()->seller1()->create();
    $this->tenant = $this->seller->tenant()->create([
        'company_name_encrypted' => makeEncr('Mgmt Co')['encrypted'],
        'company_name_hash'      => makeEncr('Mgmt Co')['hash'],
        'fantasy_name'           => 'Mgmt Co',
        'fantasy_slug'           => 'mgmt-co',
        'document'               => '12.345.678/0001-90',
        'city'                   => 'São Paulo',
        'state'                  => 'SP',
        'zipcode'                => '01000-000',
        'active'                 => true,
    ]);
});

test('carrier is linked 1:1 with user via user_id', function () {
    $user = User::factory()->carrier1()->create([
        'email' => 'transport@test.com',
    ]);

    $carrier = Carrier::create([
        'user_id'             => $user->id,
        'fantasy_name'        => 'Express Transportes',
        'document_type'       => 'cnpj',
        'document_encrypted'  => makeEncr('11.222.333/0001-44')['encrypted'],
        'document_hash'       => makeEncr('11.222.333/0001-44')['hash'],
        'address_encrypted'   => makeEncr('Av Test')['encrypted'],
        'phone_encrypted'     => makeEncr('1131234567')['encrypted'],
        'company_name_encrypted'=> makeEncr('Express Ltda')['encrypted'],
        'company_name_hash'     => makeEncr('Express Ltda')['hash'],
        'slug'                => Carrier::generateUniqueSlug('Express Transportes'),
    ]);

    expect($carrier->user)->not->toBeNull();
    expect($carrier->user->id)->toBe($user->id);
    expect($carrier->user->isCarrier())->toBeTrue();
    expect($carrier->email)->toBe('transport@test.com');
    expect($carrier->fantasy_name)->toBe('Express Transportes');
    expect($carrier->document_type)->toBe('cnpj');
});

test('carrier has coverage ranges', function () {
    $carrier = Carrier::factory()->withUser()->create();

    CarrierCoverageRange::create([
        'carrier_id' => $carrier->id,
        'title'      => 'Grande SP',
        'cep_start'  => '01000000',
        'cep_end'    => '09999999',
    ]);

    CarrierCoverageRange::create([
        'carrier_id' => $carrier->id,
        'title'      => 'Interior SP',
        'cep_start'  => '10000000',
        'cep_end'    => '19999999',
    ]);

    expect($carrier->coverageRanges)->toHaveCount(2);
    expect($carrier->doesCoverCep('05000000'))->toBeTrue();
    expect($carrier->doesCoverCep('15000000'))->toBeTrue();
    expect($carrier->doesCoverCep('25000000'))->toBeFalse();
});

test('carrier scope coversCep filters correctly', function () {
    $carrier1 = Carrier::factory()->withUser()->create();
    CarrierCoverageRange::create([
        'carrier_id' => $carrier1->id,
        'title'      => 'SP',
        'cep_start'  => '01000000',
        'cep_end'    => '09999999',
    ]);

    $carrier2 = Carrier::factory()->withUser()->create();
    CarrierCoverageRange::create([
        'carrier_id' => $carrier2->id,
        'title'      => 'RJ',
        'cep_start'  => '20000000',
        'cep_end'    => '28999999',
    ]);

    $spCarriers = Carrier::coversCep('05000000')->get();
    expect($spCarriers)->toHaveCount(1);
    expect($spCarriers->first()->id)->toBe($carrier1->id);

    $rjCarriers = Carrier::coversCep('22000000')->get();
    expect($rjCarriers)->toHaveCount(1);
    expect($rjCarriers->first()->id)->toBe($carrier2->id);
});

test('carrier tenant agreements flow', function () {
    $carrier = Carrier::factory()->withUser()->create();

    // Tenant convida carrier
    $agreement = CarrierTenantAgreement::create([
        'tenant_id'  => $this->tenant->id,
        'carrier_id' => $carrier->id,
        'status'     => CarrierTenantAgreement::STATUS_PENDING_CARRIER,
    ]);

    expect($agreement->isPending())->toBeTrue();
    expect($carrier->hasActiveAgreementWith($this->tenant->id))->toBeFalse();

    // Carrier aceita
    $agreement->activate();
    expect($agreement->fresh()->isActive())->toBeTrue();
    expect($carrier->hasActiveAgreementWith($this->tenant->id))->toBeTrue();

    // Tenant pode acessar carriers ativos
    $activeCarriers = $this->tenant->activeCarriers;
    expect($activeCarriers)->toHaveCount(1);
});

test('carrier scope available excludes inactive', function () {
    $active   = Carrier::factory()->withUser()->create(['is_active' => true]);
    $inactive = Carrier::factory()->withUser()->create(['is_active' => false]);

    $available = Carrier::available()->get();
    expect($available)->toHaveCount(1);
    expect($available->first()->id)->toBe($active->id);
});

test('carrier lgpd data is encrypted at rest', function () {
    $carrier = Carrier::factory()->withUser()->create([
        'document_encrypted' => makeEncr('12.345.678/0001-90')['encrypted'],
        'document_hash'      => makeEncr('12.345.678/0001-90')['hash'],
    ]);

    expect($carrier->document_hash)->toHaveLength(64);
    expect($carrier->document_encrypted)->not->toBe('12.345.678/0001-90');
    expect($carrier->document)->toBe('12.345.678/0001-90'); // Accessor descriptografa
});