<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Compactweb\Component\Compacttables\Administrator\Field;

use Compactweb\Component\Compacttables\Administrator\Helper\EditorToolbar;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Lets the site owner compose the editor bar: buttons are dragged (or clicked)
 * between "current layout" and "available buttons".
 */
class TmtoolbarField extends FormField
{
    protected $type = 'Tmtoolbar';

    protected function getInput()
    {
        $app = Factory::getApplication();

        // This field lives on Joomla's own Options screen, so it brings its own texts and assets.
        $app->getLanguage()->load('com_compacttables', JPATH_ADMINISTRATOR . '/components/com_compacttables');

        $wa = $app->getDocument()->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('com_compacttables');
        $wa->useStyle('com_compacttables.admin')->useScript('com_compacttables.toolbar-config');

        $layout    = EditorToolbar::parse((string) $this->value);
        $available = array_diff(array_keys(EditorToolbar::BUTTONS), $layout);
        $esc       = static fn (string $text): string => htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        $chip = static function (string $id) use ($esc): string {
            if ($id === EditorToolbar::SEPARATOR) {
                $name = Text::_('COM_COMPACTTABLES_TB_SEPARATOR');

                return '<button type="button" class="tm-chip tm-chip--sep" draggable="true" data-id="|" aria-label="' . $esc($name) . '" title="' . $esc($name) . '">'
                    . '<span class="tm-chip__line" aria-hidden="true"></span><span class="tm-chip__name">' . $esc($name) . '</span></button>';
            }

            [$icon, $key] = EditorToolbar::BUTTONS[$id];
            $name         = Text::_($key);

            return '<button type="button" class="tm-chip" draggable="true" data-id="' . $esc($id) . '" title="' . $esc($name) . '">'
                . '<span class="' . $esc($icon) . '" aria-hidden="true"></span><span class="tm-chip__name">' . $esc($name) . '</span></button>';
        };

        $id = $esc($this->id);

        return '<div class="tm-tbconf" data-tm-tbconf data-default="' . $esc(EditorToolbar::DEFAULT_LAYOUT) . '" data-none="' . $esc(EditorToolbar::NONE) . '">'
            . '<p class="tm-tbconf__title" id="' . $id . '-current">' . Text::_('COM_COMPACTTABLES_TBCONF_CURRENT') . '</p>'
            . '<div class="tm-tbconf__zone tm-tbconf__zone--current" data-zone="current" role="group" aria-labelledby="' . $id . '-current">'
            . implode('', array_map($chip, $layout))
            . '</div>'
            . '<p class="tm-tbconf__title" id="' . $id . '-available">' . Text::_('COM_COMPACTTABLES_TBCONF_AVAILABLE') . '</p>'
            . '<div class="tm-tbconf__zone" data-zone="available" role="group" aria-labelledby="' . $id . '-available">'
            . implode('', array_map($chip, $available))
            . '<span class="tm-tbconf__source" data-source>' . $chip(EditorToolbar::SEPARATOR) . '</span>'
            . '</div>'
            . '<p class="tm-tbconf__hint">' . Text::_('COM_COMPACTTABLES_TBCONF_HINT') . '</p>'
            . '<button type="button" class="btn btn-sm btn-secondary" data-reset>' . Text::_('COM_COMPACTTABLES_TBCONF_RESET') . '</button>'
            . '<div class="visually-hidden" aria-live="polite" data-live></div>'
            . '<input type="hidden" name="' . $esc($this->name) . '" id="' . $id . '" value="' . $esc($layout ? implode(',', $layout) : EditorToolbar::NONE) . '">'
            . '</div>';
    }
}
