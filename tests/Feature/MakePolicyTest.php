<?php

it('places a policy under the domain subfolder', function () {
    $this->artisan('make:policy', [
        'name' => 'OrderPolicy',
        '--domain' => 'Order',
    ])->assertSuccessful();

    $this->assertFileExists($this->appPath('Policies/Order/OrderPolicy.php'));
    $this->assertFileContains('namespace App\Policies\Order;', $this->appPath('Policies/Order/OrderPolicy.php'));
});

it('falls back to flat path without --domain', function () {
    $this->artisan('make:policy', ['name' => 'LegacyPolicy'])->assertSuccessful();

    $this->assertFileExists($this->appPath('Policies/LegacyPolicy.php'));
});
