<?php


require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../models/Facture.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Chambre.php';
require_once __DIR__ . '/../models/ReservationChambre.php';
require_once __DIR__ . '/../models/ReservationPrestation.php';
require_once __DIR__ . '/../models/Prestation.php';
require_once __DIR__ . '/../models/DemandeActivite.php';
require_once __DIR__ . '/../models/Activite.php';

require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class controllersFacture{
    private $clientModel;
    private $reservationModel;
    private $factureModel;

    public function __construct()
    {
        $this->clientModel = new Client();
        $this->reservationModel = new Reservation();
        $this->factureModel = new Facture();
    }
    // recup toute les factures client
    private function toutesFactures($idClient){
        $reservations = $this->reservationModel->findByClient($idClient);

        $factures = [];
        foreach($reservations as $reservation){
            $facture = $this->factureModel->findByReservation($reservation["id"]);
            if($facture) $factures[] = $facture;
        }
        return $factures;
    }
    
    public function recupereFactures(){
        header('Content-Type: application/json');
        if (isset($_SESSION['user_id'])) {
            $factures = $this->toutesFactures($_SESSION['user_id']);
            foreach($factures as &$facture){
                $reservation = $this->reservationModel->findById($facture['id_reservation']);
            
                $debut = new DateTime($reservation['date_debut']);
                $fin = new DateTime($reservation['date_fin']);
                $interval = $debut->diff($fin);
                $nbNuits = $interval->days;

                $reservationChambreModel = new ReservationChambre();
                $reservationChambre = $reservationChambreModel->findById($facture["id_reservation_chambre"]);
                $chambreModel = new Chambre();
                $chambre = $chambreModel->findById($reservationChambre["id_chambre"]);
                
                $reservationPrestationModel = new ReservationPrestation();
                $reservationActiviteModel = new DemandeActivite();
                $reservationPrestations = [];
                $reservationActivites = [];
                foreach($reservation["id_reservation_prestations"] as $id) $reservationPrestations[] = $reservationPrestationModel->findById($id);
                foreach($reservation["id_demandes_activite"] as $id) $reservationActivites[] = $reservationActiviteModel->findById($id);
                $facture['date_debut'] = $reservation['date_debut'];
                $facture['date_fin'] = $reservation['date_fin'];
                $facture['nuits'] = $nbNuits;
                $facture['chambre'] = $chambre['nom_chambre'];
                $facture['prix_nuit'] = $chambre['prix_nuit'];
                $facture['prestations'] = $reservationPrestations;
                $facture['activites'] = $reservationActivites;
                
            }
            $this->factureModel->update($facture["id"],
                [
                    "id_reservations_prestation" => $reservation["id_reservation_prestations"],
                    "id_demandes_activite" => $reservation["id_demandes_activite"]
                ]
            );
            echo json_encode(['success' => true, 'data' => $factures]);
            return;
        } echo json_encode(['success' => false, 'error' => 'Non connecte.']); 
    }


    // recup facture en cours
    public function recupereFactureEnCours($idReservation){
        return $this->factureModel->findByReservation($idReservation);
    }

    // télécharger facture
    public function telechargementFacture(){
        if(isset($_GET["id_facture"])) $idFacture = $_GET["id_facture"];
        // 🔹 Récupération de la facture
        $facture = $this->factureModel->findById($idFacture);

        if (!$facture) {
            die("Facture introuvable");
        }

        if (!isLoggedIn() || ($facture['id_client'] !== $_SESSION['user_id'] && !isAdmin())) {
            die("Accès non autorisé");
        }

        // 🔹 Récupération des données liées
        $client = $this->clientModel->findById($facture["id_client"]);
        $reservation = $this->reservationModel->findById($facture["id_reservation"]);

        // 🔹 Modèles
        $reservationChambreModel = new ReservationChambre();
        $reservationPrestationModel = new ReservationPrestation();
        $reservationActiviteModel = new DemandeActivite();

        // 🔹 Données liées à la facture
        $reservationChambre = $reservationChambreModel->findById($facture["id_reservation_chambre"]);
        $reservationPrestations = [];
        $reservationActivites = [];
        
        $total = 0;

        // chambre
        if ($reservationChambre) {
            $chambreModel = new Chambre();
            $chambre = $chambreModel->findById($reservationChambre["id_chambre"]);
            $total += $chambre["prix"] ?? 0;
        }


        if(isset($facture["id_reservation_prestation"])){
            foreach($facture["id_reservation_prestation"] as $id){
                $reservationPrestation = $reservationPrestationModel->findById($id);
                $prestationModel = new Prestation();
                $prestation = $prestationModel->findById($reservationPrestation["id_prestation"]);
                $reservationPrestation["nom"] = $prestation['nom'];
                $total += $reservationPrestation["total"] ?? 0;
                $reservationPrestations[] = $reservationPrestation;
            } 
        }
        if(isset($facture["id_demande_activite"])){
            foreach($facture["id_demande_activite"] as $id){
                $reservationActivite = $reservationActiviteModel->findById($id);
                if ($reservationActivite["statut"] === "validee") {
                    $activiteModel = new Activite();
                    $activite = $activiteModel->findById($reservationActivite["id_activite"]);
                    $reservationActivite["nom"] = $activite['nom'];
                    $total += $activite["prix"] ?? 0;
                }
                $reservationActivites[] = $reservationActivite;
            }
        }
         

        // 🔹 Calcul du total (exemple)
        

        // 🔹 Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        // 🔹 Passage des variables à la vue
        ob_start();
        include __DIR__ . '/../views/template/factureTemplate.php';
        $html = ob_get_clean();

        // 🔹 Génération PDF
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // 🔹 Téléchargement
        $dompdf->stream("facture_" . $facture["id"] . ".pdf", ["Attachment" => false]);

        exit;
    }
    
}

?>


