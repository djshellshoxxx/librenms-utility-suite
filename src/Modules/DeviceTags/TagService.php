<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Modules\DeviceTags;

use InvalidArgumentException;

final class TagService
{
    private array $tags = [];
    private array $deviceTags = [];

    public function create(string $name): array
    {
        $normalized = strtolower(trim($name));
        if ($normalized === '') throw new InvalidArgumentException('Tag name cannot be empty');
        foreach ($this->tags as $tag) if ($tag['normalized'] === $normalized) return $tag;
        $id = count($this->tags) + 1;
        return $this->tags[$id] = ['id' => $id, 'name' => trim($name), 'normalized' => $normalized];
    }

    public function assign(int $deviceId, int $tagId): void { $this->deviceTags[$deviceId][$tagId] = true; }
    public function remove(int $deviceId, int $tagId): void { unset($this->deviceTags[$deviceId][$tagId]); }

    public function devicesMatching(array $tagIds): array
    {
        $tagIds = array_values(array_unique(array_map('intval', $tagIds)));
        $out = [];
        foreach ($this->deviceTags as $deviceId => $tags) if (! array_diff($tagIds, array_keys($tags))) $out[] = $deviceId;
        sort($out);
        return $out;
    }
}
