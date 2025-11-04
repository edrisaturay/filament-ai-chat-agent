<?php

namespace EdrisaTuray\FilamentAiChatAgent\Support;

use EdrisaTuray\FilamentAiChatAgent\Support\Contracts\AiProviderContract;
use Illuminate\Support\Facades\Log;

abstract class GPTChat
{
    protected array $messages = [];
    protected ?AiProviderContract $provider = null;

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
     * Send the chat request to the AI provider.
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
            $formattedMessage = [
                'role' => $message['role'] ?? 'user',
            ];

            // Handle function messages - they require 'name' field
            if (($message['role'] ?? '') === 'function') {
                $formattedMessage['name'] = $message['name'] ?? '';
                $formattedMessage['content'] = $message['content'] ?? '';
            } 
            // Handle assistant messages with function calls
            elseif (($message['role'] ?? '') === 'assistant' && isset($message['function_call'])) {
                $formattedMessage['function_call'] = $message['function_call'];
                $formattedMessage['content'] = $message['content'] ?? null;
            } 
            // Regular messages
            else {
                $formattedMessage['content'] = $message['content'] ?? '';
            }

            $messages[] = $formattedMessage;
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
     * Get the AI provider instance.
     *
     * @return AiProviderContract
     */
    protected function getProvider(): AiProviderContract
    {
        if ($this->provider === null) {
            $this->provider = AiProviderFactory::make();
        }

        return $this->provider;
    }

    /**
     * Make the AI API request using the configured provider.
     *
     * @param array $payload
     * @return array
     * @throws \RuntimeException
     */
    protected function makeOpenAIRequest(array $payload): array
    {
        try {
            return $this->getProvider()->makeRequest($payload);
        } catch (\RuntimeException $e) {
            Log::error('AI Provider Error', [
                'provider' => get_class($this->getProvider()),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
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

