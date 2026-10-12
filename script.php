<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

/**
 * Package installer: checks the Joomla version, switches newly added plugins on (so {table 1}
 * and the editor button work straight after installing) and takes over from Table Maker,
 * the name this extension had until 0.0.16.
 */
class Pkg_CompacttablesInstallerScript
{
    private const MINIMUM_JOOMLA = '5.0.0';

    private const PLUGINS = ['content', 'editors-xtd'];

    /**
     * @var  string[]  Plugin groups in which our plugin was installed before this run.
     */
    private array $existingPlugins = [];

    /**
     * @var  string  Version of Compact Tables that was installed before this run ('' on a new installation).
     */
    private string $previousVersion = '';

    public function preflight($type, $parent): bool
    {
        if ($type !== 'uninstall' && version_compare(JVERSION, self::MINIMUM_JOOMLA, '<')) {
            Log::add(Text::sprintf('PKG_COMPACTTABLES_ERROR_JOOMLA_VERSION', self::MINIMUM_JOOMLA), Log::WARNING, 'jerror');

            return false;
        }

        if ($type !== 'uninstall') {
            $this->existingPlugins = array_values(array_filter(self::PLUGINS, fn (string $folder): bool => $this->pluginExists($folder)));
            $this->previousVersion = $this->installedVersion();
        }

        return true;
    }

    public function postflight($type, $parent): void
    {
        if ($type === 'uninstall') {
            return;
        }

        // Switch on the plugins that this installation added. Plugins that were
        // already there keep the state the site owner gave them.
        foreach (self::PLUGINS as $folder) {
            if (!\in_array($folder, $this->existingPlugins, true)) {
                $this->enablePlugin($folder);
            }
        }

        $this->takeOverFromTableMaker();
        $this->splitCellLines();
        $this->clearOldTableClass();
    }

    /**
     * Up to 0.9.2 every table got the class 'table' by default, which puts Bootstrap's table
     * styling over the template's own styling of ordinary tables (most visible in the top row).
     * Since 0.9.3 the option is empty by default. On an update from an older version an option
     * that still has the old default is emptied once; a class the site owner chose is kept.
     */
    private function clearOldTableClass(): void
    {
        if ($this->previousVersion === '' || version_compare($this->previousVersion, '0.9.3', '>=')) {
            return;
        }

        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName(['extension_id', 'params']))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('component'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('com_compacttables'));
            $row = $db->setQuery($query)->loadObject();

            if (!$row) {
                return;
            }

            $params = json_decode((string) $row->params, true);

            if (!\is_array($params) || !\array_key_exists('table_class', $params) || trim((string) $params['table_class']) !== 'table') {
                return;
            }

            $params['table_class'] = '';

