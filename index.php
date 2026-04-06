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

    // --- FACTURES ---
    case 'recuperefactures':
        header('Content-Type: application/json');
        if (isset($_SESSION['user_id'])) {
            require_once __DIR__ . '/models/Reservation.php';
            require_once __DIR__ . '/models/Facture.php';
            require_once __DIR__ . '/models/Prestation.php';
            require_once __DIR__ . '/models/Activite.php';

            $reservationModel = new Reservation();
            $factureModel = new Facture();
            $prestationModel = new Prestation();
            $activiteModel = new Activite();
            $chambreDB = new JsonDB("Chambre");
            $rcDB = new JsonDB("ReservationChambre");
            $rpDB = new JsonDB("ReservationPrestation");
            $daDB = new JsonDB("DemandeActivite");

            $reservations = $reservationModel->findByClient($_SESSION['user_id']);
            $factures = [];

            foreach ($reservations ?: [] as $r) {
                $idRes = $r['id_reservation'] ?? $r['id'];
                $facture = $factureModel->findByReservation($idRes);
                if (!$facture) continue;

                // Chambre
                $chambreNom = '';
                $chambrePrix = 0;
                foreach ($rcDB->selectAll() ?: [] as $rc) {
                    if (($rc['id_reservation'] ?? null) == $idRes) {
                        $ch = $chambreDB->find($rc['id_chambre'] ?? 0);
                        if ($ch) {
                            $chambreNom = $ch['nom_chambre'];
                            $chambrePrix = $ch['prix_nuit'];
                        }
                        break;
                    }
                }

                // Nuits
                $nuits = 0;
                if (!empty($r['date_debut']) && !empty($r['date_fin'])) {
                    $nuits = max(0, (new DateTime($r['date_fin']))->diff(new DateTime($r['date_debut']))->days);
                }

                // Prestations
                $prestations = [];
                foreach ($rpDB->selectAll() ?: [] as $rp) {
                    if (($rp['id_reservation'] ?? null) == $idRes) {
                        $p = $prestationModel->findById($rp['id_prestation'] ?? 0);
                        $prestations[] = [
                            'nom' => $p ? $p['nom'] : 'Prestation',
                            'quantite' => $rp['quantite'] ?? 1,
                            'prix_unitaire' => $p ? $p['prix_unitaire'] : 0,
                            'reduction' => $rp['reduction'] ?? 0,
                            'total' => $rp['total'] ?? 0
                        ];
                    }
                }

                // Activités
                $activites = [];
                foreach ($daDB->selectAll() ?: [] as $da) {
                    if (($da['id_reservation'] ?? null) == $idRes) {
                        $act = $activiteModel->findById($da['id_activite'] ?? 0);
                        $activites[] = [
                            'nom' => $act ? $act['nom'] : 'Activite',
                            'prix' => $act ? $act['prix'] : 0
                        ];
                    }
                }

                $montantFinal = $factureModel->calculerMontantFinal(
                    $facture['montant_total'] ?? 0,
                    $facture['avoirs'] ?? 0,
                    $facture['reduction'] ?? 0
                );

                $factures[] = [
                    'id' => $facture['id'],
                    'id_reservation' => $idRes,
                    'date_debut' => $r['date_debut'] ?? '',
                    'date_fin' => $r['date_fin'] ?? '',
                    'nuits' => $nuits,
                    'chambre' => $chambreNom,
                    'prix_nuit' => $chambrePrix,
                    'prestations' => $prestations,
                    'activites' => $activites,
                    'avoirs' => $facture['avoirs'] ?? 0,
                    'reduction' => $facture['reduction'] ?? 0,
                    'montant_total' => $facture['montant_total'] ?? 0,
                    'montant_final' => $montantFinal,
                    'statut' => $facture['statut'] ?? ''
                ];
            }
            echo json_encode(['success' => true, 'data' => $factures]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Non connecte.']);
        }
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
        header('Content-Type: application/json');
        if (isset($_SESSION['user_id'])) {
            require_once __DIR__ . '/models/Reservation.php';
            require_once __DIR__ . '/models/Activite.php';
            $reservationModel = new Reservation();
            $activiteModel = new Activite();
            $reservations = $reservationModel->findByClient($_SESSION['user_id']);

            $result = [];
            if (!empty($reservations)) {
                $reservationIds = [];
                foreach ($reservations as $r) {
                    $reservationIds[] = $r['id_reservation'] ?? $r['id'];
                }

                $demandeDB = new JsonDB("DemandeActivite");
                $prevueDB = new JsonDB("ActivitePrevue");
                $allDemandes = $demandeDB->selectAll();
                $allPrevues = $prevueDB->selectAll();

                foreach ($allDemandes ?: [] as $d) {
                    if (in_array($d['id_reservation'], $reservationIds)) {
                        foreach ($allPrevues ?: [] as $ap) {
                            if ($ap['id_activite'] == $d['id_activite'] && $ap['date'] == $d['date']) {
                                $activite = $activiteModel->findById($d['id_activite']);
                                $result[] = [
                                    'nom' => $activite ? $activite['nom'] : 'Activite',
                                    'date' => $ap['date'],
                                    'creneau' => $ap['creneau'],
                                    'message' => $ap['message'] ?: $d['message']
                                ];
                            }
                        }
                    }
                }
            }
            echo json_encode(['success' => true, 'data' => $result]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Non connecte.']);
        }
        break;

    // --- CHAMBRES DISPONIBLES ---
    case 'chambresdisponibles':
        header('Content-Type: application/json');
        $dateDebut = $_GET['date_debut'] ?? '';
        $dateFin = $_GET['date_fin'] ?? '';

        if (!$dateDebut || !$dateFin) {
            echo json_encode(['success' => false, 'error' => 'Dates manquantes.']);
            break;
        }

        require_once __DIR__ . '/models/Reservation.php';
        $chambreDB = new JsonDB("Chambre");
        $rcDB = new JsonDB("ReservationChambre");
        $reservationModel = new Reservation();

        $toutesChambres = $chambreDB->selectAll();
        $toutesRC = $rcDB->selectAll();

        // Trouver les chambres occupées sur la période
        $chambresOccupees = [];
        foreach ($toutesRC ?: [] as $rc) {
            $idChambre = $rc['id_chambre'] ?? null;
            $idReservation = $rc['id_reservation'] ?? null;
            if (!$idChambre || !$idReservation || $idChambre === 'undefined') continue;

            $reservation = $reservationModel->findById($idReservation);
            if (!$reservation) continue;
            if (($reservation['statut'] ?? '') === 'refusée') continue;

            $rDebut = $reservation['date_debut'] ?? '';
            $rFin = $reservation['date_fin'] ?? '';
            if ($rDebut && $rFin && $dateDebut < $rFin && $dateFin > $rDebut) {
                $chambresOccupees[] = $idChambre;
            }
        }

        $disponibles = [];
        foreach ($toutesChambres ?: [] as $ch) {
            $idCh = $ch['id_chambre'] ?? null;
            if ($idCh && !in_array($idCh, $chambresOccupees)) {
                $disponibles[] = $ch;
            }
        }

        echo json_encode(['success' => true, 'data' => $disponibles]);
        break;

    // --- PAGE PRINCIPALE ---
    case 'index':
    default:
        require_once __DIR__ . '/views/index.php';
        break;
}
?>
