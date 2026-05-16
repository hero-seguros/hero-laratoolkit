<?php

namespace HeroLaraToolkit\Commands;

use HeroLaraToolkit\Commands\Concerns\HasDomainOption;
use Illuminate\Foundation\Console\PolicyMakeCommand as NativePolicyMakeCommand;

class MakePolicyCommand extends NativePolicyMakeCommand
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
