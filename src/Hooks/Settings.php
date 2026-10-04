<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Hooks;

use LibreNMS\Interfaces\Plugins\Hooks\SettingsHook;

final class Settings implements SettingsHook
{
    public function authorize(): bool { return auth()->user()?->can('admin') ?? false; }
    public function handle(string $pluginName): array { return ["$pluginName::hooks.settings", []]; }
}
