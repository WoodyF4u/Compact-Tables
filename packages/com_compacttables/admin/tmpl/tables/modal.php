<?php

/**
 * List of tables shown in a dialog when the "Compact Tables" editor button is used.
 *
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

/** @var \Compactweb\Component\Compacttables\Administrator\View\Tables\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useStyle('com_compacttables.admin')
    ->useScript('com_compacttables.modal');

$editor = Factory::getApplication()->getInput()->getCmd('editor', '');

$canDelete = !empty($this->adminActions['delete']);

$this->getDocument()->addScriptOptions('com_compacttables.modal', [
    'editor'    => $editor,
    'deleteUrl' => $canDelete ? Uri::base() . 'index.php?option=com_compacttables&task=picker.delete&format=json' : '',
    'token'     => $canDelete ? Session::getFormToken() : '',
]);

if ($canDelete) {
    Text::script('COM_COMPACTTABLES_CONFIRM_DELETE');
    Text::script('COM_COMPACTTABLES_MODAL_DELETE_FAILED');
}

// Making and editing a table happens right here in the dialog, for those who may.
// Drawn here rather than taken from an icon font: not every site template has the same icons.
$svg = static fn (string $path): string => '<svg class="tm-modal__icon" viewBox="0 0 16 16" width="16" height="16" fill="currentColor" aria-hidden="true" focusable="false"><path d="' . $path . '"/></svg>';
$iconEdit   = $svg('M12.9 1.6a1.5 1.5 0 0 1 2.1 0l.4.4a1.5 1.5 0 0 1 0 2.1l-8.5 8.5a1 1 0 0 1-.4.25l-3.2 1a.5.5 0 0 1-.63-.63l1-3.2a1 1 0 0 1 .25-.4l8.5-8.5zM11.5 4.4 4.6 11.3l-.5 1.6 1.6-.5 6.9-6.9-1.1-1.1z');
$iconDelete = $svg('M6 1.5A1.5 1.5 0 0 0 4.5 3H2a.75.75 0 0 0 0 1.5h.6l.7 8.6A2 2 0 0 0 5.3 15h5.4a2 2 0 0 0 2-1.9l.7-8.6h.6A.75.75 0 0 0 14 3h-2.5A1.5 1.5 0 0 0 10 1.5H6zm.25 4.25a.75.75 0 0 1 .75.75v5a.75.75 0 0 1-1.5 0v-5a.75.75 0 0 1 .75-.75zm3.5 0a.75.75 0 0 1 .75.75v5a.75.75 0 0 1-1.5 0v-5a.75.75 0 0 1 .75-.75z');
$iconNew    = $svg('M8 2a.9.9 0 0 1 .9.9v4.2h4.2a.9.9 0 0 1 0 1.8H8.9v4.2a.9.9 0 0 1-1.8 0V8.9H2.9a.9.9 0 0 1 0-1.8h4.2V2.9A.9.9 0 0 1 8 2z');

$editBase  = Uri::base() . 'index.php?option=com_compacttables&tmpl=component&picker=1&editor=' . rawurlencode($editor);
$canEdit   = !empty($this->adminActions['edit']);
$canCreate = !empty($this->adminActions['create']);
?>
<div class="tm-modal">
<div class="tm-modal__inner">
    <?php // Messages such as "Table saved" after coming back from the editor. ?>
    <?php foreach (Factory::getApplication()->getMessageQueue(true) as $message) : ?>
        <p class="tm-modal__message tm-modal__message--<?php echo $this->escape($message['type']); ?>" role="status"><?php echo $message['message']; ?></p>
    <?php endforeach; ?>
    <h1 class="visually-hidden"><?php echo Text::_('COM_COMPACTTABLES_MODAL_TITLE'); ?></h1>
    <div class="tm-modal__error" role="alert" hidden></div>

    <?php if (!$this->items) : ?>
        <div class="alert alert-info">
            <span class="icon-info-circle" aria-hidden="true"></span>
            <?php echo Text::_('COM_COMPACTTABLES_MODAL_EMPTY'); ?>
        </div>
    <?php else : ?>
        <p class="tm-modal__intro"><?php echo Text::_('COM_COMPACTTABLES_MODAL_INTRO'); ?></p>

        <?php if (\count($this->items) > 8) : ?>
            <div class="tm-modal__filter">
                <label class="visually-hidden" for="tm-modal-filter"><?php echo Text::_('COM_COMPACTTABLES_MODAL_FILTER'); ?></label>
                <input type="search" class="form-control" id="tm-modal-filter" autocomplete="off"
                    placeholder="<?php echo Text::_('COM_COMPACTTABLES_MODAL_FILTER'); ?>">
            </div>
            <p class="alert alert-info" id="tm-modal-nomatch" hidden><?php echo Text::_('COM_COMPACTTABLES_MODAL_NO_MATCH'); ?></p>
        <?php endif; ?>

        <ul class="tm-modal__list">
            <?php foreach ($this->items as $item) : ?>
                <?php
                $shortcode = '{table ' . (int) $item->id . '}';
                $title     = $this->escape($item->title);
                ?>
                <li data-tm-search="<?php echo $this->escape(mb_strtolower($item->title . ' ' . (int) $item->id)); ?>">
                    <button type="button" class="tm-modal__pick" data-tm-insert="<?php echo $shortcode; ?>"
                        aria-label="<?php echo Text::sprintf('COM_COMPACTTABLES_MODAL_INSERT', $title); ?>">
                        <span class="tm-modal__name"><?php echo $title; ?></span>
                        <span class="tm-modal__meta">
                            <?php echo Text::sprintf('COM_COMPACTTABLES_SIZE', (int) $item->num_cols, (int) $item->num_rows); ?>
                            <code class="tm-shortcode"><?php echo $shortcode; ?></code>
                        </span>
                    </button>
                    <?php if ($canEdit) : ?>
                        <a class="tm-modal__edit" href="<?php echo $this->escape($editBase . '&task=table.edit&id=' . (int) $item->id); ?>"
                            title="<?php echo Text::sprintf('COM_COMPACTTABLES_EDIT_TABLE', $title); ?>">
                            <?php echo $iconEdit; ?>
                            <span class="tm-modal__edit-text"><?php echo Text::_('JACTION_EDIT'); ?></span>
                            <span class="visually-hidden"><?php echo $title; ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if ($canDelete) : ?>
                        <button type="button" class="tm-modal__delete" data-tm-delete="<?php echo (int) $item->id; ?>"
                            data-tm-title="<?php echo $title; ?>" data-tm-shortcode="<?php echo $shortcode; ?>"
                            title="<?php echo Text::sprintf('COM_COMPACTTABLES_DELETE_TABLE', $title); ?>">
                            <?php echo $iconDelete; ?>
                            <span class="visually-hidden"><?php echo Text::sprintf('COM_COMPACTTABLES_DELETE_TABLE', $title); ?></span>
                        </button>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($canCreate) : ?>
        <div class="tm-modal__admin">
            <a class="tm-modal__new" href="<?php echo $this->escape($editBase . '&task=table.add'); ?>">
                <?php echo $iconNew; ?>
                <?php echo Text::_('COM_COMPACTTABLES_MODAL_NEW'); ?>
            </a>
        </div>
    <?php endif; ?>
    <?php // Which version is running: handy when testing, and the first thing to check when something looks off. ?>
    <p class="tm-modal__version"><?php echo Text::_('COM_COMPACTTABLES'); ?> &middot; v<?php echo $this->escape(\Compactweb\Component\Compacttables\Administrator\Helper\VersionHelper::get()); ?></p>
</div>
</div>
