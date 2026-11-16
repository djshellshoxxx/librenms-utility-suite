<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Modules\DescriptionAudit;

final class DescriptionAuditService
{
    public function audit(iterable $ports, DescriptionRuleSet $rules): array
    {
        $ports = is_array($ports) ? $ports : iterator_to_array($ports, false);
        $duplicates = [];
        foreach ($ports as $port) {
            $description = strtolower(trim((string) ($port['description'] ?? '')));
            if ($description !== '') {
                $duplicates[$port['device_id']][$description] = ($duplicates[$port['device_id']][$description] ?? 0) + 1;
            }
        }

        $findings = [];
        foreach ($ports as $port) {
            if (in_array((int) $port['port_id'], $rules->excludedPortIds, true)) continue;
            $raw = trim((string) ($port['description'] ?? ''));
            $normalized = strtolower($raw);
            $reason = null;

            if ($raw === '') $reason = 'blank';
            elseif ((function_exists('mb_strlen') ? mb_strlen($raw) : strlen($raw)) < $rules->minLength) $reason = 'too_short';
            elseif (in_array($normalized, $rules->flaggedTerms, true)) $reason = 'flagged_term';
            elseif (preg_match('/^(port|interface|ethernet|gigabitethernet|ge|gi|eth)[-_ ]?\d*$/i', $raw)) $reason = 'generic';
            elseif (($duplicates[$port['device_id']][$normalized] ?? 0) > 1) $reason = 'duplicate';

            if ($reason) $findings[] = $port + ['reason' => $reason];
        }

        return $findings;
    }
}
