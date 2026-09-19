<?php
require_once __DIR__ . '/../config.php';
require_admin();

$drivers   = db()['drivers'];
$vehicles  = db()['vehicles'];
$permits   = db()['permits'];
$infractions = db()['infractions'];
$users     = db()['users'];

$permitCounts = ['valide' => 0, 'expiré' => 0, 'suspendu' => 0];
foreach ($permits as $p) {
    $status = permit_status($p);
    $permitCounts[$status] = ($permitCounts[$status] ?? 0) + 1;
}

$thisMonth = date('Y-m');
$infractionsThisMonth = array_filter($infractions, fn($i) => str_starts_with($i['date'], $thisMonth));
$agentsActifs = array_filter($users, fn($u) => ($u['statut'] ?? 'actif') === 'actif');

$sanctions = db()['sanctions'];
$sanctionById = [];
foreach ($sanctions as $s) { $sanctionById[$s['id']] = $s; }

// Répartition des infractions par type (pour le mini graphique)
$byType = [];
foreach ($infractions as $i) {
    $byType[$i['type']] = ($byType[$i['type']] ?? 0) + 1;
}
arsort($byType);
$maxType = $byType ? max($byType) : 1;

// Dernières infractions (5 plus récentes)
$recent = $infractions;
usort($recent, fn($a, $b) => strcmp($b['date'], $a['date']));
$recent = array_slice($recent, 0, 5);

$pageTitle = 'Tableau de bord';
include __DIR__ . '/../includes/header.php';
?>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="bg-white rounded-2xl border border-slate-200 p-5">
    <p class="text-sm text-slate-500">Conducteurs enregistrés</p>
    <p class="text-3xl font-bold text-slate-800 mt-1"><?= count($drivers) ?></p>
  </div>
  <div class="bg-white rounded-2xl border border-slate-200 p-5">
    <p class="text-sm text-slate-500">Véhicules / plaques</p>
    <p class="text-3xl font-bold text-slate-800 mt-1"><?= count($vehicles) ?></p>
  </div>
  <div class="bg-white rounded-2xl border border-slate-200 p-5">
    <p class="text-sm text-slate-500">Infractions ce mois-ci</p>
    <p class="text-3xl font-bold text-slate-800 mt-1"><?= count($infractionsThisMonth) ?></p>
  </div>
  <div class="bg-white rounded-2xl border border-slate-200 p-5">
    <p class="text-sm text-slate-500">Agents / admins actifs</p>
    <p class="text-3xl font-bold text-slate-800 mt-1"><?= count($agentsActifs) ?></p>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
  <div class="bg-white rounded-2xl border border-slate-200 p-5 lg:col-span-1">
    <h2 class="font-semibold text-slate-800 mb-4">État des permis</h2>
    <div class="space-y-3">
      <?php
      $total = max(1, array_sum($permitCounts));
      $rows = [
        ['label' => 'Valides',   'key' => 'valide',   'color' => 'bg-emerald-500'],
        ['label' => 'Expirés',   'key' => 'expiré',   'color' => 'bg-red-500'],
        ['label' => 'Suspendus', 'key' => 'suspendu', 'color' => 'bg-amber-500'],
      ];
      foreach ($rows as $r):
        $count = $permitCounts[$r['key']] ?? 0;
        $pct = round(($count / $total) * 100);
      ?>
      <div>
        <div class="flex justify-between text-sm mb-1">
          <span class="text-slate-600"><?= e($r['label']) ?></span>
          <span class="font-medium text-slate-700"><?= $count ?></span>
        </div>
        <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
          <div class="h-full <?= $r['color'] ?>" style="width: <?= $pct ?>%"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-slate-200 p-5 lg:col-span-2">
    <h2 class="font-semibold text-slate-800 mb-4">Infractions par type</h2>
    <?php if (!$byType): ?>
      <p class="text-sm text-slate-400">Aucune infraction enregistrée pour le moment.</p>
    <?php else: ?>
      <div class="space-y-3">
        <?php foreach ($byType as $type => $count): $pct = round(($count / $maxType) * 100); ?>
        <div>
          <div class="flex justify-between text-sm mb-1">
            <span class="text-slate-600"><?= e($type) ?></span>
            <span class="font-medium text-slate-700"><?= $count ?></span>
          </div>
          <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
            <div class="h-full bg-brand-600" style="width: <?= $pct ?>%"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="bg-white rounded-2xl border border-slate-200 p-5">
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-semibold text-slate-800">Infractions récentes</h2>
    <a href="<?= e(ROOT_URL) ?>admin/infractions.php" class="text-sm text-brand-600 hover:underline">Voir tout l'historique →</a>
  </div>
  <?php if (!$recent): ?>
    <p class="text-sm text-slate-400">Aucune infraction enregistrée pour le moment.</p>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-slate-400 border-b border-slate-100">
          <th class="py-2 pr-4 font-medium">Date</th>
          <th class="py-2 pr-4 font-medium">Conducteur</th>
          <th class="py-2 pr-4 font-medium">Type</th>
          <th class="py-2 pr-4 font-medium">Sanction</th>
          <th class="py-2 pr-4 font-medium">Statut</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recent as $inf): $d = find_by_id('drivers', (int)$inf['driver_id']); ?>
        <tr class="border-b border-slate-50">
          <td class="py-2.5 pr-4 text-slate-500"><?= e($inf['date']) ?></td>
          <td class="py-2.5 pr-4 text-slate-700"><?= $d ? e($d['prenom'] . ' ' . $d['nom']) : '—' ?></td>
          <td class="py-2.5 pr-4 text-slate-600"><?= e($inf['type']) ?></td>
          <td class="py-2.5 pr-4 text-slate-600"><?= isset($sanctionById[$inf['sanction_id']]) ? e($sanctionById[$inf['sanction_id']]['libelle']) : '—' ?></td>
          <td class="py-2.5 pr-4">
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset <?= $inf['statut'] === 'traitée' ? 'bg-emerald-100 text-emerald-700 ring-emerald-600/20' : 'bg-amber-100 text-amber-800 ring-amber-600/20' ?>">
              <?= e(ucfirst($inf['statut'])) ?>
            </span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
