<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Contracts;

interface UtilityModule
{
    public function key(): string;
    public function name(): string;
    public function enabled(): bool;
    public function boot(): void;
}
