<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var \Compactweb\Component\Compacttables\Administrator\View\Help\HtmlView $this */

$this->getDocument()->getWebAssetManager()->useStyle('com_compacttables.admin');

// Section title => items (title, body). Texts with %d get the limits filled in.
$sections = [
    'COM_COMPACTTABLES_HELP_SECTION_MAKE' => [
        ['COM_COMPACTTABLES_HELP_NEW_TITLE', Text::_('COM_COMPACTTABLES_HELP_NEW_BODY')],
        ['COM_COMPACTTABLES_HELP_NAME_TITLE', Text::_('COM_COMPACTTABLES_HELP_NAME_BODY')],
        ['COM_COMPACTTABLES_HELP_SIZE_TITLE', Text::sprintf('COM_COMPACTTABLES_HELP_SIZE_BODY', $this->maxColumns, $this->maxRows)],
        ['COM_COMPACTTABLES_HELP_HEADER_TITLE', Text::_('COM_COMPACTTABLES_HELP_HEADER_BODY')],
        ['COM_COMPACTTABLES_HELP_CELLS_TITLE', Text::_('COM_COMPACTTABLES_HELP_CELLS_BODY')],
        ['COM_COMPACTTABLES_HELP_ROWSCOLS_TITLE', Text::_('COM_COMPACTTABLES_HELP_ROWSCOLS_BODY')],
        ['COM_COMPACTTABLES_HELP_MOVE_TITLE', Text::_('COM_COMPACTTABLES_HELP_MOVE_BODY')],
        ['COM_COMPACTTABLES_HELP_TOOLBAR_TITLE', Text::_('COM_COMPACTTABLES_HELP_TOOLBAR_BODY')],
        ['COM_COMPACTTABLES_HELP_LINKS_TITLE', Text::_('COM_COMPACTTABLES_HELP_LINKS_BODY')],
        ['COM_COMPACTTABLES_HELP_SAVE_TITLE', Text::_('COM_COMPACTTABLES_HELP_SAVE_BODY')],
    ],
    'COM_COMPACTTABLES_HELP_SECTION_CSS' => [
        ['COM_COMPACTTABLES_HELP_TEMPLATE_TITLE', Text::_('COM_COMPACTTABLES_HELP_TEMPLATE_BODY')],
        ['COM_COMPACTTABLES_HELP_TOPROW_TITLE', Text::_('COM_COMPACTTABLES_HELP_TOPROW_BODY')],
        ['COM_COMPACTTABLES_HELP_BODY_TITLE', Text::_('COM_COMPACTTABLES_HELP_BODY_BODY')],
        ['COM_COMPACTTABLES_HELP_CELLALIGN_TITLE', Text::_('COM_COMPACTTABLES_HELP_CELLALIGN_BODY')],
        ['COM_COMPACTTABLES_HELP_LINES_TITLE', Text::_('COM_COMPACTTABLES_HELP_LINES_BODY')],
        ['COM_COMPACTTABLES_HELP_WIDTH_TITLE', Text::_('COM_COMPACTTABLES_HELP_WIDTH_BODY')],
        ['COM_COMPACTTABLES_HELP_SPACING_TITLE', Text::_('COM_COMPACTTABLES_HELP_SPACING_BODY')],
        ['COM_COMPACTTABLES_HELP_CELL_TEXT_TITLE', Text::_('COM_COMPACTTABLES_HELP_CELL_TEXT_BODY')],
        ['COM_COMPACTTABLES_HELP_SMALL_TITLE', Text::_('COM_COMPACTTABLES_HELP_SMALL_BODY')],
        ['COM_COMPACTTABLES_HELP_LINK_TITLE', Text::_('COM_COMPACTTABLES_HELP_LINK_BODY')],
        ['COM_COMPACTTABLES_HELP_PREVIEW_TITLE', Text::_('COM_COMPACTTABLES_HELP_PREVIEW_BODY')],
    ],
    'COM_COMPACTTABLES_HELP_SECTION_USE' => [
        ['COM_COMPACTTABLES_HELP_CODE_TITLE', Text::_('COM_COMPACTTABLES_HELP_CODE_BODY')],
        ['COM_COMPACTTABLES_HELP_BUTTON_TITLE', Text::_('COM_COMPACTTABLES_HELP_BUTTON_BODY')],
        ['COM_COMPACTTABLES_HELP_DELETE_TITLE', Text::_('COM_COMPACTTABLES_HELP_DELETE_BODY')],
    ],
    'COM_COMPACTTABLES_HELP_SECTION_SETTINGS' => [
        ['COM_COMPACTTABLES_HELP_OPTIONS_TITLE', Text::_('COM_COMPACTTABLES_HELP_OPTIONS_BODY')],
        ['COM_COMPACTTABLES_HELP_TBCONF_TITLE', Text::_('COM_COMPACTTABLES_HELP_TBCONF_BODY')],
        ['COM_COMPACTTABLES_HELP_PERMISSIONS_TITLE', Text::_('COM_COMPACTTABLES_HELP_PERMISSIONS_BODY')],
    ],
];
?>
<div class="tm-help<?php echo $this->inDialog ? ' tm-help--dialog' : ''; ?>">
    <?php if ($this->componentVersion !== '') : ?>
        <div class="tm-head tm-head--end">
            <span class="tm-version"><?php echo Text::_('COM_COMPACTTABLES'); ?> &middot; v<?php echo $this->escape($this->componentVersion); ?></span>
        </div>
    <?php endif; ?>

    <h2 class="tm-help__heading"><?php echo Text::_('COM_COMPACTTABLES_HELP_ABOUT_TITLE'); ?></h2>
    <p class="tm-help__intro"><?php echo Text::_('COM_COMPACTTABLES_HELP_ABOUT_BODY'); ?></p>

    <?php foreach ($sections as $sectionTitle => $items) : ?>
        <section class="tm-help__section">
            <h2 class="tm-help__heading"><?php echo Text::_($sectionTitle); ?></h2>
            <?php foreach ($items as [$title, $body]) : ?>
                <div class="tm-help__item">
                    <h3 class="tm-help__item-title"><?php echo Text::_($title); ?></h3>
                    <p class="tm-help__item-body"><?php echo $body; ?></p>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endforeach; ?>
</div>
