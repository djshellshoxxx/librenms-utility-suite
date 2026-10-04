<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Http\Middleware;

use Closure;
use Djshellshoxxx\LibreNMSUtilitySuite\Services\SettingsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequireEnabledModule
{
    public function __construct(private SettingsService $settings) {}

    public function handle(Request $request, Closure $next, string $module): Response
    {
        abort_unless($this->settings->moduleEnabled($module), 404);
        return $next($request);
    }
}
