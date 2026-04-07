<?php
// On charge la config seulement si on a besoin de la BDD
// require_once __DIR__ . '/../config/config.php';
$pageTitle = (isset($IS_ADMIN_PAGE) && $IS_ADMIN_PAGE === true)
    ? 'Zenyth - Espace Administrateur'
    : 'Zenyth - Complexe Sportif de Rêve';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php $isAdminPage = isset($IS_ADMIN_PAGE) && $IS_ADMIN_PAGE === true; ?>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg sticky-top<?= $isAdminPage ? ' navbar-admin' : '' ?>">
    <div class="container">
        <a class="navbar-brand" href="<?= $isAdminPage ? 'index.php' : '#accueil' ?>">
            ZENYTH<?= $isAdminPage ? ' <span class="badge-admin">Admin</span>' : '' ?>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <?php if ($isAdminPage): ?>
                <!-- ===== NAV ADMIN ===== -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link admin-tab-link active" href="#" data-tab="admin-demandes">Demandes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link admin-tab-link" href="#" data-tab="admin-reservations">Reservations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link admin-tab-link" href="#" data-tab="admin-chambres">Chambres</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link admin-tab-link" href="#" data-tab="admin-prestations">Prestations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link admin-tab-link" href="#" data-tab="admin-activites">Activites</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link admin-tab-link" href="#" data-tab="admin-animateurs">Animateurs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link admin-tab-link" href="#" data-tab="admin-factures">Factures</a>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <li class="nav-item d-none" id="admin-nav-info">
                        <span class="nav-link admin-user-info">
                            <span id="admin-user-name"></span>
                        </span>
                    </li>
                    <li class="nav-item d-none" id="admin-nav-deconnexion">
                        <a class="nav-link" href="#" id="btn-admin-deconnexion">Deconnexion</a>
                    </li>
                </ul>

            <?php else: ?>
                <!-- ===== NAV CLIENT ===== -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#accueil">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#chambres">Chambres</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#activites">Activites</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#prestations">Prestations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-popup="reservation">Reserver</a>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <li class="nav-item" id="nav-connexion">
                        <a class="nav-link" href="#" data-popup="connexion">Connexion</a>
                    </li>
                    <li class="nav-item d-none" id="nav-dashboard">
                        <a class="nav-link" href="#" data-popup="dashboard">Mon espace</a>
                    </li>
                    <li class="nav-item d-none" id="nav-deconnexion">
                        <a class="nav-link" href="#" id="btn-deconnexion">Deconnexion</a>
                    </li>
                </ul>
            <?php endif; ?>

        </div>
    </div>
</nav>

<!-- Conteneur principal -->
<main class="container py-4">
