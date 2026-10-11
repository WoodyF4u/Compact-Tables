<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Compactweb\Component\Compacttables\Administrator\View\Table\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->useStyle('com_compacttables.admin')
    ->useScript('com_compacttables.editor');

// The preview on the CSS tab uses the very same stylesheet as the website.
$wa->registerAndUseStyle('plg_content_compacttables.table', 'plg_content_compacttables/compacttables.css');

// The dialogs this screen opens itself (choose a menu item or article) look the same in every template.
$wa->registerAndUseStyle('com_compacttables.picker-dialog', 'com_compacttables/picker-dialog.css');

$embedded = $this->embedded;
$extra    = $embedded ? '&tmpl=component&picker=1&editor=' . rawurlencode($this->editorName) : '';
$id        = (int) $this->item->id;
$shortcode = '{table ' . $id . '}';
?>
<?php if ($embedded) : ?>
<div class="tm-embedded">
<?php endif; ?>
<form action="<?php echo Route::_('index.php?option=com_compacttables&view=table' . (Factory::getApplication()->isClient('site') ? '' : '&layout=edit') . '&id=' . $id . $extra); ?>"
    method="post" name="adminForm" id="adminForm" class="form-validate tm-form"
    aria-label="<?php echo Text::_($id ? 'COM_COMPACTTABLES_TABLE_EDIT' : 'COM_COMPACTTABLES_TABLE_NEW', true); ?>">

    <?php if ($embedded) : ?>
        <div class="tm-embedded__bar">
            <h1 class="tm-embedded__title"><?php echo Text::_($id ? 'COM_COMPACTTABLES_EMBEDDED_EDIT' : 'COM_COMPACTTABLES_EMBEDDED_NEW'); ?></h1>
            <div class="tm-embedded__buttons">
                <?php if ($this->canSave) : ?>
                    <button type="button" class="tm-btn tm-btn--primary" data-tm-task="table.save"><?php echo Text::_('COM_COMPACTTABLES_EMBEDDED_SAVE_BACK'); ?></button>
                    <button type="button" class="tm-btn" data-tm-task="table.apply"><?php echo Text::_('COM_COMPACTTABLES_EMBEDDED_SAVE'); ?></button>
                <?php endif; ?>
                <button type="button" class="tm-btn" data-tm-task="table.cancel"><?php echo Text::_('COM_COMPACTTABLES_EMBEDDED_BACK'); ?></button>
            </div>
        </div>
        <?php // Messages from the server (e.g. after "Save"); the page around a dialog does not always show them. ?>
        <?php foreach (\Joomla\CMS\Factory::getApplication()->getMessageQueue(true) as $message) : ?>
            <p class="tm-modal__message tm-modal__message--<?php echo $this->escape($message['type']); ?>" role="status"><?php echo $message['message']; ?></p>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'compacttablesTab', ['active' => 'tm-table', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'compacttablesTab', 'tm-table', Text::_('COM_COMPACTTABLES_TAB_TABLE')); ?>
            <div class="tm-basics">
                <div class="tm-basics__title">
                    <?php echo $this->form->renderField('title'); ?>
                </div>
                <div class="tm-basics__switch">
                    <?php echo $this->form->renderField('has_header'); ?>
                </div>
            </div>

            <?php if ($id) : ?>
                <p class="tm-usage">
                    <?php echo Text::_('COM_COMPACTTABLES_USAGE'); ?>
                    <code class="tm-shortcode"><?php echo $shortcode; ?></code>
                    <button type="button" class="btn btn-sm btn-link tm-copy" data-tm-copy="<?php echo $shortcode; ?>"
                        title="<?php echo Text::_('COM_COMPACTTABLES_COPY_SHORTCODE'); ?>">
                        <span class="tm-icon" data-tm-icon="copy" aria-hidden="true"></span>
                        <span class="visually-hidden"><?php echo Text::_('COM_COMPACTTABLES_COPY_SHORTCODE'); ?></span>
                    </button>
                </p>
            <?php else : ?>
                <p class="tm-usage"><?php echo Text::_('COM_COMPACTTABLES_USAGE_NEW'); ?></p>
            <?php endif; ?>

            <div id="tm-editor" class="tm-editor">
                <noscript><div class="alert alert-warning"><?php echo Text::_('COM_COMPACTTABLES_NOSCRIPT'); ?></div></noscript>
            </div>
            <div id="tm-live" class="visually-hidden" aria-live="polite"></div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'compacttablesTab', 'tm-css', Text::_('COM_COMPACTTABLES_TAB_CSS')); ?>
            <div class="row">
                <div class="col-12 col-xl-5" id="tm-css-fields">
                    <p class="tm-css-intro"><?php echo Text::_('COM_COMPACTTABLES_CSS_INTRO'); ?></p>
                    <?php foreach (['css_header', 'css_body', 'css_cells', 'css_borders', 'css_layout', 'css_responsive', 'css_link'] as $fieldset) : ?>
                        <fieldset class="options-form tm-css-set" data-tm-fieldset="<?php echo $fieldset; ?>">
                            <legend><?php echo Text::_($this->form->getFieldsets()[$fieldset]->label); ?></legend>
                            <?php if ($fieldset === 'css_header') : ?>
                                <p class="tm-header-off text-muted" hidden><?php echo Text::_('COM_COMPACTTABLES_CSS_HEADER_OFF'); ?></p>
                            <?php endif; ?>
                            <?php if ($fieldset === 'css_body') : ?>
                                <p class="tm-css-note"><?php echo Text::_('COM_COMPACTTABLES_FIELDSET_BODY_DESC'); ?></p>
                            <?php endif; ?>
                            <?php echo $this->form->renderFieldset($fieldset); ?>
                        </fieldset>
                    <?php endforeach; ?>
                </div>
                <div class="col-12 col-xl-7">
                    <div class="tm-preview-panel">
                        <div class="tm-preview-bar">
                            <h3 class="tm-preview-title"><?php echo Text::_('COM_COMPACTTABLES_PREVIEW'); ?></h3>
                            <div class="btn-group btn-group-sm" role="group" aria-label="<?php echo Text::_('COM_COMPACTTABLES_PREVIEW_WIDTH'); ?>">
                                <button type="button" class="btn btn-outline-secondary active" data-tm-preview-width="wide" aria-pressed="true">
                                    <span class="tm-icon" data-tm-icon="desktop" aria-hidden="true"></span> <?php echo Text::_('COM_COMPACTTABLES_PREVIEW_WIDE'); ?>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" data-tm-preview-width="narrow" aria-pressed="false">
                                    <span class="tm-icon" data-tm-icon="mobile" aria-hidden="true"></span> <?php echo Text::_('COM_COMPACTTABLES_PREVIEW_NARROW'); ?>
                                </button>
                            </div>
                        </div>
                        <div class="tm-preview-stage" data-bs-theme="light">
                            <div id="tm-preview" class="tm-preview"></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    </div>

    <?php echo $this->form->getInput('data'); ?>
    <?php echo $this->form->getInput('id'); ?>
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php if ($embedded) : ?>
</div>
<?php endif; ?>
