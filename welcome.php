<?php
/**
 * welcome.php — Écran d'ouverture de l'application.
 *
 *  1. Splash animé (~2,3 s) : la plaque d'immatriculation se compose.
 *  2. Présentation en 3 écrans paginés.
 *  3. Bouton « Commencer » → login.php
 *
 * L'onboarding n'est montré qu'une fois par navigateur (localStorage).
 * Pour le revoir : welcome.php?replay=1
 */

$bootstrap = __DIR__ . '/config.php';
if (is_file($bootstrap)) {
    require_once $bootstrap;
}

// Si l'agent/admin est déjà connecté, on ne l'embête pas avec l'onboarding.
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
if (!empty($_SESSION['user']) || !empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$replay = isset($_GET['replay']);
?>
<!doctype html>
<html lang="fr" class="h-full">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#14181d">
<title>Permis &amp; Plaques</title>

<?php
// Thème (clair/sombre) + polices + config Tailwind partagés avec le reste de l'app.
$theme = __DIR__ . '/includes/theme.php';
if (is_file($theme)) {
    include $theme;
} else { ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' };</script>
<?php } ?>

<style>
  :root{
    --asphalte:#14181d;
    --asphalte-clair:#1d232c;
    --plaque:#f7f8f6;
    --signal:#f2c230;
    --sourdine:#8a93a0;
  }
  body{ font-family:'Archivo', ui-sans-serif, system-ui, sans-serif; }
  .plaque-num{ font-variant-numeric:tabular-nums; letter-spacing:.06em; }

  /* ---------- Splash ---------- */
  #splash{ background:var(--asphalte); }
  .plaque{ transform:scale(.94); opacity:0; animation:plaque-pose .55s cubic-bezier(.2,.9,.3,1) .1s forwards; }
  @keyframes plaque-pose{ to{ transform:scale(1); opacity:1; } }

  .bande{ transform-origin:top; transform:scaleY(0); animation:bande-tombe .45s cubic-bezier(.2,.9,.3,1) .45s forwards; }
  @keyframes bande-tombe{ to{ transform:scaleY(1); } }

  .car{ opacity:0; transform:translateY(-.18em) rotateX(65deg); animation:car-tombe .3s ease-out forwards; }
  @keyframes car-tombe{ to{ opacity:1; transform:none; } }

  .scan{ animation:scan 1s ease-in-out .9s 1 both; }
  @keyframes scan{ 0%{ transform:translateX(-110%); opacity:0 } 15%{opacity:.9} 100%{ transform:translateX(110%); opacity:0 } }

  .signature{ opacity:0; animation:fondu .5s ease-out 1.35s forwards; }
  @keyframes fondu{ to{ opacity:1 } }

  #splash.sortie{ transform:translateY(-6%); opacity:0; transition:transform .5s cubic-bezier(.6,0,.2,1), opacity .45s ease-in; pointer-events:none; }

  /* ---------- Onboarding ---------- */
  #onboarding{ opacity:0; transition:opacity .45s ease-out .1s; }
  #onboarding.visible{ opacity:1; }
  .piste{ display:flex; transition:transform .4s cubic-bezier(.3,.8,.3,1); }
  .ecran{ flex:0 0 100%; }
  .marque{ width:2.25rem; height:.28rem; background:currentColor; opacity:.22; border-radius:2px; transition:opacity .3s, width .3s; }
  .marque[aria-current="true"]{ opacity:1; width:3.5rem; }

  @media (prefers-reduced-motion: reduce){
    .plaque,.bande,.car,.scan,.signature{ animation:none !important; opacity:1 !important; transform:none !important; }
    .piste{ transition:none; }
    #splash.sortie{ transition:none; }
  }
</style>
</head>

<body class="h-full bg-[color:var(--plaque)] text-[color:var(--asphalte)] dark:bg-[color:var(--asphalte)] dark:text-[color:var(--plaque)] antialiased overflow-hidden">

<!-- =================== 1. SPLASH =================== -->
<div id="splash" class="fixed inset-0 z-50 flex flex-col items-center justify-center gap-8 px-6">

  <div class="plaque relative select-none">
    <div class="relative flex items-stretch overflow-hidden rounded-lg border-[3px] border-black/70 bg-[color:var(--plaque)] shadow-[0_18px_50px_-12px_rgba(0,0,0,.7)]">
      <!-- bande latérale, comme sur une plaque officielle -->
      <div class="bande w-10 sm:w-12 bg-[color:var(--signal)] flex flex-col items-center justify-center gap-1 py-4">
        <svg viewBox="0 0 24 24" class="h-5 w-5 text-black/80" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 2 4 5.5V11c0 5 3.4 9.3 8 11 4.6-1.7 8-6 8-11V5.5z"/>
          <path d="m9 11.5 2 2 4-4"/>
        </svg>
        <span class="text-[10px] font-bold tracking-widest text-black/70">BI</span>
      </div>

      <div class="plaque-num flex items-center gap-2 px-5 py-4 sm:px-8 sm:py-6 text-4xl sm:text-6xl font-extrabold text-[color:var(--asphalte)]">
        <span class="car" style="animation-delay:.60s">P</span>
        <span class="car" style="animation-delay:.68s">P</span>
        <span class="car mx-1 h-6 w-[3px] sm:h-9 rounded bg-[color:var(--asphalte)]/25" style="animation-delay:.76s"></span>
        <span class="car" style="animation-delay:.84s">0</span>
        <span class="car" style="animation-delay:.92s">0</span>
        <span class="car" style="animation-delay:1s">1</span>
      </div>

      <!-- passage du lecteur de plaque -->
      <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="scan h-full w-24 bg-gradient-to-r from-transparent via-[color:var(--signal)]/55 to-transparent"></div>
      </div>
    </div>
  </div>

  <div class="signature text-center">
    <h1 class="text-xl font-semibold tracking-tight text-[color:var(--plaque)]">Permis &amp; Plaques</h1>
    <p class="mt-1 text-sm text-[color:var(--sourdine)]">Contrôle des permis de conduire et des immatriculations</p>
  </div>
</div>

<!-- =================== 2. ONBOARDING =================== -->
<main id="onboarding" class="relative h-full">
  <div class="mx-auto flex h-full max-w-5xl flex-col px-6 py-6 sm:px-10">

    <header class="flex items-center justify-between">
      <div class="flex items-center gap-2.5">
        <img class="h-12 w-20 rounded-xl bg-brand-600 mx-auto flex items-center justify-center text-white font-bold text-xl" src="./image/watermarked_img_10446539134336566942.jpg" alt="">
        <!-- <span class="plaque-num rounded border-2 border-current px-1.5 py-0.5 text-xs font-extrabold">PP</span> -->
        <span class="text-sm font-semibold tracking-tight">Permis &amp; Plaques</span>
      </div>
      <div class="flex items-center gap-2">
        <button type="button" id="bascule-theme"
                class="rounded-full border border-current/15 p-2 hover:bg-black/5 dark:hover:bg-white/10"
                aria-label="Basculer le thème clair / sombre">
          <svg class="h-4 w-4 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8"/>
          </svg>
          <svg class="hidden h-4 w-4 dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M19.1 4.9l-1.4 1.4M6.3 17.7l-1.4 1.4"/>
          </svg>
        </button>
        <a href="login.php" class="rounded-full px-3 py-2 text-sm text-[color:var(--sourdine)] hover:text-current">Passer</a>
      </div>
    </header>

    <!-- écrans -->
    <div class="relative flex-1 overflow-hidden" id="scene">
      <div class="piste h-full" id="piste">

        <!-- Écran 1 -->
        <section class="ecran flex h-full flex-col items-center justify-center gap-8 px-2 text-center">
          <svg viewBox="0 0 220 130" class="w-56 sm:w-64" role="img" aria-label="Plaque contrôlée par un agent">
            <rect x="14" y="30" width="150" height="54" rx="6" fill="none" stroke="currentColor" stroke-width="3"/>
            <rect x="14" y="30" width="26" height="54" rx="6" fill="var(--signal)"/>
            <g class="plaque-num" fill="currentColor" font-size="26" font-weight="800" font-family="Archivo, sans-serif">
              <text x="54" y="68">AB 4721</text>
            </g>
            <circle cx="163" cy="86" r="26" fill="none" stroke="currentColor" stroke-width="3"/>
            <path d="m181 104 18 18" stroke="currentColor" stroke-width="5" stroke-linecap="round"/>
            <path d="m152 86 7 7 14-15" fill="none" stroke="var(--signal)" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <div class="max-w-md">
            <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl">Vérifier un conducteur en quelques secondes</h2>
            <p class="mt-3 text-[15px] leading-relaxed text-[color:var(--sourdine)]">
              Une plaque ou un numéro de permis suffit : l'agent voit le propriétaire,
              l'état du permis — valide, expiré ou suspendu — et tout son historique.
            </p>
          </div>
        </section>

        <!-- Écran 2 -->
        <section class="ecran flex h-full flex-col items-center justify-center gap-8 px-2 text-center">
          <svg viewBox="0 0 220 130" class="w-56 sm:w-64" role="img" aria-label="Constat d'infraction et sanction">
            <rect x="46" y="12" width="112" height="106" rx="8" fill="none" stroke="currentColor" stroke-width="3"/>
            <path d="M64 40h60M64 56h60M64 72h34" stroke="currentColor" stroke-width="4" stroke-linecap="round" opacity=".45"/>
            <rect x="62" y="86" width="72" height="20" rx="10" fill="var(--signal)"/>
            <text x="74" y="101" font-size="13" font-weight="700" fill="#14181d" font-family="Archivo, sans-serif">-4 pts</text>
            <circle cx="158" cy="26" r="16" fill="currentColor"/>
            <path d="M158 19v9M158 33h.01" stroke="var(--plaque)" stroke-width="3.5" stroke-linecap="round"/>
          </svg>
          <div class="max-w-md">
            <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl">Enregistrer une infraction sur le terrain</h2>
            <p class="mt-3 text-[15px] leading-relaxed text-[color:var(--sourdine)]">
              L'agent décrit les faits et choisit la sanction dans une grille préétablie.
              Le montant de l'amende et les points retirés sont appliqués automatiquement.
            </p>
          </div>
        </section>

        <!-- Écran 3 -->
        <section class="ecran flex h-full flex-col items-center justify-center gap-8 px-2 text-center">
          <svg viewBox="0 0 220 130" class="w-56 sm:w-64" role="img" aria-label="Tableau de bord de l'administration">
            <rect x="20" y="18" width="180" height="94" rx="8" fill="none" stroke="currentColor" stroke-width="3"/>
            <path d="M20 40h180" stroke="currentColor" stroke-width="3"/>
            <rect x="36" y="76" width="22" height="22" rx="3" fill="currentColor" opacity=".35"/>
            <rect x="68" y="60" width="22" height="38" rx="3" fill="currentColor" opacity=".55"/>
            <rect x="100" y="52" width="22" height="46" rx="3" fill="var(--signal)"/>
            <rect x="132" y="68" width="22" height="30" rx="3" fill="currentColor" opacity=".45"/>
            <rect x="164" y="84" width="22" height="14" rx="3" fill="currentColor" opacity=".3"/>
            <circle cx="34" cy="29" r="4" fill="var(--signal)"/><circle cx="48" cy="29" r="4" fill="currentColor" opacity=".3"/>
          </svg>
          <div class="max-w-md">
            <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl">Piloter le parc et les comptes</h2>
            <p class="mt-3 text-[15px] leading-relaxed text-[color:var(--sourdine)]">
              Conducteurs, véhicules, permis, agents et rôles se gèrent depuis
              l'administration, avec les statistiques du mois en première page.
            </p>
          </div>
        </section>

      </div>
    </div>

    <!-- pagination + action -->
    <footer class="flex items-center justify-between gap-4 pb-2">
      <div class="flex items-center gap-2" id="marques" role="tablist" aria-label="Pages de présentation">
        <button class="marque" type="button" role="tab" aria-label="Page 1" aria-current="true"></button>
        <button class="marque" type="button" role="tab" aria-label="Page 2" aria-current="false"></button>
        <button class="marque" type="button" role="tab" aria-label="Page 3" aria-current="false"></button>
      </div>

      <div class="flex items-center gap-3">
        <button type="button" id="precedent"
                class="invisible rounded-full border border-current/20 px-4 py-2.5 text-sm font-medium hover:bg-black/5 dark:hover:bg-white/10">
          Retour
        </button>
        <button type="button" id="suivant"
                class="rounded-full bg-[color:var(--asphalte)] px-6 py-2.5 text-sm font-semibold text-[color:var(--plaque)] hover:opacity-90 dark:bg-[color:var(--signal)] dark:text-[color:var(--asphalte)]">
          Suivant
        </button>
        <a href="login.php" id="commencer"
           class="hidden rounded-full bg-[color:var(--signal)] px-6 py-2.5 text-sm font-semibold text-[color:var(--asphalte)] hover:brightness-95">
          Commencer
        </a>
      </div>
    </footer>
  </div>
</main>

<script>
(function () {
  const reduit  = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const splash  = document.getElementById('splash');
  const onb     = document.getElementById('onboarding');
  const piste   = document.getElementById('piste');
  const marques = [...document.querySelectorAll('#marques .marque')];
  const suivant = document.getElementById('suivant');
  const precedent = document.getElementById('precedent');
  const commencer = document.getElementById('commencer');
  const rejoue  = <?= $replay ? 'true' : 'false' ?>;
  const CLE     = 'pp.onboarding.vu';

  // --- thème ---
  document.getElementById('bascule-theme').addEventListener('click', () => {
    const sombre = document.documentElement.classList.toggle('dark');
    try { localStorage.setItem('pp.theme', sombre ? 'dark' : 'light'); } catch (e) {}
  });

  // --- splash ---
  let termine = false;
  function fermerSplash() {
    if (termine) return;
    termine = true;
    splash.classList.add('sortie');
    onb.classList.add('visible');
    setTimeout(() => splash.remove(), 600);
    document.body.classList.remove('overflow-hidden');
  }
  setTimeout(fermerSplash, reduit ? 350 : 2800);
  splash.addEventListener('click', fermerSplash);
  window.addEventListener('keydown', fermerSplash, { once: true });

  // Déjà vu : on file directement au formulaire de connexion.
  // let dejaVu = false;
  // try { dejaVu = localStorage.getItem(CLE) === '1'; } catch (e) {}
  // if (dejaVu && !rejoue) {
  //   setTimeout(() => { window.location.replace('login.php'); }, reduit ? 400 : 2300);
  //   return;
  // }

  // --- pagination ---
  let i = 0;
  const dernier = marques.length - 1;

  function afficher(n) {
    i = Math.max(0, Math.min(dernier, n));
    piste.style.transform = `translateX(-${i * 100}%)`;
    marques.forEach((m, k) => m.setAttribute('aria-current', String(k === i)));
    precedent.classList.toggle('invisible', i === 0);
    suivant.classList.toggle('hidden', i === dernier);
    commencer.classList.toggle('hidden', i !== dernier);
  }

  suivant.addEventListener('click', () => afficher(i + 1));
  precedent.addEventListener('click', () => afficher(i - 1));
  marques.forEach((m, k) => m.addEventListener('click', () => afficher(k)));
  window.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowRight') afficher(i + 1);
    if (e.key === 'ArrowLeft')  afficher(i - 1);
  });

  // glissement tactile
  let x0 = null;
  const scene = document.getElementById('scene');
  scene.addEventListener('touchstart', (e) => { x0 = e.touches[0].clientX; }, { passive: true });
  scene.addEventListener('touchend', (e) => {
    if (x0 === null) return;
    const d = e.changedTouches[0].clientX - x0;
    if (Math.abs(d) > 45) afficher(d < 0 ? i + 1 : i - 1);
    x0 = null;
  }, { passive: true });

  // on ne remontre pas l'onboarding la prochaine fois
  const marquerVu = () => { try { localStorage.setItem(CLE, '1'); } catch (e) {} };
  commencer.addEventListener('click', marquerVu);
  document.querySelectorAll('a[href="login.php"]').forEach(a => a.addEventListener('click', marquerVu));

  afficher(0);
})();
</script>
</body>
</html>
