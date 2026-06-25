<?php

use App\Enums\UserAccessGroup;
use App\Enums\UserAccessLevel;

test('user access level values match clinerules spec', function () {
    expect(UserAccessLevel::OPERATIONAL->value)->toBe(0);
    expect(UserAccessLevel::MANAGEMENT->value)->toBe(1);
    expect(UserAccessLevel::CUSTOMER->value)->toBe(5);
    expect(UserAccessLevel::ADMIN->value)->toBe(10);
});

test('isStaff returns true for operational, management and admin', function () {
    expect(UserAccessLevel::OPERATIONAL->isStaff())->toBeTrue();
    expect(UserAccessLevel::MANAGEMENT->isStaff())->toBeTrue();
    expect(UserAccessLevel::ADMIN->isStaff())->toBeTrue();
});

test('isStaff returns false for customer', function () {
    expect(UserAccessLevel::CUSTOMER->isStaff())->toBeFalse();
});

test('canAccessFinancials returns true for management and admin only', function () {
    expect(UserAccessLevel::MANAGEMENT->canAccessFinancials())->toBeTrue();
    expect(UserAccessLevel::ADMIN->canAccessFinancials())->toBeTrue();
    expect(UserAccessLevel::OPERATIONAL->canAccessFinancials())->toBeFalse();
    expect(UserAccessLevel::CUSTOMER->canAccessFinancials())->toBeFalse();
});

test('canManageTenant returns true for management and admin only', function () {
    expect(UserAccessLevel::MANAGEMENT->canManageTenant())->toBeTrue();
    expect(UserAccessLevel::ADMIN->canManageTenant())->toBeTrue();
    expect(UserAccessLevel::OPERATIONAL->canManageTenant())->toBeFalse();
    expect(UserAccessLevel::CUSTOMER->canManageTenant())->toBeFalse();
});

test('group returns correct UserAccessGroup', function () {
    expect(UserAccessLevel::OPERATIONAL->group())->toBe(UserAccessGroup::STAFF);
    expect(UserAccessLevel::MANAGEMENT->group())->toBe(UserAccessGroup::STAFF);
    expect(UserAccessLevel::ADMIN->group())->toBe(UserAccessGroup::STAFF);
    expect(UserAccessLevel::CUSTOMER->group())->toBe(UserAccessGroup::CUSTOMER);
});

test('labels are descriptive', function () {
    expect(UserAccessLevel::OPERATIONAL->label())->toBe('Operational');
    expect(UserAccessLevel::MANAGEMENT->label())->toBe('Management');
    expect(UserAccessLevel::CUSTOMER->label())->toBe('Customer');
    expect(UserAccessLevel::ADMIN->label())->toBe('Administrator');
});

test('isAdmin only true for admin', function () {
    expect(UserAccessLevel::ADMIN->isAdmin())->toBeTrue();
    expect(UserAccessLevel::MANAGEMENT->isAdmin())->toBeFalse();
    expect(UserAccessLevel::OPERATIONAL->isAdmin())->toBeFalse();
    expect(UserAccessLevel::CUSTOMER->isAdmin())->toBeFalse();
});