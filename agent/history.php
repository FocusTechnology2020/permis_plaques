<?php
require_once __DIR__ . '/../config.php';
require_agent();

$drivers  = db()['drivers'];
$vehicles = db()['vehicles'];
$sanctions = db()['sanctions'];
$users = db()['users'];

$driverName = function (?int $id) use ($drivers) {
    foreach ($drivers as $d) if ((int)$d['id'] === $id) return $d['prenom'] . ' ' . $d['nom'];
    return '—';
};
$vehiclePlate = function (?int $id) use ($vehicles) {
    foreach ($vehicles as $v) if ((int)$v['id'] === $id) return $v['plaque'];
    return '—';
};
$sanctionLabel = function (?int $id) use ($sanctions) {
    foreach ($sanctions as $s) if ((int)$s['id'] === $id) return $s['libelle'];
    return '—';
};
$agentName = function (?int $id) use ($users) {
    foreach ($users as $u) if ((int)$u['id'] === $id) return $u['nom'];
    return '—';
};

$driverIdFilter = isset($_GET['driver_id']) ? (int)$_GET['driver_id'] : null;
$search = trim($_GET['q'] ?? '');

$infractions = db()['infractions'];
if ($driverIdFilter) {
    $infractions = array_values(array_filter($infractions, fn($i) => (int)$i['driver_id'] === $driverIdFilter));
} elseif ($search !== '') {
    $needle = strtolower($search);
    $infractions = array_filter($infractions, function ($i) use ($needle, $driverName, $vehiclePlate) {
        $haystack = strtolower($driverName($i['driver_id']) . ' ' . $vehiclePlate($i['vehicle_id']) . ' ' . $i['type']);
        return str_contains($haystack, $needle);
    });
}
usort($infractions, fn($a, $b) => strcmp($b['date'], $a['date']));

$filteredDriver = $driverIdFilter ? find_by_id('drivers', $driverIdFilter) : null;

$pageTitle = 'Historique des infractions';
include __DIR__ . '/../includes/header.php';
?>

<div class="bg-white rounded-2xl border border-slate-200 p-5">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <div>
      <h2 class="font-semibold text-slate-800">
        <?= $filteredDriver ? 'Infractions de ' . e($filteredDriver['prenom'] . ' ' . $filteredDriver['nom']) : 'Toutes les infractions' ?>
        (<?= count($infractions) ?>)
      </h2>
      <?php if ($filteredDriver): ?>
        <a href="<?= e(ROOT_URL) ?>agent/history.php" class="text-xs text-brand-600 hover:underline">Voir toutes les infractions →</a>
      <?php endif; ?>
    </div>
    <?php if (!$filteredDriver): ?>
    <form method="get" class="flex gap-2">
      <input type="text" name="q" value="<?= e($search) ?>" placeholder="Conducteur, plaque, type…" class="rounded-lg border border-slate-300 px-3 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-brand-500">
      <button class="text-sm bg-slate-100 hover:bg-slate-200 rounded-lg px-3 py-2">Filtrer</button>
    </form>
    <?php endif; ?>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-slate-400 border-b border-slate-100">
          <th class="py-2 pr-4 font-medium">Date</th>
          <?php if (!$filteredDriver): ?><th class="py-2 pr-4 font-medium">Conducteur</th><?php endif; ?>
          <th class="py-2 pr-4 font-medium">Véhicule</th>
          <th class="py-2 pr-4 font-medium">Type</th>
          <th class="py-2 pr-4 font-medium">Sanction</th>
          <th class="py-2 pr-4 font-medium">Agent</th>
          <th class="py-2 pr-4 font-medium">Statut</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$infractions): ?>
          <tr><td colspan="<?= $filteredDriver ? 6 : 7 ?>" class="py-6 text-center text-slate-400">Aucune infraction trouvée.</td></tr>
        <?php endif; ?>
        <?php foreach ($infractions as $inf): ?>
        <tr class="border-b border-slate-50 hover:bg-slate-50/60 align-top">
          <td class="py-2.5 pr-4 text-slate-500 whitespace-nowrap"><?= e($inf['date']) ?></td>
          <?php if (!$filteredDriver): ?><td class="py-2.5 pr-4 text-slate-700"><?= e($driverName($inf['driver_id'])) ?></td><?php endif; ?>
          <td class="py-2.5 pr-4 font-mono text-slate-600"><?= e($vehiclePlate($inf['vehicle_id'])) ?></td>
          <td class="py-2.5 pr-4 text-slate-600">
            <?= e($inf['type']) ?>
            <?php if (!empty($inf['description'])): ?>
              <p class="text-xs text-slate-400 mt-0.5"><?= e($inf['description']) ?></p>
            <?php endif; ?>
          </td>
          <td class="py-2.5 pr-4 text-slate-600"><?= e($sanctionLabel($inf['sanction_id'])) ?></td>
          <td class="py-2.5 pr-4 text-slate-500"><?= e($agentName($inf['agent_id'])) ?></td>
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
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
