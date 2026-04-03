<?php include 'views/header.php'; ?>

<!-- ===== ACCUEIL ===== -->
<section id="accueil">

    <div class="hero">
        <div>
            <h1><span>ZENYTH</span></h1>
            <p>Complexe sportif premium au bord du lac, entre montagnes et nature sauvage.</p>
            <a href="#" class="btn btn-accent btn-lg me-2" data-popup="reservation">Reserver un sejour</a>
            <a href="#chambres" class="btn btn-outline-accent btn-lg">Decouvrir</a>
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
            <div class="lieu-img-placeholder">
                Image du domaine
            </div>
        </div>
    </div>

</section>

<!-- ===== CHAMBRES ===== -->
<section id="chambres">
    <h2 class="section-title">Nos Chambres</h2>
    <p class="section-subtitle">Des espaces pensés pour le repos et la récupération</p>
    <hr class="section-divider">
    <div class="row" id="liste-chambres">
        <p class="text-muted">Chargement...</p>
    </div>
</section>

<!-- ===== ACTIVITES ===== -->
<section id="activites">
    <h2 class="section-title">Nos Activites</h2>
    <p class="section-subtitle">Sport, nature et dépassement de soi</p>
    <hr class="section-divider">
    <div class="row" id="liste-activites">
        <p class="text-muted">Chargement...</p>
    </div>
</section>

<!-- ===== PRESTATIONS ===== -->
<section id="prestations">
    <h2 class="section-title">Nos Prestations</h2>
    <p class="section-subtitle">Des services pour un sejour sur mesure</p>
    <hr class="section-divider">
    <div class="row" id="liste-prestations">
        <p class="text-muted">Chargement...</p>
    </div>
</section>

<!-- ===== CTA FINAL ===== -->
<div class="text-center mt-5 mb-4">
    <h3>Pret a vivre l'experience Zenyth ?</h3>
    <p class="text-muted mb-4">Reservez votre sejour et composez votre programme sportif ideal.</p>
    <a href="#" class="btn btn-accent btn-lg" data-popup="reservation">Demander une reservation</a>
</div>

<!-- ===== POPUP RESERVATION ===== -->
<div id="popup-reservation" class="popup-overlay">
    <div class="popup-panel">
        <button class="popup-close">&times;</button>
        <h2 class="section-title">Demande de reservation</h2>
        <hr class="section-divider">

        <form id="form-reservation" method="POST">

            <!-- Infos personnelles (visiteur non connecte) -->
            <div id="bloc-info-client">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="res-nom" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="res-nom" name="nom" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="res-prenom" class="form-label">Prenom</label>
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
                    <label for="res-date-debut" class="form-label">Date d'arrivee</label>
                    <input type="date" class="form-control" id="res-date-debut" name="dateDebut" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="res-date-fin" class="form-label">Date de depart</label>
                    <input type="date" class="form-control" id="res-date-fin" name="dateFin" required>
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

            <!-- Activites souhaitees -->
            <div class="mb-3">
                <label class="form-label">Activites souhaitees</label>
                <div id="res-activites" class="row">
                    <p class="text-muted">Chargement des activites...</p>
                </div>
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
            <div class="mb-3">
                <label for="co-email" class="form-label">Email</label>
                <input type="email" class="form-control" id="co-email" name="email" required>
            </div>
            <div class="mb-4">
                <label for="co-password" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="co-password" name="password" required>
            </div>
            <button type="submit" class="btn btn-accent w-100 mb-3">Se connecter</button>
            <div class="text-center">
                <a href="#" id="lien-mdp-oublie" class="text-muted">Mot de passe oublie ?</a>
            </div>
        </form>

        <!-- Formulaire mot de passe oublie (cache par defaut) -->
        <div id="bloc-mdp-oublie" style="display:none;">
            <hr class="my-4" style="border-color: var(--border);">
            <h5 class="section-title" style="font-size:1.2rem;">Reinitialiser le mot de passe</h5>
            <form id="form-mdp-oublie" method="POST">
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
    <div class="popup-panel">
        <button class="popup-close">&times;</button>
        <h2 class="section-title">Mon espace</h2>
        <hr class="section-divider">
        <p class="text-muted">A venir...</p>
    </div>
</div>

<?php include 'views/footer.php'; ?>
