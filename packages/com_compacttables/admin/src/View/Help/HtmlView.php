<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Compactweb\Component\Compacttables\Administrator\View\Help;

use Compactweb\Component\Compacttables\Administrator\Helper\TableDataHelper;
use Compactweb\Component\Compacttables\Administrator\Helper\VersionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Help page: explains every option. Purely informational, no model.
 */
class HtmlView extends BaseHtmlView
{
    protected string $componentVersion = '';

    protected int $maxColumns = 0;

    protected int $maxRows = 0;

    /**
     * True when the page is shown inside a dialog (opened from the edit screen).
     */
    protected bool $inDialog = false;

    public function display($tpl = null): void
    {
        $this->componentVersion = VersionHelper::get();
        $this->maxColumns       = TableDataHelper::MAX_COLUMNS;
        $this->maxRows          = TableDataHelper::MAX_ROWS;
        $this->inDialog         = Factory::getApplication()->getInput()->getCmd('tmpl') === 'component';

        if (!$this->inDialog) {
            ToolbarHelper::title(Text::_('COM_COMPACTTABLES_HELP_TITLE'), 'question');
            $this->getDocument()->getToolbar()
                ->linkButton('back', 'COM_COMPACTTABLES_HELP_BACK')
                ->url(Route::_('index.php?option=com_compacttables&view=tables'))
                ->icon('icon-chevron-left');
        }

        parent::display($tpl);
    }
}
