<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Contracts;

interface SettingsStore
{
    public function get(string $key): mixed;
    public function set(string $key, mixed $value): void;
}
