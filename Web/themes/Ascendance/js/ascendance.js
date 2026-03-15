/**
 * Ascendance Theme JavaScript
 * /themes/Ascendance/js/ascendance.js
 */

// ── PARTICLE SYSTEM (Hero only) ──
(function () {
  var container = document.getElementById('asc-particles');
  if (!container) return;
  for (var i = 0; i < 18; i++) {
    var p = document.createElement('div');
    p.className = 'asc-particle';
    var size = Math.random() * 3.5 + 1;
    p.style.cssText = [
      'left:' + (Math.random() * 100) + '%',
      'width:' + size + 'px',
      'height:' + size + 'px',
      'animation-duration:' + (Math.random() * 10 + 9) + 's',
      'animation-delay:' + (Math.random() * 12) + 's'
    ].join(';');
    container.appendChild(p);
  }
})();

// ── MODAL ──
function ascOpenModal(id) {
  var el = document.getElementById('asc-modal-' + id);
  if (el) { el.classList.add('asc-open'); document.body.style.overflow = 'hidden'; }
}
function ascCloseModal(id) {
  var el = document.getElementById('asc-modal-' + id);
  if (el) { el.classList.remove('asc-open'); document.body.style.overflow = ''; }
}
function ascCloseModalBg(e, id) {
  if (e.target === e.currentTarget) ascCloseModal(id);
}

// ── MOBILE NAV ──
function ascToggleMobileNav() {
  var nav = document.getElementById('asc-mobile-nav');
  if (nav) nav.classList.toggle('asc-open');
}

// ── ESC KEY ──
document.addEventListener('keydown', function (e) {
  if (e.key !== 'Escape') return;
  document.querySelectorAll('.asc-modal-overlay.asc-open').forEach(function (m) {
    m.classList.remove('asc-open');
    document.body.style.overflow = '';
  });
  var nav = document.getElementById('asc-mobile-nav');
  if (nav && nav.classList.contains('asc-open')) nav.classList.remove('asc-open');
});

// ── STICKY HEADER SHADOW ──
window.addEventListener('scroll', function () {
  var h = document.getElementById('asc-header');
  if (h) h.style.boxShadow = window.scrollY > 10 ? '0 4px 30px rgba(0,0,0,0.5)' : 'none';
}, { passive: true });

// ── BOOTSTRAP ACCORDION (user_panel.php compatibility) ──
document.addEventListener('click', function (e) {
  var toggle = e.target.closest('.accordion-toggle');
  if (!toggle) return;
  e.preventDefault();
  var targetId = toggle.getAttribute('href');
  if (!targetId) return;
  var target = document.querySelector(targetId);
  if (!target) return;
  var isOpen = target.classList.contains('in');
  // close all in same parent
  var parent = toggle.closest('#accordion');
  if (parent) {
    parent.querySelectorAll('.panel-collapse.in').forEach(function (el) { el.classList.remove('in'); });
  }
  if (!isOpen) target.classList.add('in');
});
