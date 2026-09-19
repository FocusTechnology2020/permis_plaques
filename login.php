<?php
require_once __DIR__ . '/config.php';

if (is_logged_in()) {
    header('Location: ' . ROOT_URL . 'index.php');
    exit;
}

$errors = [];
$identifiant = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant'] ?? '');
    $password    = (string)($_POST['password'] ?? '');

    if ($identifiant === '' || $password === '') {
        $errors[] = "Veuillez renseigner l'identifiant et le mot de passe.";
    } else {
        $user = attempt_login($identifiant, $password);
        if ($user) {
            login_user($user);
            set_flash('Bienvenue, ' . $user['nom'] . ' !');
            header('Location: ' . ROOT_URL . 'index.php');
            exit;
        }
        $errors[] = "Identifiant ou mot de passe incorrect, ou compte désactivé.";
    }
}

$pageTitle = 'Connexion';
include __DIR__ . '/includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center px-4">
  <div class="w-full max-w-md">
    <div class="text-center mb-8">
      <img class="h-12 w-20 rounded-xl bg-brand-600 mx-auto flex items-center justify-center text-white font-bold text-xl" src="./image/watermarked_img_10446539134336566942.jpg" alt="">
      <!-- <div class="h-12 w-12 rounded-xl bg-brand-600 mx-auto flex items-center justify-center text-white font-bold text-xl">P</div> -->
      <h1 class="mt-4 text-2xl font-bold text-slate-800"><?= e(APP_NAME) ?></h1>
      <p class="text-slate-500 text-sm mt-1">Connexion à votre espace</p>
    </div>

    <div class="bg-white shadow-sm border border-slate-200 rounded-2xl p-6 sm:p-8">
      <?php if ($errors): ?>
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
          <?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="post" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Identifiant</label>
          <input type="text" name="identifiant" value="<?= e($identifiant) ?>" required autofocus
                 placeholder="Email, ou « admin » pour le super administrateur"
                 class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Mot de passe</label>
          <input type="password" name="password" required
                 class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        </div>
        <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-medium rounded-lg py-2.5 text-sm transition">
          Se connecter
        </button>
      </form>

      <p class="text-center text-sm text-slate-500 mt-5">
        Pas encore de compte agent/admin ?
        <a href="<?= e(ROOT_URL) ?>register.php" class="text-brand-600 font-medium hover:underline">S'inscrire</a>
      </p>
    </div>

    <div class="mt-6 bg-slate-100 border border-slate-200 rounded-xl p-4 text-xs text-slate-500 space-y-1">
      <p class="font-semibold text-slate-600">Comptes de démonstration :</p>
      <p>Super admin — identifiant <code class="bg-white px-1 rounded border">admin</code> / mot de passe <code class="bg-white px-1 rounded border">admin123</code></p>
      <p>Agent — identifiant <code class="bg-white px-1 rounded border">agent@permis-plaques.local</code> / mot de passe <code class="bg-white px-1 rounded border">agent123</code></p>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
