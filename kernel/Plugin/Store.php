<?php
declare(strict_types=1);

use Kernel\Context\App;
use Kernel\Exception\JSONException;
use Kernel\Plugin\Command;
use Kernel\Plugin\Const\Plugin as PluginConst;
use Kernel\Plugin\Entity\Plugin as PluginEntity;
use Kernel\Plugin\Hook;
use Kernel\Plugin\Language;
use Kernel\Plugin\Menu;
use Kernel\Plugin\Plugin;
use Kernel\Plugin\Process;
use Kernel\Plugin\Route;
use Kernel\Plugin\Sync;
use Kernel\Plugin\Usr;
use Kernel\Util\File;
use Symfony\Component\Finder\Finder;

if (!defined('MCY_PLUGIN_STATE_DEFAULT_TIME')) {
    define('MCY_PLUGIN_STATE_DEFAULT_TIME', '1997-01-01 05:20:00');
}

if (!defined('MCY_PLUGIN_STATE_STOPPING')) {
    define('MCY_PLUGIN_STATE_STOPPING', 3);
}

function mcy_plugin_hwid_path(): string
{
    return BASE_PATH . 'config/store/.hwid';
}

function mcy_plugin_generate_hwid(): string
{
    $database = App::$database ?? [];
    $parts = [
        (string)($database['driver'] ?? ''),
        (string)($database['host'] ?? ''),
        (string)($database['database'] ?? ''),
        (string)($database['username'] ?? ''),
        (string)($database['password'] ?? ''),
        (string)($database['prefix'] ?? ''),
        (string)($database['charset'] ?? ''),
        (string)($database['collation'] ?? ''),
        (string)($database['pool'] ?? ''),
    ];

    $basis = implode('|', $parts);
    if (trim($basis, '|') === '') {
        $basis = BASE_PATH;
    }

    return strtoupper(substr(md5($basis), 0, 16));
}

function mcy_plugin_default_state(): array
{
    return [
        'run' => PluginConst::STATE_STOP,
        'start' => MCY_PLUGIN_STATE_DEFAULT_TIME,
    ];
}

function mcy_plugin_state_file(string $name, string $env): ?string
{
    $info = BASE_PATH . $env . "/{$name}/Config/Info.php";
    if (!is_file($info)) {
        return null;
    }

    return BASE_PATH . $env . "/{$name}/State";
}

function mcy_plugin_read_state(string $name, string $env): array
{
    $file = mcy_plugin_state_file($name, $env);
    if ($file === null || !is_file($file)) {
        return mcy_plugin_default_state();
    }

    $contents = (string)file_get_contents($file);
    $state = Plugin::inst()->decrypt($contents);

    if (!is_array($state) || !isset($state['run'])) {
        return mcy_plugin_default_state();
    }

    return [
        'run' => (int)$state['run'],
        'start' => (string)($state['start'] ?? MCY_PLUGIN_STATE_DEFAULT_TIME),
    ];
}

function mcy_plugin_write_state(string $name, int $state, string $env): void
{
    $file = mcy_plugin_state_file($name, $env);
    if ($file === null) {
        return;
    }

    File::writeForLock($file, static function (string $contents) use ($state) {
        $payload = Plugin::inst()->decrypt($contents);
        if (!is_array($payload)) {
            $payload = [];
        }

        $payload['run'] = $state;
        $payload['start'] = date('Y-m-d H:i:s');
        return Plugin::inst()->encrypt($payload);
    });
}

function mcy_plugin_resolve(string $name, string $env): ?PluginEntity
{
    return Plugin::inst()->getPlugin($name, $env);
}

function mcy_plugin_route_user(string $env): string
{
    return Usr::inst()->envToUsr($env);
}

function mcy_plugin_has_hooks(PluginEntity $plugin): bool
{
    return is_dir($plugin->path . '/Hook');
}

function mcy_plugin_has_routes(PluginEntity $plugin): bool
{
    return !empty($plugin->route);
}

function mcy_plugin_has_menu(PluginEntity $plugin): bool
{
    return !empty($plugin->menu);
}

function mcy_plugin_has_languages(PluginEntity $plugin): bool
{
    return !empty($plugin->language);
}

function mcy_plugin_has_processes(PluginEntity $plugin): bool
{
    return is_dir($plugin->path . '/Process');
}

function mcy_plugin_has_commands(PluginEntity $plugin): bool
{
    return App::$cli && !empty($plugin->command);
}

