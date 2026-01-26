<?php

namespace EdrisaTuray\FilamentAiChatAgent\Support\Providers;

use EdrisaTuray\FilamentAiChatAgent\Support\Contracts\AiProviderContract;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiProvider implements AiProviderContract
{
    protected string $apiKey;

    protected ?string $organization;

    public function __construct()
    {
        $this->apiKey = config('ai-chat-agent.openai_api_key') ?: env('OPENAI_API_KEY', '');
        $this->organization = config('ai-chat-agent.openai_organization') ?: env('OPENAI_ORGANIZATION');
    }

    /**
     * Make a chat completion request to OpenAI.
     *
     * @throws \RuntimeException
     */
    public function makeRequest(array $payload): array
    {
        $this->validateConfig();

        $response = Http::withHeaders($this->getHeaders())
            ->timeout(120)
            ->post($this->getEndpoint(), $payload);

        if ($response->failed()) {
            $error = $response->json();
            Log::error('OpenAI API Error', [
                'status' => $response->status(),
                'error' => $error,
            ]);

            throw new \RuntimeException(
                'OpenAI API Error: '.($error['error']['message'] ?? 'Unknown error')
            );
        }

        return $response->json();
    }

    /**
     * Get the OpenAI API endpoint URL.
     */
    public function getEndpoint(): string
    {
        return 'https://api.openai.com/v1/chat/completions';
    }

    /**
     * Get the headers required for the OpenAI API request.
     */
    public function getHeaders(): array
    {
        $headers = [
            'Authorization' => 'Bearer '.$this->apiKey,
            'Content-Type' => 'application/json',
        ];

        if ($this->organization) {
            $headers['OpenAI-Organization'] = $this->organization;
        }

        return $headers;
    }

    /**
     * Validate the OpenAI configuration.
     *
     * @throws \RuntimeException
     */
    public function validateConfig(): void
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException(
                'OpenAI API key is not configured. Please set OPENAI_API_KEY in your .env file or config/ai-chat-agent.php'
            );
        }
    }
}
