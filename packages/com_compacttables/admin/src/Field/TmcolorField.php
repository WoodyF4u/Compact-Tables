<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Compactweb\Component\Compacttables\Administrator\Field;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Colour that may be left empty, meaning "use the template's colour".
 */
class TmcolorField extends FormField
{
    protected $type = 'Tmcolor';

    protected function getInput()
    {
        $value = preg_match('~^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$~', (string) $this->value) ? strtolower((string) $this->value) : '';
        $id    = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');
        $name  = htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars(Text::_((string) $this->element['label']), ENT_QUOTES, 'UTF-8');

        // The native picker cannot be empty, so it mirrors the text field.
        $picker = \strlen($value) === 4
            ? '#' . $value[1] . $value[1] . $value[2] . $value[2] . $value[3] . $value[3]
            : ($value ?: '#ffffff');

        return '<div class="input-group tm-color' . ($value === '' ? ' tm-color--empty' : '') . '" data-tm-color>'
            . '<input type="color" class="form-control form-control-color tm-color__picker" value="' . $picker . '"'
            . ' aria-label="' . $label . ' – ' . htmlspecialchars(Text::_('COM_COMPACTTABLES_COLOR_PICK'), ENT_QUOTES, 'UTF-8') . '" data-tm-color-picker>'
            . '<input type="text" class="form-control tm-color__text" id="' . $id . '" name="' . $name . '" value="' . $value . '"'
            . ' placeholder="' . htmlspecialchars(Text::_('COM_COMPACTTABLES_OPTION_TEMPLATE_DEFAULT'), ENT_QUOTES, 'UTF-8') . '"'
            . ' pattern="#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})" maxlength="7" autocomplete="off" spellcheck="false" data-tm-color-text>'
            . '<button type="button" class="btn btn-secondary" data-tm-color-clear title="'
            . htmlspecialchars(Text::_('COM_COMPACTTABLES_USE_TEMPLATE_DEFAULT'), ENT_QUOTES, 'UTF-8') . '">'
            . '<span class="tm-icon" data-tm-icon="times" aria-hidden="true"></span>'
            . '<span class="visually-hidden">' . htmlspecialchars(Text::_('COM_COMPACTTABLES_USE_TEMPLATE_DEFAULT'), ENT_QUOTES, 'UTF-8') . '</span>'
            . '</button>'
            . '</div>';
    }
}