function mcy_plugin_add_runtime(PluginEntity $plugin): array
{
    $rollbacks = [];
    $usr = mcy_plugin_route_user($plugin->env);

    mcy_plugin_forget_file_cache(
        Hook::CACHE_FILE,
        Route::CACHE_FILE,
        Menu::CACHE_FILE,
        Process::CACHE_FILE
    );

    if (mcy_plugin_has_hooks($plugin)) {
        Hook::inst()->add($plugin->name, $plugin->env);
        $rollbacks[] = static fn() => Hook::inst()->del($plugin->name, $plugin->env);
    }

    if (mcy_plugin_has_routes($plugin)) {
        Route::inst()->add($plugin->route, $plugin->name, 'plugin', $usr);
        $rollbacks[] = static fn() => Route::inst()->del($plugin->name, 'plugin', $usr);
    }

    if (mcy_plugin_has_menu($plugin)) {
        Menu::inst()->add($plugin->name, $plugin->menu, $usr);
        $rollbacks[] = static fn() => Menu::inst()->del($plugin->name, $usr);
    }

    if (mcy_plugin_has_languages($plugin)) {
        Language::inst()->add($plugin->name, $plugin->env);
        $rollbacks[] = static fn() => Language::inst()->del($plugin->name, $plugin->env);
    }

    if (mcy_plugin_has_processes($plugin)) {
        Process::inst()->add($plugin->name, $plugin->env);
        $rollbacks[] = static fn() => Process::inst()->del($plugin->name, $plugin->env);
    }

    if (mcy_plugin_has_commands($plugin)) {
        Command::inst()->add($plugin->name, $plugin->env);
        $rollbacks[] = static fn() => Command::inst()->del($plugin->name, $plugin->env);
    }

    return $rollbacks;
}

function mcy_plugin_remove_runtime(PluginEntity $plugin): void
{
    $usr = mcy_plugin_route_user($plugin->env);

    mcy_plugin_forget_file_cache(
        Hook::CACHE_FILE,
        Route::CACHE_FILE,
        Menu::CACHE_FILE,
        Process::CACHE_FILE
    );

    if (mcy_plugin_has_commands($plugin)) {
        Command::inst()->del($plugin->name, $plugin->env);
    }

    if (mcy_plugin_has_processes($plugin)) {
        Process::inst()->del($plugin->name, $plugin->env);
    }

    if (mcy_plugin_has_languages($plugin)) {
        Language::inst()->del($plugin->name, $plugin->env);
    }

    if (mcy_plugin_has_menu($plugin)) {
        Menu::inst()->del($plugin->name, $usr);
    }

    if (mcy_plugin_has_routes($plugin)) {
        Route::inst()->del($plugin->name, 'plugin', $usr);
    }

    if (mcy_plugin_has_hooks($plugin)) {
        Hook::inst()->del($plugin->name, $plugin->env);
    }
}

function mcy_plugin_in_sync(string $name, string $env): bool
{
    return Sync::inst()->has($name, $env);
}

function mcy_plugin_restore_rollbacks(array $rollbacks): void
{
    while ($rollback = array_pop($rollbacks)) {
        try {
            $rollback();
        } catch (\Throwable) {
        }
    }
}

function mcy_plugin_forget_file_cache(string ...$paths): void
{
    static $property = null;

    if ($property === null) {
        $reflection = new ReflectionClass(File::class);
        $property = $reflection->getProperty('files');
        $property->setAccessible(true);
    }

    $cache = $property->getValue();
    foreach ($paths as $path) {
        unset($cache[$path]);
    }
    $property->setValue(null, $cache);
}

function mcy_plugin_filter_type(PluginEntity $plugin, int $type): bool
{
    if ($type === PluginConst::TYPE_ANY) {
        return true;
    }

    return (int)($plugin->info[PluginConst::TYPE] ?? 0) === $type;
}

if (!function_exists('cd4d898edaf466b53198e8640e426c2f')) {
    function cd4d898edaf466b53198e8640e426c2f(): string
    {
        $path = mcy_plugin_hwid_path();
        $hwid = strtoupper(trim((string)File::read($path)));
        if (preg_match('/^[A-F0-9]{16,32}$/', $hwid)) {
            return substr($hwid, 0, 16);
        }

        $hwid = mcy_plugin_generate_hwid();
        File::write($path, $hwid);
        return $hwid;
    }
}

