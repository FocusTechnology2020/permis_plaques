<?php
require_once __DIR__ . '/../config.php';
require_agent();

$query = trim($_GET['q'] ?? '');
$result = $query !== '' ? search_driver($query) : ['driver' => null, 'permit' => null, 'vehicle' => null];
$searched = $query !== '';

$pageTitle = 'Recherche de contrôle';
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-3xl">
  <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
    <h2 class="font-semibold text-slate-800 mb-1">Recherche par plaque ou numéro de permis</h2>
    <p class="text-sm text-slate-500 mb-4">Saisissez une plaque d'immatriculation (ex. B 1234 A) ou un numéro de permis (ex. BDI-2021-00147).</p>
    <form method="get" class="flex gap-2">
      <input type="text" name="q" value="<?= e($query) ?>" autofocus
             placeholder="Plaque ou numéro de permis…"
             class="flex-1 rounded-lg border border-slate-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
      <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-medium rounded-lg px-5 py-3 text-sm transition flex items-center gap-2">
        <?= icon_svg('search') ?> Rechercher
      </button>
    </form>
  </div>

  <?php if ($searched): ?>
    <?php if (!$result['driver']): ?>
      <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl p-6 text-sm">
        Aucun conducteur, véhicule ou permis ne correspond à « <?= e($query) ?> ».
      </div>
    <?php else:
      $driver  = $result['driver'];
      $permit  = $result['permit'];
      $vehicle = $result['vehicle'];
      $status  = $permit ? permit_status($permit) : null;
      $infractions = infractions_for_driver((int)$driver['id']);
    ?>
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-6">
      <div class="p-6 border-b border-slate-100 flex items-start justify-between flex-wrap gap-4">
        <div>
          <p class="text-xs uppercase tracking-wide text-slate-400 mb-1">Conducteur</p>
          <h3 class="text-xl font-bold text-slate-800"><?= e($driver['prenom'] . ' ' . $driver['nom']) ?></h3>
          <p class="text-sm text-slate-500 mt-1">
            Né(e) le <?= e($driver['date_naissance'] ?? '—') ?> · <?= e($driver['telephone'] ?? '—') ?>
          </p>
          <p class="text-sm text-slate-500"><?= e($driver['adresse'] ?? '') ?></p>
        </div>
        <?php if ($status): ?>
          <div class="text-right">
            <p class="text-xs uppercase tracking-wide text-slate-400 mb-1">État du permis</p>
            <?= permit_status_badge($status) ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
        <div class="p-6">
          <p class="text-xs uppercase tracking-wide text-slate-400 mb-2">Permis de conduire</p>
          <?php if ($permit): ?>
            <p class="font-mono font-semibold text-slate-700"><?= e($permit['numero']) ?></p>
            <p class="text-sm text-slate-500 mt-1">Catégorie <?= e($permit['categorie'] ?? '—') ?></p>
            <p class="text-sm text-slate-500">Délivré le <?= e($permit['date_delivrance'] ?? '—') ?></p>
            <p class="text-sm text-slate-500">Expire le <?= e($permit['date_expiration']) ?></p>
          <?php else: ?>
            <p class="text-sm text-red-600">Aucun permis enregistré pour ce conducteur.</p>
          <?php endif; ?>
        </div>
        <div class="p-6">
          <p class="text-xs uppercase tracking-wide text-slate-400 mb-2">Véhicule</p>
          <?php if ($vehicle): ?>
            <p class="font-mono font-semibold text-slate-700"><?= e($vehicle['plaque']) ?></p>
            <p class="text-sm text-slate-500 mt-1"><?= e(trim(($vehicle['marque'] ?? '') . ' ' . ($vehicle['modele'] ?? ''))) ?></p>
            <p class="text-sm text-slate-500"><?= e($vehicle['couleur'] ?? '') ?> <?= !empty($vehicle['annee']) ? '· ' . e((string)$vehicle['annee']) : '' ?></p>
          <?php else: ?>
            <p class="text-sm text-slate-400">Aucun véhicule enregistré pour ce conducteur.</p>
          <?php endif; ?>
        </div>
      </div>

      <div class="p-6 bg-slate-50 flex flex-wrap gap-3">
        <a href="<?= e(ROOT_URL) ?>agent/infraction_new.php?driver_id=<?= (int)$driver['id'] ?><?= $vehicle ? '&vehicle_id=' . (int)$vehicle['id'] : '' ?>"
           class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg px-4 py-2.5 transition">
          Enregistrer une infraction
        </a>
        <a href="<?= e(ROOT_URL) ?>agent/history.php?driver_id=<?= (int)$driver['id'] ?>"
           class="bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 text-sm font-medium rounded-lg px-4 py-2.5 transition">
          Voir l'historique (<?= count($infractions) ?>)
        </a>
      </div>
    </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
