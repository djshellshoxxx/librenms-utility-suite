<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Services;

use Djshellshoxxx\LibreNMSUtilitySuite\Contracts\SettingsStore;

final class SettingsService
{
    public function __construct(private SettingsStore $store, private array $defaults = []) {}

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->store->get($key);
        return $value ?? ($this->defaults[$key] ?? $default);
    }

    public function set(string $key, mixed $value): void
    {
        $this->store->set($key, $value);
    }

    public function moduleEnabled(string $moduleKey): bool
    {
        return $this->toBool($this->get("modules.$moduleKey.enabled", false));
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) return $value;
        if (is_int($value)) return $value !== 0;
        if (is_string($value)) return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
        return (bool) $value;
    }
}
