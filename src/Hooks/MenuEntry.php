<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Hooks;

use LibreNMS\Interfaces\Plugins\Hooks\MenuEntryHook;

final class MenuEntry implements MenuEntryHook
{
    public function authorize(): bool { return true; }
    public function handle(string $pluginName): array { return ["$pluginName::hooks.menu", []]; }
}
