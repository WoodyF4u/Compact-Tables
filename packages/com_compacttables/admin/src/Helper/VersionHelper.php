<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Compactweb\Component\Compacttables\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The installed version of Compact Tables, for display.
 */
final class VersionHelper
{
    /**
     * Read straight from the component's manifest on disk, so it is right
     * immediately after an update.
     *
     * @return  string  e.g. "1.3.0", or an empty string when it cannot be determined.
     */
    public static function get(): string
    {
        static $version = null;

        if ($version !== null) {
            return $version;
        }

        $version = '';
        $path    = JPATH_ADMINISTRATOR . '/components/com_compacttables/compacttables.xml';

        if (is_file($path)) {
            $xml = @simplexml_load_file($path);

            if ($xml !== false && isset($xml->version)) {
                $version = trim((string) $xml->version);
            }
        }

        return $version;
    }
}
