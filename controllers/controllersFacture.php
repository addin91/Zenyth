<?php

class controllersFacture{
    private $pdo;
    private $clientModel;
    private $reservationModel;
    private $factureModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->clientModel = new Client($pdo);
        $this->reservationModel = new Reservation($pdo);
        $this->factureModel = new Facture($pdo);
    }
    // recup toute les factures client
    public function recupereFactures($idClient){
        $reservations = $this->reservationModel->findByClient($idClient);

        $factures = [];
        foreach($reservations as $reservation){
            $facture = $this->factureModel.findByReservation($reservation["id"]);
            if($facture) $factures[] = $facture;
        }
        return $factures;
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


// id
// id_client
// id_reservation
// id_reservation_chambre
// [id_reservation_prestation]
// [id_demande_activite]
// montant total = prix_chambre + (prix_prestation TOUT) + (prix_activite TOUT actif)
// avoirs
// reduction
// statut