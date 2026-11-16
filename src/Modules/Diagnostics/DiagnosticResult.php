<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Modules\Diagnostics;

final readonly class DiagnosticResult
{
    public function __construct(public string $status, public string $summary, public array $details = [])
    {
        if (! in_array($status, ['pass', 'warning', 'fail', 'timeout', 'permission_denied', 'not_supported'], true)) {
            throw new \InvalidArgumentException('Invalid diagnostic status');
        }
    }

    public function sanitized(array $secrets): self
    {
        $replace = [];
        foreach ($secrets as $secret) if (is_string($secret) && $secret !== '') $replace[$secret] = '[REDACTED]';
        $walk = function ($value) use (&$walk, $replace) {
            if (is_string($value)) return strtr($value, $replace);
            if (is_array($value)) return array_map($walk, $value);
            return $value;
        };
        return new self($this->status, $walk($this->summary), $walk($this->details));
    }
}