            $query = $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
                ->where($db->quoteName('extension_id') . ' = ' . (int) $row->extension_id);
            $db->setQuery($query)->execute();
        } catch (\Throwable $e) {
            // Never let this stop an installation; the option can still be changed by hand.
        }
    }

    /**
     * Version of the installed package, read before the new files are copied.
     */
    private function installedVersion(): string
    {
        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName('manifest_cache'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('package'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('pkg_compacttables'));
            $cache = json_decode((string) $db->setQuery($query)->loadResult(), true);

            return \is_array($cache) ? (string) ($cache['version'] ?? '') : '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Until 0.0.16 this extension was called Table Maker (pkg_tablemaker, com_tablemaker).
     * When that one is installed, its tables, options, permissions and plugin states are
     * taken over once, with the same table numbers so every {table N} in an article keeps
     * working, and Table Maker is then removed. Nothing is removed unless everything was copied.
     */
    private function takeOverFromTableMaker(): void
    {
        $app = Factory::getApplication();
        $db  = Factory::getContainer()->get(DatabaseInterface::class);

        try {
            $prefix = $db->getPrefix();
            $tables = $db->getTableList();

            if (!\in_array($prefix . 'tablemaker_tables', $tables, true) || !\in_array($prefix . 'compacttables_tables', $tables, true)) {
                return;
            }

            $count = static fn (string $table): int => (int) $db->setQuery(
                $db->getQuery(true)->select('COUNT(*)')->from($db->quoteName($table))
            )->loadResult();

            $old = $count('#__tablemaker_tables');

            // Only into an empty Compact Tables: never mix two sets of table numbers.
            if ($count('#__compacttables_tables') > 0) {
                return;
            }

            if ($old > 0) {
                $columns = $db->quoteName(['id', 'title', 'has_header', 'num_cols', 'num_rows', 'data', 'params', 'created', 'created_by', 'modified', 'modified_by']);
                $db->setQuery(
                    'INSERT INTO ' . $db->quoteName('#__compacttables_tables') . ' (' . implode(', ', $columns) . ')'
                    . ' SELECT ' . implode(', ', $columns) . ' FROM ' . $db->quoteName('#__tablemaker_tables')
                )->execute();

                if (stripos($db->getServerType(), 'postgres') !== false) {
                    $db->setQuery(
                        'SELECT setval(pg_get_serial_sequence(' . $db->quote($prefix . 'compacttables_tables') . ', ' . $db->quote('id') . '),'
                        . ' (SELECT MAX(' . $db->quoteName('id') . ') FROM ' . $db->quoteName('#__compacttables_tables') . '))'
                    )->execute();
                }
            }

            if ($count('#__compacttables_tables') !== $old) {
                return;
            }

            // Options (editor bar, table class) and permissions.
            $params = (string) $db->setQuery(
                $db->getQuery(true)->select($db->quoteName('params'))->from($db->quoteName('#__extensions'))
                    ->where($db->quoteName('type') . ' = ' . $db->quote('component'))
                    ->where($db->quoteName('element') . ' = ' . $db->quote('com_tablemaker'))
            )->loadResult();

            if ($params !== '' && $params !== '{}') {
                $db->setQuery(
                    $db->getQuery(true)->update($db->quoteName('#__extensions'))
                        ->set($db->quoteName('params') . ' = ' . $db->quote($params))
                        ->where($db->quoteName('type') . ' = ' . $db->quote('component'))
                        ->where($db->quoteName('element') . ' = ' . $db->quote('com_compacttables'))
                )->execute();
            }

            $rules = (string) $db->setQuery(
                $db->getQuery(true)->select($db->quoteName('rules'))->from($db->quoteName('#__assets'))
                    ->where($db->quoteName('name') . ' = ' . $db->quote('com_tablemaker'))
            )->loadResult();

            if ($rules !== '' && $rules !== '{}') {
                $db->setQuery(
                    $db->getQuery(true)->update($db->quoteName('#__assets'))
                        ->set($db->quoteName('rules') . ' = ' . $db->quote($rules))
                        ->where($db->quoteName('name') . ' = ' . $db->quote('com_compacttables'))
                )->execute();
            }

            // A plugin that was switched off stays switched off.
            foreach (self::PLUGINS as $folder) {
                $enabled = $db->setQuery(
                    $db->getQuery(true)->select($db->quoteName('enabled'))->from($db->quoteName('#__extensions'))
                        ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                        ->where($db->quoteName('folder') . ' = ' . $db->quote($folder))
                        ->where($db->quoteName('element') . ' = ' . $db->quote('tablemaker'))
                )->loadResult();

                if ($enabled !== null && (int) $enabled === 0) {
                    $db->setQuery(
                        $db->getQuery(true)->update($db->quoteName('#__extensions'))
                            ->set($db->quoteName('enabled') . ' = 0')
                            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                            ->where($db->quoteName('folder') . ' = ' . $db->quote($folder))
                            ->where($db->quoteName('element') . ' = ' . $db->quote('compacttables'))
                    )->execute();
                }
            }

            // Everything is over: remove Table Maker, so its plugins do not handle {table N} a second time.
            $removed = $this->removeTableMaker($db);
            $dutch   = str_starts_with((string) $app->getLanguage()->getTag(), 'nl');

            $app->enqueueMessage(
                $dutch
                    ? sprintf('Compact Tabellen heeft %d tabel(len), de opties en de rechten overgenomen van Tabel Maker. De nummers zijn gelijk gebleven, dus elke {table N} in je artikelen blijft werken.', $old)
                        . ($removed ? ' Tabel Maker is verwijderd.' : ' Tabel Maker kon niet automatisch worden verwijderd: verwijder het pakket ‘Tabel Maker’ zelf via Systeem → Extensies beheren.')
                    : sprintf('Compact Tables took over %d table(s), the options and the permissions from Table Maker. The numbers are the same, so every {table N} in your articles keeps working.', $old)
                        . ($removed ? ' Table Maker has been removed.' : ' Table Maker could not be removed automatically: uninstall the package ‘Table Maker’ yourself under System → Manage Extensions.'),
                $removed ? 'message' : 'warning'
            );
        } catch (\Throwable $e) {
            $app->enqueueMessage('Compact Tables: ' . $e->getMessage(), 'warning');
        }
    }

    private function removeTableMaker(DatabaseInterface $db): bool
    {
        $find = static fn (string $type, string $element, string $folder = ''): int => (int) $db->setQuery(
            $db->getQuery(true)->select($db->quoteName('extension_id'))->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote($type))
                ->where($db->quoteName('element') . ' = ' . $db->quote($element))
                ->where($db->quoteName('folder') . ' = ' . $db->quote($folder))
        )->loadResult();

        $uninstall = static function (string $type, int $id) use ($db): bool {
            if (!$id) {
                return true;
            }

            $installer = new Installer();
            $installer->setDatabase($db);

            return (bool) $installer->uninstall($type, $id);
        };

        $ok = true;

        if ($package = $find('package', 'pkg_tablemaker')) {
            $ok = $uninstall('package', $package);
        }

        // Whatever is still there (installed without the package, or left behind).
        $ok = $uninstall('plugin', $find('plugin', 'tablemaker', 'content')) && $ok;
        $ok = $uninstall('plugin', $find('plugin', 'tablemaker', 'editors-xtd')) && $ok;
        $ok = $uninstall('component', $find('component', 'com_tablemaker')) && $ok;

        return $ok && !$find('component', 'com_tablemaker');
    }

    /**
     * Up to 0.0.13 a table had one setting for the lines between cells; since 0.0.14 there are
     * separate vertical and horizontal lines. Tables made before keep the look they had.
     */
    private function splitCellLines(): void
    {
        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName(['id', 'params']))
                ->from($db->quoteName('#__compacttables_tables'))
                ->where($db->quoteName('params') . ' LIKE ' . $db->quote('%"cell_%'));

            foreach ($db->setQuery($query)->loadObjectList() as $row) {
                $params = json_decode((string) $row->params, true);

                if (!\is_array($params)) {
                    continue;
                }

                foreach (['width', 'color'] as $key) {
                    $old = (string) ($params['cell_' . $key] ?? '');
                    unset($params['cell_' . $key]);

                    foreach (['vline_', 'hline_'] as $prefix) {
                        if (($params[$prefix . $key] ?? '') === '') {
                            $params[$prefix . $key] = $old;
                        }
                    }
                }

                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__compacttables_tables'))
                    ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
                    ->where($db->quoteName('id') . ' = ' . (int) $row->id);
                $db->setQuery($query)->execute();
            }
        } catch (\Throwable $e) {
            // Never let this stop an installation; the tables themselves are untouched.
        }
    }

    private function pluginExists(string $folder): bool
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote($folder))
            ->where($db->quoteName('element') . ' = ' . $db->quote('compacttables'));

        return (int) $db->setQuery($query)->loadResult() > 0;
    }

    private function enablePlugin(string $folder): void
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('enabled') . ' = 1')
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote($folder))
            ->where($db->quoteName('element') . ' = ' . $db->quote('compacttables'));

        $db->setQuery($query)->execute();
    }
}
