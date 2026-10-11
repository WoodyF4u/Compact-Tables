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
 * A CSS length (number + unit) that may be left empty, meaning "use the template's value".
 * The value is stored as one string, e.g. "2px" or "1.1rem".
 */
class TmsizeField extends FormField
{
    protected $type = 'Tmsize';

    protected function getInput()
    {
        $units = array_values(array_filter(array_map('trim', explode(',', (string) ($this->element['units'] ?? 'px')))));
        $units = $units ?: ['px'];

        $number = '';
        $unit   = $units[0];

        if (preg_match('~^(\d+(?:\.\d+)?)([a-z%]*)$~i', trim((string) $this->value), $m)) {
            $number = $m[1];
            $unit   = \in_array($m[2], $units, true) ? $m[2] : $units[0];
        }

        $id    = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');
        $name  = htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars(Text::_((string) $this->element['label']), ENT_QUOTES, 'UTF-8');
        $attr  = '';

        foreach (['min', 'max', 'step'] as $key) {
            if (isset($this->element[$key])) {
                $attr .= ' ' . $key . '="' . htmlspecialchars((string) $this->element[$key], ENT_QUOTES, 'UTF-8') . '"';
            }
        }

        $html = '<div class="input-group tm-size" data-tm-size>'
            . '<input type="number" class="form-control" id="' . $id . '" value="' . htmlspecialchars($number, ENT_QUOTES, 'UTF-8') . '"'
            . $attr . ' inputmode="decimal" placeholder="' . htmlspecialchars(Text::_((string) ($this->element['hint'] ?? 'COM_COMPACTTABLES_OPTION_TEMPLATE_DEFAULT')), ENT_QUOTES, 'UTF-8') . '" data-tm-size-number>';

        if (\count($units) > 1) {
            $html .= '<select class="form-select tm-size__unit" aria-label="' . $label . ' – '
                . htmlspecialchars(Text::_('COM_COMPACTTABLES_UNIT'), ENT_QUOTES, 'UTF-8') . '" data-tm-size-unit>';

            foreach ($units as $option) {
                $option = htmlspecialchars($option, ENT_QUOTES, 'UTF-8');
                $html  .= '<option value="' . $option . '"' . ($option === $unit ? ' selected' : '') . '>' . $option . '</option>';
            }

            $html .= '</select>';
        } else {
            $html .= '<span class="input-group-text" data-tm-size-unit data-value="' . htmlspecialchars($unit, ENT_QUOTES, 'UTF-8') . '">'
                . htmlspecialchars($unit, ENT_QUOTES, 'UTF-8') . '</span>';
        }

        $html .= '<input type="hidden" name="' . $name . '" value="' . ($number === '' ? '' : htmlspecialchars($number . $unit, ENT_QUOTES, 'UTF-8')) . '" data-tm-size-value>'
            . '</div>';

        return $html;
    }
}
