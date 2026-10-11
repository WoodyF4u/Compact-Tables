<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Compactweb\Component\Compacttables\Administrator\Controller;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;
use Joomla\CMS\Session\Session;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Edit controller for a single table.
 *
 * Besides the normal edit screen of the administrator it serves the editor
 * inside the picker dialog (request parameter "picker"): the same form, but
 * shown without Joomla's toolbar, and going back to the picker's list
 * instead of the overview. The website uses this class through a subclass.
 */
class TableController extends FormController
{
    protected $text_prefix = 'COM_COMPACTTABLES_TABLE';

    protected $view_list = 'tables';

    public function __construct($config = [], ?MVCFactoryInterface $factory = null, ?CMSApplicationInterface $app = null, ?Input $input = null, ?FormFactoryInterface $formFactory = null)
    {
        parent::__construct($config, $factory, $app, $input, $formFactory);

        // On the website Joomla does not load these texts by itself; the messages after saving need them.
        $this->app->getLanguage()->load('com_compacttables', JPATH_ADMINISTRATOR . '/components/com_compacttables');
    }

    /**
     * Who may make a table: the permission "Create" of Compact Tables (Options > Permissions).
     * Joomla's default also looks at content categories, which have nothing to do with tables.
     */
    protected function allowAdd($data = [])
    {
        $user = $this->app->getIdentity();

        return $user && !$user->guest && $user->authorise('core.create', 'com_compacttables');
    }

    /**
     * Who may change a table: the permission "Edit" of Compact Tables.
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        $user = $this->app->getIdentity();

        return $user && !$user->guest && $user->authorise('core.edit', 'com_compacttables');
    }

    private function inPicker(): bool
    {
        return (bool) $this->input->getInt('picker');
    }

    private function pickerAppend(): string
    {
        return '&picker=1&editor=' . rawurlencode($this->input->getCmd('editor', ''));
    }

    protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
    {
        $append = parent::getRedirectToItemAppend($recordId, $urlVar);

        return $this->inPicker() ? $append . $this->pickerAppend() : $append;
    }

    protected function getRedirectToListAppend()
    {
        $append = parent::getRedirectToListAppend();

        // Back to the picker's list, which wants the session token in its address.
        return $this->inPicker()
            ? $append . '&layout=modal' . $this->pickerAppend() . '&' . Session::getFormToken() . '=1'
            : $append;
    }
}
