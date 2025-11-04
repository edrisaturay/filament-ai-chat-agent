<?php

namespace EdrisaTuray\FilamentAiChatAgent\Support\Contracts;

interface AiProviderContract
{
    /**
     * Make a chat completion request to the AI provider.
     *
     * @param array $payload
     * @return array
     * @throws \RuntimeException
     */
    public function makeRequest(array $payload): array;

    /**
     * Get the API endpoint URL.
     *
     * @return string
     */
    public function getEndpoint(): string;

    /**
     * Get the headers required for the API request.
     *
     * @return array
     */
    public function getHeaders(): array;

    /**
     * Validate the provider configuration.
     *
     * @return void
     * @throws \RuntimeException
     */
    public function validateConfig(): void;
}

