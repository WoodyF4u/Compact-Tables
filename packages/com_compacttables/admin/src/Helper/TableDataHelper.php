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
 * Validates and cleans everything an editor can put into a table:
 * the grid, the HTML inside cells and the styling options.
 */
final class TableDataHelper
{
    public const MAX_ROWS = 500;

    /**
     * HTML itself has no maximum number of columns (Bootstrap's 12 is about its
     * page grid, not about tables). These two limits only keep the editor fast
     * and stop a typing error such as "5000" from freezing the browser.
     */
    public const MAX_COLUMNS = 50;

    private const MAX_CELL_LENGTH = 20000;

    private const ALLOWED_TAGS = '<a><br><strong><b><em><i><u><s><strike><del><sub><sup>';

    public static function getMaxColumns(): int
    {
        return self::MAX_COLUMNS;
    }

    /**
     * @return  array<int, array<int, string>>  Rows of cells, or an empty array when the JSON is unusable.
     */
    public static function decode(string $json): array
    {
        if ($json === '') {
            return [];
        }

        $decoded = json_decode($json, true);

        if (!\is_array($decoded) || !isset($decoded['rows']) || !\is_array($decoded['rows'])) {
            return [];
        }

        $rows = [];

        foreach ($decoded['rows'] as $row) {
            if (!\is_array($row)) {
                continue;
            }

            $rows[] = array_map(static fn ($cell) => \is_scalar($cell) ? (string) $cell : '', array_values($row));
        }

        return $rows;
    }

