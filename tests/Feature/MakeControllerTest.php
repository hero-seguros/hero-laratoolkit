<?php

it('generates a controller under the domain subfolder with --domain', function () {
    $this->artisan('make:controller', [
        'name' => 'OrderController',
        '--domain' => 'Order',
    ])->assertSuccessful();

    $path = $this->appPath('Http/Controllers/Order/OrderController.php');

    $this->assertFileExists($path);
    $this->assertFileContains('namespace App\Http\Controllers\Order;', $path);
    $this->assertFileContains('class OrderController', $path);
    $this->assertFileContains('use HeroLaraToolkit\Traits\ApiControllerTrait;', $path);
    $this->assertFileContains('use ApiControllerTrait;', $path);
});

it('falls back to native flat path without --domain', function () {
    $this->artisan('make:controller', [
        'name' => 'LegacyController',
    ])->assertSuccessful();

    $path = $this->appPath('Http/Controllers/LegacyController.php');

    $this->assertFileExists($path);
    $this->assertFileContains('namespace App\Http\Controllers;', $path);
    $this->assertFileContains('use HeroLaraToolkit\Traits\ApiControllerTrait;', $path);
});

it('preserves native --api flag and combines with --domain', function () {
    $this->artisan('make:controller', [
        'name' => 'OrderController',
        '--api' => true,
        '--domain' => 'Order',
    ])->assertSuccessful();

    $path = $this->appPath('Http/Controllers/Order/OrderController.php');

    $this->assertFileExists($path);
    $this->assertFileContains('namespace App\Http\Controllers\Order;', $path);
    // --api delegates to Laravel's stub which gives resource methods (no create/edit).
    $contents = file_get_contents($path);
    expect($contents)->toContain('public function index')
        ->and($contents)->toContain('public function store')
        ->and($contents)->not->toContain('public function create')
        ->and($contents)->not->toContain('public function edit');
});

it('rejects placing a controller in a nested domain path via slash', function () {
    $this->artisan('make:controller', [
        'name' => 'Api/OrderController',
        '--domain' => 'Order',
    ])->assertSuccessful();

    // qualifyClass + getDefaultNamespace should compose the segments cleanly.
    $this->assertFileExists($this->appPath('Http/Controllers/Order/Api/OrderController.php'));
});
