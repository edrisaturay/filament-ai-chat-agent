<?php

namespace EdrisaTuray\FilamentAiChatAgent\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class GPTChat
{
    protected array $messages = [];

    /**
     * The message which explains the assistant what to do and which rules to follow.
     *
     * @return string|null
     */
    abstract public function systemMessage(): ?string;

    /**
     * The functions which are available to the assistant.
     *
     * @return array|null
     */
    abstract public function functions(): ?array;

    /**
     * The function call method can force the model to call a specific function.
     *
     * @return string|bool|null
     */
    abstract public function functionCall(): string|bool|null;

    /**
     * The model to use for the chat.
     *
     * @return string
     */
    abstract public function model(): string;

    /**
     * The temperature setting for the model.
     *
     * @return float|null
     */
    abstract public function temperature(): ?float;

    /**
     * The maximum tokens for the response.
     *
     * @return int|null
     */
    abstract public function maxTokens(): ?int;

    /**
     * Load messages into the chat.
     *
     * @param array $messages
     * @return $this
     */
    public function loadMessages(array $messages): static
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * Add a message to the chat.
     *
     * @param string $content
     * @param string $role
     * @return $this
     */
    public function addMessage(string $content, string $role = 'user'): static
    {
        $this->messages[] = [
            'role' => $role,
            'content' => $content,
        ];

        return $this;
    }

    /**
     * Send the chat request to OpenAI.
     *
     * @return $this
     */
    public function send(): static
    {
        $systemMessage = $this->systemMessage();
        
        $messages = [];
        
        if ($systemMessage) {
            $messages[] = [
                'role' => 'system',
                'content' => $systemMessage,
            ];
        }

        foreach ($this->messages as $message) {
            $messages[] = [
                'role' => $message['role'] ?? 'user',
                'content' => $message['content'] ?? '',
            ];
        }

        $payload = [
            'model' => $this->model(),
            'messages' => $messages,
        ];

        if ($temperature = $this->temperature()) {
            $payload['temperature'] = $temperature;
        }

        if ($maxTokens = $this->maxTokens()) {
            $payload['max_tokens'] = $maxTokens;
        }

        $functions = $this->functions();
        if (!empty($functions)) {
            $payload['functions'] = $this->formatFunctions($functions);
            $payload['function_call'] = $this->formatFunctionCall($this->functionCall());
        }

        $response = $this->makeOpenAIRequest($payload);

        if (isset($response['choices'][0]['message'])) {
            $message = $response['choices'][0]['message'];
            
            // Handle function calls
            if (isset($message['function_call'])) {
                $functionResult = $this->handleFunctionCall($message['function_call'], $functions);
                
                // Add function call to messages
                $this->messages[] = [
                    'role' => 'assistant',
                    'content' => null,
                    'function_call' => $message['function_call'],
                ];
                
                // Add function result to messages
                $this->messages[] = [
                    'role' => 'function',
                    'name' => $message['function_call']['name'],
                    'content' => $functionResult,
                ];
                
                // Send again with function result (recursive call)
                return $this->send();
            }
            
            // Add assistant response to messages
            $this->messages[] = [
                'role' => 'assistant',
                'content' => $message['content'] ?? '',
            ];
        }

        return $this;
    }

    /**
     * Get the latest message from the chat.
     *
     * @return object|null
     */
    public function latestMessage(): ?object
    {
        $lastMessage = end($this->messages);
        
        if (!$lastMessage) {
            return null;
        }

        return (object) [
            'role' => $lastMessage['role'] ?? 'assistant',
            'content' => $lastMessage['content'] ?? '',
        ];
    }

    /**
     * Make the OpenAI API request.
     *
     * @param array $payload
     * @return array
     * @throws \Exception
     */
    protected function makeOpenAIRequest(array $payload): array
    {
        $apiKey = config('ai-chat-agent.openai_api_key') ?: env('OPENAI_API_KEY');
        $organization = config('ai-chat-agent.openai_organization') ?: env('OPENAI_ORGANIZATION');

        if (!$apiKey) {
            throw new \RuntimeException('OpenAI API key is not configured. Please set OPENAI_API_KEY in your .env file or config/ai-chat-agent.php');
        }

        $headers = [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ];

        if ($organization) {
            $headers['OpenAI-Organization'] = $organization;
        }

        $response = Http::withHeaders($headers)
            ->timeout(120)
            ->post('https://api.openai.com/v1/chat/completions', $payload);

        if ($response->failed()) {
            $error = $response->json();
            Log::error('OpenAI API Error', [
                'status' => $response->status(),
                'error' => $error,
            ]);
            
            throw new \RuntimeException(
                'OpenAI API Error: ' . ($error['error']['message'] ?? 'Unknown error')
            );
        }

        return $response->json();
    }

    /**
     * Format functions for OpenAI API.
     *
     * @param array $functions
     * @return array
     */
    protected function formatFunctions(array $functions): array
    {
        return collect($functions)->map(function ($function) {
            if (is_object($function) && method_exists($function, 'toArray')) {
                return $function->toArray();
            }
            
            if (is_array($function)) {
                return $function;
            }
            
            return null;
        })->filter()->values()->toArray();
    }

    /**
     * Format function call setting.
     *
     * @param string|bool|null $functionCall
     * @return string|array|null
     */
    protected function formatFunctionCall(string|bool|null $functionCall): string|array|null
    {
        if ($functionCall === false) {
            return 'none';
        }
        
        if ($functionCall === true || $functionCall === null) {
            return 'auto';
        }
        
        if (is_string($functionCall)) {
            return ['name' => $functionCall];
        }
        
        return 'auto';
    }

    /**
     * Handle function call execution.
     *
     * @param array $functionCall
     * @param array|null $functions
     * @return string
     */
    protected function handleFunctionCall(array $functionCall, ?array $functions): string
    {
        if (empty($functions)) {
            return json_encode(['error' => 'Function not available']);
        }

        $functionName = $functionCall['name'] ?? null;
        $arguments = json_decode($functionCall['arguments'] ?? '{}', true);

        foreach ($functions as $function) {
            if (is_object($function)) {
                $reflection = new \ReflectionClass($function);
                $method = $reflection->getMethod('execute');
                
                if ($method) {
                    $functionArray = $function->toArray();
                    if (($functionArray['name'] ?? null) === $functionName) {
                        try {
                            $result = $method->invoke($function, $arguments);
                            return is_string($result) ? $result : json_encode($result);
                        } catch (\Exception $e) {
                            Log::error('Function execution error', [
                                'function' => $functionName,
                                'error' => $e->getMessage(),
                            ]);
                            return json_encode(['error' => $e->getMessage()]);
                        }
                    }
                }
            }
        }

        return json_encode(['error' => 'Function not found']);
    }
}

