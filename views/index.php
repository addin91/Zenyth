<?php include __DIR__ . '/header.php'; ?>

<!-- ===== ACCUEIL ===== -->
<section id="accueil">

    <div class="hero">
        <div>
            <h1><span>ZENYTH</span></h1>
            <p>Complexe sportif premium au bord du lac, entre montagnes et nature sauvage.</p>
            <a href="#" class="btn btn-accent btn-lg me-2" data-popup="reservation">Reserver un sejour</a>
            <a href="#chambres" class="btn btn-outline-accent btn-lg">Découvrir</a>
        </div>
    </div>

    <div class="row mb-5 align-items-center">
        <div class="col-md-6 mb-4">
            <h2 class="section-title">Le domaine</h2>
            <hr class="section-divider">
            <p class="lieu-description">
                Niché au coeur d'un écrin de nature entre lac cristallin et sommets enneigés,
                Zenyth est un complexe sportif d'exception. Ici, chaque journée est une
                invitation à se dépasser : tennis au lever du soleil, sortie en bateau sur le lac,
                randonnée en altitude ou coaching personnalisé en salle.
            </p>
            <p class="lieu-description">
                Le soir venu, retrouvez le calme d'une chambre avec vue sur les montagnes,
                un diner préparé par notre chef, et la sérénité d'un lieu pensé pour le corps et l'esprit.
            </p>
        </div>
        <div class="col-md-6 mb-4">
            <img src="assets/img/domaine.png" alt="Vue du domaine Zenyth - lac et montagnes" class="lieu-img">
        </div>
    </div>

</section>

<!-- ===== CHAMBRES ===== -->
<section id="chambres">
    <h2 class="section-title">Nos Chambres</h2>
    <p class="section-subtitle">Des espaces pensés pour le repos et la récupération</p>
    <hr class="section-divider">
    <div class="carrousel-wrapper">
        <button class="carrousel-btn carrousel-prev" data-cible="liste-chambres"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M10 3L5 8L10 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
        <div class="carrousel-track" id="liste-chambres">
            <p class="text-muted">Chargement...</p>
        </div>
        <button class="carrousel-btn carrousel-next" data-cible="liste-chambres"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 3L11 8L6 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
    </div>
</section>

<!-- ===== ACTIVITES ===== -->
<section id="activites">
    <h2 class="section-title">Nos Activités</h2>
    <p class="section-subtitle">Sport, nature et dépassement de soi</p>
    <hr class="section-divider">
    <div class="carrousel-wrapper">
        <button class="carrousel-btn carrousel-prev" data-cible="liste-activites"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M10 3L5 8L10 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
        <div class="carrousel-track" id="liste-activites">
            <p class="text-muted">Chargement...</p>
        </div>
        <button class="carrousel-btn carrousel-next" data-cible="liste-activites"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 3L11 8L6 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
    </div>
</section>

<!-- ===== PRESTATIONS ===== -->
<section id="prestations">
    <h2 class="section-title">Nos Préstations</h2>
    <p class="section-subtitle">Des services pour un séjour sur mesure</p>
    <hr class="section-divider">
    <div class="carrousel-wrapper">
        <button class="carrousel-btn carrousel-prev" data-cible="liste-prestations"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M10 3L5 8L10 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
        <div class="carrousel-track" id="liste-prestations">
            <p class="text-muted">Chargement...</p>
        </div>
        <button class="carrousel-btn carrousel-next" data-cible="liste-prestations"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 3L11 8L6 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
    </div>
</section>

<!-- ===== CTA FINAL ===== -->
<div class="text-center mt-5 mb-4">
    <h3>Prêt à vivre l'expérience Zenyth ?</h3>
    <p class="text-muted mb-4">Réservez votre séjour et composez votre programme sportif idéal.</p>
    <a href="#" class="btn btn-accent btn-lg" data-popup="reservation">Demander une réservation</a>
</div>

