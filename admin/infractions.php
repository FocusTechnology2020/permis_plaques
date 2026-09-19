<?php
require_once __DIR__ . '/../config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'delete') {
        delete_row('infractions', $id);
        set_flash('Infraction supprimée.');
    } elseif ($action === 'toggle_statut') {
        $inf = find_by_id('infractions', $id);
        if ($inf) {
            $new = $inf['statut'] === 'traitée' ? 'en attente' : 'traitée';
            update_row('infractions', $id, ['statut' => $new]);
            set_flash('Statut mis à jour.');
        }
    }
    header('Location: ' . ROOT_URL . 'admin/infractions.php' . (isset($_GET) && $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''));
    exit;
}

$drivers  = db()['drivers'];
$vehicles = db()['vehicles'];
$sanctions = db()['sanctions'];

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

$search = trim($_GET['q'] ?? '');
$statusFilter = $_GET['statut'] ?? '';

$infractions = db()['infractions'];
if ($search !== '') {
    $needle = strtolower($search);
    $infractions = array_filter($infractions, function ($i) use ($needle, $driverName, $vehiclePlate) {
        $haystack = strtolower($driverName($i['driver_id']) . ' ' . $vehiclePlate($i['vehicle_id']) . ' ' . $i['type']);
        return str_contains($haystack, $needle);
    });
}
if ($statusFilter !== '') {
    $infractions = array_filter($infractions, fn($i) => $i['statut'] === $statusFilter);
}
usort($infractions, fn($a, $b) => strcmp($b['date'], $a['date']));

$pageTitle = 'Historique des infractions';
include __DIR__ . '/../includes/header.php';
?>

<div class="bg-white rounded-2xl border border-slate-200 p-5">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <h2 class="font-semibold text-slate-800">Infractions (<?= count($infractions) ?>)</h2>
    <form method="get" class="flex flex-wrap gap-2">
      <input type="text" name="q" value="<?= e($search) ?>" placeholder="Conducteur, plaque, type…" class="rounded-lg border border-slate-300 px-3 py-2 text-sm w-56 focus:outline-none focus:ring-2 focus:ring-brand-500">
      <select name="statut" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
        <option value="" <?= $statusFilter === '' ? 'selected' : '' ?>>Tous les statuts</option>
        <option value="en attente" <?= $statusFilter === 'en attente' ? 'selected' : '' ?>>En attente</option>
        <option value="traitée" <?= $statusFilter === 'traitée' ? 'selected' : '' ?>>Traitée</option>
      </select>
      <button class="text-sm bg-slate-100 hover:bg-slate-200 rounded-lg px-3 py-2">Filtrer</button>
    </form>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-slate-400 border-b border-slate-100">
          <th class="py-2 pr-4 font-medium">Date</th>
          <th class="py-2 pr-4 font-medium">Conducteur</th>
          <th class="py-2 pr-4 font-medium">Véhicule</th>
          <th class="py-2 pr-4 font-medium">Type</th>
          <th class="py-2 pr-4 font-medium">Sanction</th>
          <th class="py-2 pr-4 font-medium">Statut</th>
          <th class="py-2 pr-4 font-medium text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$infractions): ?>
          <tr><td colspan="7" class="py-6 text-center text-slate-400">Aucune infraction trouvée.</td></tr>
        <?php endif; ?>
        <?php foreach ($infractions as $inf): ?>
        <tr class="border-b border-slate-50 hover:bg-slate-50/60 align-top">
          <td class="py-2.5 pr-4 text-slate-500 whitespace-nowrap"><?= e($inf['date']) ?></td>
          <td class="py-2.5 pr-4 text-slate-700"><?= e($driverName($inf['driver_id'])) ?></td>
          <td class="py-2.5 pr-4 font-mono text-slate-600"><?= e($vehiclePlate($inf['vehicle_id'])) ?></td>
          <td class="py-2.5 pr-4 text-slate-600">
            <?= e($inf['type']) ?>
            <?php if (!empty($inf['description'])): ?>
              <p class="text-xs text-slate-400 mt-0.5"><?= e($inf['description']) ?></p>
            <?php endif; ?>
          </td>
          <td class="py-2.5 pr-4 text-slate-600"><?= e($sanctionLabel($inf['sanction_id'])) ?></td>
          <td class="py-2.5 pr-4">
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset <?= $inf['statut'] === 'traitée' ? 'bg-emerald-100 text-emerald-700 ring-emerald-600/20' : 'bg-amber-100 text-amber-800 ring-amber-600/20' ?>">
              <?= e(ucfirst($inf['statut'])) ?>
            </span>
          </td>
          <td class="py-2.5 pr-4">
            <div class="flex justify-end gap-2">
              <form method="post">
                <input type="hidden" name="action" value="toggle_statut">
                <input type="hidden" name="id" value="<?= (int)$inf['id'] ?>">
                <button type="submit" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 whitespace-nowrap">
                  <?= $inf['statut'] === 'traitée' ? 'Rouvrir' : 'Marquer traitée' ?>
                </button>
              </form>
              <form method="post" onsubmit="return confirm('Supprimer cette infraction ?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$inf['id'] ?>">
                <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-red-500" title="Supprimer"><?= icon_svg('trash') ?></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
