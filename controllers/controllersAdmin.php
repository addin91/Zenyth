<?php


require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/MailService.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../models/ReservationChambre.php';
require_once __DIR__ . '/../models/Activite.php';
require_once __DIR__ . '/../models/Animateur.php';
require_once __DIR__ . '/../models/Facture.php';
require_once __DIR__ . '/../models/DemandeActivite.php';
require_once __DIR__ . '/../models/ActivitePrevue.php';
require_once __DIR__ . '/../models/Prestation.php';

class controllersAdmin{
    private $clientModel;
    private $reservationModel;

    public function __construct()
    {
        $this->clientModel = new Client();
        $this->reservationModel = new Reservation();
    }

    public function accepteReservationChambre(){
        if(isAdmin()){
            if(isset($_POST['id_reservation']) && controlPostForm()){
                $idReservation = $_POST['id_reservation'];
                $reservation = $this->reservationModel->findById($idReservation);
                $client = $this->clientModel->findById($reservation["id_client"]);
                $motDePasse = $this->clientModel->definiMotDePasseClient($client["id"]);
                $this->clientModel->activeClient($client["id"]);
                $mailservice = new MailService();
                $mailservice->envoiePassword($client["email"], $motDePasse);
                // accepte reservation
                $this->reservationModel->validerReservation($idReservation);
                // factures
                $factureModel = new Facture();
                $reservationChambreModel = new ReservationChambre();
                $debut = new DateTime($reservation['date_debut']);
                $fin = new DateTime($reservation['date_fin']);
                $interval = $debut->diff($fin);
                $nbNuits = $interval->days;
                $factureModel->create($client["id"], $idReservation, $reservation["id_reservation_chambre"], [], [], $reservationChambreModel->prixTotalReservationChambre($reservation["id_reservation_chambre"], $nbNuits), 0, 0);
                echo json_encode(['success' => true, 'message' => "Réservation accepté"]);
                return;
            }
        }
        echo json_encode(['success' => false, 'error' => !isAdmin() ? "Autorisation manquante" : "Erreur dans la requete"]);
        return;
    }


