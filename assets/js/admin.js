$(document).ready(function() {

    // ===== TOGGLE AFFICHAGE MOT DE PASSE (icone oeil) =====
    // Meme comportement que app.js pour les inputs password .password-field > .toggle-password
    $(document).on('click', '.toggle-password', function() {
        var $input = $(this).parent().find('input');
        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
        } else {
            $input.attr('type', 'password');
        }
        $(this).toggleClass('fa-eye fa-eye-slash');
    });

    // ===== POPUPS (memes handlers que app.js) =====
    $('.popup-close').on('click', function() {
        $(this).closest('.popup-overlay').removeClass('active');
    });

    $('.popup-overlay').on('click', function(e) {
        if (e.target === this) {
            $(this).removeClass('active');
        }
    });

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('.popup-overlay.active').removeClass('active');
        }
    });

    // ===== AFFICHAGE INITIAL : connecte ou popup connexion =====
    if (typeof ADMIN_SESSION !== 'undefined' && ADMIN_SESSION.connecte) {
        afficherDashboardAdmin(ADMIN_SESSION.nom);
    } else {
        $('#popup-admin-connexion').addClass('active');
    }

    // ===== FORMULAIRE CONNEXION ADMIN =====
    // La session serveur est mise a jour par PHP des la reponse OK,
    // donc les AJAX suivants seront bien authentifies. Pas de reload necessaire.
    $('#form-admin-connexion').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: 'index.php?action=loginadmin',
            method: 'POST',
            data: formData,
            dataType: 'json'
        }).done(function(res) {
            if (res && res.success) {
                var nom = (res.data && res.data.nom) || '';
                $('#popup-admin-connexion').removeClass('active');
                afficherDashboardAdmin(nom);
                showToast('Bienvenue ' + nom + ' !');
            } else {
                showToast((res && res.error) || 'Identifiants incorrects.', 'error');
            }
        }).fail(function() {
            showToast('Erreur serveur lors de la connexion.', 'error');
        });
    });

    // ===== DECONNEXION ADMIN =====
    // On utilise la route 'logout' existante qui detruit toute la session
    // ($_SESSION = []; session_destroy()). On recharge ensuite la page
    // pour que PHP regenere ADMIN_SESSION = {connecte: false} et que la
    // popup de connexion s'affiche proprement.
    $('#btn-admin-deconnexion').on('click', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'index.php?action=logout',
            method: 'POST',
            data: { csrf_token: $('#csrf-global').val() },
            dataType: 'json'
        }).always(function() {
            // Succes ou echec : on recharge pour forcer la relecture serveur
            window.location.href = 'index.php?action=admin';
        });
    });

    // ===== NAVIGATION ONGLETS =====
    $('.admin-tab-link').on('click', function(e) {
        e.preventDefault();
        var tab = $(this).data('tab');
        $('.admin-tab-link').removeClass('active');
        $(this).addClass('active');
        $('.admin-panel').removeClass('active');
        $('#' + tab).addClass('active');

        // Recharger les donnees au passage sur l'onglet
        switch (tab) {
            case 'admin-demandes': chargerDemandes('en_attente'); break;
            case 'admin-reservations': chargerReservations('validée'); break;
            case 'admin-chambres': chargerChambres(); break;
            case 'admin-prestations': chargerPrestations(); break;
            case 'admin-activites': chargerDemandesActivites(null); chargerActivitesPrevues(); break;
            case 'admin-animateurs': chargerAnimateurs(); break;
            case 'admin-clients': chargerClients('all'); break;
            case 'admin-factures': chargerFactures('all'); break;
        }
    });

    // ===== SOUS-ONGLETS ACTIVITES =====
    $('.admin-subtab').on('click', function() {
        var sub = $(this).data('subtab');
        $('.admin-subtab').removeClass('active');
        $(this).addClass('active');
        $('.admin-subpanel').removeClass('active');
        $('#' + sub).addClass('active');
    });

    // ===== FILTRES STATUT =====
    $('.admin-filter-btn').on('click', function() {
        $('.admin-filter-btn').removeClass('active');
        $(this).addClass('active');
        chargerDemandes($(this).data('statut'));
    });

    $('.admin-filter-btn-res').on('click', function() {
        $('.admin-filter-btn-res').removeClass('active');
        $(this).addClass('active');
        chargerReservations($(this).data('statut'));
    });

    $('.admin-filter-btn-fact').on('click', function() {
        $('.admin-filter-btn-fact').removeClass('active');
        $(this).addClass('active');
        chargerFactures($(this).data('statut'));
    });

    $('.admin-filter-btn-client').on('click', function() {
        $('.admin-filter-btn-client').removeClass('active');
        $(this).addClass('active');
        chargerClients($(this).data('statut'));
    });

    // ===== FILTRE DATE ACTIVITES =====
    $('#adm-act-filtre-date').on('change', function() {
        chargerDemandesActivites($(this).val());
    });
    $('#btn-adm-act-reset').on('click', function() {
        $('#adm-act-filtre-date').val('');
        chargerDemandesActivites(null);
    });

    // ===== VERIFIER DISPOS CHAMBRES =====
    $('#btn-adm-verifier-dispo').on('click', function() {
        var debut = $('#adm-ch-debut').val();
        var fin = $('#adm-ch-fin').val();
        var personnes = parseInt($('#adm-ch-personnes').val()) || 1;

        if (!debut || !fin) {
            showToast('Selectionnez une periode complete.', 'error');
            return;
        }
        if (debut >= fin) {
            showToast('La date de fin doit etre apres la date de debut.', 'error');
            return;
        }

        $.ajax({
            url: 'index.php?action=chambresdisponibles&date_debut=' + debut + '&date_fin=' + fin + '&nombre_personnes=' + personnes,
            method: 'GET',
            dataType: 'json'
        }).done(function(res) {
            if (res.success && res.data && res.data.length > 0) {
                var nb = res.data.length;
                var html = '<div class="admin-dispo-header">';
                html += '<span class="admin-badge badge-validee">' + nb + ' chambre' + (nb > 1 ? 's' : '') + ' disponible' + (nb > 1 ? 's' : '') + '</span>';
                html += '<small class="text-muted">du <strong>' + debut + '</strong> au <strong>' + fin + '</strong> &mdash; ' + personnes + ' pers.</small>';
                html += '</div>';
                html += '<div class="admin-table-wrapper mt-2"><table class="admin-table">';
                html += '<thead><tr><th>#</th><th>Nom</th><th>Type</th><th>Capacite</th><th>Prix/nuit</th></tr></thead><tbody>';
                $.each(res.data, function(i, ch) {
                    html += '<tr>';
                    html += '<td>#' + ch.id_chambre + '</td>';
                    html += '<td><strong>' + ch.nom_chambre + '</strong></td>';
                    html += '<td>' + ch.type_chambre + '</td>';
                    html += '<td>' + ch.capacite + ' pers.</td>';
                    html += '<td>' + ch.prix_nuit + ' &euro;</td>';
                    html += '</tr>';
                });
                html += '</tbody></table></div>';
                $('#adm-ch-resultat').html(html);
            } else {
                $('#adm-ch-resultat').html('<div class="admin-empty">Aucune chambre disponible sur cette periode.</div>');
            }
        }).fail(function() {
            $('#adm-ch-resultat').html('<div class="admin-error">Erreur lors de la verification.</div>');
        });
    });

    // ===== ACTIONS DEMANDES (delegation) =====
    // Le backend envoie lui-meme le mail de bienvenue (MailService::envoiePassword)
    // donc pas besoin d'ouvrir le popup message mail cote front.
    $('#admin-liste-demandes').on('click', '.btn-accepter-resa', function() {
        var id = $(this).data('id');
        if (!confirm('Confirmer l\'acceptation de cette reservation ? Un compte client va etre cree et un mail envoye.')) return;

        $.ajax({
            url: 'index.php?action=adminaccepterreservation',
            method: 'POST',
            data: { id_reservation: id, csrf_token: $('#csrf-global').val() },
            dataType: 'json'
        }).done(function(res) {
            if (res && res.success) {
                showToast(res.message || 'Reservation acceptee.');
                chargerDemandes('en_attente');
            } else {
                showToast((res && res.error) || 'Erreur lors de l\'acceptation.', 'error');
            }
        }).fail(function() {
            showToast('Erreur serveur lors de l\'acceptation.', 'error');
        });
    });

    $('#admin-liste-demandes').on('click', '.btn-refuser-resa', function() {
        var id = $(this).data('id');
        if (!confirm('Refuser cette reservation ?')) return;

        $.ajax({
            url: 'index.php?action=adminrefuserreservation',
            method: 'POST',
            data: { id_reservation: id, csrf_token: $('#csrf-global').val() },
            dataType: 'json'
        }).done(function(res) {
            if (res && res.success) {
                showToast(res.message || 'Reservation refusee.');
                chargerDemandes('en_attente');
            } else {
                showToast((res && res.error) || 'Erreur lors du refus.', 'error');
            }
        }).fail(function() {
            showToast('Erreur serveur lors du refus.', 'error');
        });
    });

    // ===== VALIDER ACTIVITE (ouverture modal) =====
    $('#admin-liste-demandes-activites').on('click', '.btn-valider-activite', function() {
        var id = $(this).data('id');
        var date = $(this).data('date');
        var creneau = $(this).data('creneau');
        var personnes = $(this).data('personnes');
        var nomActivite = $(this).data('nom');

        $('#va-id-demande').val(id);
        $('#va-info-demande').html(
            '<strong>' + nomActivite + '</strong><br>' +
            '<small class="text-muted">' + date + ' &mdash; ' + creneau + ' &mdash; ' + personnes + ' pers.</small>'
        );

        // Charger les animateurs dans le select
        chargerAnimateursPourSelect();
        $('#popup-valider-activite').addClass('active');
    });

    // NOTE : ces endpoints ne sont PAS encore routes cote backend.
    // Les formulaires restent fonctionnels (validation, UX) et .fail()
    // affichera un toast explicite en attendant que le backend les expose.

    $('#form-valider-activite').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: 'index.php?action=adminvaliderdemandeactivite',
            method: 'POST',
            data: formData,
            dataType: 'json'
        }).done(function(res) {
            if (res && res.success) {
                showToast('Activite programmee.');
                $('#popup-valider-activite').removeClass('active');
                chargerDemandesActivites($('#adm-act-filtre-date').val() || null);
                chargerActivitesPrevues();
            } else {
                showToast((res && res.error) || 'Erreur.', 'error');
            }
        }).fail(function() {
            showToast('En attente backend : validation d\'activite.', 'error');
        });
    });

    // ===== CREATION ANIMATEUR =====
    $('#form-creation-animateur').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: 'index.php?action=admincreeranimateur',
            method: 'POST',
            data: formData,
            dataType: 'json'
        }).done(function(res) {
            if (res && res.success) {
                showToast('Animateur ajoute.');
                $('#form-creation-animateur')[0].reset();
                chargerAnimateurs();
            } else {
                showToast((res && res.error) || 'Erreur.', 'error');
            }
        }).fail(function() {
            showToast('En attente backend : creation d\'animateur.', 'error');
        });
    });

    // Suppression animateur (delegation)
    $('#admin-liste-animateurs').on('click', '.btn-supprimer-animateur', function() {
        var id = $(this).data('id');
        if (!confirm('Supprimer cet animateur ?')) return;

        $.ajax({
            url: 'index.php?action=adminsupprimeranimateur',
            method: 'POST',
            data: { id_animateur: id, csrf_token: $('#csrf-global').val() },
            dataType: 'json'
        }).done(function(res) {
            if (res && res.success) {
                showToast('Animateur supprime.');
                chargerAnimateurs();
            } else {
                showToast((res && res.error) || 'Erreur.', 'error');
            }
        }).fail(function() {
            showToast('En attente backend : suppression d\'animateur.', 'error');
        });
    });

    // ===== EDITION FACTURE (ouverture modal) =====
    $('#admin-liste-factures').on('click', '.btn-editer-facture', function() {
        var id = $(this).data('id');
        var arrhes = $(this).data('arrhes') || 0;
        var reduction = $(this).data('reduction') || 0;

        $('#edit-fact-id').val(id);
        $('#edit-fact-arrhes').val(arrhes);
        $('#edit-fact-reduction').val(reduction);

        // Marquer le bouton de reduction selectionne
        $('.btn-reduction').removeClass('active');
        $('.btn-reduction[data-value="' + reduction + '"]').addClass('active');

        $('#popup-edit-facture').addClass('active');
    });

    $('.btn-reduction').on('click', function() {
        $('.btn-reduction').removeClass('active');
        $(this).addClass('active');
        $('#edit-fact-reduction').val($(this).data('value'));
    });

    $('#form-edit-facture').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: 'index.php?action=adminediterfacture',
            method: 'POST',
            data: formData,
            dataType: 'json'
        }).done(function(res) {
            if (res && res.success) {
                showToast('Facture mise a jour.');
                $('#popup-edit-facture').removeClass('active');
                chargerFactures('all');
            } else {
                showToast((res && res.error) || 'Erreur.', 'error');
            }
        }).fail(function() {
            showToast('En attente backend : edition de facture.', 'error');
        });
    });

    // Emettre une facture
    $('#admin-liste-factures').on('click', '.btn-emettre-facture', function() {
        var id = $(this).data('id');
        if (!confirm('Emettre cette facture definitivement ?')) return;

        $.ajax({
            url: 'index.php?action=adminemettrefacture',
            method: 'POST',
            data: { id_facture: id, csrf_token: $('#csrf-global').val() },
            dataType: 'json'
        }).done(function(res) {
            if (res && res.success) {
                showToast('Facture emise.');
                chargerFactures('all');
            } else {
                showToast((res && res.error) || 'Erreur.', 'error');
            }
        }).fail(function() {
            showToast('En attente backend : emission de facture.', 'error');
        });
    });

    // ===== COPIER MESSAGE MAIL =====
    $('#btn-copier-message').on('click', function() {
        var corps = $('#mail-corps').val();
        if (navigator.clipboard) {
            navigator.clipboard.writeText(corps).then(function() {
                showToast('Message copie dans le presse-papier.');
            });
        } else {
            $('#mail-corps').select();
            document.execCommand('copy');
            showToast('Message copie.');
        }
    });

});

