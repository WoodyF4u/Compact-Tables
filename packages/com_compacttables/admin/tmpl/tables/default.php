<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Compactweb\Component\Compacttables\Administrator\Helper\LinkHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Compactweb\Component\Compacttables\Administrator\View\Tables\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useStyle('com_compacttables.admin')
    ->useScript('com_compacttables.list');

Text::script('COM_COMPACTTABLES_CONFIRM_DELETE');
Text::script('COM_COMPACTTABLES_SHORTCODE_COPIED');

$canCreate = $this->canDo->get('core.create');
$canEdit   = $this->canDo->get('core.edit');
$canDelete = $this->canDo->get('core.delete');
$addUrl    = Route::_('index.php?option=com_compacttables&task=table.add');
?>
<form action="<?php echo Route::_('index.php?option=com_compacttables&view=tables'); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container" class="j-main-container">
        <h2 class="tm-page-title"><?php echo Text::_('COM_COMPACTTABLES'); ?></h2>
        <div class="tm-head">
            <p class="tm-summary">
                <?php echo $this->items
                    ? Text::plural('COM_COMPACTTABLES_SUMMARY_N_TABLES', $this->pagination->total)
                    : Text::_($canCreate ? 'COM_COMPACTTABLES_SUMMARY_EMPTY' : 'COM_COMPACTTABLES_SUMMARY_EMPTY_NO_CREATE'); ?>
            </p>
            <?php if ($this->componentVersion !== '') : ?>
                <span class="tm-version"><?php echo Text::_('COM_COMPACTTABLES'); ?> &middot; v<?php echo $this->escape($this->componentVersion); ?></span>
            <?php endif; ?>
        </div>

        <?php if (!$this->items) : ?>
            <div class="tm-empty">
                <?php if ($canCreate) : ?>
                    <a class="tm-add tm-add--first" href="<?php echo $addUrl; ?>" title="<?php echo Text::_('COM_COMPACTTABLES_ADD_FIRST_TABLE'); ?>">
                        <span class="icon-plus" aria-hidden="true"></span>
                        <span class="visually-hidden"><?php echo Text::_('COM_COMPACTTABLES_ADD_FIRST_TABLE'); ?></span>
                    </a>
                <?php endif; ?>
            </div>
        <?php else : ?>
            <table class="table itemList tm-list" id="compacttablesList">
                <caption class="visually-hidden"><?php echo Text::_('COM_COMPACTTABLES_TABLES_CAPTION'); ?></caption>
                <thead>
                    <tr>
                        <th scope="col"><?php echo Text::_('COM_COMPACTTABLES_HEADING_NAME'); ?></th>
                        <th scope="col" class="w-20"><?php echo Text::_('COM_COMPACTTABLES_HEADING_SHORTCODE'); ?></th>
                        <th scope="col" class="w-20"><?php echo Text::_('COM_COMPACTTABLES_HEADING_LINKED'); ?></th>
                        <th scope="col" class="w-15 d-none d-lg-table-cell"><?php echo Text::_('JGLOBAL_MODIFIED'); ?></th>
                        <th scope="col" class="w-10 text-end"><?php echo Text::_('COM_COMPACTTABLES_HEADING_ACTIONS'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->items as $item) : ?>
                        <?php
                        $editUrl   = Route::_('index.php?option=com_compacttables&task=table.edit&id=' . (int) $item->id);
                        $shortcode = '{table ' . (int) $item->id . '}';
                        $title     = $this->escape($item->title);
                        ?>
                        <tr>
                            <th scope="row">
                                <?php if ($canEdit) : ?>
                                    <a href="<?php echo $editUrl; ?>"><?php echo $title; ?></a>
                                <?php else : ?>
                                    <?php echo $title; ?>
                                <?php endif; ?>
                            </th>
                            <td>
                                <code class="tm-shortcode"><?php echo $shortcode; ?></code>
                                <button type="button" class="btn btn-sm btn-link tm-copy" data-tm-copy="<?php echo $shortcode; ?>"
                                    title="<?php echo Text::_('COM_COMPACTTABLES_COPY_SHORTCODE'); ?>">
                                    <span class="icon-copy" aria-hidden="true"></span>
                                    <span class="visually-hidden"><?php echo Text::sprintf('COM_COMPACTTABLES_COPY_SHORTCODE_OF', $title); ?></span>
                                </button>
                            </td>
                            <td>
                                <?php $linked = LinkHelper::getLinked((int) $item->id); ?>
                                <?php if ($linked) : ?>
                                    <ul class="tm-linked">
                                        <?php foreach ($linked as $linkedTitle) : ?>
                                            <li><?php echo $this->escape($linkedTitle); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else : ?>
                                    <span class="tm-linked--none"><?php echo Text::_('COM_COMPACTTABLES_LINKED_NONE_LOWER'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="d-none d-lg-table-cell">
                                <?php echo HTMLHelper::_('date', $item->modified, Text::_('DATE_FORMAT_LC4')); ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <?php if ($canEdit) : ?>
                                    <a class="tm-action" href="<?php echo $editUrl; ?>" title="<?php echo Text::_('JACTION_EDIT'); ?>">
                                        <span class="icon-pencil-alt" aria-hidden="true"></span>
                                        <span class="visually-hidden"><?php echo Text::sprintf('COM_COMPACTTABLES_EDIT_TABLE', $title); ?></span>
                                    </a>
                                <?php endif; ?>
                                <?php if ($canDelete) : ?>
                                    <button type="button" class="tm-action tm-action--delete" data-tm-delete="<?php echo (int) $item->id; ?>"
                                        data-tm-title="<?php echo $title; ?>" data-tm-shortcode="<?php echo $shortcode; ?>" title="<?php echo Text::_('JACTION_DELETE'); ?>">
                                        <span class="icon-trash" aria-hidden="true"></span>
                                        <span class="visually-hidden"><?php echo Text::sprintf('COM_COMPACTTABLES_DELETE_TABLE', $title); ?></span>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($canCreate) : ?>
                <a class="tm-add" href="<?php echo $addUrl; ?>" title="<?php echo Text::_('COM_COMPACTTABLES_ADD_TABLE'); ?>">
                    <span class="icon-plus" aria-hidden="true"></span>
                    <span class="visually-hidden"><?php echo Text::_('COM_COMPACTTABLES_ADD_TABLE'); ?></span>
                </a>
            <?php endif; ?>

            <?php echo $this->pagination->getListFooter(); ?>
        <?php endif; ?>

        <input type="hidden" name="task" value="">
        <input type="hidden" name="boxchecked" value="0">
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
