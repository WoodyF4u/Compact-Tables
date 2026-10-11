<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Compactweb\Component\Compacttables\Administrator\Helper;

use Joomla\CMS\User\User;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Who may use the editor button and its table picker.
 */
final class PickerAccess
{
    /**
     * Anyone who manages Compact Tables, and anyone who is logged in and may write
     * or edit articles (in general or in at least one category). The picker only
     * shows names and codes of tables, which such a person sees in articles anyway.
     */
    public static function isAllowed(?User $user): bool
    {
        if (!$user || $user->guest) {
            return false;
        }

        if ($user->authorise('core.manage', 'com_compacttables')) {
            return true;
        }

        foreach (['core.create', 'core.edit', 'core.edit.own'] as $action) {
            if ($user->authorise($action, 'com_content') || \count($user->getAuthorisedCategories('com_content', $action)) > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * What a user may do with tables from the picker: the permissions of
     * Compact Tables itself (Options > Permissions), in the administrator and on
     * the website alike.
     *
     * @return  array{edit: bool, create: bool, delete: bool}
     */
    public static function getTableActions(?User $user): array
    {
        $known = $user && !$user->guest;

        return [
            'edit'   => $known && $user->authorise('core.edit', 'com_compacttables'),
            'create' => $known && $user->authorise('core.create', 'com_compacttables'),
            'delete' => $known && $user->authorise('core.delete', 'com_compacttables'),
        ];
    }
}