// =============================================
//  FONCTIONS D'AFFICHAGE
// =============================================

function afficherDashboardAdmin(nom) {
    $('#admin-dashboard').removeClass('d-none');
    $('#admin-nav-info').removeClass('d-none');
    $('#admin-nav-deconnexion').removeClass('d-none');
    $('#admin-user-name').text(nom || '');
    // Charger l'onglet par defaut
    chargerDemandes('en_attente');
}

// =============================================
//  CHARGEMENT DES DONNEES (AJAX)
// =============================================

function chargerDemandes(statut) {
    // Les statuts contiennent des accents cote backend (validée, refusée) : on encode
    var url = 'index.php?action=adminrecuperereservations&statut=' + encodeURIComponent(statut || 'en_attente');
    $('#admin-liste-demandes').html('<p class="text-muted">Chargement...</p>');

    $.ajax({ url: url, method: 'GET', dataType: 'json' })
    .done(function(res) {
        if (!res || res.success !== true) {
            $('#admin-liste-demandes').html('<div class="admin-error">' + ((res && res.error) || 'Reponse backend invalide.') + '</div>');
            return;
        }
        var liste = res.data ? Object.values(res.data) : [];
        if (liste.length > 0) {
            var html = '';
            $.each(liste, function(i, r) {
                html += renderCarteDemande(r);
            });
            $('#admin-liste-demandes').html(html);
        } else {
            $('#admin-liste-demandes').html('<div class="admin-empty">Aucune demande.</div>');
        }
    })
    .fail(function() {
        $('#admin-liste-demandes').html('<div class="admin-error">Erreur de chargement des demandes (session expiree ou backend indisponible).</div>');
    });
}

