<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Compactweb\Component\Compacttables\Administrator\View\Tables;

use Compactweb\Component\Compacttables\Administrator\Helper\PickerAccess;
use Compactweb\Component\Compacttables\Administrator\Helper\VersionHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Overview of all tables.
 */
class HtmlView extends BaseHtmlView
{
    protected $items = [];

    protected $pagination;

    protected $state;

    protected $canDo;

    protected string $componentVersion = '';

    /**
     * @var  array{edit: bool, create: bool, delete: bool}  What the picker offers besides choosing a table.
     */
    protected array $adminActions = ['edit' => false, 'create' => false, 'delete' => false];

    public function display($tpl = null): void
    {
        $model   = $this->getModel();
        $isModal = $this->getLayout() === 'modal';

        if ($isModal) {
            // The picker in the editor shows every table on one page.
            $model->getState();
            $model->setState('list.limit', 0);
            $model->setState('list.start', 0);
        }

        $this->items      = $model->getItems() ?: [];
        $this->pagination = $model->getPagination();
        $this->state      = $model->getState();
        $this->canDo      = ContentHelper::getActions('com_compacttables');
        $this->componentVersion = VersionHelper::get();
        $this->adminActions     = PickerAccess::getTableActions($this->getCurrentUser());

        if (!$isModal) {
            $this->addToolbar();
        }

        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(Text::_('COM_COMPACTTABLES'), 'th');

        // With an empty list the big plus sign is the only way in, as intended.
        if ($this->items && $this->canDo->get('core.create')) {
            $toolbar->addNew('table.add');
        }

        // Options docks itself (and what follows) to the right edge; without it Help does that itself.
        $hasOptions = $this->canDo->get('core.admin') || $this->canDo->get('core.options');

        if ($hasOptions) {
            $toolbar->preferences('com_compacttables');
        }

        $toolbar->linkButton('help', 'JHELP')
            ->url(Route::_('index.php?option=com_compacttables&view=help'))
            ->icon('icon-question')
            ->buttonClass('btn btn-info' . ($hasOptions ? '' : ' tm-toolbar-end'));
    }
}
