<?php
require_once __DIR__ . '/../config.php';
require_admin();

$me = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $target = find_by_id('users', $id);

    if ($action === 'create') {
        $nom      = trim($_POST['nom'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $role     = ($_POST['role'] ?? 'agent') === 'admin' ? 'admin' : 'agent';

        if ($nom === '' || $email === '' || $password === '') {
            $errors[] = 'Tous les champs sont obligatoires.';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'adresse email n'est pas valide.";
        }
        if (strlen($password) < 6) {
            $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
        }
        if (!$errors) {
            foreach (db()['users'] as $u) {
                if (strcasecmp($u['email'], $email) === 0) {
                    $errors[] = 'Un compte existe déjà avec cet email.';
                    break;
                }
            }
        }
        if (!$errors) {
            insert_row('users', [
                'nom' => $nom, 'username' => null, 'email' => $email,
                'password' => $password, 'role' => $role, 'statut' => 'actif',
                'cree_le' => date('Y-m-d'),
            ]);
            set_flash('Compte créé.');
            header('Location: ' . ROOT_URL . 'admin/agents.php');
            exit;
        }
    } elseif ($target && ($target['username'] ?? null) !== 'admin') {
        // Empêche de modifier/supprimer le super administrateur fixe
        if ($action === 'toggle_role') {
            $newRole = $target['role'] === 'admin' ? 'agent' : 'admin';
            update_row('users', $id, ['role' => $newRole]);
            set_flash('Rôle mis à jour.');
        } elseif ($action === 'toggle_statut') {
            $newStatut = ($target['statut'] ?? 'actif') === 'actif' ? 'inactif' : 'actif';
            if ($id === (int)$me['id'] && $newStatut === 'inactif') {
                set_flash('Vous ne pouvez pas désactiver votre propre compte.', 'error');
            } else {
                update_row('users', $id, ['statut' => $newStatut]);
                set_flash('Statut du compte mis à jour.');
            }
        } elseif ($action === 'delete') {
            if ($id === (int)$me['id']) {
                set_flash('Vous ne pouvez pas supprimer votre propre compte.', 'error');
            } else {
                delete_row('users', $id);
                set_flash('Compte supprimé.');
            }
        }
        header('Location: ' . ROOT_URL . 'admin/agents.php');
        exit;
    } elseif ($target) {
        set_flash('Le compte super administrateur ne peut pas être modifié.', 'error');
        header('Location: ' . ROOT_URL . 'admin/agents.php');
        exit;
    }
}

$users = db()['users'];
usort($users, fn($a, $b) => strcmp($a['nom'], $b['nom']));

$pageTitle = 'Agents & rôles';
include __DIR__ . '/../includes/header.php';
?>

<div class="bg-white rounded-2xl border border-slate-200 p-5 mb-6">
  <h2 class="font-semibold text-slate-800 mb-4">Créer un compte agent / administrateur</h2>
  <?php if ($errors): ?>
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
      <?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
    </div>
  <?php endif; ?>
  <form method="post" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
    <input type="hidden" name="action" value="create">
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Nom complet</label>
      <input type="text" name="nom" required value="<?= e($_POST['nom'] ?? '') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Email</label>
      <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Mot de passe</label>
      <input type="password" name="password" required minlength="6" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Rôle</label>
      <select name="role" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
        <option value="agent">Agent de contrôle</option>
        <option value="admin">Administrateur</option>
      </select>
    </div>
    <div class="flex items-end">
      <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg px-4 py-2 transition">Créer le compte</button>
    </div>
  </form>
</div>

<div class="bg-white rounded-2xl border border-slate-200 p-5">
  <h2 class="font-semibold text-slate-800 mb-4">Comptes (<?= count($users) ?>)</h2>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-slate-400 border-b border-slate-100">
          <th class="py-2 pr-4 font-medium">Nom</th>
          <th class="py-2 pr-4 font-medium">Email</th>
          <th class="py-2 pr-4 font-medium">Rôle</th>
          <th class="py-2 pr-4 font-medium">Statut</th>
          <th class="py-2 pr-4 font-medium">Créé le</th>
          <th class="py-2 pr-4 font-medium text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): $isSuperAdmin = ($u['username'] ?? null) === 'admin'; ?>
        <tr class="border-b border-slate-50 hover:bg-slate-50/60">
          <td class="py-2.5 pr-4 font-medium text-slate-700">
            <?= e($u['nom']) ?>
            <?php if ($isSuperAdmin): ?><span class="text-xs text-slate-400 ml-1">(super admin)</span><?php endif; ?>
            <?php if ((int)$u['id'] === (int)$me['id']): ?><span class="text-xs text-brand-600 ml-1">(vous)</span><?php endif; ?>
          </td>
          <td class="py-2.5 pr-4 text-slate-500"><?= e($u['email']) ?></td>
          <td class="py-2.5 pr-4">
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset <?= $u['role'] === 'admin' ? 'bg-brand-100 text-brand-700 ring-brand-600/20' : 'bg-slate-100 text-slate-600 ring-slate-500/20' ?>">
              <?= $u['role'] === 'admin' ? 'Administrateur' : 'Agent' ?>
            </span>
          </td>
          <td class="py-2.5 pr-4">
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset <?= ($u['statut'] ?? 'actif') === 'actif' ? 'bg-emerald-100 text-emerald-700 ring-emerald-600/20' : 'bg-red-100 text-red-700 ring-red-600/20' ?>">
              <?= ($u['statut'] ?? 'actif') === 'actif' ? 'Actif' : 'Inactif' ?>
            </span>
          </td>
          <td class="py-2.5 pr-4 text-slate-500"><?= e($u['cree_le'] ?? '') ?></td>
          <td class="py-2.5 pr-4">
            <?php if (!$isSuperAdmin): ?>
            <div class="flex justify-end gap-2 flex-wrap">
              <form method="post">
                <input type="hidden" name="action" value="toggle_role">
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <button type="submit" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 whitespace-nowrap">
                  <?= $u['role'] === 'admin' ? 'Rétrograder en agent' : 'Promouvoir admin' ?>
                </button>
              </form>
              <form method="post">
                <input type="hidden" name="action" value="toggle_statut">
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <button type="submit" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 whitespace-nowrap">
                  <?= ($u['statut'] ?? 'actif') === 'actif' ? 'Désactiver' : 'Activer' ?>
                </button>
              </form>
              <form method="post" onsubmit="return confirm('Supprimer ce compte ?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-red-500" title="Supprimer"><?= icon_svg('trash') ?></button>
              </form>
            </div>
            <?php else: ?>
              <span class="text-xs text-slate-300">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
