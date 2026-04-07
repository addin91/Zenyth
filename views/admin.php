<?php
// Marqueur pour header.php / footer.php
$IS_ADMIN_PAGE = true;
include __DIR__ . '/header.php';
?>

<!-- ===== HERO ADMIN ===== -->
<section id="admin-hero" class="admin-hero">
    <div>
        <h1>Espace <span>Administrateur</span></h1>
        <p>Gestion des reservations, chambres, prestations, activites et factures du complexe Zenyth.</p>
    </div>
</section>

<!-- ===== DASHBOARD ADMIN (cache tant que pas connecte) ===== -->
<section id="admin-dashboard" class="admin-dashboard d-none">

    <!-- ===== ONGLET : DEMANDES DE RESERVATION ===== -->
    <div class="admin-panel active" id="admin-demandes">
        <div class="admin-panel-header">
            <h2 class="section-title">Demandes de reservation</h2>
            <hr class="section-divider">
            <p class="section-subtitle">Reservations en attente de validation</p>
        </div>

        <div class="admin-filters">
            <button class="btn btn-sm btn-outline-accent admin-filter-btn active" data-statut="en_attente">En attente</button>
            <button class="btn btn-sm btn-outline-accent admin-filter-btn" data-statut="all">Toutes</button>
        </div>

        <div id="admin-liste-demandes">
            <p class="text-muted">Chargement...</p>
        </div>
    </div>

    <!-- ===== ONGLET : RESERVATIONS ===== -->
    <div class="admin-panel" id="admin-reservations">
        <div class="admin-panel-header">
            <h2 class="section-title">Reservations</h2>
            <hr class="section-divider">
            <p class="section-subtitle">Historique des reservations validees et refusees</p>
        </div>

        <div class="admin-filters">
            <button class="btn btn-sm btn-outline-accent admin-filter-btn-res active" data-statut="validée">Validees</button>
            <button class="btn btn-sm btn-outline-accent admin-filter-btn-res" data-statut="refusée">Refusees</button>
            <button class="btn btn-sm btn-outline-accent admin-filter-btn-res" data-statut="all">Toutes</button>
        </div>

        <div id="admin-liste-reservations">
            <p class="text-muted">Chargement...</p>
        </div>
    </div>

    <!-- ===== ONGLET : CHAMBRES ===== -->
    <div class="admin-panel" id="admin-chambres">
        <div class="admin-panel-header">
            <h2 class="section-title">Gestion des chambres</h2>
            <hr class="section-divider">
            <p class="section-subtitle">Visualisation des chambres et disponibilites</p>
        </div>

        <!-- Filtre dispo par periode -->
        <div class="admin-card mb-4">
            <h5>Verifier les disponibilites</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="adm-ch-debut" class="form-label">Date de debut</label>
                    <input type="date" class="form-control" id="adm-ch-debut">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="adm-ch-fin" class="form-label">Date de fin</label>
                    <input type="date" class="form-control" id="adm-ch-fin">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="adm-ch-personnes" class="form-label">Nombre de personnes</label>
                    <input type="number" class="form-control" id="adm-ch-personnes" min="1" value="1">
                </div>
            </div>
            <button class="btn btn-accent" id="btn-adm-verifier-dispo">Verifier</button>
            <div id="adm-ch-resultat" class="mt-3"></div>
        </div>

        <h5>Toutes les chambres</h5>
        <div id="admin-liste-chambres">
            <p class="text-muted">Chargement...</p>
        </div>
    </div>

    <!-- ===== ONGLET : PRESTATIONS ===== -->
    <div class="admin-panel" id="admin-prestations">
        <div class="admin-panel-header">
            <h2 class="section-title">Gestion des prestations</h2>
            <hr class="section-divider">
            <p class="section-subtitle">Liste des prestations proposees</p>
        </div>

        <div id="admin-liste-prestations">
            <p class="text-muted">Chargement...</p>
        </div>
    </div>

    <!-- ===== ONGLET : ACTIVITES ===== -->
    <div class="admin-panel" id="admin-activites">
        <div class="admin-panel-header">
            <h2 class="section-title">Gestion des activites</h2>
            <hr class="section-divider">
            <p class="section-subtitle">Demandes en attente et programmation des activites prevues</p>
        </div>

        <!-- Sous-onglets -->
        <ul class="admin-subtabs">
            <li class="admin-subtab active" data-subtab="adm-act-demandes">Demandes en attente</li>
            <li class="admin-subtab" data-subtab="adm-act-prevues">Activites prevues</li>
        </ul>

        <!-- SOUS-ONGLET : Demandes -->
        <div class="admin-subpanel active" id="adm-act-demandes">
            <div class="admin-card mb-3">
                <label for="adm-act-filtre-date" class="form-label">Filtrer par date</label>
                <div class="row">
                    <div class="col-md-6">
                        <input type="date" class="form-control" id="adm-act-filtre-date">
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-outline-accent" id="btn-adm-act-reset">Reinitialiser</button>
                    </div>
                </div>
            </div>

            <div id="admin-liste-demandes-activites">
                <p class="text-muted">Chargement...</p>
            </div>
        </div>

        <!-- SOUS-ONGLET : Activites prevues -->
        <div class="admin-subpanel" id="adm-act-prevues">
            <div id="admin-liste-activites-prevues">
                <p class="text-muted">Chargement...</p>
            </div>
        </div>
    </div>

    <!-- ===== ONGLET : ANIMATEURS ===== -->
    <div class="admin-panel" id="admin-animateurs">
        <div class="admin-panel-header">
            <h2 class="section-title">Gestion des animateurs</h2>
            <hr class="section-divider">
            <p class="section-subtitle">Creer et gerer les animateurs disponibles pour les activites</p>
        </div>

        <div class="admin-card mb-4">
            <h5>Ajouter un animateur</h5>
            <form id="form-creation-animateur" method="POST">
                <?= csrfField() ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="anim-nom" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="anim-nom" name="nom" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="anim-prenom" class="form-label">Prenom</label>
                        <input type="text" class="form-control" id="anim-prenom" name="prenom" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="anim-specialite" class="form-label">Specialite</label>
                    <input type="text" class="form-control" id="anim-specialite" name="specialite" placeholder="Ex : tennis, natation, fitness collectif..." required>
                </div>
                <button type="submit" class="btn btn-accent">Ajouter l'animateur</button>
            </form>
        </div>

        <h5>Animateurs existants</h5>
        <div id="admin-liste-animateurs">
            <p class="text-muted">Chargement...</p>
        </div>
    </div>

    <!-- ===== ONGLET : CLIENTS ===== -->
    <div class="admin-panel" id="admin-clients">
        <div class="admin-panel-header">
            <h2 class="section-title">Gestion des clients</h2>
            <hr class="section-divider">
            <p class="section-subtitle">Liste des clients inscrits et leur statut</p>
        </div>

        <div class="admin-filters">
            <button class="btn btn-sm btn-outline-accent admin-filter-btn-client active" data-statut="all">Tous</button>
            <button class="btn btn-sm btn-outline-accent admin-filter-btn-client" data-statut="actif">Actifs</button>
            <button class="btn btn-sm btn-outline-accent admin-filter-btn-client" data-statut="invité">Invites</button>
            <button class="btn btn-sm btn-outline-accent admin-filter-btn-client" data-statut="inactif">Inactifs</button>
        </div>

        <div id="admin-liste-clients">
            <p class="text-muted">Chargement...</p>
        </div>
    </div>

    <!-- ===== ONGLET : FACTURES ===== -->
    <div class="admin-panel" id="admin-factures">
        <div class="admin-panel-header">
            <h2 class="section-title">Suivi des factures</h2>
            <hr class="section-divider">
            <p class="section-subtitle">Edition des arrhes, reductions et emission des factures</p>
        </div>

        <div class="admin-filters">
            <button class="btn btn-sm btn-outline-accent admin-filter-btn-fact active" data-statut="all">Toutes</button>
            <button class="btn btn-sm btn-outline-accent admin-filter-btn-fact" data-statut="Provisoire">Provisoires</button>
            <button class="btn btn-sm btn-outline-accent admin-filter-btn-fact" data-statut="emise">Emises</button>
            <button class="btn btn-sm btn-outline-accent admin-filter-btn-fact" data-statut="payée">Payees</button>
        </div>

        <div id="admin-liste-factures">
            <p class="text-muted">Chargement...</p>
        </div>
    </div>

