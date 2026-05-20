<?php

namespace HeroLaraToolkit\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:helper')]
class MakeHelperCommand extends GeneratorCommand
{
    protected $name = 'make:helper';

    protected $description = 'Create a new static helper class';

    protected $type = 'Helper';

    protected function getStub()
    {
        return __DIR__ . '/stubs/helper.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Helpers';
    }

    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->laravel['path'] . '/' . str_replace('\\', '/', $name) . 'Helper.php';
    }
}
