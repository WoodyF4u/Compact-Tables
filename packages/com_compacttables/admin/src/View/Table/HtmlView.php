<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Compactweb\Component\Compacttables\Administrator\View\Table;

use Compactweb\Component\Compacttables\Administrator\Helper\EditorToolbar;
use Compactweb\Component\Compacttables\Administrator\Helper\TableDataHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Create / edit a table.
 */
class HtmlView extends BaseHtmlView
{
    protected $form;

    protected $item;

    protected $state;

    protected $canDo;

    /**
     * True when the editor is shown inside the picker dialog: no Joomla toolbar
     * there, so the screen brings its own Save and Cancel buttons.
     */
    protected bool $embedded = false;

    /**
     * Name of the article editor the picker belongs to (travels along, so the picker still knows it afterwards).
     */
    protected string $editorName = '';

    protected bool $canSave = false;

    public function display($tpl = null): void
    {
        $model = $this->getModel();

        $this->form  = $model->getForm();
        $this->item  = $model->getItem();
        $this->state = $model->getState();
        $this->canDo = ContentHelper::getActions('com_compacttables');

        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $input            = Factory::getApplication()->getInput();
        $this->embedded   = $input->getCmd('tmpl') === 'component' && (bool) $input->getInt('picker');
        $this->editorName = $input->getCmd('editor', '');
        $this->canSave    = (bool) $this->canDo->get(empty($this->item->id) ? 'core.create' : 'core.edit');

        if (!$this->embedded) {
            $this->addToolbar();
        }

        $this->addEditorOptions();

        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        // Joomla's own menu stays visible here; its Toggle Menu button collapses it when more room is wanted.

        $isNew   = empty($this->item->id);
        $canDo   = $this->canDo;
        $canSave = $isNew ? $canDo->get('core.create') : $canDo->get('core.edit');
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(
            Text::_($isNew ? 'COM_COMPACTTABLES_TABLE_NEW' : 'COM_COMPACTTABLES_TABLE_EDIT'),
            'th'
        );

        if ($canSave) {
            $toolbar->apply('table.apply');

            $saveGroup = $toolbar->dropdownButton('save-group');
            $saveGroup->configure(
                function (Toolbar $childBar) use ($canDo, $isNew) {
                    $childBar->save('table.save');

                    if ($canDo->get('core.create')) {
                        $childBar->save2new('table.save2new');

                        // Only an existing table can be copied.
                        if (!$isNew) {
                            $childBar->save2copy('table.save2copy');
                        }
                    }
                }
            );
        }

        $toolbar->cancel('table.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        // Help opens in a dialog here: leaving the page would throw away unsaved work.
        $toolbar->popupButton('help', 'JHELP')
            ->popupType('iframe')
            ->textHeader(Text::_('COM_COMPACTTABLES_HELP_TITLE'))
            ->url(Route::_('index.php?option=com_compacttables&view=help&tmpl=component', false))
            ->modalWidth('60rem')
            ->modalHeight('80vh')
            ->icon('icon-question')
            ->buttonClass('btn btn-info tm-toolbar-end');
    }

    /**
     * Everything the grid editor (JavaScript) needs.
     */
    protected function addEditorOptions(): void
    {
        $token = Session::getFormToken() . '=1';

        $this->getDocument()->addScriptOptions('com_compacttables.editor', [
            'maxCols' => TableDataHelper::getMaxColumns(),
            'maxRows' => TableDataHelper::MAX_ROWS,
            // Joomla's own lists to choose from; they exist in the administrator and on the website.
            'urls'    => [
                // Joomla only fills its list of menu items for people who manage menus; for others the choice is left out.
                'menu'    => $this->getCurrentUser()->authorise('core.manage', 'com_menus')
                    ? Uri::base(true) . '/index.php?option=com_menus&view=items&layout=modal&tmpl=component&client_id=0&' . $token
                    : '',
                'article' => Uri::base(true) . '/index.php?option=com_content&view=articles&layout=modal&tmpl=component&' . $token,
            ],
            'embedded' => $this->embedded,
            'toolbar'    => EditorToolbar::getLayout(),
            'tableClass' => trim((string) \Joomla\CMS\Component\ComponentHelper::getParams('com_compacttables')->get('table_class', 'table')),
        ]);

        foreach (
            [
                'COM_COMPACTTABLES_EDITOR_ADD_ROW', 'COM_COMPACTTABLES_EDITOR_ADD_COLUMN', 'COM_COMPACTTABLES_EDITOR_DELETE_ROW',
                'COM_COMPACTTABLES_EDITOR_DELETE_COLUMN', 'COM_COMPACTTABLES_EDITOR_MOVE_ROW', 'COM_COMPACTTABLES_EDITOR_CELL_LABEL',
                'COM_COMPACTTABLES_EDITOR_CONFIRM_DELETE_ROW', 'COM_COMPACTTABLES_EDITOR_CONFIRM_DELETE_COLUMN',
                'COM_COMPACTTABLES_EDITOR_MAX_COLUMNS', 'COM_COMPACTTABLES_EDITOR_MAX_ROWS',
                'COM_COMPACTTABLES_EDITOR_LINK_MENU', 'COM_COMPACTTABLES_EDITOR_LINK_ARTICLE', 'COM_COMPACTTABLES_EDITOR_LINK_URL', 'COM_COMPACTTABLES_EDITOR_LINK_TEL', 'COM_COMPACTTABLES_EDITOR_LINK_EMAIL',
                'COM_COMPACTTABLES_EDITOR_TEL_LABEL', 'COM_COMPACTTABLES_EDITOR_TEL_INVALID', 'COM_COMPACTTABLES_EDITOR_EMAIL_LABEL', 'COM_COMPACTTABLES_EDITOR_EMAIL_INVALID',
                'COM_COMPACTTABLES_EDITOR_UNLINK', 'COM_COMPACTTABLES_EDITOR_LINK_CURRENT', 'COM_COMPACTTABLES_TB_LABEL', 'COM_COMPACTTABLES_TB_BOLD',
                'COM_COMPACTTABLES_TB_ITALIC', 'COM_COMPACTTABLES_TB_UNDERLINE', 'COM_COMPACTTABLES_TB_STRIKETHROUGH', 'COM_COMPACTTABLES_TB_SUBSCRIPT',
                'COM_COMPACTTABLES_TB_SUPERSCRIPT', 'COM_COMPACTTABLES_TB_LINK', 'COM_COMPACTTABLES_TB_REMOVEFORMAT',
                'COM_COMPACTTABLES_EDITOR_SELECT_MENU', 'COM_COMPACTTABLES_EDITOR_SELECT_ARTICLE', 'COM_COMPACTTABLES_EDITOR_URL_LABEL',
                'COM_COMPACTTABLES_EDITOR_URL_NEW_TAB', 'COM_COMPACTTABLES_EDITOR_URL_INVALID', 'COM_COMPACTTABLES_EDITOR_HEADER_ROW',
                'COM_COMPACTTABLES_EDITOR_ROW_MOVED', 'COM_COMPACTTABLES_PREVIEW_SAMPLE', 'JAPPLY', 'JCANCEL',
            ] as $key
        ) {
            Text::script($key);
        }
    }
}
