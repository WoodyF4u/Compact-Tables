/**
 * Compact Tables – Options > Editor Bar: compose the bar by dragging buttons
 * between "current layout" and "available buttons".
 *
 * Without a mouse: Enter or Space moves the focused button to the other
 * list, and the left/right arrow keys shift it within the current layout.
 *
 * @copyright  (C) 2026 CompactWeb
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

document.querySelectorAll('[data-tm-tbconf]').forEach((root) => {
  const current = root.querySelector('[data-zone="current"]');
  const available = root.querySelector('[data-zone="available"]');
  const source = root.querySelector('[data-source]');
  const input = root.querySelector('input[type="hidden"]');
  const live = root.querySelector('[data-live]');
  const separatorTemplate = source.querySelector('.tm-chip').cloneNode(true);

  const isSeparator = (chip) => chip.dataset.id === '|';
  const name = (chip) => chip.getAttribute('title');

  /** The stored value follows what is in "current layout". */
  const sync = () => {
    const ids = [...current.querySelectorAll('.tm-chip')].map((chip) => chip.dataset.id);
    input.value = ids.length ? ids.join(',') : root.dataset.none;
    current.classList.toggle('tm-tbconf__zone--empty', ids.length === 0);
  };

  const toAvailable = (chip) => {
    // A separator is not kept: the list of available buttons always offers a fresh one.
    if (isSeparator(chip)) chip.remove();
    else available.insertBefore(chip, source);
  };

  const toCurrent = (chip, before = null) => {
    const item = chip.parentNode === source ? separatorTemplate.cloneNode(true) : chip;
    current.insertBefore(item, before);
    return item;
  };

  /* ---- Dragging ------------------------------------------------------- */

  let dragged = null;

  root.addEventListener('dragstart', (event) => {
    const chip = event.target.closest('.tm-chip');
    if (!chip) return;
    dragged = chip;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', chip.dataset.id);
    window.requestAnimationFrame(() => chip.classList.add('tm-chip--dragging'));
  });

  root.addEventListener('dragend', () => {
    if (dragged) dragged.classList.remove('tm-chip--dragging');
    dragged = null;
    root.querySelectorAll('.tm-tbconf__zone--over').forEach((zone) => zone.classList.remove('tm-tbconf__zone--over'));
  });

  /** The chip in the current layout before which a drop at this point should land. */
  const dropTarget = (x, y) => {
    const chips = [...current.querySelectorAll('.tm-chip')].filter((chip) => chip !== dragged);
    return chips.find((chip) => {
      const box = chip.getBoundingClientRect();
      // Same row and left of its middle, or on an earlier row.
      return y < box.top || (y <= box.bottom && x < box.left + box.width / 2);
    }) || null;
  };

  [current, available].forEach((zone) => {
    zone.addEventListener('dragover', (event) => {
      if (!dragged) return;
      event.preventDefault();
      event.dataTransfer.dropEffect = 'move';
      zone.classList.add('tm-tbconf__zone--over');
    });

    zone.addEventListener('dragleave', (event) => {
      if (!zone.contains(event.relatedTarget)) zone.classList.remove('tm-tbconf__zone--over');
    });

    zone.addEventListener('drop', (event) => {
      if (!dragged) return;
      event.preventDefault();
      zone.classList.remove('tm-tbconf__zone--over');

      if (zone === current) toCurrent(dragged, dropTarget(event.clientX, event.clientY));
      else if (dragged.parentNode === current) toAvailable(dragged);

      sync();
    });
  });

  /* ---- Clicking and keyboard ------------------------------------------ */

  root.addEventListener('click', (event) => {
    if (event.target.closest('[data-reset]')) {
      [...current.querySelectorAll('.tm-chip')].forEach(toAvailable);
      root.dataset.default.split(',').forEach((id) => {
        const chip = id === '|' ? source.querySelector('.tm-chip') : available.querySelector(`.tm-chip[data-id="${id}"]`);
        if (chip) toCurrent(chip);
      });
      sync();
      return;
    }

    const chip = event.target.closest('.tm-chip');
    if (!chip) return;

    if (chip.parentNode === current) {
      const next = chip.nextElementSibling || chip.previousElementSibling;
      const text = name(chip);
      toAvailable(chip);
      (chip.isConnected ? chip : next || source.querySelector('.tm-chip')).focus();
      live.textContent = text;
    } else {
      toCurrent(chip).focus();
    }

    sync();
  });

  current.addEventListener('keydown', (event) => {
    const chip = event.target.closest('.tm-chip');
    if (!chip || (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight')) return;

    event.preventDefault();
    const rtl = getComputedStyle(current).direction === 'rtl';
    const back = (event.key === 'ArrowLeft') !== rtl;

    if (back && chip.previousElementSibling) current.insertBefore(chip, chip.previousElementSibling);
    else if (!back && chip.nextElementSibling) current.insertBefore(chip.nextElementSibling, chip);

    chip.focus();
    sync();
  });

  sync();
});