<!-- ===== POPUP RESERVATION ===== -->
<div id="popup-reservation" class="popup-overlay">
    <div class="popup-panel">
        <button class="popup-close">&times;</button>
        <h2 class="section-title">Demande de reservation</h2>
        <hr class="section-divider">

        <form id="form-reservation" method="POST">
            <?= csrfField() ?>

            <!-- Infos personnelles (visiteur non connecte) -->
            <div id="bloc-info-client">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="res-nom" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="res-nom" name="nom" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="res-prenom" class="form-label">Prénom</label>
                        <input type="text" class="form-control" id="res-prenom" name="prenom" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="res-email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="res-email" name="email" required>
                </div>
            </div>

            <!-- Dates -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="res-date-debut" class="form-label">Date d'arrivée</label>
                    <input type="date" class="form-control" id="res-date-debut" name="dateDebut" max="9999-12-31" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="res-date-fin" class="form-label">Date de départ</label>
                    <input type="date" class="form-control" id="res-date-fin" name="dateFin" max="9999-12-31" required>
                </div>
            </div>

            <!-- Nombre de personnes -->
            <div class="mb-3">
                <label for="res-personnes" class="form-label">Nombre de personnes</label>
                <select class="form-select" id="res-personnes" name="nombrePersonne" required>
                    <option value="" selected disabled>Choisir...</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                    <option value="9">9</option>
                </select>
            </div>

            <!-- Choix de chambre -->
            <div class="mb-3">
                <label for="res-chambre" class="form-label">Type de chambre</label>
                <select class="form-select" id="res-chambre" name="id_chambre" required>
                    <option value="" selected disabled>Chargement des chambres...</option>
                </select>
            </div>

            <!-- Commentaire -->
            <div class="mb-4">
                <label for="res-commentaire" class="form-label">Commentaire / souhaits particuliers</label>
                <textarea class="form-control" id="res-commentaire" name="commentaire" rows="3" placeholder="Ex : arrivee tardive, allergie alimentaire..."></textarea>
            </div>

            <button type="submit" class="btn btn-accent w-100">Envoyer la demande</button>

        </form>
    </div>
</div>

<!-- ===== POPUP CONNEXION ===== -->
<div id="popup-connexion" class="popup-overlay">
    <div class="popup-panel">
        <button class="popup-close">&times;</button>
        <h2 class="section-title">Connexion</h2>
        <hr class="section-divider">

        <form id="form-connexion" method="POST">
            <?= csrfField() ?>
            <div class="mb-3">
                <label for="co-email" class="form-label">Email</label>
                <input type="email" class="form-control" id="co-email" name="email" required>
            </div>
            <div class="mb-4 password-field">
                <label for="co-password" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="co-password" name="password" required>
                <i class="toggle-password fa fa-eye" title="Afficher le mot de passe"></i>
            </div>
            <button type="submit" class="btn btn-accent w-100 mb-3">Se connecter</button>
            <div class="text-center">
                <a href="#" id="lien-mdp-oublie" class="text-muted">Mot de passe oublié ?</a>
            </div>
        </form>

        <!-- Formulaire mot de passe oublié (caché par défaut) -->
        <div id="bloc-mdp-oublie" style="display:none;">
            <hr class="my-4" style="border-color: var(--border);">
            <h5 class="section-title" style="font-size:1.2rem;">Réinitialiser le mot de passe</h5>
            <form id="form-mdp-oublie" method="POST">
                <?= csrfField() ?>
                <div class="mb-3">
                    <label for="oubli-email" class="form-label">Votre email</label>
                    <input type="email" class="form-control" id="oubli-email" name="email" required>
                </div>
                <button type="submit" class="btn btn-outline-accent w-100">Envoyer</button>
            </form>
        </div>
    </div>
</div>

