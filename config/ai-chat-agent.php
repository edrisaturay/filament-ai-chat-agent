<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Chat Agent Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the default configuration for the AI Chat Agent
    | package. You can publish this file using:
    |
    | php artisan vendor:publish --tag="ai-chat-agent-config"
    |
    */

    /*
    |--------------------------------------------------------------------------
    | OpenAI API Configuration
    |--------------------------------------------------------------------------
    |
    | Your OpenAI API key and organization ID. These should be set in your
    | .env file and will be read from there.
    |
    | Get your API key from: https://platform.openai.com/api-keys
    | Get your Organization ID from: https://platform.openai.com/account/org-settings
    |
    */

    'openai_api_key' => env('OPENAI_API_KEY'),

    'openai_organization' => env('OPENAI_ORGANIZATION'),

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable the AI Chat Agent. Can be a boolean or a closure
    | that returns a boolean. Defaults to checking if the user is authenticated.
    |
    */

    'enabled' => null, // null means auth()->check()

    /*
    |--------------------------------------------------------------------------
    | Bot Name
    |--------------------------------------------------------------------------
    |
    | The displayed name of the AI chat bot.
    |
    */

    'bot_name' => null, // null means uses translation file

    /*
    |--------------------------------------------------------------------------
    | Button Text
    |--------------------------------------------------------------------------
    |
    | The text displayed on the chat button.
    |
    */

    'button_text' => null, // null means uses translation file

    /*
    |--------------------------------------------------------------------------
    | Button Icon
    |--------------------------------------------------------------------------
    |
    | The icon for the chat button. Uses Heroicons.
    |
    */

    'button_icon' => 'heroicon-m-sparkles',

    /*
    |--------------------------------------------------------------------------
    | Sending Text
    |--------------------------------------------------------------------------
    |
    | The text displayed while sending a message.
    |
    */

    'sending_text' => null, // null means uses translation file

    /*
    |--------------------------------------------------------------------------
    | OpenAI Model
    |--------------------------------------------------------------------------
    |
    | The ChatGPT model to use. Options include:
    | - gpt-4o
    | - gpt-4o-mini
    | - gpt-4-turbo
    | - gpt-3.5-turbo
    |
    */

    'model' => 'gpt-4o-mini',

    /*
    |--------------------------------------------------------------------------
    | Temperature
    |--------------------------------------------------------------------------
    |
    | Controls the randomness of the AI's responses.
    | Range: 0.0 to 2.0. Lower values make responses more deterministic.
    |
    */

    'temperature' => 0.7,

    /*
    |--------------------------------------------------------------------------
    | Max Tokens
    |--------------------------------------------------------------------------
    |
    | Maximum number of tokens in the AI's response.
    | Set to null for no limit.
    |
    */

    'max_tokens' => null,

    /*
    |--------------------------------------------------------------------------
    | System Message
    |--------------------------------------------------------------------------
    |
    | System instructions for the AI bot. This defines how the AI should
    | behave and respond.
    |
    */

    'system_message' => '',

    /*
    |--------------------------------------------------------------------------
    | GPT Functions
    |--------------------------------------------------------------------------
    |
    | Array of GPTFunction classes that the AI can call.
    | These allow the AI to execute custom tasks within your application.
    |
    | Example:
    | 'functions' => [
    |     new App\GPT\Functions\YourCustomGPTFunction(),
    | ],
    |
    */

    'functions' => [],

    /*
    |--------------------------------------------------------------------------
    | Default Panel Width
    |--------------------------------------------------------------------------
    |
    | The default width of the chat panel.
    |
    */

    'default_panel_width' => '350px',

    /*
    |--------------------------------------------------------------------------
    | Page Watcher Enabled
    |--------------------------------------------------------------------------
    |
    | Enable the page watcher feature. When enabled, the AI will receive
    | the page content as context with each message.
    |
    | WARNING: This sends page content to ChatGPT. Use with caution.
    |
    */

    'page_watcher_enabled' => false,

    /*
    |--------------------------------------------------------------------------
    | Page Watcher Selector
    |--------------------------------------------------------------------------
    |
    | CSS selector for the element whose content should be sent to ChatGPT.
    |
    */

    'page_watcher_selector' => '.fi-page',

    /*
    |--------------------------------------------------------------------------
    | Page Watcher Message
    |--------------------------------------------------------------------------
    |
    | Custom message prefix for page watcher context.
    | Set to null to use the default translation.
    |
    */

    'page_watcher_message' => null,

    /*
    |--------------------------------------------------------------------------
    | Start Message
    |--------------------------------------------------------------------------
    |
    | Default message shown when the chat panel is opened.
    | Set to false to disable.
    |
    */

    'start_message' => false,

    /*
    |--------------------------------------------------------------------------
    | Logo URL
    |--------------------------------------------------------------------------
    |
    | Custom logo URL for the chat bot avatar.
    | Set to false to use the default GPT icon.
    |
    */

    'logo_url' => false,
];

