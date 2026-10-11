<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Compactweb\Component\Compacttables\Administrator\Helper;

use Joomla\CMS\Component\ComponentHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The editor bar above the grid: which buttons exist, and which of them the
 * site owner chose under Options > Editor Bar.
 *
 * To add a button: add it here, to BUTTONS in media/js/editor.js, and give it
 * a language string and a line on the help page.
 */
final class EditorToolbar
{
    public const SEPARATOR = '|';

    /**
     * Stored when every button was removed (an empty value would mean "not set").
     */
    public const NONE = 'none';

    /**
     * Button id => [icon class, language key of its name].
     */
    public const BUTTONS = [
        'bold'          => ['fa-solid fa-bold', 'COM_COMPACTTABLES_TB_BOLD'],
        'italic'        => ['fa-solid fa-italic', 'COM_COMPACTTABLES_TB_ITALIC'],
        'underline'     => ['fa-solid fa-underline', 'COM_COMPACTTABLES_TB_UNDERLINE'],
        'strikethrough' => ['fa-solid fa-strikethrough', 'COM_COMPACTTABLES_TB_STRIKETHROUGH'],
        'subscript'     => ['fa-solid fa-subscript', 'COM_COMPACTTABLES_TB_SUBSCRIPT'],
        'superscript'   => ['fa-solid fa-superscript', 'COM_COMPACTTABLES_TB_SUPERSCRIPT'],
        'link'          => ['fa-solid fa-link', 'COM_COMPACTTABLES_TB_LINK'],
        'unlink'        => ['fa-solid fa-link-slash', 'COM_COMPACTTABLES_EDITOR_UNLINK'],
        'removeformat'  => ['fa-solid fa-text-slash', 'COM_COMPACTTABLES_TB_REMOVEFORMAT'],
    ];

    public const DEFAULT_LAYOUT = 'bold,italic,underline,|,link,unlink,|,removeformat';

    /**
     * @return  string[]  Button ids and separators, in order. Unknown ids and doubles are dropped.
     */
    public static function parse(?string $value): array
    {
        $value = trim((string) $value);

        if ($value === '') {
            $value = self::DEFAULT_LAYOUT;
        }

        if ($value === self::NONE) {
            return [];
        }

        $layout = [];

        foreach (explode(',', $value) as $id) {
            $id = trim($id);

            if ($id === self::SEPARATOR) {
                // No separator at the start, and never two in a row.
                if ($layout && end($layout) !== self::SEPARATOR) {
                    $layout[] = $id;
                }
            } elseif (isset(self::BUTTONS[$id]) && !\in_array($id, $layout, true)) {
                $layout[] = $id;
            }
        }

        while ($layout && end($layout) === self::SEPARATOR) {
            array_pop($layout);
        }

        return $layout;
    }

    /**
     * @return  string[]  The layout chosen under Options.
     */
    public static function getLayout(): array
    {
        return self::parse((string) ComponentHelper::getParams('com_compacttables')->get('toolbar', self::DEFAULT_LAYOUT));
    }
}
