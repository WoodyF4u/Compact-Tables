<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Compactweb\Component\Compacttables\Administrator\Model;

use Compactweb\Component\Compacttables\Administrator\Helper\LinkHelper;
use Compactweb\Component\Compacttables\Administrator\Helper\TableDataHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * A single table.
 */
class TableModel extends AdminModel
{
    public $typeAlias = 'com_compacttables.table';

    protected $text_prefix = 'COM_COMPACTTABLES_TABLE';

    public function getForm($data = [], $loadData = true)
    {
        // The form definition lives with the administrator part, also when this runs on the website.
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_compacttables/forms');

        $form = $this->loadForm('com_compacttables.table', 'table', ['control' => 'jform', 'load_data' => $loadData]);

        if (!$form instanceof Form) {
            return false;
        }

        return $form;
    }

    public function getTable($name = 'Table', $prefix = 'Administrator', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_compacttables.edit.table.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_compacttables.table', $data);

        return $data;
    }

    /**
     * The grid (cells) is the source of truth: it is validated and cleaned
     * here, and the column/row counts are derived from it.
     */
    public function save($data)
    {
        $maxCols = TableDataHelper::getMaxColumns();

        $rows = TableDataHelper::decode((string) ($data['data'] ?? ''));

        if (!$rows) {
            // No grid posted (e.g. JavaScript disabled): start with the same empty grid as the edit screen.
            $rows = TableDataHelper::blank(3, 3);
        }

        $rows = TableDataHelper::normalise($rows, $maxCols, TableDataHelper::MAX_ROWS);

        $data['data']       = TableDataHelper::encode($rows);
        $data['num_rows']   = \count($rows);
        $data['num_cols']   = \count($rows[0]);
        $data['has_header'] = empty($data['has_header']) ? 0 : 1;
        $data['params']     = TableDataHelper::cleanParams((array) ($data['params'] ?? []));

        // Linked tables: the choice arrives as "linked" (IDs); the group itself is kept in the params
        // and only changed by LinkHelper, also for the other tables of the group.
        $isCopy   = Factory::getApplication()->getInput()->getCmd('task') === 'save2copy';
        $linked   = \array_key_exists('linked', $data) ? array_filter(array_map('intval', (array) $data['linked'])) : null;
        $tables   = LinkHelper::getTables(true);
        $existing = (int) ($data['id'] ?? 0);
        unset($data['linked']);

        $data['params']['link_group'] = !$isCopy && $existing ? ($tables[$existing]['group'] ?? '') : '';

        // "Save as Copy": if the name was left as it was, number the copy so the two can be told apart.
        if ($isCopy) {
            $original = $this->getTable();


            if ($original->load((int) Factory::getApplication()->getInput()->getInt('id')) && $original->title === trim((string) ($data['title'] ?? ''))) {
                $data['title'] = StringHelper::increment($original->title);
            }
        }

        if (!parent::save($data)) {
            return false;
        }

        // A copy starts on its own, so the original's group stays as it was.
        if ($linked !== null && !$isCopy) {
            try {
                LinkHelper::setLinks((int) $this->getState($this->getName() . '.id'), $linked);
            } catch (\Throwable $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
            }
        }

        return true;
    }

    public function delete(&$pks)
    {
        $result = parent::delete($pks);

        // Tables that were linked to a deleted table: a group of one is no group.
        try {
            LinkHelper::tidy();
        } catch (\Throwable $e) {
            // The deletion itself succeeded; a stale group only means no lining up.
        }

        return $result;
    }

    protected function canDelete($record)
    {
        return $this->getCurrentUser()->authorise('core.delete', 'com_compacttables');
    }
}
