<?php

namespace HeroLaraToolkit\Commands;

use HeroLaraToolkit\Commands\Concerns\HasDomainOption;
use Illuminate\Routing\Console\MiddlewareMakeCommand as NativeMiddlewareMakeCommand;
use Symfony\Component\Console\Input\InputOption;

class MakeMiddlewareCommand extends NativeMiddlewareMakeCommand
{
    use HasDomainOption;

    protected function getDefaultNamespace($rootNamespace)
    {
        return parent::getDefaultNamespace($rootNamespace) . $this->domainNamespaceSegment();
    }

    protected function getOptions()
    {
        // Native middleware command has no getOptions() defined — its only option
        // is --force from GeneratorCommand. Add --domain alongside.
        return [$this->domainOptionDefinition()];
    }
}
