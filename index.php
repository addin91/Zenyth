<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/database/db/JsonDB.php';
require_once __DIR__ . '/controllers/controllersAuthentification.php';
require_once __DIR__ . '/controllers/controllersAdmin.php';
require_once __DIR__ . '/controllers/controllersReservations.php';
require_once __DIR__ . '/controllers/controllersFacture.php';
require_once __DIR__ . '/controllers/controllersApi.php';

$action = isset($_GET['action']) ? strtolower($_GET['action']) : 'index';


error_log(ini_get('session.gc_maxlifetime'));

switch ($action) {

    // --- CONNEXION ---
    case 'login':
        $controller = new controllersAuthentification();
        $controller->connexion();
        break;

    // --- DECONNEXION ---
    case 'logout':
        $controller = new controllersAuthentification();
        $controller->deconnexion();
        break;

    // --- MOT DE PASSE OUBLIE ---
    case 'motdepasseoublie':
        $controller = new controllersAuthentification();
        $controller->motDePasseOublie();
        break;

    // --- CHANGEMENT MOT DE PASSE ---
    case 'changementmotdepasse':
        $controller = new controllersAuthentification();
        $controller->changementMotDePasse();
        break;

    // --- RESERVATION CHAMBRE ---
    case 'reservationchambre':
        $controller = new controllersReservations();
        $controller->reservationChambre();
        break;

    // --- RESERVATION PRESTATION ---
    case 'reservationprestation':
        $controller = new controllersReservations();
        $controller->reservationPrestation();
        break;

    // --- RESERVATION ACTIVITE ---
    case 'reservationactivite':
        $controller = new controllersReservations();
        $controller->reservationActivite();
        break;

    // --- RESERVATIONS CLIENT ---
    case 'recuperereservations':
        $controller = new controllersReservations();
        $controller->recupeRereservations();
        break;

    // --- FACTURES ---
    case 'recuperefactures':
        $controller = new controllersFacture();
        $controller->recupereFactures();
        break;

    case 'recuperefactureencours':
        $controller = new controllersFacture();
        $controller->recupereFactureEnCours();
        break;

    case 'telechargementfacture':
        $controller = new controllersFacture();
        $controller->telechargementFacture();
        break;

    // --- ACTIVITES VALIDEES ---
    case 'recupereactivitesvalidees':
        $controller = new controllersReservations();
        $controller->ActivitesValidees();
        break;

    // --- CHAMBRES DISPONIBLES ---
    case 'chambresdisponibles':
        $controller = new controllersReservations();
        $controller->chambresDisponibles();
        break;

    // --- PAGE ADMIN (front uniquement, la vue ge re l'auth via popup) ---
    case 'admin':
        require_once __DIR__ . '/views/admin.php';
        break;

    case 'loginadmin':
        $controller = new controllersAuthentification();
        $controller->connexionAdmin();
        break;

    case 'adminrecuperereservations':
        $controller = new controllersAdmin();
        $controller->recupereReservation();
        break;
    
    case 'adminaccepterreservation':
        $controller = new controllersAdmin();
        $controller->accepteReservationChambre();
        break;

    case 'adminrefuserreservation':
        $controller = new controllersAdmin();
        $controller->refuseReservationChambre();
        break;

    case 'adminrefuseractvite':
        $controller = new controllersAdmin();
        $controller->refuserActvite();
        break;

    case 'admintoggleprestation':
        $controller = new controllersAdmin();
        $controller->gereActivation();
        break;

    case 'adminvaliderdemandeactivite':
        $controller = new controllersAdmin();
        $controller->prevoirActivite();
        break;

    case 'admincreeranimateur':
        $controller = new controllersAdmin();
        $controller->creerAnimateur();
        break;

    case 'adminsupprimeranimateur':
        $controller = new controllersAdmin();
        $controller->supprimerAnimateur();
        break;

    case 'adminrecuperefactures':
        $controller = new controllersAdmin();
        $controller->recuperefactures();
        break;

    case 'adminediterfacture':
        $controller = new controllersAdmin();
        $controller->editerFacture();
        break;

    case 'adminemettrefacture':
        $controller = new controllersAdmin();
        $controller->emettreFacture();
        break;

    // --- API ---
    case 'apichambre':
        $controller = new controllersApi();
        $controller->recupereChambre();
        break;

    case 'apiactivite':
        $controller = new controllersApi();
        $controller->recupereActivite();
        break;     

    case 'apiprestation':
        $controller = new controllersApi();
        $controller->recuperePrestations();
        break;     

    case 'apianimateur':
        $controller = new controllersApi();
        $controller->recupereAnimateur();
        break;
        
    case 'apiactivitesprevues':
        $controller = new controllersApi();
        $controller->recupereActivitesPrevues();
        break;

    
    case 'apidemandesactivites':
        $controller = new controllersApi();
        $controller->recupereDemandesActivites();
        break;

    case 'apiclient':
        $controller = new controllersApi();
        $controller->recupereClient();
        break;

    // --- PAGE PRINCIPALE ---
    case 'index':
    default:
        require_once __DIR__ . '/views/index.php';
        break;
}
?>
