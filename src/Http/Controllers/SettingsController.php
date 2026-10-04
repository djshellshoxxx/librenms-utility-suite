<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Http\Controllers;

use Djshellshoxxx\LibreNMSUtilitySuite\Services\AccessService;
use Djshellshoxxx\LibreNMSUtilitySuite\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SettingsController
{
    public function index(Request $request, AccessService $access): View
    {
        $access->requireAdmin($request->user());
        return view('librenms-utility-suite::settings', ['modules' => config('librenms-utility-suite.modules', [])]);
    }

    public function update(Request $request, AccessService $access, SettingsService $settings): RedirectResponse
    {
        $access->requireAdmin($request->user());
        $known = array_keys(config('librenms-utility-suite.modules', []));
        foreach ($known as $key) $settings->set("modules.$key.enabled", $request->boolean("modules.$key"));
        return back()->with('status', 'Utility Suite settings saved.');
    }
}
