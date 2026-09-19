<?php
/**
 * config.php
 * -----------------------------------------------------------------------
 * Bootstrap du prototype "Permis & Plaques".
 *
 * IMPORTANT — Ceci est un PROTOTYPE :
 * Les "données" sont stockées dans la session PHP ($_SESSION['db']) et sont
 * donc réinitialisées à chaque nouvelle session (nouveau navigateur / après
 * un session_destroy()). Il n'y a pas de vraie base de données.
 *
 * Pour une vraie version en production, voir le README.md :
 * MySQL + PDO (requêtes préparées), password_hash()/password_verify(),
 * jetons CSRF sur chaque formulaire, permissions serveur, journal d'audit,
 * table de sanctions/règles routières gérée par un vrai référentiel légal.
 * -----------------------------------------------------------------------
 */

declare(strict_types=1);

// Session sécurisée a minima (cookie httponly). En production : ajouter
// secure => true (HTTPS) et samesite => 'Strict'.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

define('APP_NAME', 'Permis & Plaques');
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/'));

// Racine de l'application (pour construire des chemins/URLs relatifs fiables
// quelle que soit la profondeur du script courant : /admin/xxx.php, etc.)
function app_root_url(): string
{
    // Le dossier qui contient index.php est considéré comme la racine de l'app.
    $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';

    // Cas d'un script dans admin/ ou agent/ : tout ce qui précède ce segment
    // est déjà la racine de l'app, avec un slash final.
    if (preg_match('#^(.*/)(admin|agent)/[^/]+\.php$#', $script, $m)) {
        return $m[1];
    }

    // Sinon (index.php, login.php, register.php, logout.php à la racine).
    $dir = rtrim(dirname($script), '/');
    return $dir === '' ? '/' : $dir . '/';
}
define('ROOT_URL', app_root_url());

require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';

seed_database();
