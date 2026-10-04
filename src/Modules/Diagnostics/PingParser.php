<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Modules\Diagnostics;

final class PingParser
{
    public function parse(string $output, int $exitCode): DiagnosticResult
    {
        if ($exitCode === 124) return new DiagnosticResult('timeout', 'Ping timed out');
        if (preg_match('/(\d+(?:\.\d+)?)% packet loss/', $output, $match)) {
            $loss = (float) $match[1];
            $status = $loss === 0.0 ? 'pass' : ($loss < 100 ? 'warning' : 'fail');
            $details = ['loss_percent' => $loss];
            if (preg_match('/=\s*([\d.]+)\/([\d.]+)\/([\d.]+)\//', $output, $rtt)) {
                $details += ['min_ms' => (float) $rtt[1], 'avg_ms' => (float) $rtt[2], 'max_ms' => (float) $rtt[3]];
            }
            return new DiagnosticResult($status, "Packet loss: {$loss}%", $details);
        }
        return new DiagnosticResult($exitCode === 0 ? 'pass' : 'fail', $exitCode === 0 ? 'Ping succeeded' : 'Ping failed');
    }
}
