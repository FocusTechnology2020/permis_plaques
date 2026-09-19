<?php
require_once __DIR__ . '/../config.php';
require_admin();

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$editVehicle = $editId ? find_by_id('vehicles', $editId) : null;
$errors = [];
$drivers = db()['drivers'];
usort($drivers, fn($a, $b) => strcmp($a['nom'], $b['nom']));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $linked = false;
        foreach (db()['infractions'] as $row) {
            if ((int)$row['vehicle_id'] === $id) { $linked = true; break; }
        }
        if ($linked) {
            set_flash('Impossible de supprimer : ce véhicule est lié à des infractions.', 'error');
        } else {
            delete_row('vehicles', $id);
            set_flash('Véhicule supprimé.');
        }
        header('Location: ' . ROOT_URL . 'admin/vehicles.php');
        exit;
    }

    $fields = [
        'plaque'    => strtoupper(trim($_POST['plaque'] ?? '')),
        'marque'    => trim($_POST['marque'] ?? ''),
        'modele'    => trim($_POST['modele'] ?? ''),
        'couleur'   => trim($_POST['couleur'] ?? ''),
        'annee'     => (int)($_POST['annee'] ?? 0) ?: null,
        'driver_id' => (int)($_POST['driver_id'] ?? 0) ?: null,
    ];

    if ($fields['plaque'] === '') {
        $errors[] = 'Le numéro de plaque est obligatoire.';
    }
    if (!$fields['driver_id']) {
        $errors[] = 'Veuillez sélectionner un propriétaire.';
    }

    // Unicité de la plaque
    if (!$errors) {
        foreach (db()['vehicles'] as $v) {
            $sameId = ($action === 'update') && (int)$v['id'] === (int)($_POST['id'] ?? 0);
            if (!$sameId && strcasecmp($v['plaque'], $fields['plaque']) === 0) {
                $errors[] = 'Cette plaque est déjà enregistrée pour un autre véhicule.';
                break;
            }
        }
    }

    if (!$errors) {
        if ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            update_row('vehicles', $id, $fields);
            set_flash('Véhicule mis à jour.');
        } else {
            insert_row('vehicles', $fields);
            set_flash('Véhicule ajouté.');
        }
        header('Location: ' . ROOT_URL . 'admin/vehicles.php');
        exit;
    } else {
        $editVehicle = array_merge(['id' => $_POST['id'] ?? null], $fields);
    }
}

$search = trim($_GET['q'] ?? '');
$vehicles = db()['vehicles'];
if ($search !== '') {
    $vehicles = array_filter($vehicles, function ($v) use ($search) {
        $haystack = strtolower($v['plaque'] . ' ' . $v['marque'] . ' ' . $v['modele']);
        return str_contains($haystack, strtolower($search));
    });
}
usort($vehicles, fn($a, $b) => strcmp($a['plaque'], $b['plaque']));

$driverName = function (?int $id) use ($drivers) {
    foreach ($drivers as $d) {
        if ((int)$d['id'] === $id) return $d['prenom'] . ' ' . $d['nom'];
    }
    return '—';
};

$pageTitle = 'Plaques / Véhicules';
include __DIR__ . '/../includes/header.php';
?>

<div class="bg-white rounded-2xl border border-slate-200 p-5 mb-6">
  <h2 class="font-semibold text-slate-800 mb-4"><?= $editVehicle ? 'Modifier le véhicule' : 'Ajouter un véhicule' ?></h2>
  <?php if ($errors): ?>
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
      <?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
    </div>
  <?php endif; ?>
  <form method="post" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
    <input type="hidden" name="action" value="<?= $editVehicle && !empty($editVehicle['id']) ? 'update' : 'create' ?>">
    <?php if ($editVehicle && !empty($editVehicle['id'])): ?>
      <input type="hidden" name="id" value="<?= e((string)$editVehicle['id']) ?>">
    <?php endif; ?>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Plaque</label>
      <input type="text" name="plaque" required value="<?= e($editVehicle['plaque'] ?? '') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Marque</label>
      <input type="text" name="marque" value="<?= e($editVehicle['marque'] ?? '') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Modèle</label>
      <input type="text" name="modele" value="<?= e($editVehicle['modele'] ?? '') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Couleur</label>
      <input type="text" name="couleur" value="<?= e($editVehicle['couleur'] ?? '') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Année</label>
      <input type="number" name="annee" min="1970" max="<?= (int)date('Y') + 1 ?>" value="<?= e((string)($editVehicle['annee'] ?? '')) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Propriétaire</label>
      <select name="driver_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
        <option value="">— Sélectionner —</option>
        <?php foreach ($drivers as $d): ?>
          <option value="<?= (int)$d['id'] ?>" <?= (int)($editVehicle['driver_id'] ?? 0) === (int)$d['id'] ? 'selected' : '' ?>>
            <?= e($d['prenom'] . ' ' . $d['nom']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="lg:col-span-6 flex gap-2">
      <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg px-4 py-2 transition">
        <?= $editVehicle && !empty($editVehicle['id']) ? 'Enregistrer les modifications' : 'Ajouter le véhicule' ?>
      </button>
      <?php if ($editVehicle): ?>
        <a href="<?= e(ROOT_URL) ?>admin/vehicles.php" class="text-sm text-slate-500 hover:text-slate-700 px-4 py-2">Annuler</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div class="bg-white rounded-2xl border border-slate-200 p-5">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <h2 class="font-semibold text-slate-800">Véhicules enregistrés (<?= count($vehicles) ?>)</h2>
    <form method="get" class="flex gap-2">
      <input type="text" name="q" value="<?= e($search) ?>" placeholder="Rechercher plaque, marque, modèle…" class="rounded-lg border border-slate-300 px-3 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-brand-500">
      <button class="text-sm bg-slate-100 hover:bg-slate-200 rounded-lg px-3 py-2">Filtrer</button>
    </form>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-slate-400 border-b border-slate-100">
          <th class="py-2 pr-4 font-medium">Plaque</th>
          <th class="py-2 pr-4 font-medium">Véhicule</th>
          <th class="py-2 pr-4 font-medium">Couleur</th>
          <th class="py-2 pr-4 font-medium">Année</th>
          <th class="py-2 pr-4 font-medium">Propriétaire</th>
          <th class="py-2 pr-4 font-medium text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$vehicles): ?>
          <tr><td colspan="6" class="py-6 text-center text-slate-400">Aucun véhicule trouvé.</td></tr>
        <?php endif; ?>
        <?php foreach ($vehicles as $v): ?>
        <tr class="border-b border-slate-50 hover:bg-slate-50/60">
          <td class="py-2.5 pr-4 font-mono font-medium text-slate-700"><?= e($v['plaque']) ?></td>
          <td class="py-2.5 pr-4 text-slate-600"><?= e(trim(($v['marque'] ?? '') . ' ' . ($v['modele'] ?? ''))) ?></td>
          <td class="py-2.5 pr-4 text-slate-500"><?= e($v['couleur'] ?? '') ?></td>
          <td class="py-2.5 pr-4 text-slate-500"><?= e((string)($v['annee'] ?? '')) ?></td>
          <td class="py-2.5 pr-4 text-slate-600"><?= e($driverName($v['driver_id'] ?? null)) ?></td>
          <td class="py-2.5 pr-4">
            <div class="flex justify-end gap-2">
              <a href="?edit=<?= (int)$v['id'] ?>" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500" title="Modifier"><?= icon_svg('edit') ?></a>
              <form method="post" onsubmit="return confirm('Supprimer ce véhicule ?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
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