</section>

<!-- ===== POPUP CONNEXION ADMIN ===== -->
<div id="popup-admin-connexion" class="popup-overlay">
    <div class="popup-panel">
        <h2 class="section-title">Connexion administrateur</h2>
        <hr class="section-divider">
        <p class="text-muted mb-3">Cet espace est reserve aux administrateurs Zenyth.</p>

        <form id="form-admin-connexion" method="POST">
            <?= csrfField() ?>
            <div class="mb-3">
                <label for="adm-email" class="form-label">Email</label>
                <input type="email" class="form-control" id="adm-email" name="email" required>
            </div>
            <div class="mb-4 password-field">
                <label for="adm-password" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="adm-password" name="password" required>
                <i class="toggle-password fa fa-eye" title="Afficher le mot de passe"></i>
            </div>
            <button type="submit" class="btn btn-accent w-100">Se connecter</button>
        </form>

        <div class="text-center mt-3">
            <a href="index.php" class="text-muted">&larr; Retour au site</a>
        </div>
    </div>
</div>

<!-- ===== MODAL : VALIDATION RESERVATION (message mail) ===== -->
<div id="popup-message-mail" class="popup-overlay">
    <div class="popup-panel popup-panel-large">
        <button class="popup-close">&times;</button>
        <h2 class="section-title">Message de confirmation</h2>
        <hr class="section-divider">
        <p class="text-muted">Copiez ce message dans votre logiciel de mail pour l'envoyer au client.</p>

        <div class="mb-3">
            <label class="form-label">Destinataire</label>
            <input type="text" class="form-control" id="mail-destinataire" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Sujet</label>
            <input type="text" class="form-control" id="mail-sujet" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea class="form-control" id="mail-corps" rows="12" readonly></textarea>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-accent" id="btn-copier-message">Copier le message</button>
            <a href="#" class="btn btn-outline-accent" id="btn-mailto" target="_blank">Ouvrir dans le client mail</a>
        </div>
    </div>
