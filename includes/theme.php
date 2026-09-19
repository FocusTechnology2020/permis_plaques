<?php
/**
 * includes/theme.php — à inclure dans le <head> de CHAQUE page,
 * à la place de la ligne qui charge Tailwind.
 *
 *   <?php include __DIR__ . '/includes/theme.php'; ?>
 *
 * Contient : Tailwind (darkMode par classe), la police, le script anti-flash
 * et une couche CSS qui repeint automatiquement en sombre les classes
 * Tailwind déjà utilisées dans le projet (bg-white, text-gray-800, etc.),
 * sans avoir à modifier toutes les vues.
 */
if (defined('PP_THEME_INCLUDED')) { return; }
define('PP_THEME_INCLUDED', true);
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Anti-flash : le thème est appliqué AVANT le premier rendu -->
<script>
(function () {
  try {
    var t = localStorage.getItem('pp.theme');
    if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    document.documentElement.classList.toggle('dark', t === 'dark');
  } catch (e) {}
})();
</script>

<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    darkMode: 'class',
    theme: {
      extend: {
        fontFamily: { sans: ['Archivo', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
        colors: {
          asphalte: { DEFAULT: '#14181d', clair: '#1d232c', bord: '#2a323d' },
          plaque:   '#f7f8f6',
          signal:   '#f2c230'
        }
      }
    }
  };
</script>

<style>
/*
 * Couche de compatibilité mode sombre.
 * Les sélecteurs utilisent html:where(.dark) : la spécificité reste faible,
 * donc toute classe `dark:` que vous écrirez plus tard reprend le dessus.
 */
html:where(.dark){ color-scheme: dark; }
html:where(.dark) body{ background-color:#14181d; color:#e7eaee; }

/* fonds */
html:where(.dark) .bg-white,
html:where(.dark) .bg-gray-50,
html:where(.dark) .bg-slate-50   { background-color:#1d232c; }
html:where(.dark) .bg-gray-100,
html:where(.dark) .bg-slate-100  { background-color:#232b35; }
html:where(.dark) .bg-gray-200,
html:where(.dark) .bg-slate-200  { background-color:#2a323d; }
html:where(.dark) .bg-gray-800,
html:where(.dark) .bg-gray-900,
html:where(.dark) .bg-slate-800,
html:where(.dark) .bg-slate-900  { background-color:#0f1319; }

/* textes */
html:where(.dark) .text-black,
html:where(.dark) .text-gray-900,
html:where(.dark) .text-slate-900 { color:#f2f5f8; }
html:where(.dark) .text-gray-800,
html:where(.dark) .text-gray-700,
html:where(.dark) .text-slate-800,
html:where(.dark) .text-slate-700 { color:#dde2e8; }
html:where(.dark) .text-gray-600,
html:where(.dark) .text-slate-600 { color:#b3bcc8; }
html:where(.dark) .text-gray-500,
html:where(.dark) .text-gray-400,
html:where(.dark) .text-slate-500 { color:#8a93a0; }

/* bordures et séparateurs */
html:where(.dark) .border,
html:where(.dark) .border-b,
html:where(.dark) .border-t,
html:where(.dark) .border-l,
html:where(.dark) .border-r,
html:where(.dark) .border-gray-100,
html:where(.dark) .border-gray-200,
html:where(.dark) .border-gray-300,
html:where(.dark) .border-slate-200 { border-color:#2a323d; }
html:where(.dark) .divide-gray-100 > * + *,
html:where(.dark) .divide-gray-200 > * + * { border-color:#2a323d; }

/* survols de lignes / de menu */
html:where(.dark) .hover\:bg-gray-50:hover,
html:where(.dark) .hover\:bg-gray-100:hover { background-color:#262f3a; }

/* tableaux */
html:where(.dark) thead,
html:where(.dark) thead .bg-gray-50 { background-color:#232b35; }
html:where(.dark) tbody tr { border-color:#2a323d; }

/* champs de formulaire */
html:where(.dark) input:not([type=checkbox]):not([type=radio]):not([type=submit]),
html:where(.dark) select,
html:where(.dark) textarea {
  background-color:#161b22; color:#e7eaee; border-color:#2f3946;
}
html:where(.dark) input::placeholder,
html:where(.dark) textarea::placeholder { color:#6f7886; }

/* ombres : invisibles sur fond sombre, on les remplace par un liseré */
html:where(.dark) .shadow,
html:where(.dark) .shadow-sm,
html:where(.dark) .shadow-md,
html:where(.dark) .shadow-lg {
  box-shadow: 0 1px 0 0 #2a323d, 0 10px 30px -18px rgba(0,0,0,.9);
}

/* pastilles de statut : on garde la couleur, on adoucit le fond */
html:where(.dark) .bg-green-100 { background-color:#123021; }
html:where(.dark) .text-green-800, html:where(.dark) .text-green-700 { color:#6ee7a8; }
html:where(.dark) .bg-red-100   { background-color:#3a1518; }
html:where(.dark) .text-red-800, html:where(.dark) .text-red-700   { color:#fca5a5; }
html:where(.dark) .bg-yellow-100{ background-color:#3a2f10; }
html:where(.dark) .text-yellow-800, html:where(.dark) .text-yellow-700 { color:#f6d47a; }
html:where(.dark) .bg-blue-100  { background-color:#132a3f; }
html:where(.dark) .text-blue-800, html:where(.dark) .text-blue-700  { color:#93c5fd; }

/* le focus doit rester visible dans les deux thèmes */
:focus-visible { outline:2px solid #f2c230; outline-offset:2px; }
</style>