function chargerReservations(statut) {
    // Les statuts contiennent des accents cote backend (validée, refusée) : on encode
    var url = 'index.php?action=adminrecuperereservations&statut=' + encodeURIComponent(statut || 'all');
    $('#admin-liste-reservations').html('<p class="text-muted">Chargement...</p>');

    $.ajax({ url: url, method: 'GET', dataType: 'json' })
    .done(function(res) {
        if (!res || res.success !== true) {
            $('#admin-liste-reservations').html('<div class="admin-error">' + ((res && res.error) || 'Reponse backend invalide.') + '</div>');
            return;
        }
        var liste = res.data ? Object.values(res.data) : [];
        if (liste.length > 0) {
            var html = '<div class="admin-table-wrapper"><table class="admin-table">';
            html += '<thead><tr><th>#</th><th>Client</th><th>Dates</th><th>Pers.</th><th>Statut</th></tr></thead><tbody>';
            $.each(liste, function(i, r) {
                var idRes = r.id_reservation || r.id || '?';
                var clientLabel = r.nom_temp || r.email_temp || (r.id_client ? 'Client #' + r.id_client : '—');
                var statutBrut = r.statut || 'en_attente';
                var statutCss = statutBrut.replace(/[éè]/g, 'e').replace('_', '-');
                html += '<tr>';
                html += '<td>#' + idRes + '</td>';
                html += '<td>' + clientLabel + '</td>';
                html += '<td>' + (r.date_debut || '?') + '<br><small>' + (r.date_fin || '?') + '</small></td>';
                html += '<td>' + (r.nombre_personnes || '?') + '</td>';
                html += '<td><span class="admin-badge badge-' + statutCss + '">' + statutBrut + '</span></td>';
                html += '</tr>';
            });
            html += '</tbody></table></div>';
            $('#admin-liste-reservations').html(html);
        } else {
            $('#admin-liste-reservations').html('<div class="admin-empty">Aucune reservation.</div>');
        }
    })
    .fail(function() {
        $('#admin-liste-reservations').html('<div class="admin-error">Erreur de chargement des reservations (session expiree ou backend indisponible).</div>');
    });
}

