<?php

it('generates a service file with --domain', function () {
    $this->artisan('make:service', [
        'name' => 'Create',
        '--domain' => 'Order',
    ])->assertSuccessful();

    $path = $this->appPath('Services/Order/CreateService.php');

    $this->assertFileExists($path);
    $this->assertFileContains('namespace App\Services\Order;', $path);
    $this->assertFileContains('class CreateService', $path);
    $this->assertFileContains('public function execute', $path);
});

it('rejects calls without --domain', function () {
    $this->artisan('make:service', ['name' => 'Create'])->assertFailed();

    expect(is_file($this->appPath('Services/CreateService.php')))->toBeFalse();
});

it('studly-cases the domain name', function () {
    $this->artisan('make:service', [
        'name' => 'Create',
        '--domain' => 'order_items',
    ])->assertSuccessful();

    $this->assertFileExists($this->appPath('Services/OrderItems/CreateService.php'));
    $this->assertFileContains('namespace App\Services\OrderItems;', $this->appPath('Services/OrderItems/CreateService.php'));
});
