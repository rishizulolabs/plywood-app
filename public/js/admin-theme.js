(function () {
  var sidebar = document.getElementById('sidebar');
  var mainWithSidebar = document.getElementById('main-with-sidebar');
  var overlay = document.getElementById('sidebar-overlay');
  var toggleBtn = document.getElementById('sidebar-toggle-inline');
  if (!sidebar || !mainWithSidebar) return;

  function toggleSidebar() {
    var isMobile = window.matchMedia('(max-width: 1023px)').matches;

    if (isMobile) {
      sidebar.classList.toggle('is-open');
      var isOpen = sidebar.classList.contains('is-open');
      if (overlay) overlay.classList.toggle('is-visible', isOpen);
      document.body.classList.toggle('sidebar-open', isOpen);
      if (toggleBtn) toggleBtn.setAttribute('aria-expanded', String(isOpen));
    } else {
      sidebar.classList.toggle('is-collapsed');
      var collapsed = sidebar.classList.contains('is-collapsed');
      mainWithSidebar.classList.toggle('sidebar-collapsed', collapsed);
      if (toggleBtn) toggleBtn.setAttribute('aria-expanded', String(!collapsed));
    }
  }

  function closeSidebarOnMobile() {
    if (window.matchMedia('(max-width: 1023px)').matches) {
      sidebar.classList.remove('is-open');
      if (overlay) overlay.classList.remove('is-visible');
      document.body.classList.remove('sidebar-open');
      if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'false');
    }
  }

  if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
  if (overlay) overlay.addEventListener('click', closeSidebarOnMobile);

  window.addEventListener('resize', function () {
    if (window.matchMedia('(min-width: 1024px)').matches) {
      sidebar.classList.remove('is-open');
      if (overlay) overlay.classList.remove('is-visible');
      document.body.classList.remove('sidebar-open');
      if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
    } else {
      sidebar.classList.remove('is-collapsed');
      mainWithSidebar.classList.remove('sidebar-collapsed');
    }
  });
})();

(function () {
  document.querySelectorAll('[data-offcanvas-open]').forEach(function (btn) {
    var targetId = btn.getAttribute('data-offcanvas-open');
    var backdrop = document.getElementById('offcanvas-backdrop');
    var offcanvas = document.getElementById(targetId);
    if (!backdrop || !offcanvas) return;

    function openOffcanvas() {
      backdrop.classList.add('is-visible');
      offcanvas.classList.add('is-open');
      backdrop.setAttribute('aria-hidden', 'false');
      offcanvas.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }

    function closeOffcanvas() {
      backdrop.classList.remove('is-visible');
      offcanvas.classList.remove('is-open');
      backdrop.setAttribute('aria-hidden', 'true');
      offcanvas.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    }

    btn.addEventListener('click', openOffcanvas);
    backdrop.addEventListener('click', closeOffcanvas);
    offcanvas.querySelectorAll('[data-offcanvas-close]').forEach(function (closeBtn) {
      closeBtn.addEventListener('click', closeOffcanvas);
    });
  });
})();

(function () {
  function dismissAlert(alert) {
    if (!alert || alert.dataset.dismissing === 'true') return;
    alert.dataset.dismissing = 'true';
    alert.classList.add('is-dismissing');

    window.setTimeout(function () {
      alert.style.display = 'none';

      var stack = alert.closest('.alerts-stack');
      if (stack) {
        var hasVisibleAlert = Array.from(stack.querySelectorAll('.alert')).some(function (el) {
          return el.style.display !== 'none';
        });
        if (!hasVisibleAlert) stack.style.display = 'none';
      }
    }, 200);
  }

  document.querySelectorAll('.alert-dismiss').forEach(function (btn) {
    btn.addEventListener('click', function () {
      dismissAlert(btn.closest('.alert'));
    });
  });

  document.querySelectorAll('.alert-success').forEach(function (alert) {
    window.setTimeout(function () {
      dismissAlert(alert);
    }, 3000);
  });
})();
