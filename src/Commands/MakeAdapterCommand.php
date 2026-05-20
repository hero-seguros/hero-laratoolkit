<?php

namespace HeroLaraToolkit\Commands;

use HeroLaraToolkit\Commands\Concerns\HasDomainOption;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:adapter')]
class MakeAdapterCommand extends GeneratorCommand
{
    use HasDomainOption;

    protected $name = 'make:adapter';

    protected $description = 'Create a new HTTP adapter class (Guzzle-based)';

    protected $type = 'Adapter';

    protected function getStub()
    {
        return __DIR__ . '/stubs/adapter.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Adapters' . $this->domainNamespaceSegment();
    }

    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->laravel['path'] . '/' . str_replace('\\', '/', $name) . 'Adapter.php';
    }

    protected function getOptions()
    {
        return [$this->domainOptionDefinition()];
    }
}
