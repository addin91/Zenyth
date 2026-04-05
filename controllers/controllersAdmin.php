<?php


require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/MailService.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../models/ReservationChambre.php';
require_once __DIR__ . '/../models/Activite.php';
require_once __DIR__ . '/../models/Animateur.php';
require_once __DIR__ . '/../models/DemandeActivite.php';
require_once __DIR__ . '/../models/ActivitePrevue.php';

class controllersAdmin{
    private $clientModel;
    private $reservationModel;

    public function __construct()
    {
        $this->clientModel = new Client();
        $this->reservationModel = new Reservation();
    }

    public function accepteReservationChambre($idReservationChambre){
        $reservationChambreModel = new ReservationChambre();
        $reservationChambre = $reservationChambreModel->findById($idReservationChambre);
        // créer client 
        $reservation = $this->reservationModel->findById($reservationChambre["id_reservation"]);
        $client = $this->clientModel->findById($reservation["id_client"]);
        $motDePasse = $this->clientModel->definiMotDePasseClient($client["id"]);
        $this->clientModel->activeClient($client["id"]);
        $mailservice = new MailService();
        $mailservice->envoiePassword($client["email"], $motDePasse);
        // accepte reservation
        $reservationChambreModel->validerReservation($idReservationChambre);
    }


    // refuse acceptation
    public function refuseReservationChambre($idReservationChambre){
        $reservationChambreModel = new reservationChambre();
        $reservation = $this->reservationModel->findById($reservationChambreModel["id_reservation"]);
        $client = $this->clientModel->findById($reservation["id_client"]);

        $reservationChambreModel->delete($idReservationChambre);
        $reservation->delete($reservationChambreModel["id_reservation"]);
        if($client["statut_compte"] == "invité") $client->delete($reservation["id_client"]);

        $mailservice = new MailService();
        $mailservice->envoieMail($client["email"], "Reservation annulé", "Votre réservation a été annulé", true);
    }

    public function prevoirActivite($id_activite, $id_animateur, $id_demandes_actvites, $date, $creneau, $message){

        $activiteModel = new Activite();
        $activite = $activiteModel->findById($id_activite);

        $animateurModel = new Animateur();
        $animateur = $animateurModel->findById($id_animateur);

        $capacite_restante = $activite["capacite_min"];
        $demandeActiviteModel = new DemandeActivite();
        foreach($id_demandes_actvites as $id){
            $demandeActivite = $demandeActiviteModel->findById($id);
            $capacite_restante-= $demandeActivite["nombre_personnes_concernees"];
        } 

        if($capacite_restante < 0) // Erreur;
        $activitePrevuModel = new ActivitePrevue();
        $activitePrevu = $activitePrevuModel->create($id_activite, $id_animateur, $id_demandes_actvites, $date, $creneau, $message, $capacite_restante);
    }

}

?>