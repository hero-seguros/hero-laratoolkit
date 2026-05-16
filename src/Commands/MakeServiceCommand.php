<?php

namespace HeroLaraToolkit\Commands;

use HeroLaraToolkit\Commands\Concerns\HasDomainOption;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:service')]
class MakeServiceCommand extends GeneratorCommand
{
    use HasDomainOption;

    protected $signature = 'make:service {name : The service action name (e.g. Create, Update)} {--domain= : Domain folder under app/Services (required)}';

    protected $description = 'Create a new Service class scoped to a domain';

    protected $type = 'Service';

    public function handle()
    {
        if (! $this->domain()) {
            $this->components->error('The --domain option is required (e.g. make:service Create --domain=Order).');

            return self::FAILURE;
        }

        return parent::handle();
    }

    protected function getStub()
    {
        return __DIR__ . '/stubs/service.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Services\\' . $this->domain();
    }

    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->laravel['path'] . '/' . str_replace('\\', '/', $name) . 'Service.php';
    }

    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }
}
