<?php

it('boots the testbench app with the toolkit provider', function () {
    expect($this->app)->not->toBeNull()
        ->and($this->app->getProviders(\HeroLaraToolkit\Providers\HeroLaraToolkitServiceProvider::class))
        ->not->toBeEmpty();
});