function chargerChambres() {
    $('#admin-liste-chambres').html('<p class="text-muted">Chargement...</p>');

    $.ajax({ url: 'index.php?action=apichambre', method: 'GET', dataType: 'json' })
    .done(function(chambres) {
        if (chambres && Object.values(chambres).length > 0) {
            var html = '<div class="admin-table-wrapper"><table class="admin-table">';
            html += '<thead><tr><th>#</th><th>Nom</th><th>Type</th><th>Capacite</th><th>Prix/nuit</th></tr></thead><tbody>';
            $.each(chambres, function(i, ch) {
                html += '<tr>';
                html += '<td>#' + ch.id_chambre + '</td>';
                html += '<td><strong>' + ch.nom_chambre + '</strong></td>';
                html += '<td>' + ch.type_chambre + '</td>';
                html += '<td>' + ch.capacite + ' pers.</td>';
                html += '<td>' + ch.prix_nuit + ' &euro;</td>';
                html += '</tr>';
            });
            html += '</tbody></table></div>';
            $('#admin-liste-chambres').html(html);
        } else {
            $('#admin-liste-chambres').html('<div class="admin-empty">Aucune chambre.</div>');
        }
    })
    .fail(function() {
        $('#admin-liste-chambres').html('<div class="admin-error">Erreur de chargement des chambres.</div>');
    });
}

