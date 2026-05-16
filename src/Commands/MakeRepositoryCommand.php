<?php

namespace HeroLaraToolkit\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:repository')]
class MakeRepositoryCommand extends Command
{
    protected $signature = 'make:repository {name : Model name (e.g. Order, Customer)} {--force : Overwrite existing files}';

    protected $description = 'Create a repository (interface + implementation) and register its bind in RepositoryServiceProvider';

    private const BIND_MARKER = '// {{ marker }}';

    public function __construct(private readonly Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $force = (bool) $this->option('force');

        $interfacePath = app_path("Contracts/Repositories/{$name}RepositoryInterface.php");
        $implPath = app_path("Repositories/{$name}Repository.php");
        $providerPath = app_path('Providers/RepositoryServiceProvider.php');

        $this->writeStub(
            $interfacePath,
            __DIR__ . '/stubs/repository-interface.stub',
            ['{{ namespace }}' => 'App\\Contracts\\Repositories', '{{ class }}' => "{$name}RepositoryInterface"],
            $force,
            'Repository interface',
        );

        $this->writeStub(
            $implPath,
            __DIR__ . '/stubs/repository.stub',
            [
                '{{ namespace }}' => 'App\\Repositories',
                '{{ class }}' => "{$name}Repository",
                '{{ name }}' => $name,
            ],
            $force,
            'Repository',
        );

        $this->upsertProvider($providerPath, $name);

        return self::SUCCESS;
    }

    private function writeStub(string $target, string $stub, array $replacements, bool $force, string $label): void
    {
        if ($this->files->exists($target) && ! $force) {
            $this->components->warn("{$label} already exists at {$target} (use --force to overwrite).");

            return;
        }

        $this->files->ensureDirectoryExists(dirname($target));
        $content = strtr($this->files->get($stub), $replacements);
        $this->files->put($target, $content);

        $this->components->info("{$label} created: {$target}");
    }

    private function upsertProvider(string $providerPath, string $name): void
    {
        $interfaceFqcn = "\\App\\Contracts\\Repositories\\{$name}RepositoryInterface";
        $concreteFqcn = "\\App\\Repositories\\{$name}Repository";
        $bindLine = "        {$interfaceFqcn}::class => {$concreteFqcn}::class,";

        if (! $this->files->exists($providerPath)) {
            $this->files->ensureDirectoryExists(dirname($providerPath));
            $template = $this->files->get(__DIR__ . '/stubs/repository-service-provider.stub');
            $this->files->put($providerPath, str_replace(self::BIND_MARKER, trim($bindLine), $template));
            $this->components->info("RepositoryServiceProvider created with bind for {$name}.");

            return;
        }

        $contents = $this->files->get($providerPath);

        if (str_contains($contents, "{$interfaceFqcn}::class")) {
            $this->components->info("Bind for {$name} already present in RepositoryServiceProvider.");

            return;
        }

        // Insert before the marker comment if present (preferred), otherwise before the closing `];` of $repositories array.
        if (str_contains($contents, self::BIND_MARKER)) {
            $updated = str_replace(self::BIND_MARKER, $bindLine . "\n        " . self::BIND_MARKER, $contents);
        } elseif (preg_match('/(protected\s+array\s+\$repositories\s*=\s*\[)([\s\S]*?)(\];)/', $contents, $matches)) {
            $body = rtrim($matches[2]);
            $newBody = $body === '' ? "\n{$bindLine}\n    " : "{$body}\n{$bindLine}\n    ";
            $updated = str_replace($matches[0], $matches[1] . $newBody . $matches[3], $contents);
        } else {
            $this->components->warn("Could not locate \$repositories array in {$providerPath}; please add manually: {$bindLine}");

            return;
        }

        $this->files->put($providerPath, $updated);
        $this->components->info("RepositoryServiceProvider updated with bind for {$name}.");
    }
}
