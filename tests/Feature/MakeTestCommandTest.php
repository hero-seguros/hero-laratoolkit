<?php

it('places a feature test under tests/Feature/{Domain}', function () {
    // Pest feature stubs are procedural and don't declare a namespace — we only verify path placement.
    $this->artisan('make:test', [
        'name' => 'CreateOrderTest',
        '--type' => 'feature',
        '--domain' => 'Order',
    ])->assertSuccessful();

    $this->assertFileExists($this->basePath('tests/Feature/Order/CreateOrderTest.php'));
});

it('places a unit-service test under tests/Unit/Services/{Domain}', function () {
    $this->artisan('make:test', [
        'name' => 'CreateServiceTest',
        '--type' => 'unit-service',
        '--domain' => 'Order',
    ])->assertSuccessful();

    $this->assertFileExists($this->basePath('tests/Unit/Services/Order/CreateServiceTest.php'));
});

it('places a unit-controller test under tests/Unit/Controllers/{Domain}', function () {
    $this->artisan('make:test', [
        'name' => 'OrderControllerTest',
        '--type' => 'unit-controller',
        '--domain' => 'Order',
    ])->assertSuccessful();

    $this->assertFileExists($this->basePath('tests/Unit/Controllers/Order/OrderControllerTest.php'));
});

it('places a unit-policy test under tests/Unit/Policies/{Domain}', function () {
    $this->artisan('make:test', [
        'name' => 'OrderPolicyTest',
        '--type' => 'unit-policy',
        '--domain' => 'Order',
    ])->assertSuccessful();

    $this->assertFileExists($this->basePath('tests/Unit/Policies/Order/OrderPolicyTest.php'));
});

it('places a unit-helper test under tests/Unit/Helpers (no domain)', function () {
    $this->artisan('make:test', [
        'name' => 'FormatHelperTest',
        '--type' => 'unit-helper',
    ])->assertSuccessful();

    $this->assertFileExists($this->basePath('tests/Unit/Helpers/FormatHelperTest.php'));
});

it('rejects an invalid --type', function () {
    $this->artisan('make:test', [
        'name' => 'BadTest',
        '--type' => 'bogus',
    ])->assertFailed();
});

it('falls back to native behaviour without --type', function () {
    $this->artisan('make:test', ['name' => 'LegacyTest'])->assertSuccessful();

    $this->assertFileExists($this->basePath('tests/Feature/LegacyTest.php'));
});
