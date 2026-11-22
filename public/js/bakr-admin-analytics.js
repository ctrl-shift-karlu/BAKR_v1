/* BAKR admin Analytics: long tables and lists start collapsed, showing the first few rows
   (data-peek="N"), with a "Show all" button. Without this script everything is simply shown. */
(function () {
  'use strict';

  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var uid = 0;

  /**
   * The rows of a collapsible box: table body rows, bar-chart rows or list items.
   * @param {HTMLElement} box  element with data-peek
   * @returns {NodeListOf<Element>}
   */
  function rows(box) {
    return box.querySelectorAll('tbody > tr, .viz-stack > li, :scope > li');
  }

  /**
   * Height in px that shows the first n rows (plus a hint of the next one), measured from the box's top.
   * Returns 0 when there is nothing to hide.
   * @param {HTMLElement} box
   * @param {number} n  rows to keep visible
   * @returns {number}
   */
  function peekHeight(box, n) {
    var list = rows(box);
    if (list.length <= n) return 0;
    var last = list[n - 1].getBoundingClientRect();
    var top = box.getBoundingClientRect().top;
    // A little of the next row stays visible under the fade, so it is clear there is more.
    var next = list[n].getBoundingClientRect();
    return Math.round(last.bottom - top + Math.min(18, (next.bottom - next.top) / 2));
  }

  /**
   * Makes one box collapsible: collapsed to its first rows, with a Show all / Show less button after it.
   * Boxes with no more rows than data-peek are left alone.
   * @param {HTMLElement} box
   */
  function setup(box) {
    var n = parseInt(box.getAttribute('data-peek'), 10) || 5;
    var total = rows(box).length;
    if (total <= n) return;

    var id = box.id || (box.id = 'an-peek-' + (++uid));
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'an-peek-toggle';
    btn.setAttribute('aria-controls', id);
    var host = box.tagName === 'TABLE' ? box.parentNode : box;
    host.insertAdjacentElement('afterend', btn);

    var open = false;

    function paint() {
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      btn.innerHTML = open
        ? 'Show less <i class="bi bi-chevron-up" aria-hidden="true"></i>'
        : 'Show all ' + total + ' <i class="bi bi-chevron-down" aria-hidden="true"></i>';
    }

    function collapse() {
      box.classList.add('is-peek');
      box.classList.toggle('is-instant', reduce);
      box.style.setProperty('--peek-h', peekHeight(box, n) + 'px');
    }

    function expand() {
      box.style.setProperty('--peek-h', box.scrollHeight + 'px');
      box.classList.remove('is-peek');
      // Let the height animation finish, then free the box so it can grow with its content.
      window.setTimeout(function () { if (open) box.style.removeProperty('--peek-h'); }, reduce ? 0 : 320);
    }

    btn.addEventListener('click', function () {
      open = !open;
      if (open) {
        box.style.setProperty('--peek-h', peekHeight(box, n) + 'px');
        box.classList.add('is-peek');
        void box.offsetHeight; // start the transition from the collapsed height
        expand();
      } else {
        box.style.setProperty('--peek-h', box.scrollHeight + 'px');
        void box.offsetHeight;
        collapse();
      }
      paint();
    });

    window.addEventListener('resize', function () { if (!open) collapse(); });

    collapse();
    paint();
  }

  /** Sets up every [data-peek] box on the page. */
  function init() {
    document.querySelectorAll('[data-peek]').forEach(setup);
  }

  // Fonts change row heights: measure once they are ready.
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(init); else init();
})();

