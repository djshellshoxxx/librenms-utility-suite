<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Core;

use Djshellshoxxx\LibreNMSUtilitySuite\Contracts\UtilityModule;
use Djshellshoxxx\LibreNMSUtilitySuite\Services\SettingsService;

final class ConfiguredModule implements UtilityModule
{
    public function __construct(private string $key, private string $name, private SettingsService $settings, private ?\Closure $booter = null) {}
    public function key(): string { return $this->key; }
    public function name(): string { return $this->name; }
    public function enabled(): bool { return $this->settings->moduleEnabled($this->key); }
    public function boot(): void { if ($this->enabled() && $this->booter) ($this->booter)(); }
}
