<?php

it('generates interface, implementation, and provider on first run', function () {
    $this->artisan('make:repository', ['name' => 'Order'])->assertSuccessful();

    $interface = $this->appPath('Contracts/Repositories/OrderRepositoryInterface.php');
    $impl = $this->appPath('Repositories/OrderRepository.php');
    $provider = $this->appPath('Providers/RepositoryServiceProvider.php');

    $this->assertFileExists($interface);
    $this->assertFileContains('namespace App\Contracts\Repositories;', $interface);
    $this->assertFileContains('interface OrderRepositoryInterface', $interface);

    $this->assertFileExists($impl);
    $this->assertFileContains('namespace App\Repositories;', $impl);
    $this->assertFileContains('class OrderRepository extends AbstractRepository implements OrderRepositoryInterface', $impl);
    $this->assertFileContains('use HeroLaraToolkit\Abstractions\AbstractRepository;', $impl);
    $this->assertFileContains('use App\Contracts\Repositories\OrderRepositoryInterface;', $impl);
    $this->assertFileContains('protected string $modelClass = Order::class;', $impl);

    $this->assertFileExists($provider);
    $this->assertFileContains('class RepositoryServiceProvider', $provider);
    $this->assertFileContains('\App\Contracts\Repositories\OrderRepositoryInterface::class => \App\Repositories\OrderRepository::class,', $provider);
});

it('appends to existing provider on subsequent runs', function () {
    $this->artisan('make:repository', ['name' => 'Order'])->assertSuccessful();
    $this->artisan('make:repository', ['name' => 'Customer'])->assertSuccessful();

    $provider = $this->appPath('Providers/RepositoryServiceProvider.php');
    $contents = file_get_contents($provider);

    expect(substr_count($contents, '::class =>'))->toBe(2)
        ->and($contents)->toContain('OrderRepositoryInterface::class => \App\Repositories\OrderRepository::class')
        ->and($contents)->toContain('CustomerRepositoryInterface::class => \App\Repositories\CustomerRepository::class');
});

it('is idempotent: repeating the same repository does not duplicate the bind', function () {
    $this->artisan('make:repository', ['name' => 'Order'])->assertSuccessful();
    $this->artisan('make:repository', ['name' => 'Order', '--force' => true])->assertSuccessful();

    $provider = $this->appPath('Providers/RepositoryServiceProvider.php');
    $contents = file_get_contents($provider);

    expect(substr_count($contents, 'OrderRepositoryInterface::class =>'))->toBe(1);
});

it('refuses to overwrite existing files without --force', function () {
    $this->artisan('make:repository', ['name' => 'Order'])->assertSuccessful();

    // Mutate the impl so we can detect overwrite.
    $impl = $this->appPath('Repositories/OrderRepository.php');
    file_put_contents($impl, "<?php\n// custom edits\n");

    $this->artisan('make:repository', ['name' => 'Order'])->assertSuccessful();

    expect(file_get_contents($impl))->toContain('// custom edits');
});
