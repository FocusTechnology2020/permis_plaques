<?php
/**
 * includes/header.php
 * Attend éventuellement une variable $pageTitle définie par la page appelante.
 */
$pageTitle = $pageTitle ?? APP_NAME;
$user = current_user();
$flash = get_flash();
include __DIR__ . '/theme.php'; 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> — <?= e(APP_NAME) ?></title>
<!-- <script src="https://cdn.tailwindcss.com"></script> -->
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          brand: {
            50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',
            500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a'
          }
        }
      }
    }
  }
</script>
<style>
  [x-cloak] { display: none !important; }
  body { font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
</style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">

<?php if ($user): ?>
<div class="min-h-screen flex">
  <?php include __DIR__ . '/sidebar.php'; ?>

  <div class="flex-1 flex flex-col min-w-0">
    <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-6 sticky top-0 z-20">
      <div class="flex items-center gap-3">
        <button onclick="document.getElementById('mobile-sidebar').classList.toggle('hidden')" class="md:hidden text-slate-500">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
        </button>
        <h1 class="text-lg font-semibold text-slate-800"><?= e($pageTitle) ?></h1>
      </div>
      <div class="flex items-center gap-3">
        <span class="hidden sm:flex items-center gap-2 text-sm text-slate-500">
          <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset <?= $user['role'] === 'admin' ? 'bg-brand-100 text-brand-700 ring-brand-600/20' : 'bg-slate-100 text-slate-600 ring-slate-500/20' ?>">
            <?= $user['role'] === 'admin' ? 'Administrateur' : 'Agent de contrôle' ?>
          </span>
          <span class="font-medium text-slate-700"><?= e($user['nom']) ?></span>
        </span>
        <a href="<?= e(ROOT_URL) ?>logout.php" class="text-sm text-slate-500 hover:text-red-600 transition">Déconnexion</a>
        <?php include __DIR__ . '/theme_toggle.php'; ?>
      </div>
    </header>

    <main class="flex-1 p-4 md:p-6">
      <?php if ($flash): ?>
        <div class="mb-4 rounded-lg px-4 py-3 text-sm border <?= $flash['type'] === 'error' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' ?>">
          <?= e($flash['message']) ?>
        </div>
      <?php endif; ?>
<?php else: ?>
<?php if ($flash): ?>
  <div class="max-w-md mx-auto mt-6 rounded-lg px-4 py-3 text-sm border <?= $flash['type'] === 'error' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' ?>">
    <?= e($flash['message']) ?>
  </div>
<?php endif; ?>
<?php endif; ?>
