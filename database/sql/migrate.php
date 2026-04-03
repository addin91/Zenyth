<?php
/**
 * migrate.php — Exécution de la migration SQL
 * Complexe touristique sportif — Système de réservation
 *
 * Lit le fichier bd.sql et exécute chaque instruction.
 * Usage CLI  : php migrate.php
 * Usage HTTP : http://votre-domaine/migrate.php  (à supprimer après usage)
 */


require_once __DIR__ . '/config/config.php'; // récupère $pdo existant

// ─────────────────────────────────────────────
// DÉTECTION DU MODE
// ─────────────────────────────────────────────
$estModeCli = PHP_SAPI === 'cli';

// ─────────────────────────────────────────────
// FONCTIONS D'AFFICHAGE
// ─────────────────────────────────────────────
function afficherMessage(string $emoji, string $message){
    global $estModeCli;
    if ($estModeCli) {
        echo $emoji . ' ' . $message . PHP_EOL;
    } else {
        $couleur = match($emoji) {
            '✅'    => '#2e7d32',
            '❌'    => '#c62828',
            '⚠️'   => '#e65100',
            default => '#333',
        };
        printf(
            '<p style="font-family:monospace;color:%s">%s %s</p>' . PHP_EOL,
            htmlspecialchars($couleur),
            $emoji,
            htmlspecialchars($message)
        );
    }
}

function afficherSeparateur(){
    global $estModeCli;
    echo $estModeCli
        ? str_repeat('─', 56) . PHP_EOL
        : '<hr style="border:1px solid #ccc">' . PHP_EOL;
}

// ─────────────────────────────────────────────
// AFFICHAGE EN MODE WEB
// ─────────────────────────────────────────────
if (!$estModeCli) {
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8">'
       . '<title>Migration BDD</title></head><body>'
       . '<h2 style="font-family:sans-serif">Migration — Complexe sportif</h2>' . PHP_EOL;
}

// ─────────────────────────────────────────────
// VÉRIFICATION DU FICHIER SQL
// ─────────────────────────────────────────────
define('FICHIER_SQL', __DIR__ . '/bd.sql');

if (!file_exists(FICHIER_SQL)) {
    afficherMessage('❌', 'Fichier introuvable : ' . FICHIER_SQL);
    exit(1);
}

$contenuSql = file_get_contents(FICHIER_SQL);
if ($contenuSql === false || trim($contenuSql) === '') {
    afficherMessage('❌', 'Impossible de lire bd.sql ou fichier vide.');
    exit(1);
}

afficherMessage('✅', 'Fichier bd.sql chargé (' . strlen($contenuSql) . ' octets).');
afficherSeparateur();

// ─────────────────────────────────────────────
// NETTOYAGE ET DÉCOUPAGE DU SQL
// ─────────────────────────────────────────────
$contenuSql = preg_replace('/--[^\n]*\n/', "\n", $contenuSql); // commentaires -- 
$contenuSql = preg_replace('/\/\*.*?\*\//s', '', $contenuSql); // commentaires /* */

$instructionsSql = array_filter(
    array_map('trim', explode(';', $contenuSql)),
    fn(string $instruction) => $instruction !== ''
);

$totalInstructions = count($instructionsSql);
$succes = 0;
$erreurs = 0;

afficherMessage('✅', "Nombre d'instructions détectées : {$totalInstructions}");
afficherSeparateur();

// ─────────────────────────────────────────────
// EXÉCUTION DES INSTRUCTIONS
// ─────────────────────────────────────────────
foreach ($instructionsSql as $index => $instruction) {
    $lignes = array_filter(explode("\n", $instruction));
    $premiereLigne = trim(reset($lignes));
    $label = mb_strlen($premiereLigne) > 80
        ? mb_substr($premiereLigne, 0, 77) . '…'
        : $premiereLigne;

    try {
        $pdo->exec($instruction); // utilise le PDO de config.php
        afficherMessage('✅', "[{$index}] {$label}");
        $succes++;
    } catch (PDOException $exception) {
        afficherMessage('❌', "[{$index}] {$label}");
        afficherMessage('❌', "     → " . $exception->getMessage());
        $erreurs++;
    }
}

// ─────────────────────────────────────────────
// RÉSUMÉ
// ─────────────────────────────────────────────
afficherSeparateur();
afficherMessage('✅', "Migration terminée — {$succes}/{$totalInstructions} instruction(s) réussie(s).");

if ($erreurs > 0) {
    afficherMessage('⚠️', "{$erreurs} erreur(s) rencontrée(s). Consultez les messages ci-dessus.");
} else {
    afficherMessage('✅', 'Aucune erreur. La base de données est prête.');
}

afficherMessage('⚠️', 'Pensez à supprimer ou sécuriser ce fichier en production !');

if (!$estModeCli) {
    echo '</body></html>' . PHP_EOL;
}