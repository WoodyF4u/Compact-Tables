/**
 * Compact Tables – grid editor, link picker and CSS preview.
 *
 * @copyright  (C) 2026 CompactWeb
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

import JoomlaDialog from 'joomla.dialog';

const options = Joomla.getOptions('com_compacttables.editor', {});
const MAX_COLS = options.maxCols || 12;
const MAX_ROWS = options.maxRows || 500;

/** Translate, filling %s placeholders in order. */
const text = (key, ...values) => values.reduce((string, value) => string.replace('%s', value), Joomla.Text._(key));

/** Small DOM builder. */
const el = (tag, attributes = {}, ...children) => {
  const node = document.createElement(tag);
  Object.entries(attributes).forEach(([name, value]) => {
    if (value === false || value === null || value === undefined) return;
    if (name === 'class') node.className = value;
    else if (name === 'html') node.innerHTML = value;
    else node.setAttribute(name, value === true ? '' : value);
  });
  children.flat().forEach((child) => {
    if (child === null || child === undefined || child === false) return;
    node.append(child.nodeType ? child : document.createTextNode(child));
  });
  return node;
};

/*
 * Icons are drawn here, as small inline SVGs. An icon font would depend on the template around
 * the editor: on the website every template brings its own (or none), and the symbols went missing.
 */
const ICON_TEXT = 'fill="currentColor" stroke="none" text-anchor="middle" font-family="Georgia, \'Times New Roman\', serif"';
const ICONS = {
  bold: `<text x="8" y="13" ${ICON_TEXT} font-size="14" font-weight="700">B</text>`,
  italic: `<text x="8" y="13" ${ICON_TEXT} font-size="14" font-style="italic" font-weight="600">I</text>`,
  underline: `<text x="8" y="11.5" ${ICON_TEXT} font-size="12.5" font-weight="600">U</text><path d="M3.5 14.25h9"/>`,
  strikethrough: `<text x="8" y="12.5" ${ICON_TEXT} font-size="13.5" font-weight="600">S</text><path d="M2.5 8.25h11"/>`,
  subscript: `<text x="6" y="11" ${ICON_TEXT} font-size="12" font-style="italic" font-weight="600">x</text><text x="12.75" y="15" ${ICON_TEXT} font-size="7.5" font-weight="700">2</text>`,
  superscript: `<text x="6" y="13.5" ${ICON_TEXT} font-size="12" font-style="italic" font-weight="600">x</text><text x="12.75" y="7" ${ICON_TEXT} font-size="7.5" font-weight="700">2</text>`,
  link: '<path d="M6.4 9.6l3.2-3.2"/><path d="M7.3 4.7l1.2-1.2a2.6 2.6 0 0 1 3.7 3.7L11 8.4"/><path d="M8.7 11.3l-1.2 1.2a2.6 2.6 0 0 1-3.7-3.7L5 7.6"/>',
  unlink: '<path d="M7.3 4.7l1.2-1.2a2.6 2.6 0 0 1 3.7 3.7L11 8.4"/><path d="M8.7 11.3l-1.2 1.2a2.6 2.6 0 0 1-3.7-3.7L5 7.6"/><path d="M2.5 2.5l11 11"/>',
  removeformat: `<text x="7" y="12.5" ${ICON_TEXT} font-size="13" font-weight="600">T</text><path d="M10.5 10.5l3.5 3.5M14 10.5L10.5 14"/>`,
  caret: '<path d="M4 6l4 4 4-4"/>',
  list: '<path d="M6 4h7.5M6 8h7.5M6 12h7.5M2.6 4h.4M2.6 8h.4M2.6 12h.4"/>',
  file: '<path d="M4 1.75h5l3 3v9.5H4z"/><path d="M9 1.75v3h3M6.25 8.5h3.5M6.25 11h3.5"/>',
  globe: '<circle cx="8" cy="8" r="6.25"/><path d="M1.75 8h12.5M8 1.75c2.4 2 2.4 10.5 0 12.5M8 1.75c-2.4 2-2.4 10.5 0 12.5"/>',
  trash: '<path d="M2.5 4.5h11M6 4.5V2.75h4V4.5M4 4.5l.6 8.75h6.8L12 4.5M6.75 7v4M9.25 7v4"/>',
  move: '<path d="M8 2v12M5 5l3-3 3 3M5 11l3 3 3-3"/>',
  plus: '<path d="M8 3v10M3 8h10" stroke-width="2"/>',
  copy: '<path d="M5.5 5.5h8v8h-8z"/><path d="M3.25 10.5H2.5v-8h8v.75"/>',
  check: '<path d="M3 8.5l3.2 3.2L13 5" stroke-width="2"/>',
  times: '<path d="M4 4l8 8M12 4l-8 8"/>',
  desktop: '<path d="M2 3h12v8H2zM5.75 14h4.5M8 11v3"/>',
  mobile: '<path d="M4.75 1.75h6.5v12.5h-6.5zM7.4 12h1.2"/>',
  phone: '<path d="M3.2 2.2h2.6l1.2 3-1.6 1.2a8 8 0 0 0 4.2 4.2l1.2-1.6 3 1.2v2.6a1.2 1.2 0 0 1-1.3 1.2A11.2 11.2 0 0 1 2 3.5a1.2 1.2 0 0 1 1.2-1.3z"/>',
  envelope: '<path d="M1.75 3.5h12.5v9H1.75z"/><path d="M2 4l6 4.75L14 4"/>',
};
const iconMarkup = (name) => `<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" focusable="false">${ICONS[name] || ''}</svg>`;
const icon = (name, extraClass = '') => {
  const span = el('span', { class: `tm-icon${extraClass ? ` ${extraClass}` : ''}`, 'aria-hidden': 'true' });
  span.innerHTML = iconMarkup(name);
  return span;
};
/* Icons in the page itself (written by the server as an empty placeholder). */
const fillIcons = (root) => root.querySelectorAll('[data-tm-icon]').forEach((span) => { span.innerHTML = iconMarkup(span.dataset.tmIcon); });
fillIcons(document);
const columnName = (index) => {
  let name = '';
  for (let n = index + 1; n > 0; n = Math.floor((n - 1) / 26)) name = String.fromCharCode(65 + ((n - 1) % 26)) + name;
  return name;
};

