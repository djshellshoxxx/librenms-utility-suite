<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Modules\Diagnostics;

final class SnmpResultSanitizer
{
    public function sanitize(DiagnosticResult $result, array $credentials): DiagnosticResult
    {
        return $result->sanitized(array_values($credentials));
    }
}
