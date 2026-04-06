$(document).ready(function() {

    // ===== POPUPS =====
    $('[data-popup]').on('click', function(e) {
        e.preventDefault();
        var nom = $(this).data('popup');
        $('#popup-' + nom).addClass('active');
        $('.navbar-collapse').collapse('hide');
    });

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

    // ===== MOT DE PASSE OUBLIE =====
    $('#lien-mdp-oublie').on('click', function(e) {
        e.preventDefault();
        $('#bloc-mdp-oublie').slideToggle(300);
    });

    // ===== FORMULAIRE CONNEXION =====
    $('#form-connexion').on('submit', function(e) {
        e.preventDefault();

        if (!validerEmail($('#co-email').val())) {
            showToast("Veuillez entrer une adresse email valide.", "error");
            return;
        }

        var formData = $(this).serialize();

        $.post('index.php?action=login', formData, function(res) {
            if (res.success) {
                var d = res.data || {};
                $('#popup-connexion').removeClass('active');
                $('#nav-connexion').addClass('d-none');
                $('#nav-dashboard').removeClass('d-none');
                $('#nav-deconnexion').removeClass('d-none');
                $('#bloc-info-client').hide();
                $('#info-nom').val(d.nom || '');
                $('#info-prenom').val(d.prenom || '');
                $('#info-email').val(d.email || '');
                chargerDashboard();
                $('#popup-dashboard').addClass('active');
                showToast('Bienvenue ' + (d.prenom || '') + ' ' + (d.nom || '') + ' !');
            } else {
                showToast(res.error || 'Erreur de connexion.', 'error');
            }
        }, 'json');
    });

    // ===== DECONNEXION =====
    $('#btn-deconnexion').on('click', function(e) {
        e.preventDefault();
        $.post('index.php?action=logout', { csrf_token: $('#csrf-global').val() }, function(res) {
            if (res.success) {
                $('#nav-connexion').removeClass('d-none');
                $('#nav-dashboard').addClass('d-none');
                $('#nav-deconnexion').addClass('d-none');
                $('#bloc-info-client').show();
                // Fermer le dashboard si ouvert
                $('#popup-dashboard').removeClass('active');
                // Vider les infos perso
                $('#info-nom').val('');
                $('#info-prenom').val('');
                $('#info-email').val('');
                // Vider les onglets du dashboard
                $('#dash-liste-reservations').html('<p class="text-muted">Aucune reservation pour le moment.</p>');
                $('#dash-liste-prestations').html('<p class="text-muted">Chargement...</p>');
                $('#dash-activites-validees').html('<p class="text-muted">Aucune activite validee pour le moment.</p>');
                $('#dash-liste-factures').html('<p class="text-muted">Aucune facture pour le moment.</p>');
                // Reset les formulaires du dashboard
                $('#form-demande-activite')[0].reset();
                $('#form-change-mdp')[0].reset();
                // Mettre à jour le CSRF token
                if (res.csrf_token) {
                    $('#csrf-global').val(res.csrf_token);
                    $('input[name="csrf_token"]').val(res.csrf_token);
                }
                showToast('Deconnexion reussie.');
            }
        }, 'json');
    });

    // ===== FORMULAIRE MOT DE PASSE OUBLIE =====
    $('#form-mdp-oublie').on('submit', function(e) {
        e.preventDefault();

        if (!validerEmail($('#oubli-email').val())) {
            showToast("Veuillez entrer une adresse email valide.", "error");
            return;
        }

        var formData = $(this).serialize();

        $.post('index.php?action=motdepasseoublie', formData, function(res) {
            if (res.success) {
                showToast(res.message || 'Si ce mail existe, un lien a ete envoye.');
            } else {
                showToast(res.error || 'Erreur lors de la demande.', 'error');
            }
            $('#bloc-mdp-oublie').slideUp(300);
        }, 'json');
    });

    // ===== FORMULAIRE RESERVATION =====
    var today = new Date().toISOString().split('T')[0];
    $('#res-date-debut').attr('min', today);
    $('#da-date').attr('min', today);

    $('#res-date-debut').on('change', function() {
        var debut = $(this).val();
        $('#res-date-fin').attr('min', debut);
        if ($('#res-date-fin').val() && $('#res-date-fin').val() <= debut) {
            $('#res-date-fin').val('');
        }
        majChambresDisponibles();
    });

    $('#res-date-fin').on('change', function() {
        majChambresDisponibles();
    });

    $('#res-personnes').on('change', function() {
        majChambresDisponibles();
    });

    $('#form-reservation').on('submit', function(e) {
        e.preventDefault();

        var debut = $('#res-date-debut').val();
        var fin = $('#res-date-fin').val();

        var emailVisible = $('#bloc-info-client').is(':visible');
        if (emailVisible && !validerEmail($('#res-email').val())) {
            showToast("Veuillez entrer une adresse email valide.", "error");
            return;
        }

        if (debut < today) {
            showToast("La date d'arrivee ne peut pas etre dans le passe.", "error");
            return;
        }
        if (fin <= debut) {
            showToast("La date de depart doit etre apres la date d'arrivee.", "error");
            return;
        }

        var chambreOption = $('#res-chambre option:selected');
        var capacite = parseInt(chambreOption.data('capacite'));
        var personnes = parseInt($('#res-personnes').val());
        if (capacite && personnes > capacite) {
            showToast("Cette chambre a une capacite de " + capacite + " personnes maximum.", "error");
            return;
        }

        var formData = $(this).serialize();

        $.post('index.php?action=reservationchambre', formData, function(res) {
            if (res.success) {
                showToast(res.message || 'Reservation envoyee.');
                $('#popup-reservation').removeClass('active');
                $('#form-reservation')[0].reset();
            } else {
                showToast(res.error || 'Erreur lors de la reservation.', 'error');
            }
        }, 'json');
    });

    // ===== CARROUSEL =====
    $('.carrousel-next').on('click', function() {
        var track = $('#' + $(this).data('cible'));
        var item = track.find('.carrousel-item').first();
        var step = item.outerWidth() + 20;
        track.scrollLeft(track.scrollLeft() + step);
        setTimeout(function() { majFleches(track); }, 550);
    });

    $('.carrousel-prev').on('click', function() {
        var track = $('#' + $(this).data('cible'));
        var item = track.find('.carrousel-item').first();
        var step = item.outerWidth() + 20;
        track.scrollLeft(track.scrollLeft() - step);
        setTimeout(function() { majFleches(track); }, 550);
    });

    // ===== ONGLETS DASHBOARD =====
    $('.dash-tab').on('click', function() {
        $('.dash-tab').removeClass('active');
        $(this).addClass('active');
        $('.dash-panel').removeClass('active');
        $('#' + $(this).data('tab')).addClass('active');
    });

    // ===== FORMULAIRE CHANGEMENT MDP =====
    $('#form-change-mdp').on('submit', function(e) {
        e.preventDefault();

        var ancien = $('#mdp-ancien').val();
        var nouveau = $('#mdp-nouveau').val();
        var confirmer = $('#mdp-confirmer').val();

        if (nouveau === ancien) {
            showToast("Le nouveau mot de passe doit etre different de l'ancien.", "error");
            return;
        }
        if (nouveau !== confirmer) {
            showToast("Les deux mots de passe ne correspondent pas.", "error");
            return;
        }
        if (!validerMotDePasse(nouveau)) {
            return;
        }

        var formData = $(this).serialize();
        $.post('index.php?action=changementmotdepasse', formData, function(res) {
            if (res.success) {
                showToast(res.message || 'Mot de passe modifie.');
                $('#form-change-mdp')[0].reset();
            } else {
                showToast(res.error || 'Erreur lors du changement.', 'error');
            }
        }, 'json');
    });

    // ===== FORMULAIRE DEMANDE ACTIVITE =====
    $('#form-demande-activite').on('submit', function(e) {
        e.preventDefault();

        var date = $('#da-date').val();

        if (date < today) {
            showToast("La date ne peut pas etre dans le passe.", "error");
            return;
        }

        var formData = $(this).serialize();
        $.post('index.php?action=reservationactivite', formData, function(res) {
            if (res.success) {
                showToast(res.message || "Demande d'activite envoyee.");
                $('#form-demande-activite')[0].reset();
            } else {
                showToast(res.error || "Erreur lors de la demande.", "error");
            }
        }, 'json');
    });

    // ===== AJOUT PRESTATION (delegation) =====
    var prestationsAjoutees = [];

    $('#dash-liste-prestations').on('click', '.btn-ajout-presta', function() {
        var btn = $(this);
        var idPrestation = btn.data('id');

        if (btn.prop('disabled') || prestationsAjoutees.indexOf(idPrestation) !== -1) {
            showToast('Cette prestation a deja ete ajoutee.', 'error');
            return;
        }

        btn.prop('disabled', true).text('Ajout...');

        $.post('index.php?action=reservationprestation', { id_prestation: idPrestation, csrf_token: $('#csrf-global').val() }, function(res) {
            if (res.success) {
                prestationsAjoutees.push(idPrestation);
                showToast(res.message || 'Prestation ajoutee.');
                btn.text('Ajoutee').addClass('disabled');
            } else {
                showToast(res.error || "Erreur lors de l'ajout.", 'error');
                btn.prop('disabled', false).text('Ajouter');
            }
        }, 'json').fail(function(xhr) {
            showToast("Erreur serveur lors de l'ajout.", 'error');
            btn.prop('disabled', false).text('Ajouter');
        });
    });

    // ===== TELECHARGER FACTURE PDF =====
    $('#dash-liste-factures').on('click', '.btn-telecharger-facture', function() {
        var idReservation = $(this).data('id-reservation');
        window.open('index.php?action=telechargementfacture&id_reservation=' + idReservation, '_blank');
    });

    // ===== SESSION : RESTAURER L'ETAT CONNECTE =====
    if (typeof SESSION_USER !== 'undefined' && SESSION_USER.connecte) {
        $('#nav-connexion').addClass('d-none');
        $('#nav-dashboard').removeClass('d-none');
        $('#nav-deconnexion').removeClass('d-none');
        $('#bloc-info-client').hide();
        $('#info-nom').val(SESSION_USER.nom);
        $('#info-prenom').val(SESSION_USER.prenom);
        $('#info-email').val(SESSION_USER.email);
        chargerDashboard();
    }

    // ===== CHARGEMENT INITIAL (AJAX) =====
    chargerChambresAccueil();
    chargerActivitesAccueil();
    chargerPrestationsAccueil();
    chargerChambresFormulaire();
    chargerActivitesFormulaire();

});


