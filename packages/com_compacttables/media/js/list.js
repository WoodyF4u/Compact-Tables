/**
 * Compact Tables – list of tables: delete with confirmation, copy shortcode.
 *
 * @copyright  (C) 2026 CompactWeb
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

const copyShortcode = async (button) => {
  const code = button.dataset.tmCopy;

  try {
    await navigator.clipboard.writeText(code);
  } catch (error) {
    // Clipboard API unavailable (e.g. no https): fall back to a temporary field.
    const field = document.createElement('textarea');
    field.value = code;
    field.setAttribute('readonly', '');
    field.style.position = 'fixed';
    field.style.opacity = '0';
    document.body.appendChild(field);
    field.select();
    document.execCommand('copy');
    field.remove();
  }

  const icon = button.querySelector('[class^="icon-"]');
  if (icon) {
    icon.className = 'icon-check';
    window.setTimeout(() => { icon.className = 'icon-copy'; }, 1500);
  }

  Joomla.renderMessages({ message: [Joomla.Text._('COM_COMPACTTABLES_SHORTCODE_COPIED').replace('%s', code)] }, undefined, false, 3000);
};

document.addEventListener('click', (event) => {
  const copyButton = event.target.closest('[data-tm-copy]');
  if (copyButton) {
    event.preventDefault();
    copyShortcode(copyButton);
    return;
  }

  const deleteButton = event.target.closest('[data-tm-delete]');
  if (!deleteButton) return;

  event.preventDefault();

  const message = Joomla.Text._('COM_COMPACTTABLES_CONFIRM_DELETE')
    .replace('%1$s', deleteButton.dataset.tmTitle)
    .replace('%2$s', deleteButton.dataset.tmShortcode);

  // eslint-disable-next-line no-alert
  if (!window.confirm(message)) return;

  const form = document.getElementById('adminForm');
  const input = document.createElement('input');
  input.type = 'hidden';
  input.name = 'cid[]';
  input.value = deleteButton.dataset.tmDelete;
  form.appendChild(input);
  form.task.value = 'tables.delete';
  form.submit();
});