    // refuse acceptation
    public function refuseReservationChambre(){
        if(isAdmin()){
            if(isset($_POST['id_reservation']) && controlPostForm()){
                $idReservation = $_POST['id_reservation'];
                $reservation = $this->reservationModel->findById($idReservation);
                $client = $this->clientModel->findById($reservation["id_client"]);
                $reservationChambreModel = new ReservationChambre();

                $reservationChambreModel->deleteByReservation($idReservation);
                $this->reservationModel->updateStatut($idReservation, "refusée");
                $this->reservationModel->update($idReservation,
                    [
                        "id_reservation_chambre" => null,
                        "id_client" => null,

                    ]
                );
                if($client["statut_compte"] == "invité") $this->clientModel->delete($reservation["id_client"]);

                $mailservice = new MailService();
                $mailservice->envoieMail($client["email"], "Reservation annulé", "Votre réservation a été annulé", true);
                echo json_encode(['success' => true, 'message' => "Réservation anulé"]);
                return;
            }
        }
        echo json_encode(['success' => false, 'error' => !isAdmin() ? "Autorisation manquante" : "Erreur dans la requete"]);
        return;
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

    public function recupereReservation(){
        if(isAdmin()){
            if(isset($_GET['statut'])){
            $statut = $_GET['statut'];
            if($statut === "en_attente") $statut = "en_attente";
            else if($statut === "validée") $statut = "validée";
            else if($statut === "refusée") $statut = "refusée";
            else if($statut === "all"){
                echo json_encode(['success' => true, 'data' => $this->reservationModel->findAll()]);
                return;
            }
            else{
                echo json_encode(['success' => false, 'error' => "Requete invalide"]);
                return;
            }
            echo json_encode(['success' => true, 'data' => $this->reservationModel->findByStatut($statut)]);
            return;
            }
        } else echo json_encode(['success' => false, 'error' => "Autorisation manquante"]);
    }

    
    public function gereActivation(){
         if(isAdmin()){
            if(isset($_POST['id_prestation'], $_POST['actif']) && controlPostForm()){
                $idPrestation = $_POST['id_prestation'];
                $actif = $_POST['actif'];
                $prestationModel = new Prestation();
                if($actif == 1) $prestationModel->update($idPrestation, ["actif" => true]);
                else if($actif == 0) $prestationModel->update($idPrestation, ["actif" => false]);
                $prestation = $prestationModel->findById($idPrestation);
                error_log($prestation["actif"]);
                echo json_encode(['success' => true, 'message' => "Statut actif modifié"]);
                return;
            }
        }
        echo json_encode(['success' => false, 'error' => !isAdmin() ? "Autorisation manquante" : "Erreur dans la requete"]);
        return;
    }



    public function recupereDemandesActivites(){
        if(isAdmin()){
            if(isset($_GET['statut'])){
            $statut = $_GET['statut'];
            if($statut === "valide") $statut = "en_attente";
            else if($statut === "all") $statut = "validée";
            else if($statut === "all") $statut = "validée";
            else{
                echo json_encode(['success' => false, 'error' => "Requete invalide"]);
                return;
            }
            echo json_encode(['success' => true, 'data' => $this->reservationModel->findByStatut($statut)]);
            return;
            }
        } else echo json_encode(['success' => false, 'error' => "Autorisation manquante"]);
    }

    public function recuperePrestation(){
        if(isAdmin()){
            if(isset($_GET['statut'])){
            $statut = $_GET['statut'];
            if($statut === "valide") $statut = "en_attente";
            else if($statut === "all") $statut = "validée";
            else if($statut === "all") $statut = "validée";
            else{
                echo json_encode(['success' => false, 'error' => "Requete invalide"]);
                return;
            }
            echo json_encode(['success' => true, 'data' => $this->reservationModel->findByStatut($statut)]);
            return;
            }
        } else echo json_encode(['success' => false, 'error' => "Autorisation manquante"]);
    }

    public function recupereActivite(){
        if(isAdmin()){
            if(isset($_GET['statut'])){
            $statut = $_GET['statut'];
            if($statut === "valide") $statut = "en_attente";
            else if($statut === "all") $statut = "validée";
            else if($statut === "all") $statut = "validée";
            else{
                echo json_encode(['success' => false, 'error' => "Requete invalide"]);
                return;
            }
            echo json_encode(['success' => true, 'data' => $this->reservationModel->findByStatut($statut)]);
            return;
            }
        } else echo json_encode(['success' => false, 'error' => "Autorisation manquante"]);
    }



    public function recupereAnimateur(){
        if(isAdmin()){
            if(isset($_GET['statut'])){
            $statut = $_GET['statut'];
            if($statut === "valide") $statut = "en_attente";
            else if($statut === "all") $statut = "validée";
            else if($statut === "all") $statut = "validée";
            else{
                echo json_encode(['success' => false, 'error' => "Requete invalide"]);
                return;
            }
            echo json_encode(['success' => true, 'data' => $this->reservationModel->findByStatut($statut)]);
            return;
            }
        } else echo json_encode(['success' => false, 'error' => "Autorisation manquante"]);
    }

    public function recupereFacture(){
        if(isAdmin()){
            if(isset($_GET['statut'])){
            $statut = $_GET['statut'];
            if($statut === "valide") $statut = "en_attente";
            else if($statut === "all") $statut = "validée";
            else if($statut === "all") $statut = "validée";
            else{
                echo json_encode(['success' => false, 'error' => "Requete invalide"]);
                return;
            }
            echo json_encode(['success' => true, 'data' => $this->reservationModel->findByStatut($statut)]);
            return;
            }
        } else echo json_encode(['success' => false, 'error' => "Autorisation manquante"]);
    }

}

?>