function chargerPrestations() {
    $('#admin-liste-prestations').html('<p class="text-muted">Chargement...</p>');

    $.ajax({ url: 'index.php?action=apiprestation', method: 'GET', dataType: 'json' })
    .done(function(prestas) {
        if (prestas && Object.values(prestas).length > 0) {
            var html = '<div class="admin-table-wrapper"><table class="admin-table">';
            html += '<thead><tr><th>#</th><th>Nom</th><th>Description</th><th>Prix unitaire</th></tr></thead><tbody>';
            $.each(prestas, function(i, p) {
                html += '<tr>';
                html += '<td>#' + p.id_prestation + '</td>';
                html += '<td><strong>' + p.nom + '</strong></td>';
                html += '<td>' + (p.description || '') + '</td>';
                html += '<td>' + p.prix_unitaire + ' &euro;</td>';
                html += '</tr>';
            });
            html += '</tbody></table></div>';
            $('#admin-liste-prestations').html(html);
        } else {
            $('#admin-liste-prestations').html('<div class="admin-empty">Aucune prestation.</div>');
        }
    })
    .fail(function() {
        $('#admin-liste-prestations').html('<div class="admin-error">Erreur de chargement des prestations.</div>');
    });
}

function chargerDemandesActivites(date) {
    $('#admin-liste-demandes-activites').html('<p class="text-muted">Chargement...</p>');

    // Les APIs renvoient des objets bruts (pas {success, data})
    // On charge en parallele demandes + activites pour resoudre les noms
    $.when(
        $.ajax({ url: 'index.php?action=apidemandesactivites', method: 'GET', dataType: 'json' }),
        $.ajax({ url: 'index.php?action=apiactivite', method: 'GET', dataType: 'json' })
    ).done(function(demandesArr, activitesArr) {
        var demandes = demandesArr[0];
        var activites = activitesArr[0];
        var liste = demandes ? Object.values(demandes) : [];

        // Lookup activites par id
        var actById = {};
        if (activites) {
            $.each(Object.values(activites), function(i, a) {
                actById[String(a.id_activite)] = a;
            });
        }

        // On ne garde que les demandes en attente
        liste = liste.filter(function(d) { return (d.statut || '') === 'en_attente'; });

        // Filtrage date cote client
        if (date) {
            liste = liste.filter(function(d) { return d.date === date; });
        }

        if (liste.length > 0) {
            var html = '<div class="admin-table-wrapper"><table class="admin-table">';
            html += '<thead><tr><th>#</th><th>Activite</th><th>Date</th><th>Creneau</th><th>Pers.</th><th>Message</th><th>Action</th></tr></thead><tbody>';
            $.each(liste, function(i, d) {
                var id = d.id_demande || d.id || '';
                var nbPers = d.nombre_personnes_concernees || d.nombre_personnes || '?';
                var act = actById[String(d.id_activite)];
                var nomAct = act ? act.nom : ('Activite #' + (d.id_activite || '?'));
                html += '<tr>';
                html += '<td>#' + (id || '?') + '</td>';
                html += '<td><strong>' + nomAct + '</strong></td>';
                html += '<td>' + (d.date || '?') + '</td>';
                html += '<td>' + (d.creneau || '?') + '</td>';
                html += '<td>' + nbPers + '</td>';
                html += '<td><small>' + (d.message || '') + '</small></td>';
                html += '<td><button class="btn btn-sm btn-accent btn-valider-activite"';
                html += ' data-id="' + id + '"';
                html += ' data-date="' + (d.date || '') + '"';
                html += ' data-creneau="' + (d.creneau || '') + '"';
                html += ' data-personnes="' + nbPers + '"';
                html += ' data-nom="' + nomAct + '">Programmer</button></td>';
                html += '</tr>';
            });
            html += '</tbody></table></div>';
            $('#admin-liste-demandes-activites').html(html);
        } else {
            $('#admin-liste-demandes-activites').html('<div class="admin-empty">Aucune demande en attente.</div>');
        }
    })
    .fail(function() {
        $('#admin-liste-demandes-activites').html('<div class="admin-error">Erreur de chargement des demandes d\'activites.</div>');
    });
}

