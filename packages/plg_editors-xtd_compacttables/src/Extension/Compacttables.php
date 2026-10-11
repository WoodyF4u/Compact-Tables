<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Compactweb\Plugin\EditorsXtd\Compacttables\Extension;

use Compactweb\Component\Compacttables\Administrator\Helper\PickerAccess;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Editor\Button\Button;
use Joomla\CMS\Event\Editor\EditorButtonsSetupEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Editor button "Compact Tables": pick a table from a list and its {table N}
 * code is placed in the text. Works in every editor that shows Joomla's
 * editor buttons (TinyMCE, JCE, CodeMirror, ...).
 */
final class Compacttables extends CMSPlugin implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return ['onEditorButtonsSetup' => 'onEditorButtonsSetup'];
    }

    public function onEditorButtonsSetup(EditorButtonsSetupEvent $event): void
    {
        if (\in_array($this->_name, $event->getDisabledButtons())) {
            return;
        }

        $app = $this->getApplication();

        // Administrator: the picker sits behind the component's "Access Administration Interface".
        // Website: everyone who may write or edit articles gets it (see PickerAccess).
        if (!$app || !ComponentHelper::isEnabled('com_compacttables') || !class_exists(PickerAccess::class)) {
            return;
        }

        $user = $app->getIdentity();

        if ($app->isClient('administrator')) {
            if (!$user || !$user->authorise('core.manage', 'com_compacttables')) {
                return;
            }
        } elseif (!$app->isClient('site') || !PickerAccess::isAllowed($user)) {
            return;
        }

        $this->loadLanguage();

        // The dialog gets its own complete look: many site templates do not style Joomla's dialogs at all.
        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            $document->getWebAssetManager()
                ->registerAndUseStyle('com_compacttables.picker-dialog', 'com_compacttables/picker-dialog.css');
        }

        $link = 'index.php?option=com_compacttables&view=tables&layout=modal&tmpl=component&'
            . Session::getFormToken() . '=1&editor=' . rawurlencode($event->getEditorId());

        $event->getButtonsRegistry()->add(
            new Button(
                $this->_name,
                [
                    'action'  => 'modal',
                    'link'    => $link,
                    'text'    => Text::_('PLG_EDITORS-XTD_COMPACTTABLES_BUTTON'),
                    'icon'    => 'th',
                    'iconSVG' => '<svg viewBox="0 0 32 32" width="24" height="24"><path d="M2 4v24h28V4H2zm2 8h7v6H4v-6zm9 0h8v6h-8v-6z'
                        . 'm10 0h5v6h-5v-6zM4 20h7v6H4v-6zm9 0h8v6h-8v-6zm10 0h5v6h-5v-6z"></path></svg>',
                    // Whole plugin name, as Joomla's own buttons do for backward compatibility.
                    'name' => $this->_type . '_' . $this->_name,
                ],
                // Passed on to the dialog: lets picker-dialog.css recognise it.
                ['className' => 'tm-picker-dialog']
            )
        );
    }
}
