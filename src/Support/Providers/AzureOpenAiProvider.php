<?php

namespace EdrisaTuray\FilamentAiChatAgent\Support\Providers;

use EdrisaTuray\FilamentAiChatAgent\Support\Contracts\AiProviderContract;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AzureOpenAiProvider implements AiProviderContract
{
    protected string $endpoint;

    protected string $apiKey;

    protected ?string $region;

    protected string $deploymentName;

    public function __construct()
    {
        $this->endpoint = config('ai-chat-agent.azure_openai_endpoint') ?: env('AZURE_OPENAI_ENDPOINT', '');
        $this->apiKey = config('ai-chat-agent.azure_openai_api_key') ?: env('AZURE_OPENAI_API_KEY', '');
        $this->region = config('ai-chat-agent.azure_openai_region') ?: env('AZURE_OPENAI_REGION');
        $this->deploymentName = config('ai-chat-agent.azure_openai_deployment_name') ?: env('AZURE_OPENAI_DEPLOYMENT_NAME', '');
    }

    /**
     * Make a chat completion request to Azure OpenAI.
     *
     * @throws \RuntimeException
     */
    public function makeRequest(array $payload): array
    {
        $this->validateConfig();

        // Azure OpenAI doesn't use the 'model' field in the payload
        // The deployment name is used in the URL instead
        // Remove model from payload if present
        unset($payload['model']);

        $response = Http::withHeaders($this->getHeaders())
            ->timeout(120)
            ->post($this->getEndpoint(), $payload);

        if ($response->failed()) {
            $error = $response->json();
            Log::error('Azure OpenAI API Error', [
                'status' => $response->status(),
                'error' => $error,
            ]);

            $errorMessage = 'Unknown error';
            if (isset($error['error'])) {
                if (is_string($error['error'])) {
                    $errorMessage = $error['error'];
                } elseif (isset($error['error']['message'])) {
                    $errorMessage = $error['error']['message'];
                }
            }

            throw new \RuntimeException('Azure OpenAI API Error: '.$errorMessage);
        }

        return $response->json();
    }

    /**
     * Get the Azure OpenAI API endpoint URL.
     */
    public function getEndpoint(): string
    {
        $baseUrl = rtrim($this->endpoint, '/');
        $deployment = $this->deploymentName;
        $apiVersion = config('ai-chat-agent.azure_openai_api_version', '2024-02-15-preview');

        return "{$baseUrl}/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";
    }

    /**
     * Get the headers required for the Azure OpenAI API request.
     */
    public function getHeaders(): array
    {
        $headers = [
            'api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];

        if ($this->region) {
            $headers['x-ms-region'] = $this->region;
        }

        return $headers;
    }

    /**
     * Validate the Azure OpenAI configuration.
     *
     * @throws \RuntimeException
     */
    public function validateConfig(): void
    {
        if (empty($this->endpoint)) {
            throw new \RuntimeException(
                'Azure OpenAI endpoint is not configured. Please set AZURE_OPENAI_ENDPOINT in your .env file or config/ai-chat-agent.php'
            );
        }

        if (empty($this->apiKey)) {
            throw new \RuntimeException(
                'Azure OpenAI API key is not configured. Please set AZURE_OPENAI_API_KEY in your .env file or config/ai-chat-agent.php'
            );
        }

        if (empty($this->deploymentName)) {
            throw new \RuntimeException(
                'Azure OpenAI deployment name is not configured. Please set AZURE_OPENAI_DEPLOYMENT_NAME in your .env file or config/ai-chat-agent.php'
            );
        }
    }
}