function chargerActivitesPrevues() {
    $('#admin-liste-activites-prevues').html('<p class="text-muted">Chargement...</p>');

    // On charge en parallele activites prevues + activites + animateurs
    // pour resoudre les noms via lookup cote front
    $.when(
        $.ajax({ url: 'index.php?action=apiactivitesprevues', method: 'GET', dataType: 'json' }),
        $.ajax({ url: 'index.php?action=apiactivite', method: 'GET', dataType: 'json' }),
        $.ajax({ url: 'index.php?action=apianimateur', method: 'GET', dataType: 'json' })
    ).done(function(prevuesArr, activitesArr, animateursArr) {
        var prevues = prevuesArr[0];
        var activites = activitesArr[0];
        var animateurs = animateursArr[0];
        var liste = prevues ? Object.values(prevues) : [];

        var actById = {};
        if (activites) {
            $.each(Object.values(activites), function(i, a) {
                actById[String(a.id_activite)] = a;
            });
        }
        var animById = {};
        if (animateurs) {
            $.each(Object.values(animateurs), function(i, a) {
                animById[String(a.id_animateur)] = a;
            });
        }

        if (liste.length > 0) {
            var html = '<div class="admin-table-wrapper"><table class="admin-table">';
            html += '<thead><tr><th>#</th><th>Activite</th><th>Date</th><th>Creneau</th><th>Animateur</th><th>Capacite restante</th></tr></thead><tbody>';
            $.each(liste, function(i, a) {
                var id = a.id_activite_prevue || a.id || '?';
                var act = actById[String(a.id_activite)];
                var nomAct = act ? act.nom : ('Activite #' + (a.id_activite || '?'));
                var anim = animById[String(a.id_animateur)];
                var nomAnim = anim ? (anim.prenom + ' ' + anim.nom) : ('Animateur #' + (a.id_animateur || '?'));
                html += '<tr>';
                html += '<td>#' + id + '</td>';
                html += '<td><strong>' + nomAct + '</strong></td>';
                html += '<td>' + (a.date || '?') + '</td>';
                html += '<td>' + (a.creneau || '?') + '</td>';
                html += '<td>' + nomAnim + '</td>';
                html += '<td>' + (a.capacite_restante != null ? a.capacite_restante : 0) + '</td>';
                html += '</tr>';
            });
            html += '</tbody></table></div>';
            $('#admin-liste-activites-prevues').html(html);
        } else {
            $('#admin-liste-activites-prevues').html('<div class="admin-empty">Aucune activite prevue.</div>');
        }
    })
    .fail(function() {
        $('#admin-liste-activites-prevues').html('<div class="admin-error">Erreur de chargement des activites prevues.</div>');
    });
}

function chargerClients(statut) {
    $('#admin-liste-clients').html('<p class="text-muted">Chargement...</p>');

    // apiclient renvoie un objet brut { "1": {...}, "2": {...} } (pas {success, data})
    // Le filtrage par statut est fait cote client
    $.ajax({ url: 'index.php?action=apiclient', method: 'GET', dataType: 'json' })
    .done(function(clients) {
        var liste = clients ? Object.values(clients) : [];

        if (statut && statut !== 'all') {
            liste = liste.filter(function(c) { return (c.statut_compte || '') === statut; });
        }

        if (liste.length > 0) {
            var html = '<div class="admin-table-wrapper"><table class="admin-table">';
            html += '<thead><tr><th>#</th><th>Nom</th><th>Prenom</th><th>Email</th><th>Statut</th><th>Inscrit le</th></tr></thead><tbody>';
            $.each(liste, function(i, c) {
                var id = c.id_client || c.id || '?';
                var stc = (c.statut_compte || 'inconnu');
                var badge = 'admin-badge ';
                if (stc === 'actif') badge += 'badge-validee';
                else if (stc === 'invité') badge += 'badge-provisoire';
                else badge += 'badge-refusee';
                html += '<tr>';
                html += '<td>#' + id + '</td>';
                html += '<td><strong>' + (c.nom || '?') + '</strong></td>';
                html += '<td>' + (c.prenom || '?') + '</td>';
                html += '<td><small>' + (c.email || '?') + '</small></td>';
                html += '<td><span class="' + badge + '">' + stc + '</span></td>';
                html += '<td><small>' + (c.date_creation || '?') + '</small></td>';
                html += '</tr>';
            });
            html += '</tbody></table></div>';
            $('#admin-liste-clients').html(html);
        } else {
            $('#admin-liste-clients').html('<div class="admin-empty">Aucun client.</div>');
        }
    })
    .fail(function() {
        $('#admin-liste-clients').html('<div class="admin-error">Erreur de chargement des clients. (Route <code>apiclient</code> en attente cote backend)</div>');
    });
}

