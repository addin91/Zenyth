<?php
// On charge la config seulement si on a besoin de la BDD
// require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zenyth - Complexe Sportif de Rêve</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="#accueil">ZENYTH</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
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
        </div>
    </div>
</nav>

<!-- Conteneur principal -->
<main class="container py-4">
