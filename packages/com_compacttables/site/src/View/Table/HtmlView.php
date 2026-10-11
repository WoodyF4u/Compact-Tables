<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Compactweb\Component\Compacttables\Site\View\Table;

use Compactweb\Component\Compacttables\Administrator\View\Table\HtmlView as AdministratorHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The table editor on the website. It only exists inside the picker dialog.
 */
class HtmlView extends AdministratorHtmlView
{
    public function display($tpl = null): void
    {
        $app  = Factory::getApplication();
        $user = $app->getIdentity();
        $id   = $app->getInput()->getInt('id');

        // The controller already checked this before it sent the visitor here; checked again because this is the website.
        if (!$user || $user->guest || !$user->authorise($id ? 'core.edit' : 'core.create', 'com_compacttables')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $language = $app->getLanguage();
        $language->load('com_compacttables', JPATH_ADMINISTRATOR . '/components/com_compacttables')
            || $language->load('com_compacttables', JPATH_ADMINISTRATOR);
        // Texts of Joomla's own buttons and messages that only the administrator has by default.
        $language->load('joomla', JPATH_ADMINISTRATOR);

        // Screens, forms and fields are those of the administrator.
        $this->addTemplatePath(JPATH_ADMINISTRATOR . '/components/com_compacttables/tmpl/table');
        $this->addTemplatePath(JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_compacttables/table');
        $this->setLayout('edit');

        $this->getDocument()->setMetaData('robots', 'noindex, nofollow');

        // Icons of the editor: Joomla's own icon set, whatever the site template brings.
        $this->getDocument()->getWebAssetManager()->useStyle('fontawesome');

        parent::display($tpl);
    }
}
