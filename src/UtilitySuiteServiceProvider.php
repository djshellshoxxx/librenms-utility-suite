<?php
namespace Djshellshoxxx\LibreNMSUtilitySuite;

use Djshellshoxxx\LibreNMSUtilitySuite\Contracts\SettingsStore;
use Djshellshoxxx\LibreNMSUtilitySuite\Core\ConfiguredModule;
use Djshellshoxxx\LibreNMSUtilitySuite\Core\ModuleRegistry;
use Djshellshoxxx\LibreNMSUtilitySuite\Hooks\MenuEntry;
use Djshellshoxxx\LibreNMSUtilitySuite\Hooks\Settings;
use Djshellshoxxx\LibreNMSUtilitySuite\Http\Middleware\RequireEnabledModule;
use Djshellshoxxx\LibreNMSUtilitySuite\Services\DatabaseSettingsStore;
use Djshellshoxxx\LibreNMSUtilitySuite\Services\SettingsService;
use Illuminate\Support\ServiceProvider;
use LibreNMS\Interfaces\Plugins\Hooks\MenuEntryHook;
use LibreNMS\Interfaces\Plugins\Hooks\SettingsHook;
use LibreNMS\Interfaces\Plugins\PluginManagerInterface;

final class UtilitySuiteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/utility-suite.php', UtilitySuitePlugin::NAME);
        $this->app->singleton(SettingsStore::class, DatabaseSettingsStore::class);
        $this->app->singleton(SettingsService::class, function ($app): SettingsService {
            $defaults = [];
            foreach ((array) config(UtilitySuitePlugin::NAME.'.modules', []) as $key => $module) {
                $defaults["modules.$key.enabled"] = (bool) ($module['enabled'] ?? false);
            }
            return new SettingsService($app->make(SettingsStore::class), $defaults);
        });
        $this->app->singleton(ModuleRegistry::class, function ($app): ModuleRegistry {
            $settings = $app->make(SettingsService::class);
            $modules = [];
            foreach ((array) config(UtilitySuitePlugin::NAME.'.modules', []) as $key => $module) {
                $modules[] = new ConfiguredModule($key, (string) $module['name'], $settings);
            }
            return new ModuleRegistry($modules);
        });
    }

    public function boot(PluginManagerInterface $pluginManager): void
    {
        $pluginManager->publishHook(UtilitySuitePlugin::NAME, MenuEntryHook::class, MenuEntry::class);
        $pluginManager->publishHook(UtilitySuitePlugin::NAME, SettingsHook::class, Settings::class);

        if (! $pluginManager->pluginEnabled(UtilitySuitePlugin::NAME)) return;

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', UtilitySuitePlugin::NAME);
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->app['router']->aliasMiddleware('lus.module', RequireEnabledModule::class);
    }
}
