<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Compactweb\Component\Compacttables\Site\Controller;

use Compactweb\Component\Compacttables\Administrator\Controller\TableController as AdministratorTableController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Making and editing a table on the website (inside the picker dialog):
 * the same code and the same permission checks as in the administrator.
 */
class TableController extends AdministratorTableController
{
    /**
     * Keeps "layout=edit" out of the address on the website: some site templates (T4)
     * take that as their cue to show their own article-editing page. The view needs no layout.
     */
    protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
    {
        return str_replace('&layout=edit', '', parent::getRedirectToItemAppend($recordId, $urlVar));
    }
}