// ===== TOAST =====
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


// ===== CARROUSEL - MAJ FLECHES =====
function majFleches(track) {
    var wrapper = track.closest('.carrousel-wrapper');
    var scrollLeft = track.scrollLeft();
    var maxScroll = track[0].scrollWidth - track[0].clientWidth;

    wrapper.find('.carrousel-prev').toggleClass('carrousel-disabled', scrollLeft <= 0);
    wrapper.find('.carrousel-next').toggleClass('carrousel-disabled', scrollLeft >= maxScroll - 1);
}


// ===== VALIDATION EMAIL =====
function validerEmail(email) {
    var regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return regex.test(email);
}


// ===== VALIDATION MOT DE PASSE =====
function validerMotDePasse(mdp) {
    var erreurs = [];
    if (mdp.length < 8) erreurs.push("au moins 8 caracteres");
    if (!/[A-Z]/.test(mdp)) erreurs.push("au moins une majuscule");
    if (!/[0-9]/.test(mdp)) erreurs.push("au moins un chiffre");
    if (!/[\W_]/.test(mdp)) erreurs.push("au moins un caractere special");

    if (erreurs.length > 0) {
        showToast("Le mot de passe doit contenir : " + erreurs.join(", ") + ".", "error");
        return false;
    }
    return true;
}


