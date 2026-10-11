/**
 * Compact Tables – picker shown by the editor button.
 *
 * The chosen code goes to the editor in its own paragraph: an editor splits
 * the surrounding text around it, which is what a table needs.
 *
 * @copyright  (C) 2026 CompactWeb
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

const { editor, deleteUrl, token } = Joomla.getOptions('com_compacttables.modal', {});

const insert = (code) => {
  const html = `<p>${code}</p>`;
  const host = window.parent;

  // Joomla 5/6 editors (TinyMCE, JCE, CodeMirror, ...) wait for a message and close the dialog themselves.
  if (host.JoomlaExpectingPostMessage) {
    host.postMessage({ messageType: 'joomla:content-select', html, text: code });
    return;
  }

  // Older integrations: write to the editor directly and close the dialog.
  const instance = host.Joomla && host.Joomla.editors && host.Joomla.editors.instances
    ? host.Joomla.editors.instances[editor]
    : null;

  if (instance) instance.replaceSelection(html);

  if (host.Joomla && host.Joomla.Modal && host.Joomla.Modal.getCurrent()) {
    host.Joomla.Modal.getCurrent().close();
  }
};

document.addEventListener('click', (event) => {
  const button = event.target.closest('[data-tm-insert]');
  if (!button) return;
  event.preventDefault();
  insert(button.dataset.tmInsert);
});

/* Delete a table from the list, after confirmation; the list is then loaded again. */
document.addEventListener('click', async (event) => {
  const button = event.target.closest('[data-tm-delete]');
  if (!button || !deleteUrl) return;
  event.preventDefault();

  const message = Joomla.Text._('COM_COMPACTTABLES_CONFIRM_DELETE')
    .replace('%1$s', button.dataset.tmTitle)
    .replace('%2$s', button.dataset.tmShortcode);

  // eslint-disable-next-line no-alert
  if (!window.confirm(message)) return;

  const errorBox = document.querySelector('.tm-modal__error');
  const fail = (text) => {
    errorBox.textContent = `${Joomla.Text._('COM_COMPACTTABLES_MODAL_DELETE_FAILED')} ${text || ''}`.trim();
    errorBox.hidden = false;
    button.disabled = false;
  };

  button.disabled = true;
  errorBox.hidden = true;

  try {
    const body = new URLSearchParams();
    body.set('id', button.dataset.tmDelete);
    body.set(token, '1');

    const response = await fetch(deleteUrl, {
      method: 'POST', body, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    const result = await response.json().catch(() => null);

    if (!response.ok || !result || !result.success) {
      fail(result && result.message);
      return;
    }

    window.location.reload();
  } catch (error) {
    fail('');
  }
});

/*
 * The dialog is only as tall as the list needs: this page tells the frame it
 * sits in how tall its content is. With many tables the frame stops growing
 * (see picker-dialog.css) and the list scrolls inside it.
 */
const inner = document.querySelector('.tm-modal__inner');
const frame = (() => { try { return window.frameElement; } catch (error) { return null; } })();

if (inner && frame && frame.closest && frame.closest('.tm-picker-dialog')) {
  // Back from the table editor, which makes the dialog wide.
  frame.closest('.tm-picker-dialog').classList.remove('tm-picker-dialog--wide');

  const fit = () => {
    const box = getComputedStyle(inner.parentNode);
    const height = Math.ceil(inner.getBoundingClientRect().height + parseFloat(box.paddingTop) + parseFloat(box.paddingBottom));
    frame.style.height = `${height}px`;
    frame.style.blockSize = `${height}px`;
  };

  fit();
  if (window.ResizeObserver) new ResizeObserver(fit).observe(inner);
  window.addEventListener('load', fit);
}

const filter = document.getElementById('tm-modal-filter');

if (filter) {
  const rows = [...document.querySelectorAll('[data-tm-search]')];
  const noMatch = document.getElementById('tm-modal-nomatch');

  filter.addEventListener('input', () => {
    const query = filter.value.trim().toLowerCase();
    let shown = 0;
    rows.forEach((row) => {
      const match = row.dataset.tmSearch.includes(query);
      row.hidden = !match;
      if (match) shown += 1;
    });
    if (noMatch) noMatch.hidden = shown > 0;
  });
}
