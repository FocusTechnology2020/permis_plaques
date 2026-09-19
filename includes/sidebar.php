<?php
$current = basename($_SERVER['SCRIPT_NAME']);
$navItem = function (string $href, string $label, string $icon, string $matchFile) use ($current) {
    $active = $current === $matchFile;
    $base = 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition';
    $cls = $active
        ? "$base bg-brand-600 text-white shadow-sm"
        : "$base text-slate-600 hover:bg-slate-100";
    echo '<a href="' . e($href) . '" class="' . $cls . '">' . $icon . '<span>' . e($label) . '</span></a>';
};
?>
<aside id="mobile-sidebar" class="hidden md:flex md:flex-col w-64 bg-white border-r border-slate-200 shrink-0">
  <div class="h-16 flex items-center gap-2 px-5 border-b border-slate-200">
    <div class="h-8 w-8 rounded-lg bg-brand-600 flex items-center justify-center text-white font-bold">P</div>
    <div class="leading-tight">
      <p class="font-semibold text-slate-800 text-sm">Permis & Plaques</p>
      <p class="text-xs text-slate-400">Contrôle routier</p>
    </div>
  </div>

  <nav class="flex-1 overflow-y-auto p-3 space-y-1">
    <?php if (is_admin()): ?>
      <p class="px-3 pt-2 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Administration</p>
      <?php
      $navItem(ROOT_URL . 'admin/dashboard.php', 'Tableau de bord', icon_svg('grid'), 'dashboard.php');
      $navItem(ROOT_URL . 'admin/drivers.php', 'Conducteurs', icon_svg('user'), 'drivers.php');
      $navItem(ROOT_URL . 'admin/vehicles.php', 'Plaques / Véhicules', icon_svg('car'), 'vehicles.php');
      $navItem(ROOT_URL . 'admin/permits.php', 'Permis', icon_svg('id'), 'permits.php');
      $navItem(ROOT_URL . 'admin/infractions.php', 'Infractions', icon_svg('alert'), 'infractions.php');
      $navItem(ROOT_URL . 'admin/agents.php', 'Agents & rôles', icon_svg('shield'), 'agents.php');
      ?>
      <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Espace agent</p>
      <?php $navItem(ROOT_URL . 'agent/search.php', 'Recherche contrôle', icon_svg('search'), 'search.php'); ?>
    <?php else: ?>
      <p class="px-3 pt-2 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Agent de contrôle</p>
      <?php
      $navItem(ROOT_URL . 'agent/search.php', 'Recherche', icon_svg('search'), 'search.php');
      $navItem(ROOT_URL . 'agent/history.php', 'Historique infractions', icon_svg('alert'), 'history.php');
      ?>
    <?php endif; ?>
  </nav>

  <div class="p-3 border-t border-slate-200 text-xs text-slate-400">
    Prototype — données en session PHP
  </div>
</aside>
