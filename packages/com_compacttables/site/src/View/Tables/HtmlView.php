<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Compactweb\Component\Compacttables\Site\View\Tables;

use Compactweb\Component\Compacttables\Administrator\Helper\PickerAccess;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Session\Session;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Table picker for editing articles on the website.
 */
class HtmlView extends BaseHtmlView
{
    protected $items = [];

    /**
     * @var  array{edit: bool, create: bool, delete: bool}  What the picker offers besides choosing a table.
     */
    protected array $adminActions = ['edit' => false, 'create' => false, 'delete' => false];

    public function display($tpl = null): void
    {
        $app = Factory::getApplication();

        // Only for logged-in people who may write or edit content, and only when opened from the editor.
        if (!PickerAccess::isAllowed($app->getIdentity()) || !Session::checkToken('get')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        // All texts live with the administrator part of the component.
        $app->getLanguage()->load('com_compacttables', JPATH_ADMINISTRATOR . '/components/com_compacttables')
            || $app->getLanguage()->load('com_compacttables', JPATH_ADMINISTRATOR);

        $model = $this->getModel();
        $model->getState();
        $model->setState('list.limit', 0);
        $model->setState('list.start', 0);

        $this->items = $model->getItems() ?: [];
        $this->adminActions = PickerAccess::getTableActions($app->getIdentity());

        // One layout for both sides; a template override of either side keeps working.
        $this->addTemplatePath(JPATH_ADMINISTRATOR . '/components/com_compacttables/tmpl/tables');
        $this->addTemplatePath(JPATH_SITE . '/components/com_compacttables/tmpl/tables');
        $this->addTemplatePath(JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_compacttables/tables');
        $this->setLayout('modal');

        $this->getDocument()->setMetaData('robots', 'noindex, nofollow');

        parent::display($tpl);
    }
}
