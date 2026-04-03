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

    // ===== CHARGEMENT INITIAL (AJAX) =====
    chargerChambresAccueil();
    chargerActivitesAccueil();
    chargerPrestationsAccueil();

});


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
            html += '<div class="col-md-4 mb-4">';
            html += '  <div class="glass-card">';
            html += '    <h5>' + ch.nom_chambre + '</h5>';
            html += '    <p class="text-muted">' + ch.type_chambre + ' - ' + ch.capacite + ' pers.</p>';
            html += '    <p class="prix">' + ch.prix_nuit + ' &euro; / nuit</p>';
            html += '  </div>';
            html += '</div>';
        });
        $('#liste-chambres').html(html);
    })
    .fail(function() {
        var msg = '<p class="text-muted">Les chambres seront disponibles prochainement.</p>';
        $('#liste-chambres').html(msg);
    });
}

function chargerActivitesAccueil() {
    $.ajax({
        url: 'api/activites.php',
        method: 'GET',
        dataType: 'json',
    })
    .done(function(activites) {
        var html = '';
        $.each(activites, function(i, act) {
            // Badge couleur selon le type d'activite
            var badgeClass = 'bg-secondary';
            if (act.type) {
                var t = act.type.toLowerCase();
                if (t === 'aquatique') badgeClass = 'badge-aquatique';
                else if (t === 'exterieur') badgeClass = 'badge-exterieur';
                else if (t === 'interieur') badgeClass = 'badge-interieur';
                else if (t === 'collectif') badgeClass = 'badge-collectif';
                else if (t === 'individuel') badgeClass = 'badge-individuel';
            }

            html += '<div class="col-md-4 mb-4">';
            html += '  <div class="glass-card">';
            html += '    <h5>' + act.nom + '</h5>';
            html += '    <span class="badge ' + badgeClass + '">' + (act.type || '') + '</span>';
            html += '    <p class="text-muted mt-2">' + act.capacite_min + ' - ' + act.capacite_max + ' personnes</p>';
            html += '    <p class="prix">' + act.prix + ' &euro;</p>';
            html += '  </div>';
            html += '</div>';
        });
        $('#liste-activites').html(html);
    })
    .fail(function() {
        var msg = '<p class="text-muted">Les activites seront disponibles prochainement.</p>';
        $('#liste-activites').html(msg);
    });
}

function chargerPrestationsAccueil() {
    $.ajax({
        url: 'api/prestations.php',
        method: 'GET',
        dataType: 'json',
    })
    .done(function(prestations) {
        var html = '';
        $.each(prestations, function(i, p) {
            html += '<div class="col-md-4 mb-4">';
            html += '  <div class="glass-card">';
            html += '    <h5>' + p.nom + '</h5>';
            html += '    <p class="text-muted">' + (p.description || '') + '</p>';
            html += '    <p class="prix">' + p.prix_unitaire + ' &euro;</p>';
            html += '  </div>';
            html += '</div>';
        });
        $('#liste-prestations').html(html);
    })
    .fail(function() {
        var msg = '<p class="text-muted">Les prestations seront disponibles prochainement.</p>';
        $('#liste-prestations').html(msg);
    });
}