    public static function encode(array $rows): string
    {
        return json_encode(['rows' => $rows], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public static function blank(int $cols, int $rows): array
    {
        $cols = max(1, $cols);
        $rows = max(1, $rows);

        return array_fill(0, $rows, array_fill(0, $cols, ''));
    }

    /**
     * Makes the grid rectangular, enforces the limits and cleans every cell.
     */
    public static function normalise(array $rows, int $maxCols, int $maxRows): array
    {
        if (!$rows) {
            $rows = self::blank(1, 1);
        }

        $rows = \array_slice($rows, 0, $maxRows);
        $cols = 1;

        foreach ($rows as $row) {
            $cols = max($cols, \count($row));
        }

        $cols = min($cols, $maxCols);

        foreach ($rows as $r => $row) {
            $row      = \array_slice(array_pad($row, $cols, ''), 0, $cols);
            $rows[$r] = array_map([self::class, 'cleanCell'], $row);
        }

        return $rows;
    }

    /**
     * Cells may contain text, line breaks, simple inline formatting
     * (bold, italic, underline, strikethrough, sub- and superscript) and links. Nothing else.
     */
    public static function cleanCell(string $html): string
    {
        $html = trim(mb_substr($html, 0, self::MAX_CELL_LENGTH));

        if ($html === '') {
            return '';
        }

        if (!str_contains($html, '<') && !str_contains($html, '&')) {
            return preg_replace('~\R~u', '<br>', htmlspecialchars($html, ENT_NOQUOTES, 'UTF-8'));
        }

        $html = strip_tags($html, self::ALLOWED_TAGS);

        $doc      = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML(
            '<?xml encoding="UTF-8"><html><body><div id="tm-root">' . $html . '</div></body></html>',
            LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = null;

        foreach ($doc->getElementsByTagName('div') as $div) {
            if ($div->getAttribute('id') === 'tm-root') {
                $root = $div;
                break;
            }
        }

        if (!$root) {
            return htmlspecialchars(strip_tags($html), ENT_NOQUOTES, 'UTF-8');
        }

        $out = self::serialise($root);

        // Trailing line breaks are an editing artefact.
        $out = preg_replace('~(?:\s*<br>\s*)+$~', '', $out);

        return trim($out);
    }

    private static function serialise(\DOMNode $node): string
    {
        $out = '';

        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMText) {
                $out .= preg_replace(
                    '~\R~u',
                    '<br>',
                    htmlspecialchars(str_replace("\u{00A0}", ' ', $child->nodeValue), ENT_NOQUOTES, 'UTF-8')
                );
                continue;
            }

            if (!$child instanceof \DOMElement) {
                continue;
            }

            $inner = self::serialise($child);

            switch (strtolower($child->tagName)) {
                case 'br':
                    $out .= '<br>';
                    break;

                case 'strong':
                case 'b':
                    $out .= $inner === '' ? '' : '<strong>' . $inner . '</strong>';
                    break;

                case 'em':
                case 'i':
                    $out .= $inner === '' ? '' : '<em>' . $inner . '</em>';
                    break;

                case 'u':
                case 'sub':
                case 'sup':
                    $tag  = strtolower($child->tagName);
                    $out .= $inner === '' ? '' : '<' . $tag . '>' . $inner . '</' . $tag . '>';
                    break;

                case 's':
                case 'strike':
                case 'del':
                    $out .= $inner === '' ? '' : '<s>' . $inner . '</s>';
                    break;

                case 'a':
                    $href = self::cleanHref($child->getAttribute('href'));

                    if ($href === '' || $inner === '') {
                        $out .= $inner;
                        break;
                    }

                    $attributes = ' href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '"';

                    if ($child->getAttribute('target') === '_blank') {
                        $attributes .= ' target="_blank" rel="noopener noreferrer"';
                    }

                    $out .= '<a' . $attributes . '>' . $inner . '</a>';
                    break;

                default:
                    $out .= $inner;
            }
        }

        return $out;
    }

    /**
     * Only links that cannot run script: internal Joomla links, absolute/relative paths,
     * http(s), mailto and tel.
     */
    public static function cleanHref(string $href): string
    {
        $href = trim($href);

        if ($href === '' || \strlen($href) > 2000 || preg_match('~[\x00-\x20"<>]~', $href)) {
            return '';
        }

        if (preg_match('~^(?:https?://|mailto:|tel:)~i', $href)) {
            return $href;
        }

        // Anything else with a scheme (javascript:, data:, ...) is refused.
        if (preg_match('~^[a-z][a-z0-9+.\-]*:~i', $href) || str_starts_with($href, '//')) {
            return '';
        }

        return $href;
    }

    /**
     * @return  array<string, string>  Only known options with valid values; '' means "use the template".
     */
    public static function cleanParams(array $params): array
    {
        $colour = static function ($value): string {
            $value = strtolower(trim((string) $value));

            return preg_match('~^#(?:[0-9a-f]{3}|[0-9a-f]{6})$~', $value) ? $value : '';
        };

        $border = static function ($value): string {
            $value = strtolower(trim((string) $value));

            if (!preg_match('~^(\d{1,2})(?:px)?$~', $value, $m)) {
                return '';
            }

            return min(20, (int) $m[1]) . 'px';
        };

        $fontSize = static function ($value): string {
            $value = strtolower(trim(str_replace(',', '.', (string) $value)));

            if (!preg_match('~^(\d{1,3}(?:\.\d{1,3})?)(px|rem|em|%)?$~', $value, $m) || (float) $m[1] <= 0) {
                return '';
            }

            return rtrim(rtrim(number_format((float) $m[1], 3, '.', ''), '0'), '.') . ($m[2] ?? 'px');
        };

        // Width of the table on a wide screen: a percentage of the available space (below 100) or pixels.
        $width = static function ($value): string {
            $value = strtolower(trim(str_replace(',', '.', (string) $value)));

            if (!preg_match('~^(\d{1,4})(?:\.\d+)?(px|%)$~', $value, $m)) {
                return '';
            }

            $number = (int) $m[1];

            if ($m[2] === '%') {
                return $number >= 100 ? '' : max(10, $number) . '%';
            }

            return min(5000, max(100, $number)) . 'px';
        };

        // Room between the cells: a length, never a percentage (CSS has none for this).
        $spacing = static function ($value) use ($fontSize): string {
            $value = $fontSize($value);

            return str_ends_with($value, '%') ? '' : $value;
        };

        $bold       = (string) ($params['head_bold'] ?? '');
        $bodyBold   = (string) ($params['body_bold'] ?? '');
        $align      = (string) ($params['align'] ?? 'left');
        $responsive = (string) ($params['responsive'] ?? 'fit');

        return [
            'head_bg'        => $colour($params['head_bg'] ?? ''),
            'head_color'     => $colour($params['head_color'] ?? ''),
            'head_bold'      => \in_array($bold, ['0', '1'], true) ? $bold : '',
            'head_font_size' => $fontSize($params['head_font_size'] ?? ''),
            'head_link_color' => $colour($params['head_link_color'] ?? ''),
            'body_color'      => $colour($params['body_color'] ?? ''),
            'body_bold'       => \in_array($bodyBold, ['0', '1'], true) ? $bodyBold : '',
            'body_font_size'  => $fontSize($params['body_font_size'] ?? ''),
            'body_link_color' => $colour($params['body_link_color'] ?? ''),
            'outer_width'    => $border($params['outer_width'] ?? ''),
            'outer_color'    => $colour($params['outer_color'] ?? ''),
            'vline_width'     => $border($params['vline_width'] ?? ''),
            'vline_color'     => $colour($params['vline_color'] ?? ''),
            'hline_width'     => $border($params['hline_width'] ?? ''),
            'hline_color'     => $colour($params['hline_color'] ?? ''),
            'head_line_width' => $border($params['head_line_width'] ?? ''),
            'head_line_color' => $colour($params['head_line_color'] ?? ''),
            'width'          => $width($params['width'] ?? ''),
            'spacing'        => $spacing($params['spacing'] ?? ''),
            'align'          => \in_array($align, ['left', 'center', 'right'], true) ? $align : 'left',
            'responsive'     => \in_array($responsive, ['fit', 'scroll', 'stack'], true) ? $responsive : 'fit',
        ];
    }
}
