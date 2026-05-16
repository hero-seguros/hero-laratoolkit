<?php

namespace HeroLaraToolkit\Commands\Concerns;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

trait HasDomainOption
{
    protected function domainOptionDefinition(): array
    {
        return ['domain', null, InputOption::VALUE_OPTIONAL, 'Domain subfolder to place the generated file under.'];
    }

    protected function domain(): ?string
    {
        $raw = $this->option('domain');

        if ($raw === null || $raw === '') {
            return null;
        }

        return Str::studly($raw);
    }

    protected function domainNamespaceSegment(): string
    {
        $domain = $this->domain();

        return $domain ? '\\' . $domain : '';
    }
}
