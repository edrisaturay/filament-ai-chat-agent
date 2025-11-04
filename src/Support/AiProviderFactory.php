<?php

namespace EdrisaTuray\FilamentAiChatAgent\Support;

use EdrisaTuray\FilamentAiChatAgent\Support\Contracts\AiProviderContract;
use EdrisaTuray\FilamentAiChatAgent\Support\Providers\AzureOpenAiProvider;
use EdrisaTuray\FilamentAiChatAgent\Support\Providers\OpenAiProvider;
use InvalidArgumentException;

class AiProviderFactory
{
    /**
     * Create an AI provider instance based on configuration.
     *
     * @param string|null $provider
     * @return AiProviderContract
     * @throws InvalidArgumentException
     */
    public static function make(?string $provider = null): AiProviderContract
    {
        $provider = $provider ?: config('ai-chat-agent.provider') ?: env('FILAMENT_AI_CHAT_AGENT_PROVIDER', 'openai');

        return match (strtolower($provider)) {
            'openai' => new OpenAiProvider(),
            'azure' => new AzureOpenAiProvider(),
            'azure-openai' => new AzureOpenAiProvider(),
            default => throw new InvalidArgumentException(
                "Unsupported AI provider: {$provider}. Supported providers are: openai, azure, azure-openai"
            ),
        };
    }
}

