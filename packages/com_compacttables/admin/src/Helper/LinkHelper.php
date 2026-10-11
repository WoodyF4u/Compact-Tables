<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Compactweb\Component\Compacttables\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Linked tables: tables that get the same column widths when they are shown on one page.
 *
 * Every table of a group has the same "link_group" in its params; the content plugin turns
 * that into a CSS class, and compacttables.js lines the group up. The key is always "t" plus
 * the lowest ID in the group, so it is stable and never clashes with another group. A group
 * of one table is no group: its key is cleared.
 */
final class LinkHelper
{
    /**
     * @var  array<int, array{title: string, cols: int, group: string}>|null
     */
    private static ?array $tables = null;

    /**
     * All tables with their name, number of columns and link group, by ID.
     *
     * @return  array<int, array{title: string, cols: int, group: string}>
     */
    public static function getTables(bool $reload = false): array
    {
        if (self::$tables !== null && !$reload) {
            return self::$tables;
        }

        $db    = self::db();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'title', 'num_cols', 'params']))
            ->from($db->quoteName('#__compacttables_tables'))
            ->order($db->quoteName('id') . ' ASC');

        self::$tables = [];

        foreach ($db->setQuery($query)->loadObjectList() as $row) {
            $params = json_decode((string) $row->params, true);

            self::$tables[(int) $row->id] = [
                'title' => (string) $row->title,
                'cols'  => (int) $row->num_cols,
                'group' => \is_array($params) ? (string) ($params['link_group'] ?? '') : '',
            ];
        }

        return self::$tables;
    }

    /**
     * The tables linked to one table, by ID, sorted by name.
     *
     * @return  array<int, string>  ID => name
     */
    public static function getLinked(int $id): array
    {
        $tables = self::getTables();
        $group  = $tables[$id]['group'] ?? '';

        if ($group === '') {
            return [];
        }

        $linked = [];

        foreach ($tables as $otherId => $table) {
            if ($otherId !== $id && $table['group'] === $group) {
                $linked[$otherId] = $table['title'];
            }
        }

        natcasesort($linked);

        return $linked;
    }

    /**
     * Makes exactly these tables the group of $id. Tables that are chosen leave the group they
     * were in; tables of the old group that are no longer chosen stay linked to each other.
     *
     * @param   int    $id        The table being saved.
     * @param   int[]  $selected  IDs of the tables it should be linked to.
     */
    public static function setLinks(int $id, array $selected): void
    {
        $tables = self::getTables(true);

        if (!isset($tables[$id])) {
            return;
        }

        $groups = array_map(static fn (array $table): string => $table['group'], $tables);
        $chosen = array_values(array_filter(
            array_unique(array_map('intval', $selected)),
            static fn (int $other): bool => $other !== $id && isset($tables[$other])
        ));

        // Take the table and the chosen ones out of whatever group they were in...
        foreach (array_merge([$id], $chosen) as $member) {
            $groups[$member] = '';
        }

        // ...and put them together in a new one (the key is fixed by normalise()).
        if ($chosen) {
            foreach (array_merge([$id], $chosen) as $member) {
                $groups[$member] = 'new';
            }
        }

        self::write($tables, self::normalise($groups));
    }

    /**
     * Brings every group back to its proper key, for instance after tables were deleted.
     */
    public static function tidy(): void
    {
        $tables = self::getTables(true);
        $groups = array_map(static fn (array $table): string => $table['group'], $tables);

        self::write($tables, self::normalise($groups));
    }

    /**
     * @param   array<int, string>  $groups  ID => any group key
     *
     * @return  array<int, string>  ID => "t" + lowest ID of the group, or "" for a table on its own
     */
    private static function normalise(array $groups): array
    {
        $members = [];

        foreach ($groups as $id => $group) {
            if ($group !== '') {
                $members[$group][] = $id;
            }
        }

        $result = array_fill_keys(array_keys($groups), '');

        foreach ($members as $ids) {
            if (\count($ids) < 2) {
                continue;
            }

            $key = 't' . min($ids);

            foreach ($ids as $id) {
                $result[$id] = $key;
            }
        }

        return $result;
    }

    /**
     * Stores the new group keys of the tables whose key changed.
     *
     * @param   array<int, array{title: string, cols: int, group: string}>  $tables
     * @param   array<int, string>                                          $groups
     */
    private static function write(array $tables, array $groups): void
    {
        $db = self::db();

        foreach ($groups as $id => $group) {
            if (($tables[$id]['group'] ?? '') === $group) {
                continue;
            }

            $query = $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__compacttables_tables'))
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER);

            $params = json_decode((string) $db->setQuery($query)->loadResult(), true);
            $params = \is_array($params) ? $params : [];

            $params['link_group'] = $group;
            $json                 = json_encode($params);

            $query = $db->getQuery(true)
                ->update($db->quoteName('#__compacttables_tables'))
                ->set($db->quoteName('params') . ' = :params')
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':params', $json)
                ->bind(':id', $id, ParameterType::INTEGER);

            $db->setQuery($query)->execute();
        }

        self::$tables = null;
    }

    private static function db(): DatabaseInterface
    {
        return Factory::getContainer()->get(DatabaseInterface::class);
    }
}
