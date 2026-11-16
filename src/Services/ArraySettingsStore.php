<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Services;

use Djshellshoxxx\LibreNMSUtilitySuite\Contracts\SettingsStore;

final class ArraySettingsStore implements SettingsStore
{
    public function __construct(private array $values = []) {}
    public function get(string $key): mixed { return $this->values[$key] ?? null; }
    public function set(string $key, mixed $value): void { $this->values[$key] = $value; }
    public function all(): array { return $this->values; }
}
