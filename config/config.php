<?php
// Complexe touristique sportif — Système de réservation

// ─────────────────────────────────────────────
// SESSION
// ─────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false,   // local
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
    return isset($_SESSION['user_id']);
}

/** Vérifie si l'utilisateur connecté est un administrateur */
function isAdmin()
{
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
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