<?php

namespace EdrisaTuray\FilamentAiChatAgent;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Closure;

class AiChatAgentPlugin implements Plugin
{
    protected bool|Closure|null $enabled = null;
    protected string|Closure|null $botName = null;
    protected string|Closure|null $buttonText = null;
    protected string|Closure|null $buttonIcon = null;
    protected string|Closure|null $sendingText = null;
    protected string|Closure|null $model = null;
    protected float|Closure|null $temperature = null;
    protected int|Closure|null $maxTokens = null;
    protected string|Closure|null $systemMessage = null;
    protected array|Closure|null $functions = null;
    protected bool|Closure|null $pageWatcherEnabled = null;
    protected string|Closure|null $pageWatcherSelector = null;
    protected string|Closure|null $pageWatcherMessage = null;
    protected string|Closure|null $defaultPanelWidth = null;
    protected bool|string|Closure|null $startMessage = null;
    protected bool|string|Closure|null $logoUrl = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'ai-chat-agent';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->renderHook(
                'panels::body.end',
                fn () => view('ai-chat-agent::components.filament-ai-chat-agent'),
            );
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public function enabled(bool|Closure $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isEnabled(): bool
    {
        if (is_null($this->enabled)){
            $configEnabled = config('ai-chat-agent.enabled');
            if (!is_null($configEnabled)) {
                return is_callable($configEnabled) ? ($configEnabled)() : $configEnabled;
            }
            return auth()->check();
        }
        return is_callable($this->enabled) ? ($this->enabled)() : $this->enabled;
    }

    public function botName(string|Closure $name): static
    {
        $this->botName = $name;

        return $this;
    }

    public function getBotName(): string
    {
        if (is_callable($this->botName)) {
            return ($this->botName)();
        }

        if (!is_null($this->botName)) {
            return $this->botName;
        }

        $configBotName = config('ai-chat-agent.bot_name');
        if (!is_null($configBotName)) {
            return $configBotName;
        }

        return __('ai-chat-agent::translations.bot_name');
    }

    public function buttonText(string|Closure $text): static
    {
        $this->buttonText = $text;

        return $this;
    }

    public function getButtonText(): string
    {
        if (is_callable($this->buttonText)) {
            return ($this->buttonText)();
        }

        if (!is_null($this->buttonText)) {
            return $this->buttonText;
        }

        $configButtonText = config('ai-chat-agent.button_text');
        if (!is_null($configButtonText)) {
            return $configButtonText;
        }

        return __('ai-chat-agent::translations.button_text');
    }

    public function buttonIcon(string|Closure $icon): static
    {
        $this->buttonIcon = $icon;

        return $this;
    }

    public function getButtonIcon(): string
    {
        if (is_callable($this->buttonIcon)) {
            return ($this->buttonIcon)();
        }

        return $this->buttonIcon ?? config('ai-chat-agent.button_icon', 'heroicon-m-sparkles');
    }

    public function sendingText(string|Closure $text): static
    {
        $this->sendingText = $text;

        return $this;
    }

    public function getSendingText(): string
    {
        if (is_callable($this->sendingText)) {
            return ($this->sendingText)();
        }

        if (!is_null($this->sendingText)) {
            return $this->sendingText;
        }

        $configSendingText = config('ai-chat-agent.sending_text');
        if (!is_null($configSendingText)) {
            return $configSendingText;
        }

        return __('ai-chat-agent::translations.sending_text');
    }

    public function model(string|Closure $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getModel(): string
    {
        if (is_callable($this->model)) {
            return ($this->model)();
        }

        return $this->model ?? config('ai-chat-agent.model', 'gpt-4o-mini');
    }

    public function temperature(float|Closure $temperature): static
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function getTemperature(): ?float
    {
        if (is_callable($this->temperature)) {
            return ($this->temperature)();
        }

        return $this->temperature ?? config('ai-chat-agent.temperature', 0.7);
    }

    public function maxTokens(int|Closure $maxTokens): static
    {
        $this->maxTokens = $maxTokens;

        return $this;
    }

    public function getMaxTokens(): ?int
    {
        if (is_callable($this->maxTokens)) {
            return ($this->maxTokens)();
        }

        return $this->maxTokens ?? config('ai-chat-agent.max_tokens');
    }

    public function systemMessage(string|Closure $message): static
    {
        $this->systemMessage = $message;

        return $this;
    }

    public function getSystemMessage(): string
    {
        if (is_callable($this->systemMessage)) {
            return ($this->systemMessage)();
        }

        return $this->systemMessage ?? config('ai-chat-agent.system_message', '');
    }

    public function functions(array|Closure $functions): static
    {
        $this->functions = $functions;

        return $this;
    }

    public function getFunctions(): array
    {
        if (is_callable($this->functions)) {
            return ($this->functions)();
        }

        return $this->functions ?? config('ai-chat-agent.functions', []);
    }

    public function defaultPanelWidth(string|Closure $width): static
    {
        $this->defaultPanelWidth = $width;

        return $this;
    }

    public function getDefaultPanelWidth(): string
    {
        if (is_callable($this->defaultPanelWidth)) {
            return ($this->defaultPanelWidth)();
        }

        return $this->defaultPanelWidth ?? config('ai-chat-agent.default_panel_width', '350px');
    }

    public function pageWatcherEnabled(bool|Closure $enabled): static
    {
        $this->pageWatcherEnabled = $enabled;

        return $this;
    }

    public function isPageWatcherEnabled(): bool
    {
        if (is_null($this->pageWatcherEnabled)){
            return config('ai-chat-agent.page_watcher_enabled', false);
        }

        return is_callable($this->pageWatcherEnabled) ? ($this->pageWatcherEnabled)() : $this->pageWatcherEnabled;
    }

    public function pageWatcherSelector(string|Closure $selector): static
    {
        $this->pageWatcherSelector = $selector;

        return $this;
    }

    public function getPageWatcherSelector(): string
    {
        if (is_callable($this->pageWatcherSelector)) {
            return ($this->pageWatcherSelector)();
        }

        return $this->pageWatcherSelector ?? config('ai-chat-agent.page_watcher_selector', '.fi-page');
    }

    public function pageWatcherMessage(string|Closure|null $message): static
    {
        $this->pageWatcherMessage = $message;

        return $this;
    }

    public function getPageWatcherMessage(): string
    {
        if (is_callable($this->pageWatcherMessage)) {
            return ($this->pageWatcherMessage)();
        }

        if (!is_null($this->pageWatcherMessage)) {
            return $this->pageWatcherMessage;
        }

        $configMessage = config('ai-chat-agent.page_watcher_message');
        if (!is_null($configMessage)) {
            return $configMessage;
        }

        return __('ai-chat-agent::translations.page_watcher_message');
    }

    public function startMessage(string|bool|Closure $message): static
    {
        $this->startMessage = ($message === false || $message === '') ? false : $message;

        return $this;
    }

    public function getStartMessage(): string
    {
        if (is_callable($this->startMessage)) {
            return ($this->startMessage)();
        }

        return $this->startMessage ?? config('ai-chat-agent.start_message', false);
    }

    public function logoUrl(string|bool|Closure $url): static
    {
        $this->logoUrl = ($url === false || $url === '') ? false : $url;

        return $this;
    }

    public function getLogoUrl(): string
    {
        if (is_callable($this->logoUrl)) {
            return ($this->logoUrl)();
        }

        return $this->logoUrl ?? config('ai-chat-agent.logo_url', false);
    }
}
