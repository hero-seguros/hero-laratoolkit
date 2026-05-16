<?php

it('places an observer under the domain subfolder', function () {
    $this->artisan('make:observer', [
        'name' => 'OrderObserver',
        '--domain' => 'Order',
    ])->assertSuccessful();

    $path = $this->appPath('Observers/Order/OrderObserver.php');

    $this->assertFileExists($path);
    $this->assertFileContains('namespace App\Observers\Order;', $path);
});

it('falls back to flat path without --domain', function () {
    $this->artisan('make:observer', ['name' => 'FlatObserver'])->assertSuccessful();

    $this->assertFileExists($this->appPath('Observers/FlatObserver.php'));
});
