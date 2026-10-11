/**
 * Compact Tables – linked tables get the same column widths.
 *
 * Tables with the same link name (class compacttables--group-NAME) on one page
 * are measured in the browser's own automatic layout; for every column the
 * widest of those tables wins. The widths are then given to every table of the
 * group as percentages in a <colgroup>, with a fixed layout, so the columns line
 * up exactly. Only tables with the same number of columns are lined up; the
 * "scroll sideways" mode keeps its own widths. Stacked rows (small screens)
 * have no columns, so nothing is measured there.
 *
 * @copyright  (C) 2026 CompactWeb
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  const PREFIX = 'compacttables--group-';

  const firstRow = (table) => table.querySelector(':scope > thead > tr, :scope > tbody > tr');
  const columnCount = (table) => {
    const row = firstRow(table);
    return row ? row.children.length : 0;
  };

  const groups = () => {
    const found = new Map();

    document.querySelectorAll(`.compacttables[class*="${PREFIX}"]`).forEach((wrapper) => {
      if (wrapper.classList.contains('compacttables--scroll')) return;

      const name = [...wrapper.classList].find((cls) => cls.startsWith(PREFIX));
      const table = wrapper.querySelector(':scope > .compacttables__table');
      if (!name || !table) return;

      if (!found.has(name)) found.set(name, []);
      found.get(name).push({ wrapper, table });
    });

    return found;
  };

  const reset = ({ wrapper, table }) => {
    wrapper.classList.remove('compacttables--linked');
    table.querySelector(':scope > colgroup[data-compacttables]')?.remove();
  };

  const syncGroup = (members) => {
    members.forEach(reset);

    // Stacked rows (display: block) have no columns to measure.
    const visible = members.filter(({ table }) => getComputedStyle(table).display === 'table');

    // Line up the tables that share the most common number of columns.
    const counts = new Map();
    visible.forEach(({ table }) => {
      const n = columnCount(table);
      if (n > 0) counts.set(n, (counts.get(n) || 0) + 1);
    });
    const [columns, howMany] = [...counts.entries()].sort((a, b) => b[1] - a[1])[0] || [0, 0];
    if (howMany < 2) return;

    const linked = visible.filter(({ table }) => columnCount(table) === columns);

    // Measure every table in the automatic layout and keep, per column, the widest.
    linked.forEach(({ wrapper }) => wrapper.classList.add('compacttables--measuring'));
    const widest = new Array(columns).fill(0);

    linked.forEach(({ table }) => {
      [...firstRow(table).children].forEach((cell, index) => {
        widest[index] = Math.max(widest[index], cell.getBoundingClientRect().width);
      });
    });

    linked.forEach(({ wrapper }) => wrapper.classList.remove('compacttables--measuring'));

    const total = widest.reduce((sum, width) => sum + width, 0);
    if (total <= 0) return;

    linked.forEach(({ wrapper, table }) => {
      const colgroup = document.createElement('colgroup');
      colgroup.dataset.compacttables = '';
      widest.forEach((width) => {
        const col = document.createElement('col');
        col.style.width = `${((width / total) * 100).toFixed(3)}%`;
        colgroup.append(col);
      });
      table.prepend(colgroup);
      wrapper.classList.add('compacttables--linked');
    });
  };

  const syncAll = () => groups().forEach(syncGroup);

  // Measure again when the width of the page changes (rotating a phone, resizing a window),
  // but not for changes in height, and only once per frame.
  let frame = 0;
  let lastWidth = 0;
  const schedule = () => {
    cancelAnimationFrame(frame);
    frame = requestAnimationFrame(syncAll);
  };

  const start = () => {
    syncAll();
    lastWidth = document.documentElement.clientWidth;

    // Web fonts change the width of the text: measure again once they are in.
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(schedule);

    // Content that fills in later (such as cloaked email addresses) also changes the widths.
    window.addEventListener('load', schedule);

    window.addEventListener('resize', () => {
      const width = document.documentElement.clientWidth;
      if (width === lastWidth) return;
      lastWidth = width;
      schedule();
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
