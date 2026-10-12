<?php

/**
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Compactweb\Plugin\Content\Compacttables\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Replaces {table N} in content by the table with ID N made in Compact Tables.
 */
final class Compacttables extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * {table 12}, optionally the only thing inside a paragraph (editors wrap it in <p>,
     * and a table inside a paragraph is invalid HTML).
     */
    private const PATTERN = '~<p\b[^>]*>(?:\s|&nbsp;)*\{table\s+(\d+)\s*\}(?:\s|&nbsp;)*</p>|\{table\s+(\d+)\s*\}~iu';

    /**
     * @var  array<int, object|null>  Tables already loaded during this request.
     */
    private array $tables = [];

    public static function getSubscribedEvents(): array
    {
        return ['onContentPrepare' => 'onContentPrepare'];
    }

    public function onContentPrepare(ContentPrepareEvent $event): void
    {
        $app = $this->getApplication();

        if (!$app || !$app->isClient('site')) {
            return;
        }

        $item = $event->getItem();

        if (!\is_object($item) || !isset($item->text) || !\is_string($item->text) || stripos($item->text, '{table') === false) {
            return;
        }

        $text = preg_replace_callback(
            self::PATTERN,
            fn (array $match): string => $this->renderTable((int) ($match[1] !== '' ? $match[1] : ($match[2] ?? 0))),
            $item->text
        );

        if ($text !== null) {
            $item->text = $text;
        }
    }

    /**
     * @return  string  The table's HTML, or an empty string when the table does not exist (any more).
     */
    private function renderTable(int $id): string
    {
        $table = $this->loadTable($id);

        if (!$table || !$table->rows) {
            return '';
        }

        $params    = $table->params;
        $hasHeader = $table->hasHeader;
        $classes   = ['compacttables', 'compacttables--' . $params->get('responsive', 'fit'), 'compacttables--id-' . $id];
        $variables = [];

        if ($hasHeader) {
            if ($value = $params->get('head_bg')) {
                $classes[]                = 'compacttables--head-bg';
                $variables['--tm-head-bg'] = $value;
            }

            if ($value = $params->get('head_color')) {
                $classes[]                   = 'compacttables--head-color';
                $variables['--tm-head-color'] = $value;
            }

            if ($params->get('head_bold', '') === '1') {
                $classes[] = 'compacttables--head-bold';
            } elseif ($params->get('head_bold', '') === '0') {
                $classes[] = 'compacttables--head-normal';
            }

            if ($value = $params->get('head_font_size')) {
                $classes[]                  = 'compacttables--head-size';
                $variables['--tm-head-size'] = $value;
            }

            if ($value = $params->get('head_link_color')) {
                $classes[]                  = 'compacttables--head-link';
                $variables['--tm-head-link'] = $value;
            }
        }

        // The other cells: every row below the top row, or every row when there are no column titles.
        if ($value = $params->get('body_color')) {
            $classes[]                   = 'compacttables--body-color';
            $variables['--tm-body-color'] = $value;
        }

        if ($params->get('body_bold', '') === '1') {
            $classes[] = 'compacttables--body-bold';
        } elseif ($params->get('body_bold', '') === '0') {
            $classes[] = 'compacttables--body-normal';
        }

        if ($value = $params->get('body_font_size')) {
            $classes[]                  = 'compacttables--body-size';
            $variables['--tm-body-size'] = $value;
        }

        if ($value = $params->get('body_link_color')) {
            $classes[]                  = 'compacttables--body-link';
            $variables['--tm-body-link'] = $value;
        }

        // Narrower than the available space on wide screens, and then placed left, centred or right.
        if ($value = $params->get('width')) {
            $classes[]             = 'compacttables--width';
            $classes[]             = 'compacttables--align-' . (\in_array($params->get('align'), ['center', 'right'], true) ? $params->get('align') : 'left');
            $variables['--tm-width'] = $value;
        }

        // Text in the cells: left and at the top unless chosen otherwise ("template" leaves it to the site template).
        $cellAlign = (string) $params->get('cell_align', 'left');

        if (\in_array($cellAlign, ['left', 'center', 'right'], true)) {
            $classes[] = 'compacttables--text-' . $cellAlign;
        }

        $cellValign = (string) $params->get('cell_valign', 'top');

        if (\in_array($cellValign, ['top', 'middle', 'bottom'], true)) {
            $classes[] = 'compacttables--valign-' . $cellValign;
        }

        // Tables with the same link name on one page get the same column widths (compacttables.js).
        $linkGroup = (string) $params->get('link_group', '');
        $linked    = preg_match('~^[a-z0-9-]{1,30}$~', $linkGroup) === 1;

        if ($linked) {
            $classes[] = 'compacttables--group-' . $linkGroup;
        }

        // A line needs both a width and a colour; whichever was left empty gets a sensible default.
        // Room between the cells.
        if ($value = $params->get('spacing')) {
            $classes[]                = 'compacttables--spacing';
            $variables['--tm-spacing'] = $value;
        }

        foreach (['vline' => 'vline', 'hline' => 'hline', 'head_line' => 'head-line'] as $name => $class) {
            // The line under the top row only exists when there is a top row.
            if ($name === 'head_line' && !$hasHeader) {
                continue;
            }

            if ($params->get($name . '_width', '') !== '' || $params->get($name . '_color', '') !== '') {
                $classes[]                            = 'compacttables--' . $class;
                $variables['--tm-' . $class . '-w'] = $params->get($name . '_width') ?: '1px';
                $variables['--tm-' . $class . '-c'] = $params->get($name . '_color') ?: 'currentColor';
            }
        }

        if ($params->get('outer_width', '') !== '' || $params->get('outer_color', '') !== '') {
            $classes[]                 = 'compacttables--outer';
            $variables['--tm-outer-w'] = $params->get('outer_width') ?: '1px';
            $variables['--tm-outer-c'] = $params->get('outer_color') ?: 'currentColor';
        }

        $style = '';

        foreach ($variables as $name => $value) {
            // Values were validated when the table was saved; this is a second line of defence.
            if (preg_match('~^[#a-zA-Z0-9.%]+$~', (string) $value)) {
                $style .= $name . ':' . $value . ';';
            }
        }

        $rows   = array_map(fn (array $row): array => array_map([$this, 'routeLinks'], $row), $table->rows);
        $head   = $hasHeader ? array_shift($rows) : [];
        $labels = array_map(static fn (string $cell): string => trim(html_entity_decode(strip_tags($cell), ENT_QUOTES, 'UTF-8')), $head);

        $componentParams = ComponentHelper::getParams('com_compacttables');
        $tableClass      = trim('compacttables__table ' . preg_replace('~[^A-Za-z0-9_\- ]~', '', (string) $componentParams->get('table_class', '')));

        $document = $this->getApplication()->getDocument();

        if ($document instanceof HtmlDocument) {
            $assets = $document->getWebAssetManager();
            $assets->registerAndUseStyle('plg_content_compacttables.table', 'plg_content_compacttables/compacttables.css');

            if ($linked) {
                $assets->registerAndUseScript('plg_content_compacttables.link', 'plg_content_compacttables/compacttables.js', [], ['defer' => true]);
            }
        }

        $displayData = [
            'id'         => $id,
            'title'      => $table->title,
            'classes'    => implode(' ', $classes),
            'style'      => $style,
            'tableClass' => $tableClass,
            'head'       => $head,
            'labels'     => $labels,
            'rows'       => $rows,
        ];

        ob_start();
        (static function (array $displayData, string $__path): void {
            extract($displayData);
            include $__path;
        })($displayData, PluginHelper::getLayoutPath('content', 'compacttables', 'default'));

        return (string) ob_get_clean();
    }

    private function loadTable(int $id): ?object
    {
        if ($id < 1) {
            return null;
        }

        if (\array_key_exists($id, $this->tables)) {
            return $this->tables[$id];
        }

        $this->tables[$id] = null;

        try {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName(['id', 'title', 'has_header', 'data', 'params']))
                ->from($db->quoteName('#__compacttables_tables'))
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER);

            $row = $db->setQuery($query)->loadObject();
        } catch (\Throwable $e) {
            // Component not installed (any more): show nothing rather than break the page.
            return null;
        }

        if (!$row) {
            return null;
        }

        $data = json_decode((string) $row->data, true);
        $rows = [];

        foreach ((array) ($data['rows'] ?? []) as $cells) {
            if (\is_array($cells) && $cells) {
                $rows[] = array_map('strval', array_values($cells));
            }
        }

        return $this->tables[$id] = (object) [
            'id'        => (int) $row->id,
            'title'     => (string) $row->title,
            'hasHeader' => (bool) $row->has_header && \count($rows) > 0,
            'rows'      => $rows,
            'params'    => new Registry($row->params),
        ];
    }

    /**
     * Links to menu items and articles are stored as index.php?... and become
     * search engine friendly URLs here.
     */
    private function routeLinks(string $html): string
    {
        if (!str_contains($html, 'href="index.php')) {
            return $html;
        }

        return preg_replace_callback(
            '~href="(index\.php[^"]*)"~',
            static fn (array $match): string => 'href="' . Route::_(html_entity_decode($match[1], ENT_QUOTES, 'UTF-8')) . '"',
            $html
        );
    }
}
