<?php
require_once __DIR__ . '/../config.php';
require_admin();

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$editDriver = $editId ? find_by_id('drivers', $editId) : null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        // Empêche la suppression si des permis/véhicules/infractions y sont liés
        $linked = false;
        foreach (['permits', 'vehicles', 'infractions'] as $table) {
            foreach (db()[$table] as $row) {
                if ((int)$row['driver_id'] === $id) { $linked = true; break 2; }
            }
        }
        if ($linked) {
            set_flash("Impossible de supprimer : ce conducteur possède des permis, véhicules ou infractions liés.", 'error');
        } else {
            delete_row('drivers', $id);
            set_flash('Conducteur supprimé.');
        }
        header('Location: ' . ROOT_URL . 'admin/drivers.php');
        exit;
    }

    $fields = [
        'nom'            => trim($_POST['nom'] ?? ''),
        'prenom'         => trim($_POST['prenom'] ?? ''),
        'date_naissance' => trim($_POST['date_naissance'] ?? ''),
        'telephone'      => trim($_POST['telephone'] ?? ''),
        'adresse'        => trim($_POST['adresse'] ?? ''),
    ];

    if ($fields['nom'] === '' || $fields['prenom'] === '') {
        $errors[] = 'Le nom et le prénom sont obligatoires.';
    }

    if (!$errors) {
        if ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            update_row('drivers', $id, $fields);
            set_flash('Conducteur mis à jour.');
        } else {
            insert_row('drivers', $fields);
            set_flash('Conducteur ajouté.');
        }
        header('Location: ' . ROOT_URL . 'admin/drivers.php');
        exit;
    } else {
        // On réaffiche le formulaire avec les valeurs saisies
        $editDriver = array_merge(['id' => $_POST['id'] ?? null], $fields);
    }
}

$search = trim($_GET['q'] ?? '');
$drivers = db()['drivers'];
if ($search !== '') {
    $drivers = array_filter($drivers, function ($d) use ($search) {
        $haystack = strtolower($d['nom'] . ' ' . $d['prenom'] . ' ' . ($d['telephone'] ?? ''));
        return str_contains($haystack, strtolower($search));
    });
}
usort($drivers, fn($a, $b) => strcmp($a['nom'], $b['nom']));

$pageTitle = 'Gestion des conducteurs';
include __DIR__ . '/../includes/header.php';
?>

<div class="bg-white rounded-2xl border border-slate-200 p-5 mb-6">
  <h2 class="font-semibold text-slate-800 mb-4"><?= $editDriver ? 'Modifier le conducteur' : 'Ajouter un conducteur' ?></h2>
  <?php if ($errors): ?>
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
      <?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
    </div>
  <?php endif; ?>
  <form method="post" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
    <input type="hidden" name="action" value="<?= $editDriver && !empty($editDriver['id']) ? 'update' : 'create' ?>">
    <?php if ($editDriver && !empty($editDriver['id'])): ?>
      <input type="hidden" name="id" value="<?= e((string)$editDriver['id']) ?>">
    <?php endif; ?>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Nom</label>
      <input type="text" name="nom" required value="<?= e($editDriver['nom'] ?? '') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Prénom</label>
      <input type="text" name="prenom" required value="<?= e($editDriver['prenom'] ?? '') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Date de naissance</label>
      <input type="date" name="date_naissance" value="<?= e($editDriver['date_naissance'] ?? '') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Téléphone</label>
      <input type="text" name="telephone" value="<?= e($editDriver['telephone'] ?? '') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Adresse</label>
      <input type="text" name="adresse" value="<?= e($editDriver['adresse'] ?? '') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
    </div>
    <div class="lg:col-span-5 flex gap-2">
      <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg px-4 py-2 transition">
        <?= $editDriver && !empty($editDriver['id']) ? 'Enregistrer les modifications' : 'Ajouter le conducteur' ?>
      </button>
      <?php if ($editDriver): ?>
        <a href="<?= e(ROOT_URL) ?>admin/drivers.php" class="text-sm text-slate-500 hover:text-slate-700 px-4 py-2">Annuler</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div class="bg-white rounded-2xl border border-slate-200 p-5">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <h2 class="font-semibold text-slate-800">Liste des conducteurs (<?= count($drivers) ?>)</h2>
    <form method="get" class="flex gap-2">
      <input type="text" name="q" value="<?= e($search) ?>" placeholder="Rechercher nom, prénom, téléphone…" class="rounded-lg border border-slate-300 px-3 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-brand-500">
      <button class="text-sm bg-slate-100 hover:bg-slate-200 rounded-lg px-3 py-2">Filtrer</button>
    </form>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-slate-400 border-b border-slate-100">
          <th class="py-2 pr-4 font-medium">Nom</th>
          <th class="py-2 pr-4 font-medium">Prénom</th>
          <th class="py-2 pr-4 font-medium">Naissance</th>
          <th class="py-2 pr-4 font-medium">Téléphone</th>
          <th class="py-2 pr-4 font-medium">Adresse</th>
          <th class="py-2 pr-4 font-medium text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$drivers): ?>
          <tr><td colspan="6" class="py-6 text-center text-slate-400">Aucun conducteur trouvé.</td></tr>
        <?php endif; ?>
        <?php foreach ($drivers as $d): ?>
        <tr class="border-b border-slate-50 hover:bg-slate-50/60">
          <td class="py-2.5 pr-4 font-medium text-slate-700"><?= e($d['nom']) ?></td>
          <td class="py-2.5 pr-4 text-slate-600"><?= e($d['prenom']) ?></td>
          <td class="py-2.5 pr-4 text-slate-500"><?= e($d['date_naissance'] ?? '') ?></td>
          <td class="py-2.5 pr-4 text-slate-500"><?= e($d['telephone'] ?? '') ?></td>
          <td class="py-2.5 pr-4 text-slate-500"><?= e($d['adresse'] ?? '') ?></td>
          <td class="py-2.5 pr-4">
            <div class="flex justify-end gap-2">
              <a href="?edit=<?= (int)$d['id'] ?>" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500" title="Modifier"><?= icon_svg('edit') ?></a>
              <form method="post" onsubmit="return confirm('Supprimer ce conducteur ?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
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
