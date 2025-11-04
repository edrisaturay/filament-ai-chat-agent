<?php

namespace EdrisaTuray\FilamentAiChatAgent;

use EdrisaTuray\FilamentAiChatAgent\Support\GPTChat;

class AiChatChat extends GPTChat
{
    /**
     * The message which explains the assistant what to do and which rules to follow.
     *
     * @return string|null
     */
    public function systemMessage(): ?string
    {
        return filament('ai-chat-agent')->getSystemMessage();
    }

    /**
     * The functions which are available to the assistant. The functions must be
     * an array of classes (e.g. [new SaveSentimentGPTFunction()]). The functions
     * must extend the GPTFunction class.
     *
     * @return array|null
     */
    public function functions(): ?array
    {
        return filament('ai-chat-agent')->getFunctions();
    }

    /**
     * The function call method can force the model to call a specific function or
     * force the model to answer with a message. If you return with the class name
     * e.g. SaveSentimentGPTFunction::class the model will call the function. If
     * you return with false the model will answer with a message. If you return
     * with null or true the model will decide if it should call a function or
     * answer with a message.
     *
     * @return string|bool|null
     */
    public function functionCall(): string|bool|null
    {
        return null;
    }

    public function model(): string
    {
        return filament('ai-chat-agent')->getModel();
    }

    public function temperature(): ?float
    {
        return filament('ai-chat-agent')->getTemperature();
    }

    public function maxTokens(): ?int
    {
        return filament('ai-chat-agent')->getMaxTokens();
    }

}
