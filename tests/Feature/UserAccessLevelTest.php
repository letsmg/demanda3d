<?php

use App\Enums\UserAccessGroup;
use App\Enums\UserAccessLevel;

test('user access level values match clinerules spec', function () {
    expect(UserAccessLevel::SELLER_1->value)->toBe(1);
    expect(UserAccessLevel::SELLER_2->value)->toBe(2);
    expect(UserAccessLevel::CARRIER_1->value)->toBe(5);
    expect(UserAccessLevel::CARRIER_2->value)->toBe(6);
    expect(UserAccessLevel::ADMIN->value)->toBe(10);
    expect(UserAccessLevel::CUSTOMER->value)->toBe(15);
});

test('isSeller returns true for SELLER_1 and SELLER_2', function () {
    expect(UserAccessLevel::SELLER_1->isSeller())->toBeTrue();
    expect(UserAccessLevel::SELLER_2->isSeller())->toBeTrue();
    expect(UserAccessLevel::ADMIN->isSeller())->toBeFalse();
    expect(UserAccessLevel::CUSTOMER->isSeller())->toBeFalse();
    expect(UserAccessLevel::CARRIER_1->isSeller())->toBeFalse();
});

test('isCarrier returns true for CARRIER_1 and CARRIER_2', function () {
    expect(UserAccessLevel::CARRIER_1->isCarrier())->toBeTrue();
    expect(UserAccessLevel::CARRIER_2->isCarrier())->toBeTrue();
    expect(UserAccessLevel::ADMIN->isCarrier())->toBeFalse();
    expect(UserAccessLevel::CUSTOMER->isCarrier())->toBeFalse();
    expect(UserAccessLevel::SELLER_1->isCarrier())->toBeFalse();
});

test('canAccessFinancials returns true for SELLER_1 and admin only', function () {
    expect(UserAccessLevel::SELLER_1->canAccessFinancials())->toBeTrue();
    expect(UserAccessLevel::ADMIN->canAccessFinancials())->toBeTrue();
    expect(UserAccessLevel::SELLER_2->canAccessFinancials())->toBeFalse();
    expect(UserAccessLevel::CUSTOMER->canAccessFinancials())->toBeFalse();
    expect(UserAccessLevel::CARRIER_1->canAccessFinancials())->toBeFalse();
});

test('canManageTenant returns true for SELLER_1 and admin only', function () {
    expect(UserAccessLevel::SELLER_1->canManageTenant())->toBeTrue();
    expect(UserAccessLevel::ADMIN->canManageTenant())->toBeTrue();
    expect(UserAccessLevel::SELLER_2->canManageTenant())->toBeFalse();
    expect(UserAccessLevel::CUSTOMER->canManageTenant())->toBeFalse();
});

test('group returns correct UserAccessGroup', function () {
    expect(UserAccessLevel::SELLER_1->group())->toBe(UserAccessGroup::SELLERS);
    expect(UserAccessLevel::SELLER_2->group())->toBe(UserAccessGroup::SELLERS);
    expect(UserAccessLevel::CARRIER_1->group())->toBe(UserAccessGroup::CARRIERS);
    expect(UserAccessLevel::CARRIER_2->group())->toBe(UserAccessGroup::CARRIERS);
    expect(UserAccessLevel::ADMIN->group())->toBe(UserAccessGroup::PLATFORM_ADMIN);
    expect(UserAccessLevel::CUSTOMER->group())->toBe(UserAccessGroup::CUSTOMER);
});

test('labels are descriptive', function () {
    expect(UserAccessLevel::SELLER_1->label())->toBe('Vendedor Master');
    expect(UserAccessLevel::SELLER_2->label())->toBe('Vendedor Operacional');
    expect(UserAccessLevel::CARRIER_1->label())->toBe('Transportador Admin');
    expect(UserAccessLevel::CARRIER_2->label())->toBe('Transportador Motorista');
    expect(UserAccessLevel::ADMIN->label())->toBe('Administrador');
    expect(UserAccessLevel::CUSTOMER->label())->toBe('Cliente');
});

test('isAdmin only true for admin', function () {
    expect(UserAccessLevel::ADMIN->isAdmin())->toBeTrue();
    expect(UserAccessLevel::SELLER_1->isAdmin())->toBeFalse();
    expect(UserAccessLevel::SELLER_2->isAdmin())->toBeFalse();
    expect(UserAccessLevel::CUSTOMER->isAdmin())->toBeFalse();
    expect(UserAccessLevel::CARRIER_1->isAdmin())->toBeFalse();
});

test('staffPanelValues returns sellers + admin', function () {
    $values = UserAccessLevel::staffPanelValues();
    expect($values)->toContain(1);  // SELLER_1
    expect($values)->toContain(2);  // SELLER_2
    expect($values)->toContain(10); // ADMIN
    expect($values)->not->toContain(5);   // CARRIER_1
    expect($values)->not->toContain(15);  // CUSTOMER
});

test('sellerValues returns only SELLER_1 and SELLER_2', function () {
    $values = UserAccessLevel::sellerValues();
    expect($values)->toEqual([1, 2]);
});

test('carrierValues returns only CARRIER_1 and CARRIER_2', function () {
    $values = UserAccessLevel::carrierValues();
    expect($values)->toEqual([5, 6]);
});

test('User model isStaff works correctly', function () {
    $seller1  = App\Models\User::factory()->seller1()->make();
    $seller2  = App\Models\User::factory()->seller2()->make();
    $admin    = App\Models\User::factory()->admin()->make();
    $customer = App\Models\User::factory()->customer()->make();
    $carrier1 = App\Models\User::factory()->carrier1()->make();

    expect($seller1->isStaff())->toBeTrue();
    expect($seller2->isStaff())->toBeTrue();
    expect($admin->isStaff())->toBeTrue();
    expect($customer->isStaff())->toBeFalse();
    expect($carrier1->isStaff())->toBeFalse();
});