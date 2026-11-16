<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Modules\Bookmarks;

final class BookmarkService
{
    private array $rows = [];

    public function add(int $userId, int $deviceId): array
    {
        $key = "$userId:$deviceId";
        return $this->rows[$key] ??= ['user_id' => $userId, 'device_id' => $deviceId];
    }

    public function remove(int $userId, int $deviceId): void
    {
        unset($this->rows["$userId:$deviceId"]);
    }

    public function forUser(int $userId): array
    {
        return array_values(array_filter($this->rows, fn ($row) => $row['user_id'] === $userId));
    }
}
