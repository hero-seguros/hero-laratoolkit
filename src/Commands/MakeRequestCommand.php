<?php

namespace HeroLaraToolkit\Commands;

use HeroLaraToolkit\Commands\Concerns\HasDomainOption;
use Illuminate\Foundation\Console\RequestMakeCommand as NativeRequestMakeCommand;

class MakeRequestCommand extends NativeRequestMakeCommand
{
    use HasDomainOption;

    protected function getDefaultNamespace($rootNamespace)
    {
        return parent::getDefaultNamespace($rootNamespace) . $this->domainNamespaceSegment();
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [$this->domainOptionDefinition()]);
    }
}
