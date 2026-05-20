<?php

it('generates an adapter without --domain', function () {
    $this->artisan('make:adapter', ['name' => 'ZipCode'])->assertSuccessful();

    $path = $this->appPath('Adapters/ZipCodeAdapter.php');

    $this->assertFileExists($path);
    $this->assertFileContains('namespace App\Adapters;', $path);
    $this->assertFileContains('class ZipCodeAdapter', $path);
    $this->assertFileContains('use GuzzleHttp\Client;', $path);
});

it('generates an adapter under a domain folder with --domain', function () {
    $this->artisan('make:adapter', ['name' => 'Buson', '--domain' => 'Travel'])->assertSuccessful();

    $path = $this->appPath('Adapters/Travel/BusonAdapter.php');

    $this->assertFileExists($path);
    $this->assertFileContains('namespace App\Adapters\Travel;', $path);
});
