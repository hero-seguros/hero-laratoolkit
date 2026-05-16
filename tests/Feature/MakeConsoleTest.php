<?php

it('places a console command under the domain subfolder', function () {
    $this->artisan('make:command', [
        'name' => 'SyncOrdersCommand',
        '--domain' => 'Order',
    ])->assertSuccessful();

    $path = $this->appPath('Console/Commands/Order/SyncOrdersCommand.php');

    $this->assertFileExists($path);
    $this->assertFileContains('namespace App\Console\Commands\Order;', $path);
});

it('falls back to flat path without --domain', function () {
    $this->artisan('make:command', ['name' => 'LegacyCommand'])->assertSuccessful();

    $this->assertFileExists($this->appPath('Console/Commands/LegacyCommand.php'));
});
