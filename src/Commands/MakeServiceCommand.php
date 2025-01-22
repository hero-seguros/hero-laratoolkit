<?php

namespace HeroLaraToolkit\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:service')]
class MakeServiceCommand extends GeneratorCommand
{
    protected $signature = 'make:service {name} {domain}';

    protected $description = 'Create a new Service class';

    protected $type = 'Service';

    public function handle()
    {
        parent::handle();
    }

    protected function getStub()
    {
        return __DIR__ . '/stubs/service.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        $domain = ucfirst($this->argument('domain'));

        return $rootNamespace . '\\Services\\' . $domain;
    }

    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->laravel['path'] . '/' . str_replace('\\', '/', $name) . 'Service.php';
    }

    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());
        $stub = $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);

        return $stub;
    }
}