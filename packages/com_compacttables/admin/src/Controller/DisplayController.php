<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Compactweb\Component\Compacttables\Administrator\Controller;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Default controller: shows the list of tables.
 */
class DisplayController extends BaseController
{
    protected $default_view = 'tables';

    public function display($cachable = false, $urlparams = [])
    {
        $view   = $this->input->get('view', $this->default_view);
        $layout = $this->input->get('layout', 'default');
        $id     = $this->input->getInt('id');

        // The edit screen may only be reached through the edit task.
        if ($view === 'table' && $layout === 'edit' && !$this->checkEditId('com_compacttables.edit.table', $id)) {
            if (!\count($this->app->getMessageQueue())) {
                $this->setMessage(\Joomla\CMS\Language\Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
            }

            $this->setRedirect(Route::_('index.php?option=com_compacttables&view=tables', false));

            return $this;
        }

        return parent::display($cachable, $urlparams);
    }
}
