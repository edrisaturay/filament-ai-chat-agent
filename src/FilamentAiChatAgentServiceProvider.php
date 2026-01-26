<?php

namespace EdrisaTuray\FilamentAiChatAgent;

use EdrisaTuray\FilamentAiChatAgent\Components\AiChatAgent;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentAiChatAgentServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('ai-chat-agent')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews();
    }

    public function packageBooted(): void
    {
        Livewire::component('fi-ai-chat-agent', AiChatAgent::class);
    }
}
