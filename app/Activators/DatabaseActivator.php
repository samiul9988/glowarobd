<?php

namespace App\Activators;

use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository as Config;
use Illuminate\Container\Container;
use Illuminate\Database\DatabaseManager;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Module;

class DatabaseActivator implements ActivatorInterface
{
    /**
     * Laravel cache instance
     *
     * @var CacheManager
     */
    private $cache;

    /**
     * Laravel config instance
     *
     * @var Config
     */
    private $config;

    /**
     * Laravel database instance
     *
     * @var DatabaseManager
     */
    private $database;

    /**
     * @var string
     */
    private $cacheKey;

    /**
     * @var string
     */
    private $cacheLifetime;

    /**
     * @var string
     */
    private $table;

    public function __construct(Container $app)
    {
        $this->cache = $app['cache'];
        $this->config = $app['config'];
        $this->database = $app['db'];
        $this->cacheKey = $this->config->get('modules.activators.database.cache-key', 'activator.installed');
        $this->cacheLifetime = $this->config->get('modules.activators.database.cache-lifetime', 604800);
        $this->table = $this->config->get('modules.activators.database.table', 'module_activations');
    }

    /**
     * Enables a module
     *
     * @param Module $module
     */
    public function enable(Module $module): void
    {
        $this->setActiveByName($module->getName(), true);
    }

    public function setActive(Module $module, bool $active): void
    {
        $this->setActiveByName($module->getName(), $active);
    }

    /**
     * Disables a module
     *
     * @param Module $module
     */
    public function disable(Module $module): void
    {
        $this->setActiveByName($module->getName(), false);
    }

    /**
     * Determine whether the given status same with a module status.
     *
     * @param Module $module
     * @param bool $status
     *
     * @return bool
     */
    public function hasStatus(Module $module, bool $status): bool
    {
        return $this->getModuleStatus($module->getName()) === $status;
    }

    /**
     * Set active state for a module by name.
     *
     * @param string $name
     * @param bool $active
     */
    public function setActiveByName(string $name, bool $active): void
    {
        $exists = $this->database->table($this->table)
            ->where('name', $name)
            ->exists();

        if ($exists) {
            $this->database->table($this->table)
                ->where('name', $name)
                ->update([
                    'active' => $active,
                    'updated_at' => now(),
                ]);
        } else {
            $this->database->table($this->table)
                ->insert([
                    'name' => $name,
                    'active' => $active,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        $this->flushCache();
    }

    /**
     * Get module status (enabled/disabled)
     *
     * @param string $name
     * @return bool
     */
    private function getModuleStatus(string $name): bool
    {
        $statuses = $this->getModulesStatuses();

        return $statuses[$name] ?? false;
    }

    /**
     * Get modules statuses, either from the cache or from the database
     *
     * @return array
     */
    private function getModulesStatuses(): array
    {
        return $this->cache->remember($this->cacheKey, $this->cacheLifetime, function () {
            $modules = $this->database->table($this->table)->get();

            $statuses = [];
            foreach ($modules as $module) {
                $statuses[$module->name] = (bool) $module->active;
            }

            return $statuses;
        });
    }

    /**
     * Flush the cache.
     */
    public function flushCache(): void
    {
        $this->cache->forget($this->cacheKey);
    }

    /**
     * {@inheritDoc}
     */
    public function reset(): void
    {
        $this->database->table($this->table)->truncate();
        $this->flushCache();
    }

    /**
     * Delete a module activation record
     *
     * @param Module $module
     */
    public function delete(Module $module): void
    {
        $this->database->table($this->table)
            ->where('name', $module->getName())
            ->delete();

        $this->flushCache();
    }
}
