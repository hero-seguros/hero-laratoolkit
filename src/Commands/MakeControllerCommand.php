<?php

namespace HeroLaraToolkit\Commands;

use HeroLaraToolkit\Commands\Concerns\HasDomainOption;
use Illuminate\Routing\Console\ControllerMakeCommand as NativeControllerMakeCommand;

class MakeControllerCommand extends NativeControllerMakeCommand
{
    use HasDomainOption;

    protected function getDefaultNamespace($rootNamespace)
    {
        return parent::getDefaultNamespace($rootNamespace) . $this->domainNamespaceSegment();
    }

    protected function getStub()
    {
        if ($this->usesNativeStubFlag()) {
            return parent::getStub();
        }

        $local = $this->laravel->basePath('stubs/hero-controller.stub');

        return file_exists($local) ? $local : __DIR__ . '/stubs/controller.stub';
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [$this->domainOptionDefinition()]);
    }

    private function usesNativeStubFlag(): bool
    {
        foreach (['api', 'resource', 'model', 'parent', 'invokable', 'singleton', 'type'] as $flag) {
            if ($this->option($flag)) {
                return true;
            }
        }

        return false;
    }
}
