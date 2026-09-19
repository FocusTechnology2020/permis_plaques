<?php
/**
 * includes/theme_toggle.php — bouton clair / sombre.
 * À inclure dans includes/header.php (barre du haut) :
 *
 *   <?php include __DIR__ . '/theme_toggle.php'; ?>
 */
?>
<button type="button" data-theme-toggle
        class="inline-flex items-center justify-center rounded-lg border border-gray-200 p-2 text-gray-600 hover:bg-gray-50"
        aria-label="Basculer entre le thème clair et le thème sombre"
        title="Thème clair / sombre">
  <!-- lune (visible en clair) -->
  <svg class="h-5 w-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor"
       stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
  </svg>
  <!-- soleil (visible en sombre) -->
  <svg class="hidden h-5 w-5 dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor"
       stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <circle cx="12" cy="12" r="4"/>
    <path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M19.07 4.93l-1.41 1.41M6.34 17.66l-1.41 1.41"/>
  </svg>
</button>

<script>
(function () {
  if (window.__ppThemeToggleReady) return;
  window.__ppThemeToggleReady = true;

  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-theme-toggle]');
    if (!b) return;
    var sombre = document.documentElement.classList.toggle('dark');
    try { localStorage.setItem('pp.theme', sombre ? 'dark' : 'light'); } catch (err) {}
  });

  // Suit le réglage du système tant que l'utilisateur n'a pas choisi lui-même.
  try {
    if (!localStorage.getItem('pp.theme')) {
      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (ev) {
        document.documentElement.classList.toggle('dark', ev.matches);
      });
    }
  } catch (err) {}
})();
</script>