// ===== CHARGEMENT DASHBOARD =====
function chargerDashboard() {
    chargerDashReservations();
    chargerDashPrestations();
    chargerDashActivitesSelect();
    chargerDashActivitesValidees();
    chargerDashFactures();
}

function chargerDashReservations() {
    $.ajax({ url: 'index.php?action=recuperereservations', method: 'GET', dataType: 'json' })
    .done(function(res) {
        if (res.success && res.data && Object.keys(res.data).length > 0) {
            var html = '';
            $.each(res.data, function(i, r) {
                var badgeClass = 'badge-' + (r.statut || 'en-attente').replace('_', '-');
                html += '<div class="dash-history-card">';
                html += '<div class="d-flex justify-content-between align-items-center">';
                html += '<h6>Reservation #' + r.id + '</h6>';
                html += '<span class="badge ' + badgeClass + '">' + (r.statut || 'en attente') + '</span>';
                html += '</div>';
                html += '<small class="text-muted">Du ' + r.date_debut + ' au ' + r.date_fin + '</small>';
                html += '<br><small class="text-muted">' + (r.nombre_personnes || '') + ' personne(s)</small>';
                html += '</div>';
            });
            $('#dash-liste-reservations').html(html);
        } else {
            $('#dash-liste-reservations').html('<p class="text-muted">Aucune reservation pour le moment.</p>');
        }
    })
    .fail(function() {
        $('#dash-liste-reservations').html('<p class="text-muted">Aucune reservation pour le moment.</p>');
    });
}

