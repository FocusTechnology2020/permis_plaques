<?php
require_once __DIR__ . '/../config.php';
require_admin();

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$editPermit = $editId ? find_by_id('permits', $editId) : null;
$errors = [];
$drivers = db()['drivers'];
usort($drivers, fn($a, $b) => strcmp($a['nom'], $b['nom']));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        delete_row('permits', (int)($_POST['id'] ?? 0));
        set_flash('Permis supprimé.');
        header('Location: ' . ROOT_URL . 'admin/permits.php');
        exit;
    }

    if ($action === 'toggle_suspend') {
        $id = (int)($_POST['id'] ?? 0);
        $permit = find_by_id('permits', $id);
        if ($permit) {
            $newForce = (($permit['statut_force'] ?? '') === 'suspendu') ? null : 'suspendu';
            update_row('permits', $id, ['statut_force' => $newForce]);
            set_flash($newForce ? 'Permis suspendu.' : 'Suspension levée.');
        }
        header('Location: ' . ROOT_URL . 'admin/permits.php');
        exit;
    }

    $fields = [
        'numero'          => trim($_POST['numero'] ?? ''),
        'categorie'       => trim($_POST['categorie'] ?? ''),
        'date_delivrance' => trim($_POST['date_delivrance'] ?? ''),
        'date_expiration' => trim($_POST['date_expiration'] ?? ''),
        'driver_id'       => (int)($_POST['driver_id'] ?? 0) ?: null,
    ];

    if ($fields['numero'] === '') $errors[] = 'Le numéro de permis est obligatoire.';
    if (!$fields['driver_id']) $errors[] = 'Veuillez sélectionner un conducteur.';
    if ($fields['date_expiration'] === '') $errors[] = "La date d'expiration est obligatoire.";

    if (!$errors) {
        foreach (db()['permits'] as $p) {
            $sameId = ($action === 'update') && (int)$p['id'] === (int)($_POST['id'] ?? 0);
            if (!$sameId && strcasecmp($p['numero'], $fields['numero']) === 0) {
                $errors[] = 'Ce numéro de permis existe déjà.';
                break;
            }
        }
    }

    if (!$errors) {
        if ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            update_row('permits', $id, $fields);
            set_flash('Permis mis à jour.');
        } else {
            $fields['statut_force'] = null;
            insert_row('permits', $fields);
            set_flash('Permis ajouté.');
        }
        header('Location: ' . ROOT_URL . 'admin/permits.php');
        exit;
    } else {
        $editPermit = array_merge(['id' => $_POST['id'] ?? null], $fields);
    }
}

$statusFilter = $_GET['statut'] ?? '';
$permits = db()['permits'];
if ($statusFilter !== '') {
    $permits = array_filter($permits, fn($p) => permit_status($p) === $statusFilter);
}
usort($permits, fn($a, $b) => strcmp($b['date_expiration'], $a['date_expiration']));

$driverName = function (?int $id) use ($drivers) {
    foreach ($drivers as $d) {
        if ((int)$d['id'] === $id) return $d['prenom'] . ' ' . $d['nom'];
    }
    return '—';
};

$pageTitle = 'Gestion des permis';
include __DIR__ . '/../includes/header.php';
?>

