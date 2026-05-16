<?php

it('places a job under the domain subfolder', function () {
    $this->artisan('make:job', [
        'name' => 'ProcessOrderJob',
        '--domain' => 'Order',
    ])->assertSuccessful();

    $path = $this->appPath('Jobs/Order/ProcessOrderJob.php');

    $this->assertFileExists($path);
    $this->assertFileContains('namespace App\Jobs\Order;', $path);
    $this->assertFileContains('implements ShouldQueue', $path);
});

it('preserves --sync flag with --domain', function () {
    $this->artisan('make:job', [
        'name' => 'SyncJob',
        '--sync' => true,
        '--domain' => 'Order',
    ])->assertSuccessful();

    $path = $this->appPath('Jobs/Order/SyncJob.php');

    $this->assertFileExists($path);
    // Native job.stub (used for --sync) does NOT implement ShouldQueue.
    expect(file_get_contents($path))->not->toContain('implements ShouldQueue');
});
