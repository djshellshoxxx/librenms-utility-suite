<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite\Http\Controllers;

use Djshellshoxxx\LibreNMSUtilitySuite\Core\ModuleRegistry;
use Illuminate\View\View;

final class DashboardController
{
    public function __invoke(ModuleRegistry $registry): View
    {
        return view('librenms-utility-suite::dashboard', ['modules' => $registry->enabled()]);
    }
}