<div class="bg-white rounded-2xl border border-slate-200 p-5 mb-6">
  <h2 class="font-semibold text-slate-800 mb-4"><?= $editPermit ? 'Modifier le permis' : 'Ajouter un permis' ?></h2>
  <?php if ($errors): ?>
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
      <?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
    </div>
  <?php endif; ?>
  <form method="post" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
    <input type="hidden" name="action" value="<?= $editPermit && !empty($editPermit['id']) ? 'update' : 'create' ?>">
    <?php if ($editPermit && !empty($editPermit['id'])): ?>
      <input type="hidden" name="id" value="<?= e((string)$editPermit['id']) ?>">
    <?php endif; ?>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Numéro de permis</label>
      <input type="text" name="numero" required value="<?= e($editPermit['numero'] ?? '') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Catégorie</label>
      <input type="text" name="categorie" placeholder="A, B, C…" value="<?= e($editPermit['categorie'] ?? '') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Date de délivrance</label>
      <input type="date" name="date_delivrance" value="<?= e($editPermit['date_delivrance'] ?? '') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Date d'expiration</label>
      <input type="date" name="date_expiration" required value="<?= e($editPermit['date_expiration'] ?? '') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Titulaire</label>
      <select name="driver_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
        <option value="">— Sélectionner —</option>
        <?php foreach ($drivers as $d): ?>
          <option value="<?= (int)$d['id'] ?>" <?= (int)($editPermit['driver_id'] ?? 0) === (int)$d['id'] ? 'selected' : '' ?>>
            <?= e($d['prenom'] . ' ' . $d['nom']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="lg:col-span-5 flex gap-2">
      <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg px-4 py-2 transition">
        <?= $editPermit && !empty($editPermit['id']) ? 'Enregistrer les modifications' : 'Ajouter le permis' ?>
      </button>
      <?php if ($editPermit): ?>
        <a href="<?= e(ROOT_URL) ?>admin/permits.php" class="text-sm text-slate-500 hover:text-slate-700 px-4 py-2">Annuler</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div class="bg-white rounded-2xl border border-slate-200 p-5">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <h2 class="font-semibold text-slate-800">Permis enregistrés (<?= count($permits) ?>)</h2>
    <form method="get" class="flex gap-2">
      <select name="statut" onchange="this.form.submit()" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
        <option value="" <?= $statusFilter === '' ? 'selected' : '' ?>>Tous les statuts</option>
        <option value="valide" <?= $statusFilter === 'valide' ? 'selected' : '' ?>>Valides</option>
        <option value="expiré" <?= $statusFilter === 'expiré' ? 'selected' : '' ?>>Expirés</option>
        <option value="suspendu" <?= $statusFilter === 'suspendu' ? 'selected' : '' ?>>Suspendus</option>
      </select>
    </form>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-slate-400 border-b border-slate-100">
          <th class="py-2 pr-4 font-medium">Numéro</th>
          <th class="py-2 pr-4 font-medium">Titulaire</th>
          <th class="py-2 pr-4 font-medium">Catégorie</th>
          <th class="py-2 pr-4 font-medium">Expiration</th>
          <th class="py-2 pr-4 font-medium">Statut</th>
          <th class="py-2 pr-4 font-medium text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$permits): ?>
          <tr><td colspan="6" class="py-6 text-center text-slate-400">Aucun permis trouvé.</td></tr>
        <?php endif; ?>
        <?php foreach ($permits as $p): $status = permit_status($p); ?>
        <tr class="border-b border-slate-50 hover:bg-slate-50/60">
          <td class="py-2.5 pr-4 font-mono font-medium text-slate-700"><?= e($p['numero']) ?></td>
          <td class="py-2.5 pr-4 text-slate-600"><?= e($driverName($p['driver_id'] ?? null)) ?></td>
          <td class="py-2.5 pr-4 text-slate-500"><?= e($p['categorie'] ?? '') ?></td>
          <td class="py-2.5 pr-4 text-slate-500"><?= e($p['date_expiration']) ?></td>
          <td class="py-2.5 pr-4"><?= permit_status_badge($status) ?></td>
          <td class="py-2.5 pr-4">
            <div class="flex justify-end gap-2">
              <form method="post">
                <input type="hidden" name="action" value="toggle_suspend">
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <button type="submit" class="text-xs px-2.5 py-1.5 rounded-lg border <?= $status === 'suspendu' ? 'border-amber-300 text-amber-700 hover:bg-amber-50' : 'border-slate-300 text-slate-600 hover:bg-slate-50' ?>">
                  <?= $status === 'suspendu' ? 'Lever suspension' : 'Suspendre' ?>
                </button>
              </form>
              <a href="?edit=<?= (int)$p['id'] ?>" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500" title="Modifier"><?= icon_svg('edit') ?></a>
              <form method="post" onsubmit="return confirm('Supprimer ce permis ?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
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
