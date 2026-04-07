$(document).ready(function() {

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
    $('#form-admin-connexion').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.post('index.php?action=loginadmin', formData, function(res) {
            if (res.success) {
                var nom = (res.data && res.data.nom) ? res.data.nom : '';
                $('#popup-admin-connexion').removeClass('active');
                afficherDashboardAdmin(nom);
                showToast('Bienvenue ' + nom + ' !');
            } else {
                showToast(res.error || 'Identifiants incorrects.', 'error');
            }
        }, 'json').fail(function() {
            showToast('Endpoint loginadmin non disponible (en attente backend).', 'error');
        });
    });

    // ===== DECONNEXION ADMIN =====
    $('#btn-admin-deconnexion').on('click', function(e) {
        e.preventDefault();
        $.post('index.php?action=logoutadmin', { csrf_token: $('#csrf-global').val() }, function(res) {
            if (res && res.csrf_token) {
                $('#csrf-global').val(res.csrf_token);
                $('input[name="csrf_token"]').val(res.csrf_token);
            }
            $('#admin-dashboard').addClass('d-none');
            $('#admin-nav-info').addClass('d-none');
            $('#admin-nav-deconnexion').addClass('d-none');
            $('#popup-admin-connexion').addClass('active');
            showToast('Deconnexion reussie.');
        }, 'json').fail(function() {
            // Fallback : on deconnecte en local
            $('#admin-dashboard').addClass('d-none');
            $('#admin-nav-info').addClass('d-none');
            $('#admin-nav-deconnexion').addClass('d-none');
            $('#popup-admin-connexion').addClass('active');
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
                var html = '<div class="alert alert-success">' + res.data.length + ' chambre(s) disponible(s) sur la periode :</div>';
                html += '<ul class="list-group">';
                $.each(res.data, function(i, ch) {
                    html += '<li class="list-group-item d-flex justify-content-between align-items-center">';
                    html += '<span><strong>' + ch.nom_chambre + '</strong> (' + ch.type_chambre + ') &mdash; ' + ch.capacite + ' pers.</span>';
                    html += '<span class="badge bg-success">' + ch.prix_nuit + ' &euro;/nuit</span>';
                    html += '</li>';
                });
                html += '</ul>';
                $('#adm-ch-resultat').html(html);
            } else {
                $('#adm-ch-resultat').html('<div class="alert alert-warning">Aucune chambre disponible sur cette periode.</div>');
            }
        }).fail(function() {
            $('#adm-ch-resultat').html('<div class="alert alert-danger">Erreur lors de la verification.</div>');
        });
    });

    // ===== ACTIONS DEMANDES (delegation) =====
    $('#admin-liste-demandes').on('click', '.btn-accepter-resa', function() {
        var id = $(this).data('id');
        if (!confirm('Confirmer l\'acceptation de cette reservation ? Un compte client va etre cree.')) return;

        $.post('index.php?action=adminaccepterreservation', { id_reservation: id, csrf_token: $('#csrf-global').val() }, function(res) {
            if (res.success) {
                showToast(res.message || 'Reservation acceptee.');
                // Ouvrir le modal message mail si on a les infos
                if (res.email && res.password) {
                    afficherMessageMail(res.email, res.nom || '', res.prenom || '', res.password);
                }
                chargerDemandes('en_attente');
            } else {
                showToast(res.error || 'Erreur.', 'error');
            }
        }, 'json').fail(function() {
            showToast('Endpoint adminaccepterreservation non disponible.', 'error');
        });
    });

    $('#admin-liste-demandes').on('click', '.btn-refuser-resa', function() {
        var id = $(this).data('id');
        if (!confirm('Refuser cette reservation ?')) return;

        $.post('index.php?action=adminrefuserreservation', { id_reservation: id, csrf_token: $('#csrf-global').val() }, function(res) {
            if (res.success) {
                showToast('Reservation refusee.');
                chargerDemandes('en_attente');
            } else {
                showToast(res.error || 'Erreur.', 'error');
            }
        }, 'json').fail(function() {
            showToast('Endpoint adminrefuserreservation non disponible.', 'error');
        });
    });

    // ===== TOGGLE PRESTATION =====
    $('#admin-liste-prestations').on('change', '.toggle-presta', function() {
        var id = $(this).data('id');
        var actif = $(this).is(':checked') ? 1 : 0;

        $.post('index.php?action=admintoggleprestation', { id_prestation: id, actif: actif, csrf_token: $('#csrf-global').val() }, function(res) {
            if (res.success) {
                showToast('Prestation ' + (actif ? 'activee' : 'desactivee') + '.');
            } else {
                showToast(res.error || 'Erreur.', 'error');
            }
        }, 'json').fail(function() {
            showToast('Endpoint admintoggleprestation non disponible.', 'error');
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

    $('#form-valider-activite').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.post('index.php?action=adminvaliderdemandeactivite', formData, function(res) {
            if (res.success) {
                showToast('Activite programmee.');
                $('#popup-valider-activite').removeClass('active');
                chargerDemandesActivites($('#adm-act-filtre-date').val() || null);
                chargerActivitesPrevues();
            } else {
                showToast(res.error || 'Erreur.', 'error');
            }
        }, 'json').fail(function() {
            showToast('Endpoint adminvaliderdemandeactivite non disponible.', 'error');
        });
    });

    // ===== CREATION ANIMATEUR =====
    $('#form-creation-animateur').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.post('index.php?action=admincreeranimateur', formData, function(res) {
            if (res.success) {
                showToast('Animateur ajoute.');
                $('#form-creation-animateur')[0].reset();
                chargerAnimateurs();
            } else {
                showToast(res.error || 'Erreur.', 'error');
            }
        }, 'json').fail(function() {
            showToast('Endpoint admincreeranimateur non disponible.', 'error');
        });
    });

    // Suppression animateur (delegation)
    $('#admin-liste-animateurs').on('click', '.btn-supprimer-animateur', function() {
        var id = $(this).data('id');
        if (!confirm('Supprimer cet animateur ?')) return;

        $.post('index.php?action=adminsupprimeranimateur', { id_animateur: id, csrf_token: $('#csrf-global').val() }, function(res) {
            if (res.success) {
                showToast('Animateur supprime.');
                chargerAnimateurs();
            } else {
                showToast(res.error || 'Erreur.', 'error');
            }
        }, 'json').fail(function() {
            showToast('Endpoint adminsupprimeranimateur non disponible.', 'error');
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

        $.post('index.php?action=adminediterfacture', formData, function(res) {
            if (res.success) {
                showToast('Facture mise a jour.');
                $('#popup-edit-facture').removeClass('active');
                chargerFactures('all');
            } else {
                showToast(res.error || 'Erreur.', 'error');
            }
        }, 'json').fail(function() {
            showToast('Endpoint adminediterfacture non disponible.', 'error');
        });
    });

    // Emettre une facture
    $('#admin-liste-factures').on('click', '.btn-emettre-facture', function() {
        var id = $(this).data('id');
        if (!confirm('Emettre cette facture definitivement ?')) return;

        $.post('index.php?action=adminemettrefacture', { id_facture: id, csrf_token: $('#csrf-global').val() }, function(res) {
            if (res.success) {
                showToast('Facture emise.');
                chargerFactures('all');
            } else {
                showToast(res.error || 'Erreur.', 'error');
            }
        }, 'json').fail(function() {
            showToast('Endpoint adminemettrefacture non disponible.', 'error');
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
    var url = 'index.php?action=adminrecuperereservations&statut=' + (statut || 'en_attente');
    $('#admin-liste-demandes').html('<p class="text-muted">Chargement...</p>');

    $.ajax({ url: url, method: 'GET', dataType: 'json' })
    .done(function(res) {
        if (res.success && res.data && res.data.length > 0) {
            var html = '';
            $.each(res.data, function(i, r) {
                html += renderCarteDemande(r);
            });
            $('#admin-liste-demandes').html(html);
        } else {
            $('#admin-liste-demandes').html('<div class="admin-empty">Aucune demande.</div>');
        }
    })
    .fail(function() {
        $('#admin-liste-demandes').html('<div class="admin-error">En attente du backend (endpoint adminrecuperereservations non disponible).</div>');
    });
}

function chargerReservations(statut) {
    var url = 'index.php?action=adminrecuperereservations&statut=' + (statut || 'all');
    $('#admin-liste-reservations').html('<p class="text-muted">Chargement...</p>');

    $.ajax({ url: url, method: 'GET', dataType: 'json' })
    .done(function(res) {
        if (res.success && res.data && res.data.length > 0) {
            var html = '<div class="admin-table-wrapper"><table class="admin-table">';
            html += '<thead><tr><th>#</th><th>Client</th><th>Dates</th><th>Pers.</th><th>Statut</th></tr></thead><tbody>';
            $.each(res.data, function(i, r) {
                html += '<tr>';
                html += '<td>#' + (r.id || r.id_reservation || '?') + '</td>';
                html += '<td>' + (r.nom || r.nom_temp || '') + ' ' + (r.prenom || '') + '<br><small class="text-muted">' + (r.email || r.email_temp || '') + '</small></td>';
                html += '<td>' + (r.date_debut || '?') + '<br><small>' + (r.date_fin || '?') + '</small></td>';
                html += '<td>' + (r.nombre_personnes || '?') + '</td>';
                html += '<td><span class="admin-badge badge-' + (r.statut || 'en-attente').replace(/[éè]/g,'e').replace('_','-') + '">' + (r.statut || '?') + '</span></td>';
                html += '</tr>';
            });
            html += '</tbody></table></div>';
            $('#admin-liste-reservations').html(html);
        } else {
            $('#admin-liste-reservations').html('<div class="admin-empty">Aucune reservation.</div>');
        }
    })
    .fail(function() {
        $('#admin-liste-reservations').html('<div class="admin-error">En attente du backend (endpoint adminrecuperereservations non disponible).</div>');
    });
}

function chargerChambres() {
    $('#admin-liste-chambres').html('<p class="text-muted">Chargement...</p>');

    $.ajax({ url: 'api/chambres.php', method: 'GET', dataType: 'json' })
    .done(function(chambres) {
        if (chambres && chambres.length > 0) {
            var html = '<div class="admin-table-wrapper"><table class="admin-table">';
            html += '<thead><tr><th>#</th><th>Nom</th><th>Type</th><th>Capacite</th><th>Prix/nuit</th><th>Statut</th></tr></thead><tbody>';
            $.each(chambres, function(i, ch) {
                var statutClass = ch.statut === 'libre' ? 'badge-validee' : 'badge-en-attente';
                html += '<tr>';
                html += '<td>#' + ch.id_chambre + '</td>';
                html += '<td>' + ch.nom_chambre + '</td>';
                html += '<td>' + ch.type_chambre + '</td>';
                html += '<td>' + ch.capacite + '</td>';
                html += '<td>' + ch.prix_nuit + ' &euro;</td>';
                html += '<td><span class="admin-badge ' + statutClass + '">' + ch.statut + '</span></td>';
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

    $.ajax({ url: 'api/prestations.php', method: 'GET', dataType: 'json' })
    .done(function(prestas) {
        if (prestas && prestas.length > 0) {
            var html = '<div class="admin-table-wrapper"><table class="admin-table">';
            html += '<thead><tr><th>#</th><th>Nom</th><th>Description</th><th>Prix unitaire</th><th>Actif</th></tr></thead><tbody>';
            $.each(prestas, function(i, p) {
                var actif = (p.actif === undefined || p.actif === null || p.actif == 1 || p.actif === true);
                html += '<tr>';
                html += '<td>#' + p.id_prestation + '</td>';
                html += '<td><strong>' + p.nom + '</strong></td>';
                html += '<td>' + (p.description || '') + '</td>';
                html += '<td>' + p.prix_unitaire + ' &euro;</td>';
                html += '<td><div class="form-check form-switch">';
                html += '<input class="form-check-input toggle-presta" type="checkbox" data-id="' + p.id_prestation + '"' + (actif ? ' checked' : '') + '>';
                html += '</div></td>';
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
    var url = 'index.php?action=adminrecuperedemandesactivites';
    if (date) url += '&date=' + date;
    $('#admin-liste-demandes-activites').html('<p class="text-muted">Chargement...</p>');

    $.ajax({ url: url, method: 'GET', dataType: 'json' })
    .done(function(res) {
        if (res.success && res.data && res.data.length > 0) {
            var html = '<div class="admin-table-wrapper"><table class="admin-table">';
            html += '<thead><tr><th>#</th><th>Activite</th><th>Date</th><th>Creneau</th><th>Pers.</th><th>Message</th><th>Action</th></tr></thead><tbody>';
            $.each(res.data, function(i, d) {
                html += '<tr>';
                html += '<td>#' + (d.id || '?') + '</td>';
                html += '<td><strong>' + (d.nom_activite || d.nom || '?') + '</strong></td>';
                html += '<td>' + (d.date || '?') + '</td>';
                html += '<td>' + (d.creneau || '?') + '</td>';
                html += '<td>' + (d.nombre_personnes_concernees || d.nombre_personnes || '?') + '</td>';
                html += '<td><small>' + (d.message || '') + '</small></td>';
                html += '<td><button class="btn btn-sm btn-accent btn-valider-activite"';
                html += ' data-id="' + (d.id || '') + '"';
                html += ' data-date="' + (d.date || '') + '"';
                html += ' data-creneau="' + (d.creneau || '') + '"';
                html += ' data-personnes="' + (d.nombre_personnes_concernees || d.nombre_personnes || '') + '"';
                html += ' data-nom="' + (d.nom_activite || d.nom || '') + '">Programmer</button></td>';
                html += '</tr>';
            });
            html += '</tbody></table></div>';
            $('#admin-liste-demandes-activites').html(html);
        } else {
            $('#admin-liste-demandes-activites').html('<div class="admin-empty">Aucune demande en attente.</div>');
        }
    })
    .fail(function() {
        $('#admin-liste-demandes-activites').html('<div class="admin-error">En attente du backend (endpoint adminrecuperedemandesactivites non disponible).</div>');
    });
}

function chargerActivitesPrevues() {
    $('#admin-liste-activites-prevues').html('<p class="text-muted">Chargement...</p>');

    $.ajax({ url: 'index.php?action=adminrecupereactivitesprevues', method: 'GET', dataType: 'json' })
    .done(function(res) {
        if (res.success && res.data && res.data.length > 0) {
            var html = '<div class="admin-table-wrapper"><table class="admin-table">';
            html += '<thead><tr><th>#</th><th>Activite</th><th>Date</th><th>Creneau</th><th>Animateur</th><th>Capacite restante</th></tr></thead><tbody>';
            $.each(res.data, function(i, a) {
                html += '<tr>';
                html += '<td>#' + (a.id || '?') + '</td>';
                html += '<td><strong>' + (a.nom_activite || a.nom || '?') + '</strong></td>';
                html += '<td>' + (a.date || '?') + '</td>';
                html += '<td>' + (a.creneau || '?') + '</td>';
                html += '<td>' + (a.nom_animateur || '?') + '</td>';
                html += '<td>' + (a.capacite_restante || 0) + '</td>';
                html += '</tr>';
            });
            html += '</tbody></table></div>';
            $('#admin-liste-activites-prevues').html(html);
        } else {
            $('#admin-liste-activites-prevues').html('<div class="admin-empty">Aucune activite prevue.</div>');
        }
    })
    .fail(function() {
        $('#admin-liste-activites-prevues').html('<div class="admin-error">En attente du backend (endpoint adminrecupereactivitesprevues non disponible).</div>');
    });
}

function chargerAnimateurs() {
    $('#admin-liste-animateurs').html('<p class="text-muted">Chargement...</p>');

    $.ajax({ url: 'index.php?action=adminrecupereanimateurs', method: 'GET', dataType: 'json' })
    .done(function(res) {
        if (res.success && res.data && res.data.length > 0) {
            var html = '<div class="admin-table-wrapper"><table class="admin-table">';
            html += '<thead><tr><th>#</th><th>Nom</th><th>Prenom</th><th>Specialite</th><th>Action</th></tr></thead><tbody>';
            $.each(res.data, function(i, a) {
                html += '<tr>';
                html += '<td>#' + (a.id || a.id_animateur || '?') + '</td>';
                html += '<td>' + (a.nom || '?') + '</td>';
                html += '<td>' + (a.prenom || '?') + '</td>';
                html += '<td>' + (a.specialite || '?') + '</td>';
                html += '<td><button class="btn btn-sm btn-outline-danger btn-supprimer-animateur" data-id="' + (a.id || a.id_animateur) + '">Supprimer</button></td>';
                html += '</tr>';
            });
            html += '</tbody></table></div>';
            $('#admin-liste-animateurs').html(html);
        } else {
            $('#admin-liste-animateurs').html('<div class="admin-empty">Aucun animateur. Ajoutez-en avec le formulaire ci-dessus.</div>');
        }
    })
    .fail(function() {
        $('#admin-liste-animateurs').html('<div class="admin-error">En attente du backend (endpoint adminrecupereanimateurs non disponible).</div>');
    });
}

function chargerAnimateursPourSelect() {
    $.ajax({ url: 'index.php?action=adminrecupereanimateurs', method: 'GET', dataType: 'json' })
    .done(function(res) {
        var html = '<option value="" selected disabled>Choisir...</option>';
        if (res.success && res.data) {
            $.each(res.data, function(i, a) {
                html += '<option value="' + (a.id || a.id_animateur) + '">';
                html += (a.prenom || '') + ' ' + (a.nom || '') + ' (' + (a.specialite || '') + ')';
                html += '</option>';
            });
        }
        $('#va-animateur').html(html);
    });
}

function chargerFactures(statut) {
    var url = 'index.php?action=adminrecuperefactures';
    if (statut && statut !== 'all') url += '&statut=' + statut;
    $('#admin-liste-factures').html('<p class="text-muted">Chargement...</p>');

    $.ajax({ url: url, method: 'GET', dataType: 'json' })
    .done(function(res) {
        if (res.success && res.data && res.data.length > 0) {
            var html = '<div class="admin-table-wrapper"><table class="admin-table">';
            html += '<thead><tr><th>#</th><th>Client</th><th>Reservation</th><th>Total</th><th>Avoirs</th><th>Reduction</th><th>Statut</th><th>Actions</th></tr></thead><tbody>';
            $.each(res.data, function(i, f) {
                var total = parseFloat(f.montant_final != null ? f.montant_final : f.montant_total) || 0;
                var statutClass = (f.statut || '').toLowerCase().replace('é','e');
                html += '<tr>';
                html += '<td>#' + (f.id || '?') + '</td>';
                html += '<td>' + (f.nom || '') + ' ' + (f.prenom || '') + '<br><small class="text-muted">' + (f.email || '') + '</small></td>';
                html += '<td>#' + (f.id_reservation || '?') + '</td>';
                html += '<td><strong>' + total.toFixed(2) + ' &euro;</strong></td>';
                html += '<td>' + (parseFloat(f.avoirs) || 0).toFixed(2) + ' &euro;</td>';
                html += '<td>' + (parseFloat(f.reduction) || 0) + ' %</td>';
                html += '<td><span class="admin-badge badge-' + statutClass + '">' + (f.statut || '?') + '</span></td>';
                html += '<td>';
                html += '<button class="btn btn-sm btn-outline-accent btn-editer-facture" data-id="' + f.id + '" data-arrhes="' + (f.avoirs || 0) + '" data-reduction="' + (f.reduction || 0) + '">Editer</button> ';
                if ((f.statut || '').toLowerCase() === 'provisoire') {
                    html += '<button class="btn btn-sm btn-accent btn-emettre-facture" data-id="' + f.id + '">Emettre</button>';
                }
                html += '</td>';
                html += '</tr>';
            });
            html += '</tbody></table></div>';
            $('#admin-liste-factures').html(html);
        } else {
            $('#admin-liste-factures').html('<div class="admin-empty">Aucune facture.</div>');
        }
    })
    .fail(function() {
        $('#admin-liste-factures').html('<div class="admin-error">En attente du backend (endpoint adminrecuperefactures non disponible).</div>');
    });
}

// =============================================
//  RENDU CARTES
// =============================================

function renderCarteDemande(r) {
    var html = '<div class="admin-card admin-demande-card">';
    html += '<div class="d-flex justify-content-between align-items-start mb-2">';
    html += '<div>';
    html += '<h5 class="mb-1">Demande #' + (r.id || r.id_reservation) + '</h5>';
    html += '<small class="text-muted">' + (r.date_demande || '') + '</small>';
    html += '</div>';
    html += '<span class="admin-badge badge-' + (r.statut || 'en-attente').replace(/[éè]/g,'e').replace('_','-') + '">' + (r.statut || '?') + '</span>';
    html += '</div>';

    html += '<div class="row mb-3">';
    html += '<div class="col-md-6"><strong>Client</strong><br>' + (r.nom || r.nom_temp || '') + ' ' + (r.prenom || '') + '<br><small class="text-muted">' + (r.email || r.email_temp || '') + '</small></div>';
    html += '<div class="col-md-6"><strong>Sejour</strong><br>Du ' + (r.date_debut || '?') + ' au ' + (r.date_fin || '?') + '<br><small class="text-muted">' + (r.nombre_personnes || '?') + ' personne(s)</small></div>';
    html += '</div>';

    if (r.commentaire) {
        html += '<div class="mb-3"><strong>Commentaire</strong><br><small class="text-muted">' + r.commentaire + '</small></div>';
    }

    if ((r.statut || '').toLowerCase() === 'en_attente' || (r.statut || '').toLowerCase() === 'en attente') {
        html += '<div class="d-flex gap-2">';
        html += '<button class="btn btn-sm btn-accent btn-accepter-resa" data-id="' + (r.id || r.id_reservation) + '">Accepter</button>';
        html += '<button class="btn btn-sm btn-outline-danger btn-refuser-resa" data-id="' + (r.id || r.id_reservation) + '">Refuser</button>';
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
