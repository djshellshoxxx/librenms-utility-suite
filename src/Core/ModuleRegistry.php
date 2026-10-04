<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Core;

use Djshellshoxxx\LibreNMSUtilitySuite\Contracts\UtilityModule;
use InvalidArgumentException;

final class ModuleRegistry
{
    private array $modules = [];

    public function __construct(iterable $modules)
    {
        foreach ($modules as $module) {
            if (! $module instanceof UtilityModule) throw new InvalidArgumentException('All modules must implement UtilityModule');
            $key = $module->key();
            if (isset($this->modules[$key])) throw new InvalidArgumentException("Duplicate module key: $key");
            $this->modules[$key] = $module;
        }
    }

    public function enabled(): array { return array_values(array_filter($this->modules, fn (UtilityModule $m) => $m->enabled())); }
    public function find(string $key): ?UtilityModule { return $this->modules[$key] ?? null; }
    public function all(): array { return array_values($this->modules); }
}
