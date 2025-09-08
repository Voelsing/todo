<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('admin_session');
    session_start();
}

$rollen = $_SESSION['rollen_namen'] ?? [];
$adminRoles = ['Administrator', 'Geschäftsstellenmitarbeiter', 'Präsidium', 'Superadmin'];
$isAdmin = (bool)array_intersect($adminRoles, $rollen);
$canReview = (bool)array_intersect(array_merge($adminRoles, ['Fachwarte']), $rollen);
?>

<style>
    .dark-mode .navbar {
      background-color: #1f1f1f;
      border-color: #333;
    }

    .dark-mode {
      background-color: #121212;
      color: #eaeaea;
    }

    #navMenuLinks .dropdown-menu {
      position: absolute;
      right: 0;
      left: auto;
      top: calc(100% + 0.5rem); /* sit slightly below the navbar */
      display: none;
      margin-top: 0.25rem;
      padding: 0.5rem;
      background-color: #f8f9fa;
      border-color: #f8f9fa;
      font-size: 1rem;
      width: auto;
      min-width: 16rem;
      opacity: 0;
      transform: translateY(8px);
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      transition: opacity 0.2s ease, transform 0.2s ease;
    }

    #navMenuLinks .dropdown-menu.multi-column {
      column-count: 2;
      min-width: 20rem;
    }

    #navMenuLinks .dropdown-menu.multi-column .dropdown-item {
      break-inside: avoid;
    }

    #navMenuLinks .dropdown-menu.show {
      display: block;
      opacity: 1;
      transform: translateY(0);
    }

    #navMenuLinks .dropdown-menu .dropdown-item {
      color: #1f1f1f;
      padding: 0.5rem 0.75rem;
      border-bottom: 1px solid #dee2e6;
    }

    #navMenuLinks .dropdown-menu .dropdown-item:hover {
      background-color: #0a58ca;
      color: #fff;
      border-radius: 0.375rem;
    }

    .dark-mode .nav-btn {
      background-color: #0d6efd;
      color: #fff;
    }

    .dark-mode .nav-btn:hover {
      background-color: #0a58ca;
    }

    .dark-mode .dropdown-item:hover {
      background-color: #0a58ca;
      border-radius: 0.375rem;
    }

    .theme-toggle {
      cursor: pointer;
      font-size: 1.2rem;
      margin-left: 1rem;
    }

    /* Dark mode dropdown overrides */
    .dark-mode #navMenuLinks .dropdown-menu {
      background-color: #0d6efd;
      border-color: #0d6efd;
    }

    .dark-mode #navMenuLinks .dropdown-menu .dropdown-item {
      color: #fff;
      border-color: #555;
    }

    /* Burger-Button Grundstil */
    #mobileMenuToggle {
      position: static;
      top: 1rem;
      left: .5rem;
      z-index: 1100;
      height: 44px;
      min-width: 44px;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 0 .75rem;
      font-size: 1.1rem;
    }

    /* Drawer-Layout aktivieren, wenn Klasse auf Body gesetzt */
    body.drawer-nav {
      padding-top: 3.25rem;
    }

    body.drawer-nav #mobileMenuToggle {
      display: inline-flex;
      background-color: #0d6efd;
      color: #fff;
      border: none;
    }

    body.drawer-nav #mobileMenuToggle:hover {
      background-color: #0a58ca;
    }

    body.drawer-nav #navMenuLinks {
      position: fixed !important;
      top: 0;
      left: 0;
      height: 100dvh;
      width: min(80vw, 360px);
      background: #ffffff;
      color: #1f1f1f;
      padding: 1rem;
      transform: translateX(-100%);
      transition: transform .3s ease;
      box-shadow: 2px 0 16px rgba(0,0,0,.25);
      z-index: 1000;
      overflow-y: auto;
      pointer-events: none;
      flex-direction: column;
      align-items: stretch;
      gap: 0.25rem;
    }

    body.dark-mode.drawer-nav #navMenuLinks {
      background: #1f1f1f;
      color: #eaeaea;
    }

    body.drawer-nav #navMenuLinks.is-open {
      transform: translateX(0);
      pointer-events: auto;
      display: flex;
    }

    body.drawer-nav #navMenuLinks .dropdown {
      width: 100%;
    }

    body.drawer-nav #navMenuLinks .nav-btn {
      width: 100%;
      text-align: left;
      padding: .5rem .75rem;
      font-size: 1rem;
    }

    body.drawer-nav #navMenuLinks .dropdown-menu {
      position: static !important;
      width: 100%;
      margin: .25rem 0 0;
      padding: .25rem 0;
      background: inherit;
      border: none;
      box-shadow: none;
      display: none;
    }

    body.drawer-nav #navMenuLinks .dropdown-menu.multi-column {
      column-count: 1;
    }

    body.drawer-nav #navMenuLinks .dropdown-menu.show {
      display: block;
    }

    body.drawer-nav #navMenuLinks .dropdown-toggle::after {
      display: none;
    }

    :root { --nav-offset-mobile: 3rem; }

    body.drawer-nav .navbar .container-fluid > .d-flex.align-items-center:first-child {
      margin-left: var(--nav-offset-mobile);
    }


  </style>

  <div class="d-flex align-items-center">
        <button id="mobileMenuToggle"
                class="btn nav-btn btn-sm"
                aria-expanded="false"
                aria-controls="navMenuLinks"
                aria-label="Menü öffnen">
                <i class="bi bi-list" style="font-size: 1.5rem;"></i>
        </button>

        <div id="navMenuLinks" class="d-flex align-items-center ms-auto position-relative">
        <a href="index.php" class="btn nav-btn btn-sm me-2" id="dashboardBtn"><i class="lucide lucide-gauge me-1"></i>Startseite</a>
        <a href="kalender.php" class="btn nav-btn btn-sm me-2" id="kalenderBtn"><i class="lucide lucide-calendar me-1"></i>Kalender</a>

        <div class="dropdown me-2">
            <a href="#" role="button" class="btn nav-btn btn-sm dropdown-toggle" id="datenverwaltungBtn">
              <i class="lucide lucide-folder-open me-1"></i>Datenverwaltung
            </a>
            <div id="datenverwaltungSubNav" class="dropdown-menu dropdown-menu-end">
              <?php if ($isAdmin): ?>
              <a href="artikel_auswertung.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-bar-chart-line me-1"></i>Artikelauswertung</a>
              <a href="artikel_verwalten.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-box-seam me-1"></i>Artikelverwaltung</a>
              <a href="ausbildung_fortbildung.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-mortarboard me-1"></i>Aus- &amp; Fortbildung</a>
              <a href="buchungen_verwalten.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-calendar-check me-1"></i>Buchungsverwaltung</a>
              <a href="kunden_verwalten.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-people me-1"></i>Kundenverwaltung</a>
              <a href="lehrgang_create.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-calendar-plus me-1"></i>Lehrgangsverwaltungen</a>
              <a href="lizenzverwaltung.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-card-checklist me-1"></i>Lizenzverwaltung</a>
              <a href="preisliste.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-tags me-1"></i>Preisliste</a>
              <a href="sportarten_fachbereiche.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-flag me-1"></i>Sportarten &amp; Fachbereiche</a>
              <?php endif; ?>
              <?php if ($isAdmin): ?>
              <a href="Verein/admin.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-gear me-1"></i>Vereinsdatenbank Admin</a>
              <?php endif; ?>
              <a href="Verein/index.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-house me-1"></i>Vereinsdaten Übersicht</a>
            </div>
          </div>

          <div class="dropdown me-2">
            <a href="#" role="button" class="btn nav-btn btn-sm dropdown-toggle" id="finanzenBtn">
              <i class="lucide lucide-banknote me-1"></i>Finanzen
            </a>
            <div id="finanzenSubNav"       class="dropdown-menu dropdown-menu-end">
              <a href="auslagenerstattung.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-receipt-cutoff me-1"></i>Auslagenerstattung</a>
              <?php if ($canReview): ?>
              <a href="auslagen_verwalten.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-list-task me-1"></i>Auslagenverwaltung</a>
              <?php endif; ?>
              <?php if ($isAdmin): ?>
              <a href="lehrgang_abrechnung.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-cash-coin me-1"></i>Lehrgänge abrechnen</a>
              <a href="rechnungen.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-file-earmark-text me-1"></i>AN - Rechnungen - GS</a>
              <?php endif; ?>
            </div>
          </div>

          <!-- Mardorf Bereich bleibt erhalten -->
          <?php if ($isAdmin): ?>
            <div class="dropdown me-2">
              <a href="#" role="button" class="btn nav-btn btn-sm dropdown-toggle" id="mardorfBtn">
                <i class="lucide lucide-folders me-1"></i>Mardorf
              </a>

                <div id="mardorfSubNav"        class="dropdown-menu dropdown-menu-end">
                  <a href="platz_verwalten.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-geo me-1"></i>Platzverwaltung</a>
                  <a href="platzgruppen_verwalten.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-diagram-3 me-1"></i>Platzgruppen</a>
                  <a href="buchungen.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-house-door me-1"></i>Buchungen in Mardorf</a>
                  <a href="buchungen_verwalten.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-calendar-check me-1"></i>Buchungsverwaltung</a>
                  <a href="dauercamping.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-calendar2-week me-1"></i>Dauercamping</a>
                  <a href="schuppen.php" class="dropdown-item d-flex align-items-center" style="break-after: column;"><i class="bi bi-door-closed me-1"></i>Schuppen</a>
                  <a href="stegbelegung2.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-people-fill me-1"></i>Stegbelegung</a>
                  <a href="stromzaehler_mardorf.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-lightning-fill me-1"></i>Stromzähler Dauercamper</a>
                  <a href="stromzaehler_parent_mardorf.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-lightning-fill me-1"></i>Übergeordnete Stromzähler</a>
                  <a href="vertraege.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-file-earmark-text me-1"></i>Verträge</a>
                  <a href="vorgelaende.php" class="dropdown-item d-flex align-items-center"><i class="bi bi-tree me-1"></i>Vorgelände</a>
              </div>
          </div>
          <?php endif; ?>
          <?php if ($isAdmin): ?>
