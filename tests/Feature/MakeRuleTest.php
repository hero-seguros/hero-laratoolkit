<?php

it('places a rule under the domain subfolder', function () {
    $this->artisan('make:rule', [
        'name' => 'IsValidCpf',
        '--domain' => 'Customer',
    ])->assertSuccessful();

    $path = $this->appPath('Rules/Customer/IsValidCpf.php');

    $this->assertFileExists($path);
    $this->assertFileContains('namespace App\Rules\Customer;', $path);
});

it('falls back to flat path without --domain', function () {
    $this->artisan('make:rule', ['name' => 'FlatRule'])->assertSuccessful();

    $this->assertFileExists($this->appPath('Rules/FlatRule.php'));
});
