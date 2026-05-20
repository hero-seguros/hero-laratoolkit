<?php

it('places a resource under the domain subfolder', function () {
    $this->artisan('make:resource', [
        'name' => 'OrderResource',
        '--domain' => 'Order',
    ])->assertSuccessful();

    $path = $this->appPath('Http/Resources/Order/OrderResource.php');

    $this->assertFileExists($path);
    $this->assertFileContains('namespace App\Http\Resources\Order;', $path);
    $this->assertFileContains('public function toArray', $path);
});

it('preserves --collection flag with --domain', function () {
    $this->artisan('make:resource', [
        'name' => 'OrderCollection',
        '--collection' => true,
        '--domain' => 'Order',
    ])->assertSuccessful();

    $this->assertFileExists($this->appPath('Http/Resources/Order/OrderCollection.php'));
});
