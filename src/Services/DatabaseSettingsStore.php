<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Services;

use Djshellshoxxx\LibreNMSUtilitySuite\Contracts\SettingsStore;
use Djshellshoxxx\LibreNMSUtilitySuite\Models\Setting;

final class DatabaseSettingsStore implements SettingsStore
{
    public function get(string $key): mixed
    {
        $row = Setting::query()->where('key', $key)->first();
        return $row ? json_decode((string) $row->value, true) : null;
    }

    public function set(string $key, mixed $value): void
    {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => json_encode($value, JSON_THROW_ON_ERROR)]
        );
    }
}
