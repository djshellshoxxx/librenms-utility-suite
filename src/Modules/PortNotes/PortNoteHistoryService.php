<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Modules\PortNotes;

final class PortNoteHistoryService
{
    private array $history = [];

    public function record(int $portId, int $deviceId, int $userId, ?string $before, ?string $after): ?array
    {
        if (($before ?? '') === ($after ?? '')) return null;
        $row = [
            'port_id' => $portId,
            'device_id' => $deviceId,
            'user_id' => $userId,
            'previous_notes' => $before,
            'new_notes' => $after,
            'created_at' => microtime(true),
        ];
        $this->history[] = $row;
        return $row;
    }

    public function forPort(int $portId): array
    {
        return array_values(array_filter($this->history, fn ($row) => $row['port_id'] === $portId));
    }
}
