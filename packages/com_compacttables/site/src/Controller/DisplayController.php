<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Compactweb\Component\Compacttables\Site\Controller;

use Joomla\CMS\MVC\Controller\BaseController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The website side of Compact Tables lives entirely inside the dialog that the
 * editor button opens: the table picker and, for those who may, the table editor.
 */
class DisplayController extends BaseController
{
    protected $default_view = 'tables';

    public function display($cachable = false, $urlparams = [])
    {
        $view = $this->input->getCmd('view');
        $id   = $this->input->getInt('id');

        // The editor is only reachable through the add/edit tasks, which check the permissions first.
        $editing = $view === 'table'
            && $this->input->getInt('picker')
            && $this->checkEditId('com_compacttables.edit.table', $id);

        if ($editing) {
            // Not "edit": some site templates (T4) switch to their own article-editing page for layout=edit.
            // The view picks its screen itself.
            $this->input->set('layout', 'default');
        } else {
            // Nothing else is reachable here, whatever the URL asks for.
            $this->input->set('view', 'tables');
            $this->input->set('layout', 'modal');
        }

        $this->input->set('tmpl', 'component');

        return parent::display(false, $urlparams);
    }
}
