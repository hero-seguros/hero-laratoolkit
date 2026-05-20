<?php

namespace HeroLaraToolkit\Commands;

use HeroLaraToolkit\Commands\Concerns\HasDomainOption;
use Illuminate\Foundation\Console\TestMakeCommand as NativeTestMakeCommand;
use Symfony\Component\Console\Input\InputOption;

class MakeTestCommand extends NativeTestMakeCommand
{
    use HasDomainOption;

    /**
     * Map of our --type option values to (namespace_segment, is_unit) pairs.
     * The namespace segment is appended after the root "Tests" namespace, so
     * "Feature" maps to Tests\Feature and "Unit\\Controllers" maps to Tests\Unit\Controllers.
     * The is_unit flag drives the parent's stub selection (unit vs feature stub).
     */
    private const TYPE_MAP = [
        'feature'         => ['Feature', false],
        'unit-controller' => ['Unit\\Controllers', true],
        'unit-service'    => ['Unit\\Services', true],
        'unit-policy'     => ['Unit\\Policies', true],
        'unit-helper'     => ['Unit\\Helpers', true],
    ];

    public function handle()
    {
        if (! $this->option('pest') && ! $this->option('phpunit')) {
            $this->input->setOption('pest', true);
        }

        if ($type = $this->option('type')) {
            if (! array_key_exists($type, self::TYPE_MAP)) {
                $valid = implode(', ', array_keys(self::TYPE_MAP));
                $this->components->error("Invalid --type '{$type}'. Allowed: {$valid}.");

                return self::FAILURE;
            }

            if ($this->isUnitType($type)) {
                $this->input->setOption('unit', true);
            }

            if ($type === 'unit-helper' && $this->domain()) {
                $this->components->warn('Ignoring --domain for --type=unit-helper (helpers are flat in the architecture).');
            }
        }

        return parent::handle();
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        $type = $this->option('type');

        if (! $type) {
            return parent::getDefaultNamespace($rootNamespace);
        }

        [$segment, $unit] = self::TYPE_MAP[$type];

        $namespace = $rootNamespace . '\\' . $segment;

        if ($type !== 'unit-helper' && $this->domain()) {
            $namespace .= '\\' . $this->domain();
        }

        return $namespace;
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            $this->domainOptionDefinition(),
            ['type', null, InputOption::VALUE_REQUIRED, 'Test type: feature, unit-controller, unit-service, unit-policy, unit-helper'],
        ]);
    }

    private function isUnitType(string $type): bool
    {
        return self::TYPE_MAP[$type][1] ?? false;
    }
}