function chargerDashPrestations() {
    $.ajax({ url: 'index.php?action=recuperereservations', method: 'GET', dataType: 'json' })
    .done(function(resResa) {
        var prestasDejaReservees = [];
        if (resResa.success && resResa.data) {
            $.each(resResa.data, function(i, r) {
                if (r.id_reservation_prestations && r.id_reservation_prestations.length) {
                    $.each(r.id_reservation_prestations, function(j, idP) {
                        prestasDejaReservees.push(String(idP));
                    });
                }
            });
        }
        prestationsAjoutees = prestasDejaReservees.slice();

        $.ajax({ url: 'api/prestations.php', method: 'GET', dataType: 'json' })
        .done(function(prestations) {
            var html = '';
            $.each(prestations, function(i, p) {
                var dejaAjoutee = prestationsAjoutees.indexOf(String(p.id_prestation)) !== -1;
                html += '<div class="dash-presta-item">';
                html += '<div class="dash-presta-info">';
                html += '<h6>' + p.nom + '</h6>';
                html += '<small>' + (p.description || '') + ' — ' + p.prix_unitaire + ' &euro;</small>';
                html += '</div>';
                if (dejaAjoutee) {
                    html += '<button class="btn btn-sm btn-outline-accent btn-ajout-presta disabled" data-id="' + p.id_prestation + '" disabled>Ajoutee</button>';
                } else {
                    html += '<button class="btn btn-sm btn-outline-accent btn-ajout-presta" data-id="' + p.id_prestation + '">Ajouter</button>';
                }
                html += '</div>';
            });
            $('#dash-liste-prestations').html(html);
        })
        .fail(function() {
            $('#dash-liste-prestations').html('<p class="text-muted">Prestations indisponibles.</p>');
        });
    });
}

function chargerDashActivitesSelect() {
    $.ajax({ url: 'api/activites.php', method: 'GET', dataType: 'json' })
    .done(function(activites) {
        var html = '<option value="" selected disabled>Choisir...</option>';
        $.each(activites, function(i, act) {
            html += '<option value="' + act.id_activite + '">' + act.nom + ' (' + act.prix + ' &euro;)</option>';
        });
        $('#da-activite').html(html);
    });
}

function chargerDashActivitesValidees() {
    $.ajax({ url: 'index.php?action=recupereactivitesvalidees', method: 'GET', dataType: 'json' })
    .done(function(res) {
        if (res.success && res.data && res.data.length > 0) {
            var html = '';
            $.each(res.data, function(i, a) {
                html += '<div class="dash-history-card">';
                html += '<div class="d-flex justify-content-between align-items-center">';
                html += '<h6>' + a.nom + '</h6>';
                html += '<span class="badge bg-success">Validee</span>';
                html += '</div>';
                html += '<small class="text-muted">' + a.date + ' — ' + a.creneau + '</small>';
                if (a.message) {
                    html += '<br><small class="text-muted">' + a.message + '</small>';
                }
                html += '</div>';
            });
            $('#dash-activites-validees').html(html);
        } else {
            $('#dash-activites-validees').html('<p class="text-muted">Aucune activite validee pour le moment.</p>');
        }
    })
    .fail(function() {
        $('#dash-activites-validees').html('<p class="text-muted">Aucune activite validee pour le moment.</p>');
    });
}

