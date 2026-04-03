<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Reservation.php';

class controllersAdmin{
    private $pdo;
    private $clientModel;
    private $reservationModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->clientModel = new Client($pdo);
        $this->reservationModel = new Reservation($pdo);
    }

    public function accepteReservationChambre($idReservationChambre){
        $reservationChambreModel = new reservationChambre($this->pdo);
        // créer client 
        $reservation = $this->reservationModel.findById($reservationChambreModel["id_reservation"]);
        $client = $this->clientModel.findById($reservation["id_client"]);
        $motDePasse = $client.definiMotDePasseClient($client["id"]);
        $client.activeClient($client["id"]);
        $mailservice = new MailService();
        $mailservice.envoiePassword($client["email"], $motDePasse);
        // accepte reservation
        $reservationChambreModel.validerReservation($idReservationChambre);
    }


    // refuse acceptation
    public function refuseReservationChambre($idReservationChambre){
        $reservationChambreModel = new reservationChambre($this->pdo);
        $reservation = $this->reservationModel.findById($reservationChambreModel["id_reservation"]);
        $client = $this->clientModel.findById($reservation["id_client"]);

        $reservationChambreModel.delete($idReservationChambre);
        $reservation.delete($reservationChambreModel["id_reservation"]);
        if($client["statut_compte"] == "invité") $client.delete($reservation["id_client"]);

        $mailservice = new MailService();
        $mailservice.envoieMail($client["email"], "Reservation annulé", "Votre réservation a été annulé", true);
    }

    public function prevoirActivite(){
        
    }
}

?>