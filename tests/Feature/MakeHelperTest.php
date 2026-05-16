<?php

it('generates a helper in app/Helpers', function () {
    $this->artisan('make:helper', ['name' => 'Currency'])->assertSuccessful();

    $path = $this->appPath('Helpers/CurrencyHelper.php');

    $this->assertFileExists($path);
    $this->assertFileContains('namespace App\Helpers;', $path);
    $this->assertFileContains('class CurrencyHelper', $path);
});

it('does not accept --domain', function () {
    // Symfony raises InvalidOptionException synchronously when an unknown option is passed.
    expect(fn () => $this->artisan('make:helper', ['name' => 'Currency', '--domain' => 'Anything'])->run())
        ->toThrow(\Symfony\Component\Console\Exception\InvalidOptionException::class);
});
