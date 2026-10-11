<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Compactweb\Component\Compacttables\Administrator\Field;

use Compactweb\Component\Compacttables\Administrator\Helper\LinkHelper;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Drop-down list of all other tables, each with a tick box: the ticked ones are linked to this table.
 */
class TmlinksField extends FormField
{
    protected $type = 'Tmlinks';

    protected function getInput()
    {
        $form    = $this->form;
        $current = (int) $form->getValue('id');

        // After a failed save the posted choice comes back; otherwise the stored group decides.
        $posted = $this->value;
        $chosen = \is_array($posted) && $posted !== []
            ? array_map('intval', $posted)
            : array_keys(LinkHelper::getLinked($current));

        $tables = LinkHelper::getTables();
        unset($tables[$current]);
        uasort($tables, static fn (array $a, array $b): int => strnatcasecmp($a['title'], $b['title']));

        $esc  = static fn (string $text): string => htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $id   = $esc($this->id);
        $name = $esc($this->name);
        $none = Text::_('COM_COMPACTTABLES_LINKED_NONE');

        $names = [];
        $items = '';

        foreach ($tables as $tableId => $table) {
            $checked = \in_array($tableId, $chosen, true);

            if ($checked) {
                $names[] = $table['title'];
            }

            $boxId  = $id . '_' . $tableId;
            $items .= '<li class="form-check tm-links__item">'
                . '<input class="form-check-input" type="checkbox" id="' . $boxId . '" name="' . $name . '" value="' . $tableId . '"'
                . ' data-tm-links-title="' . $esc($table['title']) . '"' . ($checked ? ' checked' : '') . '>'
                . '<label class="form-check-label" for="' . $boxId . '">' . $esc($table['title'])
                . ' <span class="tm-links__meta">{table ' . $tableId . '} · ' . Text::plural('COM_COMPACTTABLES_N_COLUMNS', $table['cols']) . '</span>'
                . '</label></li>';
        }

        $summary = $names ? implode(', ', $names) : $none;

        // The hidden 0 makes sure the field is always sent, so "nothing ticked" can be told apart from "not on this form".
        $html = '<input type="hidden" name="' . $name . '" value="0">'
            . '<details class="tm-links" data-tm-links data-tm-links-none="' . $esc($none) . '">'
            . '<summary class="form-select tm-links__summary" id="' . $id . '" aria-describedby="' . $id . '-desc">'
            . '<span data-tm-links-summary>' . $esc($summary) . '</span></summary>'
            . '<div class="tm-links__panel">';

        $html .= $items !== ''
            ? '<ul class="tm-links__list list-unstyled">' . $items . '</ul>'
            : '<p class="tm-links__empty">' . Text::_('COM_COMPACTTABLES_LINKED_NO_OTHER_TABLES') . '</p>';

        return $html . '</div></details>';
    }
}
