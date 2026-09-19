<?php
/**
 * includes/auth.php
 * -----------------------------------------------------------------------
 * Authentification simplifiée pour le prototype (session PHP uniquement,
 * mots de passe en clair). Voir README.md pour la vraie version.
 * -----------------------------------------------------------------------
 */

declare(strict_types=1);

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return find_by_id('users', (int)$_SESSION['user_id']);
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    $u = current_user();
    return $u !== null && $u['role'] === 'admin';
}

/**
 * Authentifie par "identifiant" (soit le nom d'utilisateur du super admin
 * "admin", soit un email pour les agents/admins créés par inscription).
 */
function attempt_login(string $identifiant, string $password): ?array
{
    $identifiant = trim($identifiant);
    foreach (db()['users'] as $user) {
        $matchIdentifiant = (
            (!empty($user['username']) && strcasecmp($user['username'], $identifiant) === 0)
            || strcasecmp($user['email'], $identifiant) === 0
        );
        if ($matchIdentifiant && hash_equals($user['password'], $password)) {
            if (($user['statut'] ?? 'actif') !== 'actif') {
                return null; // compte désactivé
            }
            return $user;
        }
    }
    return null;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

/** Redirige vers le login si non connecté. À appeler en haut des pages protégées. */
function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: ' . ROOT_URL . 'login.php');
        exit;
    }
}

/** Redirige si l'utilisateur connecté n'est pas admin. */
function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        header('Location: ' . ROOT_URL . 'agent/search.php');
        exit;
    }
}

/** Redirige si l'utilisateur connecté n'est pas agent (les admins ont aussi accès). */
function require_agent(): void
{
    require_login();
    // Les admins peuvent aussi utiliser les écrans agent pour test/supervision.
}