<!-- ===== POPUP DASHBOARD ===== -->
<div id="popup-dashboard" class="popup-overlay">
    <div class="popup-panel popup-panel-large">
        <button class="popup-close">&times;</button>
        <h2 class="section-title">Mon espace</h2>
        <hr class="section-divider">

        <!-- Onglets -->
        <ul class="dash-tabs">
            <li class="dash-tab active" data-tab="dash-reservations">Mes reservations</li>
            <li class="dash-tab" data-tab="dash-prestations">Prestations</li>
            <li class="dash-tab" data-tab="dash-activites">Activites</li>
            <li class="dash-tab" data-tab="dash-factures">Mes factures</li>
            <li class="dash-tab" data-tab="dash-compte">Mon compte</li>
        </ul>

        <!-- ONGLET : Mes réservations -->
        <div class="dash-panel active" id="dash-reservations">
            <h5>Historique des réservations</h5>
            <div id="dash-liste-reservations">
                <p class="text-muted">Aucune réservation pour le moment.</p>
            </div>
        </div>

        <!-- ONGLET : Préstations -->
        <div class="dash-panel" id="dash-prestations">
            <h5>Ajouter des préstations</h5>
            <p class="section-subtitle">Ces services sont ajoutés directement à votre facture.</p>
            <div id="dash-liste-prestations">
                <p class="text-muted">Chargement...</p>
            </div>
        </div>

        <!-- ONGLET : Activités -->
        <div class="dash-panel" id="dash-activites">
            <h5>Demander une activité</h5>
            <form id="form-demande-activite" method="POST">
                <?= csrfField() ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="da-activite" class="form-label">Activite</label>
                        <select class="form-select" id="da-activite" name="id_activite" required>
                            <option value="" selected disabled>Choisir...</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="da-date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="da-date" name="date" max="9999-12-31" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="da-creneau" class="form-label">Créneau</label>
                        <select class="form-select" id="da-creneau" name="creneau" required>
                            <option value="heure">A l'heure</option>
                            <option value="demi-journee">Demi-journée</option>
                            <option value="journee">Journee entière</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="da-personnes" class="form-label">Personnes concernées</label>
                        <select class="form-select" id="da-personnes" name="nombrePersonne" required>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="da-message" class="form-label">Message / souhaits</label>
                    <textarea class="form-control" id="da-message" name="message" rows="2" placeholder="Ex : partie tranquille, pas de baignade..."></textarea>
                </div>
                <button type="submit" class="btn btn-accent">Envoyer la demande</button>
            </form>

            <hr class="my-4" style="border-color: var(--border);">
            <h5>Activités validées</h5>
            <div id="dash-activites-validees">
                <p class="text-muted">Aucune activité validée pour le moment.</p>
            </div>
        </div>

        <!-- ONGLET : Mes factures -->
        <div class="dash-panel" id="dash-factures">
            <h5>Historique des factures</h5>
            <div id="dash-liste-factures">
                <p class="text-muted">Aucune facture pour le moment.</p>
            </div>
        </div>

        <!-- ONGLET : Mon compte -->
        <div class="dash-panel" id="dash-compte">
            <h5>Informations personnelles</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nom</label>
                    <input type="text" class="form-control" id="info-nom" disabled>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Prenom</label>
                    <input type="text" class="form-control" id="info-prenom" disabled>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" id="info-email" disabled>
            </div>

            <hr class="my-4" style="border-color: var(--border);">

            <h5>Changer le mot de passe</h5>
            <form id="form-change-mdp" method="POST">
                <?= csrfField() ?>
                <div class="mb-3 password-field">
                    <label for="mdp-ancien" class="form-label">Mot de passe actuel</label>
                    <input type="password" class="form-control" id="mdp-ancien" name="ancienPassword" required>
                    <i class="toggle-password fa fa-eye" title="Afficher le mot de passe"></i>

                </div>
                <div class="mb-3 password-field">
                    <label for="mdp-nouveau" class="form-label">Nouveau mot de passe</label>
                    <input type="password" class="form-control" id="mdp-nouveau" name="nouvellePassword" required>
                    <i class="toggle-password fa fa-eye" title="Afficher le mot de passe"></i>
                    <small class="text-muted">Min. 8 caracteres, 1 majuscule, 1 chiffre, 1 caractere special</small>
                </div>
                <div class="mb-3 password-field">
                    <label for="mdp-confirmer" class="form-label">Confirmer le nouveau mot de passe</label>
                    <input type="password" class="form-control" id="mdp-confirmer" required>
                    <i class="toggle-password fa fa-eye" title="Afficher le mot de passe"></i>
                </div>
                <button type="submit" class="btn btn-accent">Modifier</button>
            </form>
        </div>

    </div>
</div>

<!-- CSRF token global (pour les requetes AJAX hors formulaire) -->
<input type="hidden" id="csrf-global" value="<?= e(generateCsrfToken()) ?>">

<!-- Toast container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;"></div>

<script>
    var SESSION_USER = <?= isset($_SESSION['user_id']) ? json_encode([
        'connecte' => true,
        'nom' => $_SESSION['user_name'] ?? '',
        'prenom' => $_SESSION['user_prenom'] ?? '',
        'email' => $_SESSION['user_email'] ?? ''
    ]) : '{"connecte": false}' ?>;
</script>

<?php include __DIR__ . '/footer.php'; ?>
