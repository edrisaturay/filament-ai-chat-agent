<?php

namespace EdrisaTuray\FilamentAiChatAgent\Support\Contracts;

interface AiProviderContract
{
    /**
     * Make a chat completion request to the AI provider.
     *
     * @throws \RuntimeException
     */
    public function makeRequest(array $payload): array;

    /**
     * Get the API endpoint URL.
     */
    public function getEndpoint(): string;

    /**
     * Get the headers required for the API request.
     */
    public function getHeaders(): array;

    /**
     * Validate the provider configuration.
     *
     * @throws \RuntimeException
     */
    public function validateConfig(): void;
}
