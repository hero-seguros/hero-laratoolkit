<?php

namespace HeroLaraToolkit\Tests;

use HeroLaraToolkit\Providers\HeroLaraToolkitServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanGeneratedFiles();
    }

    protected function tearDown(): void
    {
        $this->cleanGeneratedFiles();

        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [
            HeroLaraToolkitServiceProvider::class,
        ];
    }

    protected function appPath(string $relative = ''): string
    {
        return $this->app->path($relative);
    }

    protected function basePath(string $relative = ''): string
    {
        return $this->app->basePath($relative);
    }

    protected function assertFileContains(string $needle, string $path): void
    {
        $this->assertFileExists($path);
        $this->assertStringContainsString($needle, file_get_contents($path));
    }

    private function cleanGeneratedFiles(): void
    {
        $files = new Filesystem();

        foreach (['app', 'tests/Feature', 'tests/Unit'] as $dir) {
            $full = $this->basePath($dir);

            if (! is_dir($full)) {
                continue;
            }

            foreach ($files->directories($full) as $subdir) {
                $files->deleteDirectory($subdir);
            }

            foreach ($files->files($full) as $file) {
                $files->delete($file->getPathname());
            }
        }
    }
}
