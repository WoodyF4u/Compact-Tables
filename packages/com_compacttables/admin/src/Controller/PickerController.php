<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Compactweb\Component\Compacttables\Administrator\Controller;

use Compactweb\Component\Compacttables\Administrator\Helper\PickerAccess;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Actions of the table picker (the dialog behind the editor button).
 * Used as it is in the administrator; the website has a thin subclass.
 */
class PickerController extends BaseController
{
    /**
     * Deletes one table and answers in JSON, so the picker can stay open.
     * Only by POST, with the session token, for a user who may delete tables.
     */
    public function delete(): void
    {
        $app = $this->app;
        $app->getLanguage()->load('com_compacttables', JPATH_ADMINISTRATOR . '/components/com_compacttables');
        $app->setHeader('Content-Type', 'application/json; charset=utf-8');

        try {
            if ($this->input->getMethod() !== 'POST' || !Session::checkToken('post')) {
                throw new \RuntimeException(Text::_('JINVALID_TOKEN_NOTICE'), 403);
            }

            $user = $app->getIdentity();

            if (!PickerAccess::isAllowed($user) || !PickerAccess::getTableActions($user)['delete']) {
                throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), 403);
            }

            $id = $this->input->post->getInt('id');

            if ($id < 1) {
                throw new \RuntimeException(Text::_('COM_COMPACTTABLES_TABLES_NO_ITEM_SELECTED'), 400);
            }

            $model = $this->factory->createModel('Table', 'Administrator', ['ignore_request' => true]);
            $ids   = [$id];

            if (!$model || !$model->delete($ids)) {
                throw new \RuntimeException($model ? (string) $model->getError() : 'Model not available', 500);
            }

            echo new JsonResponse(['id' => $id], Text::_('COM_COMPACTTABLES_TABLES_N_ITEMS_DELETED_1'));
        } catch (\Throwable $e) {
            $code = $e->getCode();
            $app->setHeader('Status', (string) (\in_array($code, [400, 403], true) ? $code : 500), true);
            echo new JsonResponse($e);
        }

        $app->sendHeaders();
        $app->close();
    }
}