if (!function_exists('f61b4ba764466c4f43f4e564918aac09')) {
    function f61b4ba764466c4f43f4e564918aac09(): string
    {
        return cd4d898edaf466b53198e8640e426c2f();
    }
}

if (!function_exists('e3b4615fba4874ab133dfe6acb700890')) {
    function e3b4615fba4874ab133dfe6acb700890(string $name, int $state, string $env): void
    {
        mcy_plugin_write_state($name, $state, $env);
    }
}

if (!function_exists('cfb6ad5dda2af960948a27546a092608')) {
    function cfb6ad5dda2af960948a27546a092608(string $name, string $env): array
    {
        return mcy_plugin_read_state($name, $env);
    }
}

if (!function_exists('f7b791fb1d06384599337402f8f9ae68')) {
    function f7b791fb1d06384599337402f8f9ae68(string $name, string $env): void
    {
        if (mcy_plugin_in_sync($name, $env)) {
            throw new JSONException('Plugin is syncing');
        }

        $plugin = mcy_plugin_resolve($name, $env);
        if (!$plugin) {
            throw new JSONException('Plugin not found');
        }

        $run = (int)($plugin->state['run'] ?? PluginConst::STATE_STOP);
        if ($run === PluginConst::STATE_START || $run === PluginConst::STATE_SYNC) {
            throw new JSONException('Plugin is already running');
        }

        $rollbacks = [];
        try {
            $rollbacks = mcy_plugin_add_runtime($plugin);

            if (App::$cli) {
                e3b4615fba4874ab133dfe6acb700890($name, PluginConst::STATE_SYNC, $env);
                Sync::inst()->add(PluginConst::STATE_START, $name, $env);
                return;
            }

            e3b4615fba4874ab133dfe6acb700890($name, PluginConst::STATE_START, $env);
        } catch (\Throwable $e) {
            mcy_plugin_restore_rollbacks($rollbacks);
            e3b4615fba4874ab133dfe6acb700890($name, PluginConst::STATE_STOP, $env);
            throw $e;
        }
    }
}

if (!function_exists('e0363b4507cc5b1891d3775c32f04bde')) {
    function e0363b4507cc5b1891d3775c32f04bde(string $name, string $env): void
    {
        if (mcy_plugin_in_sync($name, $env)) {
            throw new JSONException('Plugin is syncing');
        }

        $plugin = mcy_plugin_resolve($name, $env);
        if (!$plugin) {
            throw new JSONException('Plugin not found');
        }

        $run = (int)($plugin->state['run'] ?? PluginConst::STATE_STOP);
        if ($run === PluginConst::STATE_STOP || $run === MCY_PLUGIN_STATE_STOPPING) {
            throw new JSONException('Plugin is not running');
        }

        try {
            mcy_plugin_remove_runtime($plugin);

            if (App::$cli) {
                e3b4615fba4874ab133dfe6acb700890($name, MCY_PLUGIN_STATE_STOPPING, $env);
                Sync::inst()->add(PluginConst::STATE_STOP, $name, $env);
                return;
            }

            e3b4615fba4874ab133dfe6acb700890($name, PluginConst::STATE_STOP, $env);
        } catch (\Throwable $e) {
            try {
                mcy_plugin_add_runtime($plugin);
            } catch (\Throwable) {
            }
            e3b4615fba4874ab133dfe6acb700890($name, PluginConst::STATE_START, $env);
            throw $e;
        }
    }
}

if (!function_exists('bd580eb07f5781f020e46ed277c0fe52')) {
    function bd580eb07f5781f020e46ed277c0fe52(int $type, string $env): array
    {
        $baseDir = BASE_PATH . $env;
        if (!is_dir($baseDir)) {
            return [];
        }

        $finder = Finder::create()
            ->in($baseDir)
            ->depth('== 0')
            ->ignoreUnreadableDirs(true)
            ->directories();

        $plugins = [];
        foreach ($finder as $item) {
            $plugin = Plugin::inst()->getPlugin($item->getFilename(), $env);
            if (!$plugin) {
                continue;
            }

            if ((int)($plugin->state['run'] ?? PluginConst::STATE_STOP) !== PluginConst::STATE_START) {
                continue;
            }

            if (!mcy_plugin_filter_type($plugin, $type)) {
                continue;
            }

            $plugins[] = $plugin;
        }

        return $plugins;
    }
}