<div class="dropdown me-2">
  <a href="#" role="button" class="btn nav-btn btn-sm dropdown-toggle" id="teamstarUserBtn">
    <i class="lucide lucide-users me-1"></i>Verwaltung TeamStar
  </a>
  <div id="teamstarUserSubNav" class="dropdown-menu dropdown-menu-end">
    <a href="benutzer_verwalten.php" class="dropdown-item d-flex align-items-center">
      <i class="bi bi-person-plus me-1"></i>Benutzerverwaltung
    </a>
    <a href="rollen_verwalten.php" class="dropdown-item d-flex align-items-center">
      <i class="bi bi-person-gear me-1"></i>Rollenverwaltung
    </a>
  </div>
</div>
<?php endif; ?>

          <a href="logout.php" class="btn nav-btn nav-btn-icon btn-sm me-2"><i class="bi bi-box-arrow-right ms-1"></i></a>


<!-- Drawer + Dropdown Logik -->
<script>
(function () {
    const drawer   = document.getElementById('navMenuLinks');
    const burger   = document.getElementById('mobileMenuToggle');

    function openDrawer() {
      drawer.classList.add('is-open');
      burger.setAttribute('aria-expanded', 'true');
      document.body.style.overflow = 'hidden';
      drawer.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));

      burger.style.display = 'none';
    }

    function closeDrawer() {
      drawer.classList.remove('is-open');
      burger.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = '';
      drawer.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));

      burger.style.display = '';
    }

    function toggleDrawer() {
      drawer.classList.contains('is-open') ? closeDrawer() : openDrawer();
    }

    burger?.addEventListener('click', (e) => {
      e.preventDefault();
      toggleDrawer();
    });

    document.addEventListener('click', (e) => {
      if (drawer.classList.contains('is-open') && !drawer.contains(e.target) && !burger.contains(e.target)) {
        closeDrawer();
      }

      if (!e.target.closest('#navMenuLinks .dropdown')) {
        drawer.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        if (drawer.classList.contains('is-open')) closeDrawer();
        drawer.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
      }
    });

    drawer.querySelectorAll('.dropdown > .nav-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const menu = btn.parentElement.querySelector('.dropdown-menu');
        const isShown = menu.classList.contains('show');
        drawer.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
        if (!isShown) menu.classList.add('show');
      });
    });

    function checkOverflow() {
      
      const tooSmall = window.innerWidth < 768;

      if (tooSmall) {
        document.body.classList.add('drawer-nav');
      } else {
        document.body.classList.remove('drawer-nav');
        closeDrawer();
      }
    }

    let lastWidth = window.innerWidth;
    window.addEventListener('resize', () => {
      const currentWidth = window.innerWidth;
      if (currentWidth === lastWidth) return;
      lastWidth = currentWidth;
      checkOverflow();
    });
    window.addEventListener('load', () => {
      lastWidth = window.innerWidth;
      checkOverflow();
    });
  })();
</script>


        </div>
        <span class="theme-toggle" onclick="toggleDarkMode()" title="Dark Mode umschalten">
          <i class="lucide lucide-moon"></i>
        </span>
      </div>