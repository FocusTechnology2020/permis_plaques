<?php
require_once __DIR__ . '/config.php';

if (is_logged_in()) {
    header('Location: ' . ROOT_URL . 'index.php');
    exit;
}

$errors = [];
$nom = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom      = trim($_POST['nom'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $confirm  = (string)($_POST['password_confirm'] ?? '');

    if ($nom === '' || $email === '' || $password === '') {
        $errors[] = 'Tous les champs sont obligatoires.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }
    if (strlen($password) < 6) {
        $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Les deux mots de passe ne correspondent pas.';
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
        $user = insert_row('users', [
            'nom'      => $nom,
            'username' => null,
            'email'    => $email,
            'password' => $password, // prototype uniquement, cf. README
            'role'     => 'agent',   // rôle par défaut ; promotion possible par un admin
            'statut'   => 'actif',
            'cree_le'  => date('Y-m-d'),
        ]);
        login_user($user);
        set_flash('Compte créé avec succès. Bienvenue, ' . $nom . ' !');
        header('Location: ' . ROOT_URL . 'index.php');
        exit;
    }
}

$pageTitle = 'Inscription';
include __DIR__ . '/includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center px-4 py-10">
  <div class="w-full max-w-md">
    <div class="text-center mb-8">
      <div class="h-12 w-12 rounded-xl bg-brand-600 mx-auto flex items-center justify-center text-white font-bold text-xl">P</div>
      <h1 class="mt-4 text-2xl font-bold text-slate-800">Créer un compte</h1>
      <p class="text-slate-500 text-sm mt-1">Inscription agent de contrôle</p>
    </div>

    <div class="bg-white shadow-sm border border-slate-200 rounded-2xl p-6 sm:p-8">
      <?php if ($errors): ?>
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
          <?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="post" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Nom complet</label>
          <input type="text" name="nom" value="<?= e($nom) ?>" required autofocus
                 class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
          <input type="email" name="email" value="<?= e($email) ?>" required
                 class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Mot de passe</label>
          <input type="password" name="password" required minlength="6"
                 class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Confirmer le mot de passe</label>
          <input type="password" name="password_confirm" required minlength="6"
                 class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        </div>
        <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-medium rounded-lg py-2.5 text-sm transition">
          Créer mon compte
        </button>
      </form>

      <p class="text-center text-sm text-slate-500 mt-5">
        Déjà inscrit ?
        <a href="<?= e(ROOT_URL) ?>login.php" class="text-brand-600 font-medium hover:underline">Se connecter</a>
      </p>
    </div>
    <p class="text-center text-xs text-slate-400 mt-4">
      Un administrateur peut ensuite vous attribuer le rôle « Administrateur » depuis Agents &amp; rôles.
    </p>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