function chargerDashFactures() {
    $.ajax({ url: 'index.php?action=recuperefactures', method: 'GET', dataType: 'json' })
    .done(function(res) {
        if (res.success && res.data && res.data.length > 0) {
            var html = '';
            $.each(res.data, function(i, f) {
                html += '<div class="dash-history-card">';

                // En-tête
                html += '<div class="d-flex justify-content-between align-items-center mb-2">';
                html += '<h6>Facture #' + f.id + '</h6>';
                var badgeClass = f.statut === 'payée' ? 'bg-success' : 'bg-warning text-dark';
                html += '<span class="badge ' + badgeClass + '">' + (f.statut || 'en attente') + '</span>';
                html += '</div>';

                // Dates
                if (f.date_debut && f.date_fin) {
                    html += '<small class="text-muted">Du ' + f.date_debut + ' au ' + f.date_fin + '</small><br>';
                }

                // Tableau détail
                html += '<table class="table table-sm mt-2" style="font-size:0.85rem;">';
                html += '<thead><tr><th>Type</th><th>Description</th><th class="text-end">Prix</th></tr></thead>';
                html += '<tbody>';

                // Chambre
                if (f.chambre) {
                    var prixChambre = (f.prix_nuit || 0) * (f.nuits || 0);
                    html += '<tr><td>Chambre</td><td>' + f.chambre + '<br><small>' + f.nuits + ' nuit(s)</small></td>';
                    html += '<td class="text-end">' + prixChambre.toFixed(2) + ' &euro;</td></tr>';
                }

                // Prestations
                $.each(f.prestations || [], function(j, p) {
                    html += '<tr><td>Prestation</td><td>' + p.nom;
                    if (p.quantite > 1) html += ' x' + p.quantite;
                    if (p.reduction > 0) html += ' <small>(-' + p.reduction + '%)</small>';
                    html += '</td><td class="text-end">' + parseFloat(p.total).toFixed(2) + ' &euro;</td></tr>';
                });

                // Activités
                $.each(f.activites || [], function(j, a) {
                    html += '<tr><td>Activite</td><td>' + a.nom + '</td>';
                    html += '<td class="text-end">' + parseFloat(a.prix).toFixed(2) + ' &euro;</td></tr>';
                });

                // Avoirs
                if (f.avoirs > 0) {
                    html += '<tr><td>Avoirs</td><td>Depot / arrhes</td>';
                    html += '<td class="text-end text-success">-' + parseFloat(f.avoirs).toFixed(2) + ' &euro;</td></tr>';
                }

                // Réduction
                if (f.reduction > 0) {
                    html += '<tr><td>Reduction</td><td>' + f.reduction + '%</td>';
                    html += '<td class="text-end text-success">-' + parseFloat(f.reduction).toFixed(2) + ' &euro;</td></tr>';
                }

                html += '</tbody>';

                // Total
                html += '<tfoot><tr><td colspan="2" class="text-end"><strong>Total</strong></td>';
                html += '<td class="text-end"><strong>' + parseFloat(f.montant_final || f.montant_total).toFixed(2) + ' &euro;</strong></td></tr></tfoot>';
                html += '</table>';

                // Bouton télécharger
                html += '<button class="btn btn-sm btn-outline-accent btn-telecharger-facture" data-id-reservation="' + f.id_reservation + '">Telecharger PDF</button>';

                html += '</div>';
            });
            $('#dash-liste-factures').html(html);
        } else {
            $('#dash-liste-factures').html('<p class="text-muted">Aucune facture pour le moment.</p>');
        }
    })
    .fail(function() {
        $('#dash-liste-factures').html('<p class="text-muted">Aucune facture pour le moment.</p>');
    });
}


// ===== FONCTIONS AJAX - ACCUEIL =====

function chargerChambresAccueil() {
    $.ajax({ url: 'api/chambres.php', method: 'GET', dataType: 'json' })
    .done(function(chambres) {
        var html = '';
        $.each(chambres, function(i, ch) {
            html += '<div class="carrousel-item">';
            html += '  <div class="glass-card">';
            html += '    <h5>' + ch.nom_chambre + '</h5>';
            html += '    <p class="text-muted">' + ch.type_chambre + ' - ' + ch.capacite + ' pers.</p>';
            html += '    <p class="prix">' + ch.prix_nuit + ' &euro; / nuit</p>';
            html += '  </div>';
            html += '</div>';
        });
        $('#liste-chambres').html(html);
        majFleches($('#liste-chambres'));
    })
    .fail(function() {
        $('#liste-chambres').html('<p class="text-muted">Les chambres seront disponibles prochainement.</p>');
    });
}