/* -------------------------------------------------------------------------
 * Cell content: text, line breaks, simple inline formatting and links. Nothing else.
 * The server applies the same rules again when saving.
 * ---------------------------------------------------------------------- */

const escapeHtml = (string) => string.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
const escapeAttribute = (string) => escapeHtml(string).replace(/"/g, '&quot;');

const isSafeHref = (href) => {
  if (!href || /[\s"<>]/.test(href)) return false;
  if (/^(https?:\/\/|mailto:|tel:)/i.test(href)) return true;
  return !/^[a-z][a-z0-9+.-]*:/i.test(href) && !href.startsWith('//');
};

const serialise = (node) => {
  let out = '';
  node.childNodes.forEach((child) => {
    if (child.nodeType === Node.TEXT_NODE) {
      // Cells keep their line breaks while typing (pre-wrap); in HTML they must be <br>.
      out += escapeHtml(child.nodeValue.replace(/\u00a0/g, ' ')).replace(/\r?\n/g, '<br>');
      return;
    }
    if (child.nodeType !== Node.ELEMENT_NODE) return;

    const inner = serialise(child);
    switch (child.tagName) {
      case 'BR':
        out += '<br>';
        break;
      case 'B':
      case 'STRONG':
        out += inner ? `<strong>${inner}</strong>` : '';
        break;
      case 'I':
      case 'EM':
        out += inner ? `<em>${inner}</em>` : '';
        break;
      case 'U':
        out += inner ? `<u>${inner}</u>` : '';
        break;
      case 'S':
      case 'STRIKE':
      case 'DEL':
        out += inner ? `<s>${inner}</s>` : '';
        break;
      case 'SUB':
        out += inner ? `<sub>${inner}</sub>` : '';
        break;
      case 'SUP':
        out += inner ? `<sup>${inner}</sup>` : '';
        break;
      case 'A': {
        const href = child.getAttribute('href') || '';
        if (!inner || !isSafeHref(href)) {
          out += inner;
          break;
        }
        const blank = child.getAttribute('target') === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '';
        out += `<a href="${escapeAttribute(href)}"${blank}>${inner}</a>`;
        break;
      }
      case 'DIV':
      case 'P':
        // Browsers wrap new lines in blocks; store them as line breaks.
        out += (out && !out.endsWith('<br>') ? '<br>' : '') + inner;
        break;
      default:
        out += inner;
    }
  });
  return out;
};

const cellHtml = (cell) => serialise(cell).replace(/(?:\s*<br>\s*)+$/, '').trim();

/* -------------------------------------------------------------------------
 * State
 * ---------------------------------------------------------------------- */

const form = document.getElementById('adminForm');
const editor = document.getElementById('tm-editor');
const live = document.getElementById('tm-live');
const dataInput = form.querySelector('[name="jform[data]"]');
const headerRadios = [...form.querySelectorAll('[name="jform[has_header]"]')];

const parseRows = (json) => {
  try {
    const parsed = JSON.parse(json || '{}');
    if (!Array.isArray(parsed.rows)) return [];
    return parsed.rows.filter(Array.isArray).map((row) => row.map((cell) => (typeof cell === 'string' ? cell : '')));
  } catch (error) {
    return [];
  }
};

let rows = parseRows(dataInput.value);

// A new table starts with three columns and three rows; the plus signs and bins do the rest.
if (!rows.length) rows = Array.from({ length: 3 }, () => Array(3).fill(''));

// Always rectangular and within the limits.
{
  const width = Math.min(MAX_COLS, Math.max(1, ...rows.map((row) => row.length)));
  rows = rows.slice(0, MAX_ROWS).map((row) => Array.from({ length: width }, (_, index) => row[index] || ''));
}

const hasHeader = () => (headerRadios.find((radio) => radio.checked) || {}).value === '1';
const announce = (message) => { live.textContent = message; };

let schedulePreview = () => {};

const sync = () => {
  dataInput.value = JSON.stringify({ rows });
  schedulePreview();
};

/* -------------------------------------------------------------------------
 * Rendering the grid
 * ---------------------------------------------------------------------- */

/*
 * Editor bar. Which buttons it has, and in which order, is set under
 * Options > Editor Bar; the list arrives here as options.toolbar.
 */
const BUTTONS = {
  bold: { icon: 'bold', label: 'COM_COMPACTTABLES_TB_BOLD', command: 'bold' },
  italic: { icon: 'italic', label: 'COM_COMPACTTABLES_TB_ITALIC', command: 'italic' },
  underline: { icon: 'underline', label: 'COM_COMPACTTABLES_TB_UNDERLINE', command: 'underline' },
  strikethrough: { icon: 'strikethrough', label: 'COM_COMPACTTABLES_TB_STRIKETHROUGH', command: 'strikeThrough' },
  subscript: { icon: 'subscript', label: 'COM_COMPACTTABLES_TB_SUBSCRIPT', command: 'subscript' },
  superscript: { icon: 'superscript', label: 'COM_COMPACTTABLES_TB_SUPERSCRIPT', command: 'superscript' },
  link: {
    icon: 'link',
    label: 'COM_COMPACTTABLES_TB_LINK',
    menu: [
      ['menu', 'list', 'COM_COMPACTTABLES_EDITOR_LINK_MENU'],
      ['article', 'file', 'COM_COMPACTTABLES_EDITOR_LINK_ARTICLE'],
      ['url', 'globe', 'COM_COMPACTTABLES_EDITOR_LINK_URL'],
      ['tel', 'phone', 'COM_COMPACTTABLES_EDITOR_LINK_TEL'],
      ['email', 'envelope', 'COM_COMPACTTABLES_EDITOR_LINK_EMAIL'],
    ],
  },
  unlink: { icon: 'unlink', label: 'COM_COMPACTTABLES_EDITOR_UNLINK', link: 'unlink' },
  removeformat: { icon: 'removeformat', label: 'COM_COMPACTTABLES_TB_REMOVEFORMAT', command: 'removeFormat' },
};

const toolbarLayout = (Array.isArray(options.toolbar) ? options.toolbar : []).filter((id) => id === '|' || BUTTONS[id]);

const toolbar = el('div', {
  class: 'tm-toolbar', role: 'toolbar', 'aria-label': text('COM_COMPACTTABLES_TB_LABEL'), hidden: toolbarLayout.length === 0,
});
const grid = el('div', { class: `tm-grid-area${toolbarLayout.length === 0 ? ' tm-grid-area--no-bar' : ''}` });
editor.append(toolbar, grid);

const toolButton = (id) => {
  const config = BUTTONS[id];
  const label = text(config.label);
  const attributes = {
    type: 'button', class: 'tm-tool', title: label, 'aria-label': label, disabled: true,
  };

  if (config.command) {
    attributes['data-tm-command'] = config.command;
    attributes['aria-pressed'] = 'false';
  }
  if (config.link) attributes['data-tm-link'] = config.link;

  if (!config.menu) return el('button', attributes, icon(config.icon));

  // A button with choices: the three kinds of link.
  attributes['data-tm-menu'] = id;
  attributes['aria-haspopup'] = 'menu';
  attributes['aria-expanded'] = 'false';
  attributes.class = 'tm-tool tm-tool--menu';

  return el(
    'span',
    { class: 'tm-tool-group' },
    el('button', attributes, icon(config.icon), icon('caret', 'tm-tool__caret')),
    el(
      'span',
      { class: 'tm-tool-menu', role: 'menu', 'aria-label': label, hidden: true },
      // A kind of link whose list is not available to this user is left out.
      config.menu.filter(([action]) => !['menu', 'article'].includes(action) || (options.urls && options.urls[action])).map(([action, iconClass, key]) => el(
        'button',
        { type: 'button', class: 'tm-tool-menu__item', role: 'menuitem', 'data-tm-link': action },
        icon(iconClass),
        text(key),
      )),
    ),
  );
};

const toolbarStatus = el('span', { class: 'tm-toolbar__status', 'aria-live': 'polite' });

toolbar.append(
  ...toolbarLayout.map((id) => (id === '|' ? el('span', { class: 'tm-tool-sep', 'aria-hidden': 'true' }) : toolButton(id))),
  toolbarStatus,
);

const iconButton = (className, iconClass, label, data = {}) => el(
  'button',
  { type: 'button', class: className, title: label, 'aria-label': label, ...data },
  icon(iconClass),
);

const render = () => {
  const cols = rows[0].length;
  const header = hasHeader();
  const canAddColumn = cols < MAX_COLS;
  const canAddRow = rows.length < MAX_ROWS;

  const headRow = el('tr', {}, el('td', { class: 'tm-grid__corner' }));
  for (let c = 0; c < cols; c += 1) {
    headRow.append(el(
      'td',
      { class: 'tm-grid__col' },
      el('span', { class: 'tm-grid__name', 'aria-hidden': 'true' }, columnName(c)),
      iconButton('tm-icon-btn tm-icon-btn--danger', 'trash', text('COM_COMPACTTABLES_EDITOR_DELETE_COLUMN', columnName(c)), {
        'data-tm-delete-col': c,
        disabled: cols === 1,
      }),
    ));
  }

  const body = el('tbody');
  rows.forEach((row, r) => {
    const tr = el('tr', { 'data-r': r, class: header && r === 0 ? 'tm-grid__header' : '' });
    tr.append(el(
      'td',
      { class: 'tm-grid__row' },
      iconButton('tm-icon-btn tm-icon-btn--move', 'move', text('COM_COMPACTTABLES_EDITOR_MOVE_ROW', r + 1), {
        'data-tm-move': r,
        disabled: rows.length === 1,
      }),
      iconButton('tm-icon-btn tm-icon-btn--danger', 'trash', text('COM_COMPACTTABLES_EDITOR_DELETE_ROW', r + 1), {
        'data-tm-delete-row': r,
        disabled: rows.length === 1,
      }),
    ));
    row.forEach((html, c) => {
      tr.append(el('td', { class: 'tm-grid__cell' }, el('div', {
        class: 'tm-cell',
        contenteditable: 'true',
        role: 'textbox',
        'aria-multiline': 'true',
        'aria-label': text('COM_COMPACTTABLES_EDITOR_CELL_LABEL', r + 1, columnName(c))
          + (header && r === 0 ? ` (${text('COM_COMPACTTABLES_EDITOR_HEADER_ROW')})` : ''),
        'data-r': r,
        'data-c': c,
        html,
      })));
    });
    body.append(tr);
  });

  const table = el('table', { class: 'tm-grid', role: 'presentation', style: `--tm-cols:${cols}` }, el('thead', {}, headRow), body);

  const addRow = el(
    'button',
    {
      type: 'button',
      class: 'tm-add-line tm-add-line--row',
      'data-tm-add-row': true,
      disabled: !canAddRow,
      title: canAddRow ? text('COM_COMPACTTABLES_EDITOR_ADD_ROW') : text('COM_COMPACTTABLES_EDITOR_MAX_ROWS', MAX_ROWS),
      'aria-label': text('COM_COMPACTTABLES_EDITOR_ADD_ROW'),
    },
    icon('plus'),
  );

  const addColumn = canAddColumn ? el(
    'button',
    {
      type: 'button',
      class: 'tm-add-line tm-add-line--col',
      'data-tm-add-col': true,
      title: text('COM_COMPACTTABLES_EDITOR_ADD_COLUMN'),
      'aria-label': text('COM_COMPACTTABLES_EDITOR_ADD_COLUMN'),
    },
    icon('plus'),
  ) : el('span', { class: 'tm-add-line tm-add-line--col tm-add-line--full', title: text('COM_COMPACTTABLES_EDITOR_MAX_COLUMNS', MAX_COLS) });

  grid.replaceChildren(el(
    'div',
    { class: 'tm-grid-wrap' },
    el('div', { class: 'tm-grid-main' }, table, addRow),
    addColumn,
  ));
};

const focusCell = (r, c) => {
  const cell = grid.querySelector(`.tm-cell[data-r="${r}"][data-c="${c}"]`);
  if (cell) cell.focus();
  return cell;
};

const hasContent = (cells) => cells.some((cell) => cell.replace(/<br>/g, '').trim() !== '');

/* -------------------------------------------------------------------------
 * Rows and columns
 * ---------------------------------------------------------------------- */

const addRow = () => {
  if (rows.length >= MAX_ROWS) return;
  rows.push(Array(rows[0].length).fill(''));
  render();
  sync();
  focusCell(rows.length - 1, 0);
};

const addColumn = () => {
  if (rows[0].length >= MAX_COLS) return;
  rows.forEach((row) => row.push(''));
  render();
  sync();
  focusCell(0, rows[0].length - 1);
};

const deleteRow = (r) => {
  if (rows.length === 1) return;
  // eslint-disable-next-line no-alert
  if (hasContent(rows[r]) && !window.confirm(text('COM_COMPACTTABLES_EDITOR_CONFIRM_DELETE_ROW', r + 1))) return;
  rows.splice(r, 1);
  render();
  sync();
};

const deleteColumn = (c) => {
  if (rows[0].length === 1) return;
  // eslint-disable-next-line no-alert
  if (hasContent(rows.map((row) => row[c])) && !window.confirm(text('COM_COMPACTTABLES_EDITOR_CONFIRM_DELETE_COLUMN', columnName(c)))) return;
  rows.forEach((row) => row.splice(c, 1));
  render();
  sync();
};

const moveRow = (from, to) => {
  if (to < 0 || to >= rows.length || from === to) return;
  rows.splice(to, 0, rows.splice(from, 1)[0]);
  render();
  sync();
  const handle = grid.querySelector(`[data-tm-move="${to}"]`);
  if (handle) handle.focus();
  announce(text('COM_COMPACTTABLES_EDITOR_ROW_MOVED', to + 1));
};

headerRadios.forEach((radio) => radio.addEventListener('change', () => { render(); sync(); }));

grid.addEventListener('click', (event) => {
  const button = event.target.closest('button');
  if (!button || button.disabled) return;

  if (button.hasAttribute('data-tm-add-row')) addRow();
  else if (button.hasAttribute('data-tm-add-col')) addColumn();
  else if (button.hasAttribute('data-tm-delete-row')) deleteRow(Number(button.dataset.tmDeleteRow));
  else if (button.hasAttribute('data-tm-delete-col')) deleteColumn(Number(button.dataset.tmDeleteCol));
});

/* Dragging a row by its handle (mouse, touch or pen). */
grid.addEventListener('pointerdown', (event) => {
  const handle = event.target.closest('[data-tm-move]');
  if (!handle || handle.disabled || event.button !== 0) return;

  const tr = handle.closest('tr');
  const tbody = tr.parentNode;

  event.preventDefault();
  handle.focus();
  handle.setPointerCapture(event.pointerId);
  tr.classList.add('tm-grid__dragging');
  grid.classList.add('tm-grid-area--dragging');

  const onMove = (moveEvent) => {
    const others = [...tbody.children].filter((row) => row !== tr);
    let position = 0;
    others.forEach((row) => {
      const box = row.getBoundingClientRect();
      if (moveEvent.clientY > box.top + box.height / 2) position += 1;
    });
    const reference = others[position] || null;
    if (tr.nextElementSibling !== reference) tbody.insertBefore(tr, reference);
  };

  const onEnd = () => {
    handle.removeEventListener('pointermove', onMove);
    handle.removeEventListener('pointerup', onEnd);
    handle.removeEventListener('pointercancel', onEnd);
    grid.classList.remove('tm-grid-area--dragging');

    const order = [...tbody.children].map((row) => Number(row.dataset.r));
    const from = Number(tr.dataset.r);
    const to = order.indexOf(from);

    rows = order.map((index) => rows[index]);
    render();
    sync();

    const newHandle = grid.querySelector(`[data-tm-move="${to}"]`);
    if (newHandle) newHandle.focus();
    if (to !== from) announce(text('COM_COMPACTTABLES_EDITOR_ROW_MOVED', to + 1));
  };

  handle.addEventListener('pointermove', onMove);
  handle.addEventListener('pointerup', onEnd);
  handle.addEventListener('pointercancel', onEnd);
});

/* -------------------------------------------------------------------------
 * Typing in cells
 * ---------------------------------------------------------------------- */

const commitCell = (cell) => {
  rows[Number(cell.dataset.r)][Number(cell.dataset.c)] = cellHtml(cell);
  sync();
};

grid.addEventListener('input', (event) => {
  const cell = event.target.closest('.tm-cell');
  if (cell) commitCell(cell);
});

grid.addEventListener('keydown', (event) => {
  const handle = event.target.closest('[data-tm-move]');
  if (handle && (event.key === 'ArrowUp' || event.key === 'ArrowDown')) {
    event.preventDefault();
    const from = Number(handle.dataset.tmMove);
    moveRow(from, from + (event.key === 'ArrowUp' ? -1 : 1));
    return;
  }

  const cell = event.target.closest('.tm-cell');
  if (!cell) return;

  if (event.key === 'Enter') {
    // One line break instead of the browser's own block elements.
    event.preventDefault();
    document.execCommand('insertLineBreak');
    return;
  }

  if (event.key === 'Tab') {
    const cells = [...grid.querySelectorAll('.tm-cell')];
    const next = cells[cells.indexOf(cell) + (event.shiftKey ? -1 : 1)];
    if (next) {
      event.preventDefault();
      next.focus();
    }
  }
});

// Paste as plain text: formatting from Word or websites does not belong in a cell.
grid.addEventListener('paste', (event) => {
  const cell = event.target.closest('.tm-cell');
  if (!cell) return;
  event.preventDefault();
  const pasted = (event.clipboardData || window.clipboardData).getData('text/plain');
  document.execCommand('insertText', false, pasted.replace(/\r\n?/g, '\n'));
});

grid.addEventListener('drop', (event) => {
  if (event.target.closest('.tm-cell')) event.preventDefault();
});

// Leaving a cell: show exactly what will be saved.
grid.addEventListener('focusout', (event) => {
  const cell = event.target.closest && event.target.closest('.tm-cell');
  if (!cell) return;
  const html = rows[Number(cell.dataset.r)] && rows[Number(cell.dataset.r)][Number(cell.dataset.c)];
  if (html !== undefined && cell.innerHTML !== html) cell.innerHTML = html;
});

/* -------------------------------------------------------------------------
 * Links: select text in a cell, then pick a menu item, an article or a URL
 * ---------------------------------------------------------------------- */

let selection = null; // { cell, range }

const closestLink = (node, cell) => {
  const element = node.nodeType === Node.ELEMENT_NODE ? node : node.parentElement;
  const link = element ? element.closest('a') : null;
  return link && cell.contains(link) ? link : null;
};

const linksInSelection = (current) => {
  if (!current) return [];
  const own = closestLink(current.range.commonAncestorContainer, current.cell);
  if (own) return [own];
  if (current.range.collapsed) return [];
  return [...current.cell.querySelectorAll('a')].filter((link) => current.range.intersectsNode(link));
};

const FORMAT_TAGS = {
  bold: ['B', 'STRONG'],
  italic: ['I', 'EM'],
  underline: ['U'],
  strikeThrough: ['S', 'STRIKE', 'DEL'],
  subscript: ['SUB'],
  superscript: ['SUP'],
};

const closeToolMenus = () => {
  toolbar.querySelectorAll('.tm-tool-menu').forEach((menu) => { menu.hidden = true; });
  toolbar.querySelectorAll('[data-tm-menu]').forEach((button) => button.setAttribute('aria-expanded', 'false'));
};

const updateToolbar = () => {
  const links = linksInSelection(selection);
  const canLink = !!selection && (!selection.range.collapsed || links.length > 0);

  // Bold, italic, ...: usable as soon as the cursor is in a cell; "pressed" when the text at the cursor has it.
  toolbar.querySelectorAll('[data-tm-command]').forEach((button) => {
    button.disabled = !selection;
    // Looked up in the cell itself: the browser's own answer also calls a link "underlined".
    const tags = FORMAT_TAGS[button.dataset.tmCommand];
    let pressed = false;
    if (selection && tags) {
      let node = selection.range.startContainer;
      if (node.nodeType !== Node.ELEMENT_NODE) node = node.parentElement;
      // At the very start of a formatted piece the cursor sits just outside it.
      if (node === selection.cell && !selection.range.collapsed) {
        const first = selection.range.startContainer.childNodes[selection.range.startOffset];
        if (first && first.nodeType === Node.ELEMENT_NODE) node = first;
      }
      for (; node && node !== selection.cell; node = node.parentElement) {
        if (tags.includes(node.tagName)) { pressed = true; break; }
      }
      if (!pressed && node === selection.cell && selection.cell.classList.contains('tm-cell')) {
        const inner = selection.range.commonAncestorContainer;
        const element = inner.nodeType === Node.ELEMENT_NODE ? inner : inner.parentElement;
        pressed = !!(element && element !== selection.cell && element.closest(tags.join(',')));
      }
    }
    button.setAttribute('aria-pressed', pressed ? 'true' : 'false');
  });

  toolbar.querySelectorAll('[data-tm-menu]').forEach((button) => { button.disabled = !canLink; });
  toolbar.querySelectorAll('.tm-tool[data-tm-link="unlink"]').forEach((button) => { button.disabled = links.length === 0; });
  if (!canLink) closeToolMenus();

  // Where the link under the cursor goes to.
  toolbarStatus.textContent = links.length === 1 ? text('COM_COMPACTTABLES_EDITOR_LINK_CURRENT', links[0].getAttribute('href')) : '';
};

document.addEventListener('selectionchange', () => {
  const current = document.getSelection();
  if (!current || !current.rangeCount) return;

  const range = current.getRangeAt(0);
  const node = range.commonAncestorContainer;
  const element = node.nodeType === Node.ELEMENT_NODE ? node : node.parentElement;
  const cell = element ? element.closest('.tm-cell') : null;

  if (cell && grid.contains(cell)) {
    selection = { cell, range: range.cloneRange() };
  } else if (!element || !element.closest('.tm-toolbar, .tm-dialog, joomla-dialog')) {
    selection = null;
  }

  updateToolbar();
});

const finishCellEdit = (cell, focusNode) => {
  commitCell(cell);
  cell.focus();
  if (focusNode && cell.contains(focusNode)) {
    const range = document.createRange();
    range.selectNodeContents(focusNode);
    const current = document.getSelection();
    current.removeAllRanges();
    current.addRange(range);
  }
};

const applyLink = (target, href, newTab) => {
  const { cell, range } = target;
  if (!cell.isConnected) return;

  let link = closestLink(range.commonAncestorContainer, cell);

  if (!link) {
    if (range.collapsed) return;
    const fragment = range.extractContents();
    fragment.querySelectorAll('a').forEach((inner) => inner.replaceWith(...inner.childNodes));
    link = document.createElement('a');
    link.append(fragment);
    range.insertNode(link);
  }

  link.setAttribute('href', href);
  if (newTab) {
    link.setAttribute('target', '_blank');
    link.setAttribute('rel', 'noopener noreferrer');
  } else {
    link.removeAttribute('target');
    link.removeAttribute('rel');
  }

  // Splitting an existing link can leave empty remains behind.
  cell.querySelectorAll('a').forEach((other) => { if (!other.textContent.trim()) other.remove(); });

  finishCellEdit(cell, link);
};

const removeLinks = (target) => {
  const links = linksInSelection(target);
  if (!links.length) return;
  links.forEach((link) => link.replaceWith(...link.childNodes));
  target.cell.normalize();
  finishCellEdit(target.cell);
};

/** Joomla's own "select a menu item / article" dialogs; they answer with postMessage. */
const pickContent = (type, target) => {
  window.JoomlaExpectingPostMessage = true;

  const dialog = new JoomlaDialog({
    popupType: 'iframe',
    // Own look for the dialog, so it is right in every site template too (see picker-dialog.css).
    className: 'tm-picker-dialog tm-picker-dialog--fill',
    src: options.urls[type],
    textHeader: text(type === 'menu' ? 'COM_COMPACTTABLES_EDITOR_SELECT_MENU' : 'COM_COMPACTTABLES_EDITOR_SELECT_ARTICLE'),
  });
  dialog.classList.add('joomla-dialog-content-select-field');

  const onMessage = (event) => {
    if (event.origin !== window.location.origin || !event.data) return;

    if (event.data.messageType === 'joomla:content-select') {
      let href = event.data.uri || '';
      if (type === 'menu' && event.data.id) href = `index.php?Itemid=${parseInt(event.data.id, 10)}`;
      dialog.close();
      if (href) applyLink(target, href, false);
    } else if (event.data.messageType === 'joomla:cancel') {
      dialog.close();
    }
  };

  dialog.addEventListener('joomla-dialog:close', () => {
    delete window.JoomlaExpectingPostMessage;
    window.removeEventListener('message', onMessage);
    dialog.destroy();
  });

  window.addEventListener('message', onMessage);
  dialog.show();
};

const normaliseUrl = (value) => {
  const url = value.trim();
  if (!url) return '';
  if (/^(https?:\/\/|mailto:|tel:|\/|#|index\.php)/i.test(url)) return isSafeHref(url) ? url : '';
  if (/^[^\s@/]+@[^\s@/]+\.[^\s@/]+$/.test(url)) return `mailto:${url}`;
  if (/^[a-z0-9-]+(\.[a-z0-9-]+)+([/?#].*)?$/i.test(url)) return `https://${url}`;
  return '';
};

/** A phone number as typed ("020 - 123 45 67", "+31 (0)6 1234 5678") becomes a tel: link a phone can dial. */
const normaliseTel = (value) => {
  let number = value.trim().replace(/^tel:/i, '');
  const international = /^(\+|00)/.test(number);
  // "+31 (0)20": the zero between brackets is not dialled from abroad.
  if (international) number = number.replace(/\(0\)/, '');
  number = number.replace(/^00/, '+').replace(/[^\d+]/g, '').replace(/(?!^)\+/g, '');
  return /^\+?\d{3,15}$/.test(number) ? `tel:${number}` : '';
};

const normaliseEmail = (value) => {
  const address = value.trim().replace(/^mailto:/i, '');
  return /^[^\s@/"<>]+@[^\s@/"<>]+\.[^\s@/"<>]+$/.test(address) ? `mailto:${address}` : '';
};

/** The three kinds of link that are typed in: what the field is called, how the value is checked. */
const TYPED_LINKS = {
  url: {
    label: 'COM_COMPACTTABLES_EDITOR_URL_LABEL', invalid: 'COM_COMPACTTABLES_EDITOR_URL_INVALID', placeholder: 'https://', type: 'text', normalise: normaliseUrl, newTab: true,
  },
  tel: {
    label: 'COM_COMPACTTABLES_EDITOR_TEL_LABEL', invalid: 'COM_COMPACTTABLES_EDITOR_TEL_INVALID', placeholder: '020 123 45 67', type: 'tel', normalise: normaliseTel, prefix: /^tel:/i,
  },
  email: {
    label: 'COM_COMPACTTABLES_EDITOR_EMAIL_LABEL', invalid: 'COM_COMPACTTABLES_EDITOR_EMAIL_INVALID', placeholder: 'naam@voorbeeld.nl', type: 'email', normalise: normaliseEmail, prefix: /^mailto:/i,
  },
};

const pickUrl = (target, kind = 'url') => {
  const config = TYPED_LINKS[kind];
  const existing = closestLink(target.range.commonAncestorContainer, target.cell);
  const input = el('input', {
    type: config.type, class: 'form-control', id: 'tm-url', placeholder: config.placeholder, autocomplete: 'off', spellcheck: 'false',
  });
  const newTab = el('input', { type: 'checkbox', class: 'form-check-input', id: 'tm-url-tab' });
  const error = el('div', { class: 'invalid-feedback', id: 'tm-url-error' }, text(config.invalid));
  const existingHref = existing ? existing.getAttribute('href') || '' : '';

  if (kind === 'url') {
    input.value = existingHref;
    newTab.checked = Boolean(existing) && existing.getAttribute('target') === '_blank';
  } else if (config.prefix.test(existingHref)) {
    input.value = existingHref.replace(config.prefix, '');
  } else {
    // The selected text is usually the number or address itself: fill it in when it is one.
    const selected = (existing ? existing.textContent : target.range.toString()).trim();
    if (config.normalise(selected)) input.value = selected;
  }

  const dialog = el(
    'dialog',
    { class: 'tm-dialog', 'aria-labelledby': 'tm-url-label' },
    el(
      'form',
      { method: 'dialog', novalidate: true },
      el('label', { class: 'form-label', for: 'tm-url', id: 'tm-url-label' }, text(config.label)),
      input,
      error,
      config.newTab ? el('div', { class: 'form-check tm-dialog__check' }, newTab, el('label', { class: 'form-check-label', for: 'tm-url-tab' }, text('COM_COMPACTTABLES_EDITOR_URL_NEW_TAB'))) : null,
      el(
        'div',
        { class: 'tm-dialog__buttons' },
        el('button', { type: 'button', class: 'btn btn-secondary', 'data-tm-cancel': true }, text('JCANCEL')),
        el('button', { type: 'submit', class: 'btn btn-primary' }, text('JAPPLY')),
      ),
    ),
  );

  dialog.querySelector('form').addEventListener('submit', (event) => {
    event.preventDefault();
    const href = config.normalise(input.value);
    if (!href) {
      input.classList.add('is-invalid');
      input.setAttribute('aria-describedby', 'tm-url-error');
      input.focus();
      return;
    }
    dialog.close();
    applyLink(target, href, Boolean(config.newTab) && newTab.checked);
  });
  input.addEventListener('input', () => input.classList.remove('is-invalid'));
  dialog.querySelector('[data-tm-cancel]').addEventListener('click', () => dialog.close());
  dialog.addEventListener('close', () => dialog.remove());

  document.body.append(dialog);
  dialog.showModal();
  input.focus();
  input.select();
};

/** Put the cursor/selection back where it was in the cell (it is lost when a button gets the focus). */
const restoreSelection = (target) => {
  target.cell.focus();
  const current = document.getSelection();
  current.removeAllRanges();
  current.addRange(target.range);
};

const runCommand = (command) => {
  if (!selection || !selection.cell.isConnected) return;

  const { cell } = selection;
  restoreSelection(selection);
  // Plain tags (<b>, <i>, ...) instead of inline styles: those are what a cell may contain.
  document.execCommand('styleWithCSS', false, false);
  document.execCommand(command, false, null);
  commitCell(cell);
  updateToolbar();
};

// Keep the text selection alive while the bar is clicked.
toolbar.addEventListener('mousedown', (event) => {
  if (event.target.closest('button')) event.preventDefault();
});

toolbar.addEventListener('click', (event) => {
  const button = event.target.closest('button');
  if (!button || button.disabled) return;

  if (button.dataset.tmMenu) {
    const menu = button.parentNode.querySelector('.tm-tool-menu');
    const open = menu.hidden;
    closeToolMenus();
    menu.hidden = !open;
    button.setAttribute('aria-expanded', open ? 'true' : 'false');
    return;
  }

  if (button.dataset.tmCommand) {
    runCommand(button.dataset.tmCommand);
    return;
  }

  if (!button.dataset.tmLink || !selection) return;

  const target = { cell: selection.cell, range: selection.range.cloneRange() };
  closeToolMenus();

  switch (button.dataset.tmLink) {
    case 'menu':
    case 'article':
      pickContent(button.dataset.tmLink, target);
      break;
    case 'url':
    case 'tel':
    case 'email':
      pickUrl(target, button.dataset.tmLink);
      break;
    default:
      removeLinks(target);
  }
});

// The menu of a button closes on Escape (wherever the focus is: it usually stays
// in the cell) and when something else is clicked.
document.addEventListener('keydown', (event) => {
  if (event.key !== 'Escape' || !toolbar.querySelector('[data-tm-menu][aria-expanded="true"]')) return;
  event.preventDefault();
  closeToolMenus();
}, true);

document.addEventListener('click', (event) => {
  if (!event.target.closest || !event.target.closest('.tm-tool-group')) closeToolMenus();
});

/* -------------------------------------------------------------------------
 * CSS tab: colour and size fields, live preview
 * ---------------------------------------------------------------------- */

const HEX = /^#(?:[0-9a-f]{3}|[0-9a-f]{6})$/i;
const expandHex = (hex) => (hex.length === 4 ? `#${hex[1]}${hex[1]}${hex[2]}${hex[2]}${hex[3]}${hex[3]}` : hex).toLowerCase();

document.querySelectorAll('[data-tm-color]').forEach((group) => {
  const picker = group.querySelector('[data-tm-color-picker]');
  const field = group.querySelector('[data-tm-color-text]');

  const refresh = () => {
    const valid = HEX.test(field.value);
    group.classList.toggle('tm-color--empty', !valid);
    if (valid) picker.value = expandHex(field.value);
    schedulePreview();
  };

  picker.addEventListener('input', () => { field.value = picker.value; refresh(); });
  field.addEventListener('input', refresh);
  field.addEventListener('change', () => {
    field.value = field.value.trim();
    if (field.value && !field.value.startsWith('#')) field.value = `#${field.value}`;
    if (!HEX.test(field.value)) field.value = '';
    refresh();
  });
  group.querySelector('[data-tm-color-clear]').addEventListener('click', () => { field.value = ''; refresh(); });
});

document.querySelectorAll('[data-tm-size]').forEach((group) => {
  const number = group.querySelector('[data-tm-size-number]');
  const unit = group.querySelector('[data-tm-size-unit]');
  const value = group.querySelector('[data-tm-size-value]');

  // One step below the lowest value means "empty again" (the template's default): the browser's own
  // lower limit moves one step down, and whatever lands below the real limit clears the field.
  const lowest = number.hasAttribute('min') ? Number(number.getAttribute('min')) : null;
  if (lowest !== null && !Number.isNaN(lowest)) number.setAttribute('min', String(lowest - 1));

  const refresh = () => {
    if (lowest !== null && number.value.trim() !== '' && Number(number.value) < lowest) number.value = '';
    const amount = number.value.trim().replace(',', '.');
    value.value = amount === '' || Number.isNaN(Number(amount)) ? '' : `${amount}${unit.value || unit.dataset.value}`;
    schedulePreview();
  };

  number.addEventListener('input', refresh);
  unit.addEventListener('change', refresh);
});

const preview = document.getElementById('tm-preview');
const param = (name) => {
  const field = form.querySelector(`[name="jform[params][${name}]"]`);
  return field ? field.value.trim() : '';
};

/** Mirrors the plugin's PHP: the same classes and CSS variables as on the website. */
const renderPreview = () => {
  if (!preview) return;

  const header = hasHeader() && rows.length > 0;
  const responsive = param('responsive') || 'fit';
  const classes = ['compacttables', `compacttables--${responsive}`];
  const variables = {};
  const colour = (name) => (HEX.test(param(name)) ? param(name) : '');

  if (header) {
    if (colour('head_bg')) { classes.push('compacttables--head-bg'); variables['--tm-head-bg'] = colour('head_bg'); }
    if (colour('head_color')) { classes.push('compacttables--head-color'); variables['--tm-head-color'] = colour('head_color'); }
    if (param('head_bold') === '1') classes.push('compacttables--head-bold');
    if (param('head_bold') === '0') classes.push('compacttables--head-normal');
    if (param('head_font_size')) { classes.push('compacttables--head-size'); variables['--tm-head-size'] = param('head_font_size'); }
    if (colour('head_link_color')) { classes.push('compacttables--head-link'); variables['--tm-head-link'] = colour('head_link_color'); }
  }

  if (colour('body_color')) { classes.push('compacttables--body-color'); variables['--tm-body-color'] = colour('body_color'); }
  if (param('body_bold') === '1') classes.push('compacttables--body-bold');
  if (param('body_bold') === '0') classes.push('compacttables--body-normal');
  if (param('body_font_size')) { classes.push('compacttables--body-size'); variables['--tm-body-size'] = param('body_font_size'); }
  if (colour('body_link_color')) { classes.push('compacttables--body-link'); variables['--tm-body-link'] = colour('body_link_color'); }

  const cellAlign = param('cell_align') || 'left';
  if (['left', 'center', 'right'].includes(cellAlign)) classes.push(`compacttables--text-${cellAlign}`);
  const cellValign = param('cell_valign') || 'top';
  if (['top', 'middle', 'bottom'].includes(cellValign)) classes.push(`compacttables--valign-${cellValign}`);
  if (['1', '1.15', '1.25', '1.5', '1.75', '2'].includes(param('line_height'))) {
    classes.push('compacttables--line-height');
    variables['--tm-line-height'] = param('line_height');
  }
  if (/^\d*\.?\d+(px|rem|em)$/.test(param('cell_padding'))) {
    classes.push('compacttables--padding');
    variables['--tm-padding'] = param('cell_padding');
  }

  if (/^\d+(px|%)$/.test(param('width')) && param('width') !== '100%') {
    classes.push('compacttables--width', `compacttables--align-${['center', 'right'].includes(param('align')) ? param('align') : 'left'}`);
    variables['--tm-width'] = param('width');
  }

  if (/^\d*\.?\d+(px|rem|em)$/.test(param('spacing')) && parseFloat(param('spacing')) > 0) {
    classes.push('compacttables--spacing');
    variables['--tm-spacing'] = param('spacing');
  }

  [['vline', 'vline'], ['hline', 'hline'], ['head_line', 'head-line']].forEach(([name, cls]) => {
    if (name === 'head_line' && !header) return;
    if (!param(`${name}_width`) && !colour(`${name}_color`)) return;
    classes.push(`compacttables--${cls}`);
    variables[`--tm-${cls}-w`] = param(`${name}_width`) || '1px';
    variables[`--tm-${cls}-c`] = colour(`${name}_color`) || 'currentColor';
  });

  if (param('outer_width') || colour('outer_color')) {
    classes.push('compacttables--outer');
    variables['--tm-outer-w'] = param('outer_width') || '1px';
    variables['--tm-outer-c'] = colour('outer_color') || 'currentColor';
  }

  const empty = !hasContent(rows.flat());
  const sample = (r, c) => (empty ? `${text('COM_COMPACTTABLES_PREVIEW_SAMPLE')} ${columnName(c)}${r + 1}` : rows[r][c]);
  const labels = header ? rows[0].map((html, c) => el('div', { html: sample(0, c) }).textContent.trim()) : [];

  const table = el('table', { class: `compacttables__table ${options.tableClass || ''}`.trim() });

  if (header) {
    table.append(el('thead', {}, el('tr', {}, rows[0].map((html, c) => el('th', { scope: 'col', html: sample(0, c) })))));
  }

  const bodyRows = rows.map((row, r) => ({ row, r })).slice(header ? 1 : 0);
  if (bodyRows.length) {
    table.append(el('tbody', {}, bodyRows.map(({ row, r }) => el('tr', {}, row.map((html, c) => el('td', {
      'data-label': labels[c] || null,
      html: sample(r, c),
    }))))));
  }

  const wrapper = el('div', { class: classes.join(' ') }, table);
  Object.entries(variables).forEach(([name, value]) => wrapper.style.setProperty(name, value));
  preview.replaceChildren(wrapper);

  const note = document.querySelector('.tm-header-off');
  if (note) note.hidden = hasHeader();

  // The line under the top row is only offered while there is a top row.
  ['head_line_width', 'head_line_color'].forEach((name) => {
    const field = document.getElementById(`jform_params_${name}`);
    const group = field && field.closest('.control-group');
    if (group) group.hidden = !hasHeader();
  });
};

let previewTimer = 0;
schedulePreview = () => {
  window.clearTimeout(previewTimer);
  previewTimer = window.setTimeout(renderPreview, 80);
};

if (preview) {
  // Links in the preview are for looking, not for leaving the form.
  preview.addEventListener('click', (event) => { if (event.target.closest('a')) event.preventDefault(); });

  const cssFields = document.getElementById('tm-css-fields');
  cssFields.addEventListener('input', schedulePreview);
  cssFields.addEventListener('change', schedulePreview);

  document.querySelectorAll('[data-tm-preview-width]').forEach((button) => {
    button.addEventListener('click', () => {
      document.querySelectorAll('[data-tm-preview-width]').forEach((other) => {
        const active = other === button;
        other.classList.toggle('active', active);
        other.setAttribute('aria-pressed', active ? 'true' : 'false');
      });
      preview.classList.toggle('tm-preview--narrow', button.dataset.tmPreviewWidth === 'narrow');
    });
  });
}

/* Linked tables: a drop-down with tick boxes; the closed list shows the names that are ticked. */
document.querySelectorAll('[data-tm-links]').forEach((links) => {
  const summary = links.querySelector('[data-tm-links-summary]');
  const update = () => {
    const names = [...links.querySelectorAll('input[type="checkbox"]:checked')].map((box) => box.dataset.tmLinksTitle);
    summary.textContent = names.length ? names.join(', ') : links.dataset.tmLinksNone;
  };

  links.addEventListener('change', update);

  // Close the list on a click elsewhere or with Escape, like an ordinary drop-down.
  document.addEventListener('click', (event) => {
    if (links.open && !links.contains(event.target)) links.open = false;
  });
  links.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && links.open) {
      links.open = false;
      links.querySelector('summary').focus();
    }
  });
});

/* Copy the {table N} code. */
document.addEventListener('click', (event) => {
  const button = event.target.closest('[data-tm-copy]');
  if (!button) return;
  event.preventDefault();
  const done = () => {
    const symbol = button.querySelector('.tm-icon');
    if (!symbol) return;
    symbol.innerHTML = iconMarkup('check');
    window.setTimeout(() => { symbol.innerHTML = iconMarkup('copy'); }, 1500);
  };
  if (navigator.clipboard) navigator.clipboard.writeText(button.dataset.tmCopy).then(done, () => {});
});

/* -------------------------------------------------------------------------
 * Inside the picker dialog: own Save / Back buttons, and a wide dialog
 * ---------------------------------------------------------------------- */

if (options.embedded) {
  // Joomla shows validation messages in this container; not every site template has one in a dialog page.
  if (!document.getElementById('system-message-container')) {
    const bar = document.querySelector('.tm-embedded__bar');
    if (bar) bar.after(el('div', { id: 'system-message-container', 'aria-live': 'polite' }));
  }

  document.querySelectorAll('[data-tm-task]').forEach((button) => {
    button.addEventListener('click', () => {
      const task = button.dataset.tmTask;
      // Going back does not need a valid form; saving does.
      Joomla.submitform(task, form, !task.endsWith('.cancel'));
    });
  });

  try {
    const frame = window.frameElement;
    const host = frame && frame.closest ? frame.closest('.tm-picker-dialog') : null;
    if (host) {
      host.classList.add('tm-picker-dialog--wide');
      // The list set a height that fitted the list; the editor takes the room the dialog allows.
      frame.style.height = '';
      frame.style.blockSize = '';
    }
  } catch (error) {
    // Not in a dialog we can reach: nothing to adjust.
  }
}

render();
sync();
renderPreview();
