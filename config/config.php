<?php
// config/config.php
// Complexe touristique sportif — Système de réservation


// Migration db a json
/*
// ─────────────────────────────────────────────
// BASE DE DONNÉES
// ─────────────────────────────────────────────
define('DB_HOST', 'localhost');
//define('DB_PORT', '');
define('DB_NAME', 'zenyth');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

try {
    $pdo = new PDO(
        'mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // requêtes préparées natives
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}
*/

// ─────────────────────────────────────────────
// SESSION
// ─────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false,   // utilisation en local
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

// ─────────────────────────────────────────────
// AUTHENTIFICATION
// ─────────────────────────────────────────────

/** Vérifie si un client est connecté */
function isLoggedIn()
{
    return isset($_SESSION['id_client']);
}

/** Vérifie si l'utilisateur connecté est un administrateur */
function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/** Redirige vers $path si l'utilisateur n'est pas connecté */
function requireLogin(string $path = '/login.php')
{
    if (!isLoggedIn()) {
        redirect($path);
    }
}

/** Redirige vers $path si l'utilisateur n'est pas administrateur */
function requireAdmin(string $path = '/index.php')
{
    if (!isAdmin()) {
        redirect($path);
    }
}

/** Vérifie le respect de la politique de mot de passe */
function verifierMotDePasse($mdp) {
    $_SESSION['error'] = [];

    // Longueur minimum 8
    if (strlen($mdp) < 8) {
        $_SESSION['error'][] = "Le mot de passe doit contenir au moins 8 caractères.";
    }

    // Au moins une majuscule
    if (!preg_match('/[A-Z]/', $mdp)) {
        $_SESSION['error'][] = "Le mot de passe doit contenir au moins une majuscule.";
    }

    // Au moins un chiffre
    if (!preg_match('/[0-9]/', $mdp)) {
        $_SESSION['error'][] = "Le mot de passe doit contenir au moins un chiffre.";
    }

    // Au moins un caractère spécial 
    if (!preg_match('/[\W_]/', $mdp)) {
        $_SESSION['error'][] = "Le mot de passe doit contenir au moins un caractère spécial (ex: !, @, #, _...).";
    }

    // Retourne true si aucune erreur
    return empty($_SESSION['error']);
}

// ─────────────────────────────────────────────
// NAVIGATION
// ─────────────────────────────────────────────

/** Redirige vers l'URL indiquée et stoppe l'exécution */
function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

// ─────────────────────────────────────────────
// PROTECTION CSRF
// ─────────────────────────────────────────────

/** Génère (ou retourne) le token CSRF de la session courante */
function generateCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Vérifie le token CSRF soumis par le formulaire */
function verifyCsrfToken(string $token) 
{
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Retourne un champ <input> CSRF prêt à insérer dans un formulaire.
 * Usage : <?= csrfField() ?>
 */
function csrfField()
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8')
        . '">';
}

function controlPostForm(){
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            return true;
        } else $_SESSION['error'] = "Erreur de sécurité (CSRF).";
    } 
    return false;
}

// ─────────────────────────────────────────────
// PROTECTION XSS
// ─────────────────────────────────────────────

/** Échappe une chaîne pour affichage sécurisé en HTML */
function e(?string $str)
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// ─────────────────────────────────────────────
// FLASH MESSAGES
// ─────────────────────────────────────────────

/**
 * Stocke un message flash en session.
 * Types : 'success', 'error', 'warning', 'info'
 */
function setFlash(string $type, string $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Retourne le message flash s'il existe et le supprime de la session.
 * Retourne null s'il n'y en a pas.
 */
function getFlash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
