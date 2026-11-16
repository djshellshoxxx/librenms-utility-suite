<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Services;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

final class AccessService
{
    public function canViewDevice(User $user, int $deviceId): bool
    {
        return $user->devices()->where('device_id', $deviceId)->exists();
    }

    public function canViewPort(User $user, int $portId): bool
    {
        return $user->ports()->where('ports.port_id', $portId)->exists();
    }

    public function requireAdmin(User $user): void
    {
        if (! $user->can('admin')) throw new AuthorizationException('Administrator access required');
    }
}
