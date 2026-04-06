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

    public function accepteReservationChambre($idReservation){
        // créer client 
        $client = $this->clientModel->findById($idReservation);
        $motDePasse = $this->clientModel->definiMotDePasseClient($client["id"]);
        $this->clientModel->activeClient($client["id"]);
        $mailservice = new MailService();
        $mailservice->envoiePassword($client["email"], $motDePasse);
        // accepte reservation
        $this->reservationModel->validerReservation($idReservation);
    }


    // refuse acceptation
    public function refuseReservationChambre($idReservation){
        $reservation = $this->reservationModel->findById($idReservation);
        $client = $this->clientModel->findById($reservation["id_client"]);
        $reservationChambreModel = new ReservationChambre();
        $reservationChambreModel->delete($reservation["id_client"]);
        $this->reservationModel->delete($idReservation);
        if($client["statut_compte"] == "invité") $this->clientModel->delete($reservation["id_client"]);

        $mailservice = new MailService();
        $mailservice->envoieMail($client["email"], "Reservation annulé", "Votre réservation a été annulé", true);
    }

    public function prevoirActivite($id_activite, $id_animateur, $id_demandes_actvites, $date, $creneau, $message){

        $activiteModel = new Activite();
        $activite = $activiteModel->findById($id_activite);

        $animateurModel = new Animateur();
        $animateur = $animateurModel->findById($id_animateur);

        $capacite_restante = $activite["capacite_max"];
        $demandeActiviteModel = new DemandeActivite();
        foreach($id_demandes_actvites as $id){
            $demandeActivite = $demandeActiviteModel->findById($id);
            $capacite_restante-= $demandeActivite["nombre_personnes_concernees"];
        } 

        if($capacite_restante < 0); // Erreur;
        $activitePrevuModel = new ActivitePrevue();
        $activitePrevu = $activitePrevuModel->create($id_activite, $id_animateur, $id_demandes_actvites, $date, $creneau, $message, $capacite_restante);
    }

}

?>