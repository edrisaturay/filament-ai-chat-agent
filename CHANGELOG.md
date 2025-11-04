# Changelog

All notable changes to `filament-ai-chat-agent` will be documented in this file.

## Fork Notice

This package is a fork of [likeabas/filament-chatgpt-agent](https://github.com/likeabas/filament-chatgpt-agent) originally created by [Bas Schleijpen](https://github.com/likeabas). This version is maintained by Edrisa Turay.

## [Unreleased]

## [2.0.5] - 2024-XX-XX

### Added
- Integrated OpenAI API functionality directly into the package
- Created `GPTChat` base class with full OpenAI Chat Completions API support
- Support for function calling with recursive responses
- Direct API key configuration from config or `.env` files

### Changed
- Removed dependency on `maltekuhr/laravel-gpt` package
- Package now works independently without external dependencies
- Updated README with GPT Functions example and documentation

### Removed
- External dependency on `maltekuhr/laravel-gpt`
- Dependency check script (`scripts/check-dependency.php`)
- Troubleshooting documentation file (`TROUBLESHOOTING.md`)
