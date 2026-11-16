<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Modules\NotesHistory;

final class DeviceNoteHistoryService
{
    private array $history = [];

    public function record(int $deviceId, int $userId, ?string $before, ?string $after, string $source = 'ui'): ?array
    {
        if (($before ?? '') === ($after ?? '')) return null;
        $row = [
            'device_id' => $deviceId,
            'user_id' => $userId,
            'previous_notes' => $before,
            'new_notes' => $after,
            'source' => $source,
            'created_at' => microtime(true),
        ];
        $this->history[] = $row;
        return $row;
    }

    public function forDevice(int $deviceId): array
    {
        return array_values(array_filter($this->history, fn ($row) => $row['device_id'] === $deviceId));
    }
}