function chargerActivitesAccueil() {
    $.ajax({ url: 'api/activites.php', method: 'GET', dataType: 'json' })
    .done(function(activites) {
        var html = '';
        $.each(activites, function(i, act) {
            var badgeClass = 'bg-secondary';
            if (act.type) {
                var t = act.type.toLowerCase();
                if (t === 'aquatique') badgeClass = 'badge-aquatique';
                else if (t === 'exterieur') badgeClass = 'badge-exterieur';
                else if (t === 'interieur') badgeClass = 'badge-interieur';
                else if (t === 'collectif') badgeClass = 'badge-collectif';
                else if (t === 'individuel') badgeClass = 'badge-individuel';
            }

            html += '<div class="carrousel-item">';
            html += '  <div class="glass-card">';
            html += '    <h5>' + act.nom + '</h5>';
            html += '    <span class="badge ' + badgeClass + '">' + (act.type || '') + '</span>';
            html += '    <p class="text-muted mt-2">' + act.capacite_min + ' - ' + act.capacite_max + ' personnes</p>';
            html += '    <p class="prix">' + act.prix + ' &euro;</p>';
            html += '  </div>';
            html += '</div>';
        });
        $('#liste-activites').html(html);
        majFleches($('#liste-activites'));
    })
    .fail(function() {
        $('#liste-activites').html('<p class="text-muted">Les activites seront disponibles prochainement.</p>');
    });
}

function chargerPrestationsAccueil() {
    $.ajax({ url: 'api/prestations.php', method: 'GET', dataType: 'json' })
    .done(function(prestations) {
        var html = '';
        $.each(prestations, function(i, p) {
            html += '<div class="carrousel-item">';
            html += '  <div class="glass-card">';
            html += '    <h5>' + p.nom + '</h5>';
            html += '    <p class="text-muted">' + (p.description || '') + '</p>';
            html += '    <p class="prix">' + p.prix_unitaire + ' &euro;</p>';
            html += '  </div>';
            html += '</div>';
        });
        $('#liste-prestations').html(html);
        majFleches($('#liste-prestations'));
    })
    .fail(function() {
        $('#liste-prestations').html('<p class="text-muted">Les prestations seront disponibles prochainement.</p>');
    });
}


// ===== REMPLISSAGE FORMULAIRE RESERVATION =====

function chargerChambresFormulaire() {
    $.ajax({ url: 'api/chambres.php', method: 'GET', dataType: 'json' })
    .done(function(chambres) {
        var html = '<option value="" selected disabled>Choisir une chambre...</option>';
        $.each(chambres, function(i, ch) {
            html += '<option value="' + ch.id_chambre + '" data-capacite="' + ch.capacite + '">';
            html += ch.nom_chambre + ' (' + ch.type_chambre + ') - ' + ch.capacite + ' pers. - ' + ch.prix_nuit + ' &euro;/nuit';
            html += '</option>';
        });
        $('#res-chambre').html(html);
    });
}

function majChambresDisponibles() {
    var debut = $('#res-date-debut').val();
    var fin = $('#res-date-fin').val();
    var personnes = parseInt($('#res-personnes').val()) || 0;

    if (!debut || !fin) return;

    $.ajax({
        url: 'index.php?action=chambresdisponibles&date_debut=' + debut + '&date_fin=' + fin,
        method: 'GET',
        dataType: 'json'
    })
    .done(function(res) {
        if (res.success) {
            var html = '<option value="" selected disabled>Choisir une chambre...</option>';
            var count = 0;
            $.each(res.data, function(i, ch) {
                if (personnes > 0 && ch.capacite < personnes) return;
                html += '<option value="' + ch.id_chambre + '" data-capacite="' + ch.capacite + '">';
                html += ch.nom_chambre + ' (' + ch.type_chambre + ') - ' + ch.capacite + ' pers. - ' + ch.prix_nuit + ' &euro;/nuit';
                html += '</option>';
                count++;
            });
            if (count === 0) {
                html = '<option value="" selected disabled>Aucune chambre disponible</option>';
            }
            $('#res-chambre').html(html);
        }
    });
}

function chargerActivitesFormulaire() {
    $.ajax({ url: 'api/activites.php', method: 'GET', dataType: 'json' })
    .done(function(activites) {
        var html = '';
        $.each(activites, function(i, act) {
            html += '<div class="col-md-6 mb-2">';
            html += '  <div class="form-check">';
            html += '    <input class="form-check-input" type="checkbox" name="activites[]" value="' + act.id_activite + '" id="act-' + act.id_activite + '">';
            html += '    <label class="form-check-label" for="act-' + act.id_activite + '">';
            html += '      ' + act.nom + ' <small class="text-muted">(' + act.prix + ' &euro;)</small>';
            html += '    </label>';
            html += '  </div>';
            html += '</div>';
        });
        $('#res-activites').html(html);
    });
}
