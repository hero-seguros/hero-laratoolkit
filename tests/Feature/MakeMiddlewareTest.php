<?php

it('places middleware under the domain subfolder when requested', function () {
    $this->artisan('make:middleware', [
        'name' => 'EnsureOrderAccess',
        '--domain' => 'Order',
    ])->assertSuccessful();

    $path = $this->appPath('Http/Middleware/Order/EnsureOrderAccess.php');

    $this->assertFileExists($path);
    $this->assertFileContains('namespace App\Http\Middleware\Order;', $path);
});

it('falls back to flat path without --domain', function () {
    $this->artisan('make:middleware', ['name' => 'FlatMiddleware'])->assertSuccessful();

    $this->assertFileExists($this->appPath('Http/Middleware/FlatMiddleware.php'));
});
