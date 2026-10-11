<?php

/**
 * Output of one table on the website.
 *
 * To change the HTML, copy this file to
 *   templates/YOUR_TEMPLATE/html/plg_content_compacttables/default.php
 *
 * @package     Compactweb.Compacttables
 * @copyright   (C) 2026 CompactWeb
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @var  integer   $id          ID of the table (the N in {table N})
 * @var  string    $title       Name of the table (not shown by default)
 * @var  string    $classes     CSS classes for the wrapper
 * @var  string    $style       CSS variables for the wrapper (may be empty)
 * @var  string    $tableClass  CSS classes for the <table>
 * @var  string[]  $head        Cells of the header row (empty when the table has none); safe HTML
 * @var  string[]  $labels      Plain text of the header cells, used when rows are stacked on small screens
 * @var  array[]   $rows        Remaining rows; each row is a list of cells with safe HTML
 */

\defined('_JEXEC') or die;

$wrapperStyle = $style !== '' ? ' style="' . $style . '"' : '';
?>
<div class="<?php echo $classes; ?>"<?php echo $wrapperStyle; ?>>
<table class="<?php echo $tableClass; ?>">
<?php if ($head) : ?>
<thead>
<tr>
<?php foreach ($head as $cell) : ?>
<th scope="col"><?php echo $cell; ?></th>
<?php endforeach; ?>
</tr>
</thead>
<?php endif; ?>
<?php if ($rows) : ?>
<tbody>
<?php foreach ($rows as $row) : ?>
<tr>
<?php foreach ($row as $index => $cell) : ?>
<?php $label = ($labels[$index] ?? '') !== '' ? ' data-label="' . htmlspecialchars($labels[$index], ENT_QUOTES, 'UTF-8') . '"' : ''; ?>
<td<?php echo $label; ?>><?php echo $cell; ?></td>
<?php endforeach; ?>
</tr>
<?php endforeach; ?>
</tbody>
<?php endif; ?>
</table>
</div>
