<?php

it('places a request under the domain subfolder', function () {
    $this->artisan('make:request', [
        'name' => 'StoreOrderRequest',
        '--domain' => 'Order',
    ])->assertSuccessful();

    $path = $this->appPath('Http/Requests/Order/StoreOrderRequest.php');

    $this->assertFileExists($path);
    $this->assertFileContains('namespace App\Http\Requests\Order;', $path);
    $this->assertFileContains('public function rules', $path);
});

it('falls back to native flat path without --domain', function () {
    $this->artisan('make:request', ['name' => 'LegacyRequest'])->assertSuccessful();

    $this->assertFileExists($this->appPath('Http/Requests/LegacyRequest.php'));
});
