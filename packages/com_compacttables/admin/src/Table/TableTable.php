<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Compactweb\Component\Compacttables\Administrator\Table;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Database row for #__compacttables_tables.
 */
class TableTable extends Table
{
    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        parent::__construct('#__compacttables_tables', 'id', $db, $dispatcher);
    }

    public function bind($src, $ignore = [])
    {
        if (\is_array($src) && isset($src['params']) && \is_array($src['params'])) {
            $src['params'] = (string) new Registry($src['params']);
        }

        return parent::bind($src, $ignore);
    }

    public function check()
    {
        $this->title = trim((string) $this->title);

        if ($this->title === '') {
            $this->setError(Text::_('COM_COMPACTTABLES_ERROR_TITLE_REQUIRED'));

            return false;
        }

        if (empty($this->data)) {
            $this->data = '{"rows":[]}';
        }

        if (empty($this->params)) {
            $this->params = '{}';
        }

        return true;
    }

    public function store($updateNulls = true)
    {
        $now  = Factory::getDate()->toSql();
        $user = Factory::getApplication()->getIdentity();

        if (!(int) $this->id) {
            $this->created    = $now;
            $this->created_by = (int) ($user->id ?? 0);
        }

        $this->modified    = $now;
        $this->modified_by = (int) ($user->id ?? 0);

        return parent::store($updateNulls);
    }
}