function chargerAnimateurs() {
    $('#admin-liste-animateurs').html('<p class="text-muted">Chargement...</p>');

    // apianimateur renvoie un tableau/objet brut (pas {success, data})
    $.ajax({ url: 'index.php?action=apianimateur', method: 'GET', dataType: 'json' })
    .done(function(animateurs) {
        var liste = animateurs ? Object.values(animateurs) : [];
        if (liste.length > 0) {
            var html = '<div class="admin-table-wrapper"><table class="admin-table">';
            html += '<thead><tr><th>#</th><th>Nom</th><th>Prenom</th><th>Specialite</th><th>Action</th></tr></thead><tbody>';
            $.each(liste, function(i, a) {
                var id = a.id_animateur || a.id || '?';
                html += '<tr>';
                html += '<td>#' + id + '</td>';
                html += '<td>' + (a.nom || '?') + '</td>';
                html += '<td>' + (a.prenom || '?') + '</td>';
                html += '<td>' + (a.specialite || '?') + '</td>';
                html += '<td><button class="btn btn-sm btn-outline-danger btn-supprimer-animateur" data-id="' + id + '">Supprimer</button></td>';
                html += '</tr>';
            });
            html += '</tbody></table></div>';
            $('#admin-liste-animateurs').html(html);
        } else {
            $('#admin-liste-animateurs').html('<div class="admin-empty">Aucun animateur. Ajoutez-en avec le formulaire ci-dessus.</div>');
        }
    })
    .fail(function() {
        $('#admin-liste-animateurs').html('<div class="admin-error">Erreur de chargement des animateurs.</div>');
    });
}

function chargerAnimateursPourSelect() {
    // apianimateur renvoie un tableau/objet brut (pas {success, data})
    $.ajax({ url: 'index.php?action=apianimateur', method: 'GET', dataType: 'json' })
    .done(function(animateurs) {
        var html = '<option value="" selected disabled>Choisir...</option>';
        var liste = animateurs ? Object.values(animateurs) : [];
        $.each(liste, function(i, a) {
            var id = a.id_animateur || a.id;
            html += '<option value="' + id + '">';
            html += (a.prenom || '') + ' ' + (a.nom || '') + ' (' + (a.specialite || '') + ')';
            html += '</option>';
        });
        $('#va-animateur').html(html);
    })
    .fail(function() {
        $('#va-animateur').html('<option value="" disabled>Erreur de chargement</option>');
    });
}

function chargerFactures(statut) {
    // Endpoint adminrecuperefactures pas encore route cote backend.
    // Tant que ce n'est pas expose, on affiche un message d'attente
    // pour eviter un faux "Aucune facture".
    var url = 'index.php?action=adminrecuperefactures';
    if (statut && statut !== 'all') url += '&statut=' + encodeURIComponent(statut);
    $('#admin-liste-factures').html('<p class="text-muted">Chargement...</p>');

    $.ajax({ url: url, method: 'GET', dataType: 'json' })
    .done(function(res) {
        // Si le backend renvoie du HTML (endpoint absent), dataType:'json' declenche .fail()
        if (!res || res.success !== true) {
            $('#admin-liste-factures').html('<div class="admin-error">En attente backend : liste des factures admin.</div>');
            return;
        }
        var liste = res.data ? Object.values(res.data) : [];
        if (liste.length === 0) {
            $('#admin-liste-factures').html('<div class="admin-empty">Aucune facture.</div>');
            return;
        }
        var html = '<div class="admin-table-wrapper"><table class="admin-table">';
        html += '<thead><tr><th>#</th><th>Client</th><th>Reservation</th><th>Total</th><th>Avoirs</th><th>Reduction</th><th>Statut</th><th>Actions</th></tr></thead><tbody>';
        $.each(liste, function(i, f) {
            var total = parseFloat(f.montant_final != null ? f.montant_final : f.montant_total) || 0;
            var statutBrut = f.statut || '';
            var statutCss = statutBrut.toLowerCase().replace(/[éè]/g, 'e');
            var idFact = f.id_facture || f.id || '?';
            var idRes = f.id_reservation || '?';
            html += '<tr>';
            html += '<td>#' + idFact + '</td>';
            html += '<td>' + (f.id_client ? 'Client #' + f.id_client : '—') + '</td>';
            html += '<td>#' + idRes + '</td>';
            html += '<td><strong>' + total.toFixed(2) + ' &euro;</strong></td>';
            html += '<td>' + (parseFloat(f.avoirs) || 0).toFixed(2) + ' &euro;</td>';
            html += '<td>' + (parseFloat(f.reduction) || 0) + ' %</td>';
            html += '<td><span class="admin-badge badge-' + statutCss + '">' + statutBrut + '</span></td>';
            html += '<td>';
            html += '<button class="btn btn-sm btn-outline-accent btn-editer-facture" data-id="' + idFact + '" data-arrhes="' + (f.avoirs || 0) + '" data-reduction="' + (f.reduction || 0) + '">Editer</button> ';
            if (statutBrut.toLowerCase() === 'provisoire') {
                html += '<button class="btn btn-sm btn-accent btn-emettre-facture" data-id="' + idFact + '">Emettre</button>';
            }
            html += '</td>';
            html += '</tr>';
        });
        html += '</tbody></table></div>';
        $('#admin-liste-factures').html(html);
    })
    .fail(function() {
        $('#admin-liste-factures').html('<div class="admin-error">En attente backend : liste des factures admin.</div>');
    });
}

