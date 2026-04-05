<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/database/db/JsonDB.php';

$action = isset($_GET['action']) ? strtolower($_GET['action']) : 'index';

switch ($action) {

    // --- CONNEXION ---
    case 'login':
        header('Content-Type: application/json');
        require_once __DIR__ . '/controllers/controllersAuthentification.php';
        $controller = new controllersAuthentification();
        $controller->connexion();
        if (isset($_SESSION['user_id'])) {
            echo json_encode([
                'success' => true,
                'message' => 'Connexion reussie.',
                'data' => [
                    'nom' => $_SESSION['user_name'],
                    'prenom' => $_SESSION['user_prenom'],
                    'email' => $_SESSION['user_email']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => $_SESSION['error'] ?? 'Erreur de connexion.']);
            unset($_SESSION['error']);
        }
        break;

    // --- DECONNEXION ---
    case 'logout':
        header('Content-Type: application/json');
        session_destroy();
        echo json_encode(['success' => true]);
        break;

    // --- MOT DE PASSE OUBLIE ---
    case 'motdepasseoublie':
        header('Content-Type: application/json');
        require_once __DIR__ . '/controllers/controllersAuthentification.php';
        $controller = new controllersAuthentification();
        $controller->motDePasseOublie();
        echo json_encode(['success' => true, 'message' => 'Si ce mail existe, un nouveau mot de passe a été envoyé.']);
        break;

    // --- CHANGEMENT MOT DE PASSE ---
    case 'changementmotdepasse':
        header('Content-Type: application/json');
        require_once __DIR__ . '/controllers/controllersAuthentification.php';
        $controller = new controllersAuthentification();
        $controller->changementMotDePasse();
        if (isset($_SESSION['error'])) {
            echo json_encode(['success' => false, 'error' => $_SESSION['error']]);
            unset($_SESSION['error']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Mot de passe modifié.']);
        }
        break;

    // --- RESERVATION CHAMBRE ---
    case 'reservationchambre':
        header('Content-Type: application/json');
        require_once __DIR__ . '/controllers/controllersReservations.php';
        $controller = new controllersReservations();
        $controller->reservationChambre();
        echo json_encode(['success' => true, 'message' => 'Votre demande de reservation a ete envoyee.']);
        break;

    // --- RESERVATION PRESTATION ---
    case 'reservationprestation':
        header('Content-Type: application/json');
        require_once __DIR__ . '/controllers/controllersReservations.php';
        $controller = new controllersReservations();
        $controller->reservationPrestation();
        echo json_encode(['success' => true, 'message' => 'Prestation ajoutee.']);
        break;

    // --- RESERVATION ACTIVITE ---
    case 'reservationactivite':
        header('Content-Type: application/json');
        require_once __DIR__ . '/controllers/controllersReservations.php';
        $controller = new controllersReservations();
        $controller->reservationActivite();
        echo json_encode(['success' => true, 'message' => "Demande d'activite envoyee."]);
        break;

    // --- RESERVATIONS CLIENT ---
    case 'recuperereservations':
        header('Content-Type: application/json');
        if (isset($_SESSION['user_id'])) {
            require_once __DIR__ . '/models/Reservation.php';
            $reservationModel = new Reservation();
            $reservations = $reservationModel->findByClient($_SESSION['user_id']);
            echo json_encode(['success' => true, 'data' => $reservations ?: []]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Non connecte.']);
        }
        break;

    // --- FACTURES ---
    case 'recuperefactures':
        header('Content-Type: application/json');
        if (isset($_SESSION['user_id'])) {
            require_once __DIR__ . '/models/Reservation.php';
            require_once __DIR__ . '/models/Facture.php';
            $reservationModel = new Reservation();
            $factureModel = new Facture();
            $reservations = $reservationModel->findByClient($_SESSION['user_id']);
            $factures = [];
            foreach ($reservations ?: [] as $r) {
                $facture = $factureModel->findByReservation($r['id']);
                if ($facture) $factures[] = $facture;
            }
            echo json_encode(['success' => true, 'data' => $factures]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Non connecte.']);
        }
        break;

    case 'recuperefactureencours':
        header('Content-Type: application/json');
        require_once __DIR__ . '/controllers/controllersFacture.php';
        $controller = new controllersFacture();
        $controller->recupereFactureEnCours();
        break;

    case 'telechargementfacture':
        require_once __DIR__ . '/controllers/controllersFacture.php';
        $controller = new controllersFacture();
        $controller->telechargementFacture();
        break;

    // --- PAGE PRINCIPALE ---
    case 'index':
    default:
        require_once __DIR__ . '/views/index.php';
        break;
}
?>
