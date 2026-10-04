<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Modules\QrCodes;

use InvalidArgumentException;

final class QrTargetService
{
    public function forDevice(array $device, string $targetType, ?string $custom = null, string $baseUrl = ''): string
    {
        return match ($targetType) {
            'librenms' => rtrim($baseUrl, '/').'/device/device='.(int) $device['device_id'].'/',
            'hostname' => (string) $device['hostname'],
            'ip' => (string) ($device['ip'] ?? $device['hostname']),
            'management_url' => $this->validateUrl((string) ($device['management_url'] ?? '')),
            'custom' => $this->validateUrl((string) $custom),
            default => throw new InvalidArgumentException('Unsupported QR target type'),
        };
    }

    private function validateUrl(string $value): string
    {
        if (! filter_var($value, FILTER_VALIDATE_URL) || ! in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https', 'ssh'], true)) {
            throw new InvalidArgumentException('Invalid QR target URL');
        }
        return $value;
    }
}