// =============================================
//  RENDU CARTES
// =============================================

function renderCarteDemande(r) {
    var idRes = r.id_reservation || r.id || '?';
    var statutBrut = r.statut || 'en_attente';
    var statutCss = statutBrut.replace(/[éè]/g, 'e').replace('_', '-');
    var clientLabel = r.nom_temp || r.email_temp || (r.id_client ? 'Client #' + r.id_client : '—');

    var html = '<div class="admin-card admin-demande-card">';
    html += '<div class="d-flex justify-content-between align-items-start mb-2">';
    html += '<div>';
    html += '<h5 class="mb-1">Demande #' + idRes + '</h5>';
    html += '<small class="text-muted">' + (r.date_demande || '') + '</small>';
    html += '</div>';
    html += '<span class="admin-badge badge-' + statutCss + '">' + statutBrut + '</span>';
    html += '</div>';

    html += '<div class="row mb-3">';
    html += '<div class="col-md-6"><strong>Client</strong><br>' + clientLabel + '</div>';
    html += '<div class="col-md-6"><strong>Sejour</strong><br>Du ' + (r.date_debut || '?') + ' au ' + (r.date_fin || '?') + '<br><small class="text-muted">' + (r.nombre_personnes || '?') + ' personne(s)</small></div>';
    html += '</div>';

    if (r.commentaire) {
        html += '<div class="mb-3"><strong>Commentaire</strong><br><small class="text-muted">' + r.commentaire + '</small></div>';
    }

    if (statutBrut === 'en_attente') {
        html += '<div class="d-flex gap-2">';
        html += '<button class="btn btn-sm btn-accent btn-accepter-resa" data-id="' + idRes + '">Accepter</button>';
        html += '<button class="btn btn-sm btn-outline-danger btn-refuser-resa" data-id="' + idRes + '">Refuser</button>';
        html += '</div>';
    }

    html += '</div>';
    return html;
}

// =============================================
//  MESSAGE MAIL DE CONFIRMATION
// =============================================

function afficherMessageMail(email, nom, prenom, motDePasse) {
    var url = window.location.origin + window.location.pathname.replace(/\/[^/]*$/, '/');
    var sujet = 'Confirmation de votre reservation - Zenyth';
    var corps = 'Bonjour ' + prenom + ' ' + nom + ',\n\n';
    corps += 'Nous avons le plaisir de vous confirmer la validation de votre reservation au complexe Zenyth.\n\n';
    corps += 'Votre compte client a ete cree avec les identifiants suivants :\n';
    corps += '  - Email : ' + email + '\n';
    corps += '  - Mot de passe : ' + motDePasse + '\n\n';
    corps += 'Vous pouvez vous connecter a votre espace personnel a l\'adresse suivante :\n';
    corps += url + '\n\n';
    corps += 'Depuis cet espace vous pourrez :\n';
    corps += '  - Consulter votre reservation\n';
    corps += '  - Ajouter des prestations a votre sejour\n';
    corps += '  - Faire des demandes d\'activites sportives\n';
    corps += '  - Consulter votre facture previsionnelle\n\n';
    corps += 'A bientot au complexe Zenyth !\n';
    corps += 'L\'equipe Zenyth';

    $('#mail-destinataire').val(email);
    $('#mail-sujet').val(sujet);
    $('#mail-corps').val(corps);
    $('#btn-mailto').attr('href', 'mailto:' + email + '?subject=' + encodeURIComponent(sujet) + '&body=' + encodeURIComponent(corps));
    $('#popup-message-mail').addClass('active');
}

// =============================================
//  TOAST (memes que app.js, dupliques pour autonomie)
// =============================================

function showToast(message, type) {
    type = type || 'success';
    var bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
    var id = 'toast-' + Date.now();

    var html = '<div id="' + id + '" class="toast align-items-center text-white ' + bgClass + ' border-0" role="alert">';
    html += '<div class="d-flex">';
    html += '<div class="toast-body">' + message + '</div>';
    html += '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>';
    html += '</div></div>';

    $('.toast-container').append(html);
    var toastEl = document.getElementById(id);
    var toast = new bootstrap.Toast(toastEl, { delay: 4000 });
    toast.show();

    toastEl.addEventListener('hidden.bs.toast', function() {
        toastEl.remove();
    });
}
