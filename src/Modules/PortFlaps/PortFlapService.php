<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Modules\PortFlaps;

final class PortFlapService
{
    public function count(iterable $states): int
    {
        $last = null;
        $changes = 0;
        foreach ($states as $state) {
            $normalized = strtolower(trim((string) $state));
            if (! in_array($normalized, ['up', 'down'], true)) continue;
            if ($last !== null && $normalized !== $last) $changes++;
            $last = $normalized;
        }
        return $changes;
    }

    public function severity(int $count, FlapThresholds $thresholds): string
    {
        if ($count >= $thresholds->critical) return 'critical';
        if ($count >= $thresholds->high) return 'high';
        if ($count >= $thresholds->warning) return 'warning';
        return 'normal';
    }
}
