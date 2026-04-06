<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/database/db/JsonDB.php';
require_once __DIR__ . '/controllers/controllersAuthentification.php';
require_once __DIR__ . '/controllers/controllersReservations.php';
require_once __DIR__ . '/controllers/controllersFacture.php';


$action = isset($_GET['action']) ? strtolower($_GET['action']) : 'index';


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
    
    // --- CHAMBRES DISPONIBLES ---
    case 'chambresdisponibles':
        $controller = new controllersReservations();
        $controller->chambresDispo();
        break;
    
    // --- ACTIVITES VALIDEES ---
    case 'recupereactivitesvalidees':
        $controller = new controllersReservations();
        $controller->ActivitesValidees();
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
        $controller->chambresDispo();
        break;

    // --- PAGE PRINCIPALE ---
    case 'index':
    default:
        require_once __DIR__ . '/views/index.php';
        break;
}
?>
