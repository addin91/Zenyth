<?php


require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../models/Facture.php';
require_once __DIR__ . '/../models/Client.php';

require 'vendor/autoload.php';

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
            echo json_encode(['success' => true, 'data' => $factures]);
        }else {
            echo json_encode(['success' => false, 'error' => 'Non connecte.']);
        }
    }


    // recup facture en cours
    public function recupereFactureEnCours($idReservation){
        return $this->factureModel->findByReservation($idReservation);
    }

    // télécharger facture
    public function telechargementFacture($idFacture){
        // 🔹 Récupération de la facture
        $facture = $this->factureModel->findById($idFacture);

        if (!$facture) {
            die("Facture introuvable");
        }

        // 🔹 Récupération des données liées
        $client = $this->clientModel->findById($facture["id_client"]);
        $reservation = $this->reservationModel->findById($facture["id_reservation"]);

        // 🔹 Modèles
        $reservationChambreModel = new ReservationChambre();
        $reservationPrestationModel = new ReservationPrestation();
        $reservationActiviteModel = new ReservationActivite();

        // 🔹 Données liées à la facture
        $reservationChambre = $reservationChambreModel->findById($facture["id_reservation_chambre"]);
        foreach($facture["id_reservation_prestation"] as $id) $reservationPrestations[] = $reservationPrestationModel->findById($id);
        foreach($facture["id_demande_activite"] as $id) $reservationActivite[] = $reservationActiviteModel->findById($id);

        // 🔹 Calcul du total (exemple)
        $total = 0;

        // chambre
        if ($reservationChambre) {
            $total += $reservationChambre["prix"];
        }

        // prestations
        if ($reservationPrestations) {
            foreach ($reservationPrestations as $prestation) {
                $total += $prestation["prix"];
            }
        }

        // activités
        if ($reservationActivite) {
            foreach ($reservationActivite as $activite) {
                if ($activite["actif"]) {
                    $total += $activite["prix"];
                }
            }
        }

        // 🔹 Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        // 🔹 Passage des variables à la vue
        ob_start();
        include __DIR__ . '/../views/invoices/invoice.php';
        $html = ob_get_clean();

        // 🔹 Génération PDF
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // 🔹 Téléchargement
        $dompdf->stream("facture_" . $facture["id"] . ".pdf", ["Attachment" => true]);

        exit;
    }
    
}

?>


