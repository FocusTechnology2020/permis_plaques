<?php
require_once __DIR__ . '/../config.php';
require_agent();

$me = current_user();
$drivers  = db()['drivers'];
$vehicles = db()['vehicles'];
$sanctions = db()['sanctions'];
usort($drivers, fn($a, $b) => strcmp($a['nom'], $b['nom']));

$errors = [];
$driverId  = (int)($_GET['driver_id'] ?? $_POST['driver_id'] ?? 0);
$vehicleId = (int)($_GET['vehicle_id'] ?? $_POST['vehicle_id'] ?? 0);
$type = '';
$description = '';
$date = date('Y-m-d');
$sanctionId = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $driverId    = (int)($_POST['driver_id'] ?? 0);
    $vehicleId   = (int)($_POST['vehicle_id'] ?? 0) ?: null;
    $type        = trim($_POST['type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date        = trim($_POST['date'] ?? date('Y-m-d'));
    $sanctionId  = (int)($_POST['sanction_id'] ?? 0);

    if (!$driverId || !find_by_id('drivers', $driverId)) {
        $errors[] = 'Veuillez sélectionner un conducteur valide.';
    }
    if ($type === '') {
        $errors[] = "Le type d'infraction est obligatoire.";
    }
    if (!$sanctionId || !find_by_id('sanctions', $sanctionId)) {
        $errors[] = 'Veuillez sélectionner une sanction.';
    }
    if ($date === '') {
        $errors[] = 'La date est obligatoire.';
    }

    if (!$errors) {
        insert_row('infractions', [
            'driver_id'   => $driverId,
            'vehicle_id'  => $vehicleId,
            'date'        => $date,
            'type'        => $type,
            'description' => $description,
            'sanction_id' => $sanctionId,
            'agent_id'    => $me['id'],
            'statut'      => 'en attente',
        ]);
        set_flash('Infraction enregistrée avec succès.');
        header('Location: ' . ROOT_URL . 'agent/history.php?driver_id=' . $driverId);
        exit;
    }
}

$driverVehicles = array_values(array_filter($vehicles, fn($v) => (int)$v['driver_id'] === $driverId));

$pageTitle = 'Enregistrer une infraction';
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-2xl">
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="font-semibold text-slate-800 mb-1">Nouvelle infraction</h2>
    <p class="text-sm text-slate-500 mb-5">Renseignez les informations du contrôle et sélectionnez la sanction applicable.</p>

    <?php if ($errors): ?>
      <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
        <?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Conducteur</label>
          <select name="driver_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="">— Sélectionner —</option>
            <?php foreach ($drivers as $d): ?>
              <option value="<?= (int)$d['id'] ?>" <?= $driverId === (int)$d['id'] ? 'selected' : '' ?>>
                <?= e($d['prenom'] . ' ' . $d['nom']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Véhicule (optionnel)</label>
          <select name="vehicle_id" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="">— Aucun / non concerné —</option>
            <?php foreach ($vehicles as $v): ?>
              <option value="<?= (int)$v['id'] ?>" <?= $vehicleId === (int)$v['id'] ? 'selected' : '' ?>>
                <?= e($v['plaque']) ?> — <?= e(trim(($v['marque'] ?? '') . ' ' . ($v['modele'] ?? ''))) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Date de l'infraction</label>
          <input type="date" name="date" value="<?= e($date) ?>" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Type d'infraction</label>
          <input type="text" name="type" value="<?= e($type) ?>" required list="types-infraction" placeholder="Ex. Excès de vitesse" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
          <datalist id="types-infraction">
            <?php foreach ($sanctions as $s): ?><option value="<?= e($s['libelle']) ?>"><?php endforeach; ?>
          </datalist>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Description / constat</label>
        <textarea name="description" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" placeholder="Détails du contrôle…"><?= e($description) ?></textarea>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">Sanction applicable</label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <?php foreach ($sanctions as $s): ?>
            <label class="flex items-start gap-3 border border-slate-200 rounded-lg p-3 cursor-pointer hover:border-brand-400 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
              <input type="radio" name="sanction_id" value="<?= (int)$s['id'] ?>" <?= $sanctionId === (int)$s['id'] ? 'checked' : '' ?> class="mt-1" required>
              <span>
                <span class="block text-sm font-medium text-slate-700"><?= e($s['libelle']) ?></span>
                <span class="block text-xs text-slate-500 mt-0.5"><?= number_format($s['montant_amende'], 0, ',', ' ') ?> FBu · <?= (int)$s['points'] ?> point(s)</span>
              </span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="flex gap-2 pt-2">
        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg px-5 py-2.5 transition">
          Enregistrer l'infraction
        </button>
        <a href="<?= e(ROOT_URL) ?>agent/search.php" class="text-sm text-slate-500 hover:text-slate-700 px-4 py-2.5">Annuler</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