</div>

<!-- ===== MODAL : EDITER FACTURE (arrhes / reduction) ===== -->
<div id="popup-edit-facture" class="popup-overlay">
    <div class="popup-panel">
        <button class="popup-close">&times;</button>
        <h2 class="section-title">Editer la facture</h2>
        <hr class="section-divider">

        <form id="form-edit-facture" method="POST">
            <?= csrfField() ?>
            <input type="hidden" id="edit-fact-id" name="id_facture">

            <div class="mb-3">
                <label for="edit-fact-arrhes" class="form-label">Avoirs / arrhes (en &euro;)</label>
                <input type="number" class="form-control" id="edit-fact-arrhes" name="avoirs" min="0" step="0.01" value="0">
                <small class="text-muted">Apparaitra en negatif sur la facture du client.</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Reduction</label>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-accent btn-reduction" data-value="0">0 %</button>
                    <button type="button" class="btn btn-outline-accent btn-reduction" data-value="10">-10 %</button>
                    <button type="button" class="btn btn-outline-accent btn-reduction" data-value="20">-20 %</button>
                    <button type="button" class="btn btn-outline-accent btn-reduction" data-value="50">-50 %</button>
                </div>
                <input type="hidden" id="edit-fact-reduction" name="reduction" value="0">
            </div>

            <button type="submit" class="btn btn-accent w-100">Enregistrer</button>
        </form>
    </div>
</div>

<!-- ===== MODAL : VALIDER ACTIVITE (choix animateur) ===== -->
<div id="popup-valider-activite" class="popup-overlay">
    <div class="popup-panel">
        <button class="popup-close">&times;</button>
        <h2 class="section-title">Programmer l'activite</h2>
        <hr class="section-divider">

        <form id="form-valider-activite" method="POST">
            <?= csrfField() ?>
            <input type="hidden" id="va-id-demande" name="id_demande_activite">

            <div class="mb-3" id="va-info-demande">
                <p class="text-muted">Informations de la demande...</p>
            </div>

            <div class="mb-3">
                <label for="va-animateur" class="form-label">Choisir un animateur</label>
                <select class="form-select" id="va-animateur" name="id_animateur" required>
                    <option value="" selected disabled>Choisir...</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="va-message" class="form-label">Message / contrainte</label>
                <textarea class="form-control" id="va-message" name="message" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-accent w-100">Valider et programmer</button>
        </form>
    </div>
</div>

<!-- CSRF token global pour AJAX -->
<input type="hidden" id="csrf-global" value="<?= generateCsrfToken() ?>">

<!-- Toast container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;"></div>

<script>
    // Etat de session admin pour le JS
    var ADMIN_SESSION = <?= isset($_SESSION['admin_id']) ? json_encode([
        'connecte' => true,
        'nom' => $_SESSION['user_name'] ?? '',
        'id' => $_SESSION['admin_id']
    ]) : '{"connecte": false}' ?>;
</script>

<?php include __DIR__ . '/footer.php'; ?>
