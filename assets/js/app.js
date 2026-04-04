$(document).ready(function() {

    // ===== POPUPS =====
    $('[data-popup]').on('click', function(e) {
        e.preventDefault();
        var nom = $(this).data('popup');
        $('#popup-' + nom).addClass('active');
        // Ferme le menu mobile si ouvert
        $('.navbar-collapse').collapse('hide');
    });

    // Fermer avec le bouton X
    $('.popup-close').on('click', function() {
        $(this).closest('.popup-overlay').removeClass('active');
    });

    // Fermer en cliquant sur le fond
    $('.popup-overlay').on('click', function(e) {
        if (e.target === this) {
            $(this).removeClass('active');
        }
    });

    // Fermer avec Echap
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
        var formData = $(this).serialize() + '&action=connexion';

        $.post('index.php', formData, function(res) {
            if (res.success) {
                $('#popup-connexion').removeClass('active');
                $('#nav-connexion').addClass('d-none');
                $('#nav-dashboard').removeClass('d-none');
                $('#nav-deconnexion').removeClass('d-none');
                $('#bloc-info-client').hide();
                // Remplir les infos perso dans le dashboard
                $('#info-nom').val(res.nom);
                $('#info-prenom').val(res.prenom);
                $('#info-email').val(res.email);
                alert('Bienvenue ' + res.prenom + ' ' + res.nom + ' !');
            } else {
                alert(res.error);
            }
        }, 'json');
    });

    // ===== DECONNEXION =====
    $('#btn-deconnexion').on('click', function(e) {
        e.preventDefault();
        $.post('index.php', { action: 'deconnexion' }, function(res) {
            if (res.success) {
                $('#nav-connexion').removeClass('d-none');
                $('#nav-dashboard').addClass('d-none');
                $('#nav-deconnexion').addClass('d-none');
                $('#bloc-info-client').show();
            }
        }, 'json');
    });

    // ===== FORMULAIRE MOT DE PASSE OUBLIE =====
    $('#form-mdp-oublie').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&action=mdp_oublie';

        $.post('index.php', formData, function(res) {
            alert(res.message || res.error);
            $('#bloc-mdp-oublie').slideUp(300);
        }, 'json');
    });

    // ===== FORMULAIRE RESERVATION =====
    $('#form-reservation').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&action=reservation';

        $.post('index.php', formData, function(res) {
            if (res.success) {
                alert(res.message);
                $('#popup-reservation').removeClass('active');
                $('#form-reservation')[0].reset();
            } else {
                alert(res.error);
            }
        }, 'json');
    });

    // ===== CARROUSEL =====
    $('.carrousel-next').on('click', function() {
        var track = $('#' + $(this).data('cible'));
        var item = track.find('.carrousel-item').first();
        var step = item.outerWidth() + 20;
        track.scrollLeft(track.scrollLeft() + step * 3);
        setTimeout(function() { majFleches(track); }, 550);
    });

    $('.carrousel-prev').on('click', function() {
        var track = $('#' + $(this).data('cible'));
        var item = track.find('.carrousel-item').first();
        var step = item.outerWidth() + 20;
        track.scrollLeft(track.scrollLeft() - step * 3);
        setTimeout(function() { majFleches(track); }, 550);
    });

    // ===== ONGLETS DASHBOARD =====
    $('.dash-tab').on('click', function() {
        $('.dash-tab').removeClass('active');
        $(this).addClass('active');
        $('.dash-panel').removeClass('active');
        $('#' + $(this).data('tab')).addClass('active');
    });

    // ===== CHARGEMENT INITIAL (AJAX) =====
    chargerChambresAccueil();
    chargerActivitesAccueil();
    chargerPrestationsAccueil();
    chargerChambresFormulaire();
    chargerActivitesFormulaire();

});


// ===== CARROUSEL - MAJ FLECHES =====
function majFleches(track) {
    var wrapper = track.closest('.carrousel-wrapper');
    var scrollLeft = track.scrollLeft();
    var maxScroll = track[0].scrollWidth - track[0].clientWidth;

    wrapper.find('.carrousel-prev').toggleClass('carrousel-disabled', scrollLeft <= 0);
    wrapper.find('.carrousel-next').toggleClass('carrousel-disabled', scrollLeft >= maxScroll - 1);
}


// ===== FONCTIONS AJAX =====

function chargerChambresAccueil() {
    $.ajax({
        url: 'api/chambres.php',
        method: 'GET',
        dataType: 'json'
    })
    .done(function(chambres) {
        var html = '';
        $.each(chambres, function(i, ch) {
            html += '<div class="carrousel-item">';
            html += '  <div class="glass-card">';
            html += '    <h5>' + ch.nom + '</h5>';
            html += '    <p class="text-muted">' + ch.type + ' - ' + ch.capacite + ' pers.</p>';
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
    $.ajax({
        url: 'api/activites.php',
        method: 'GET',
        dataType: 'json'
    })
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
    $.ajax({
        url: 'api/prestations.php',
        method: 'GET',
        dataType: 'json'
    })
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
    $.ajax({
        url: 'api/chambres.php',
        method: 'GET',
        dataType: 'json'
    })
    .done(function(chambres) {
        var html = '<option value="" selected disabled>Choisir une chambre...</option>';
        $.each(chambres, function(i, ch) {
            html += '<option value="' + ch.id + '">';
            html += ch.nom + ' (' + ch.type + ') - ' + ch.capacite + ' pers. - ' + ch.prix_nuit + ' &euro;/nuit';
            html += '</option>';
        });
        $('#res-chambre').html(html);
    });
}

function chargerActivitesFormulaire() {
    $.ajax({
        url: 'api/activites.php',
        method: 'GET',
        dataType: 'json'
    })
    .done(function(activites) {
        var html = '';
        $.each(activites, function(i, act) {
            html += '<div class="col-md-6 mb-2">';
            html += '  <div class="form-check">';
            html += '    <input class="form-check-input" type="checkbox" name="activites[]" value="' + act.id + '" id="act-' + act.id + '">';
            html += '    <label class="form-check-label" for="act-' + act.id + '">';
            html += '      ' + act.nom + ' <small class="text-muted">(' + act.prix + ' &euro;)</small>';
            html += '    </label>';
            html += '  </div>';
            html += '</div>';
        });
        $('#res-activites').html(html);
    });
}