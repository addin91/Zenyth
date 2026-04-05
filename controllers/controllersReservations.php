<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../models/ReservationChambre.php';

class controllersReservations{
    private $clientModel;
    private $reservationModel;

    public function __construct()
    {
        $this->clientModel = new Client();
        $this->reservationModel = new Reservation();
    }

    public function recupeRereservations(){
        header('Content-Type: application/json');
        if (isset($_SESSION['user_id'])) {
            $reservations = $this->reservationModel->findByClient($_SESSION['user_id']);
            echo json_encode(['success' => true, 'data' => $reservations ?: []]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Non connecte.']);
        }
    }
    

    public function reservationChambre(){
        if (controlPostForm()) {
            if (isset($_POST['dateDebut'], $_POST['dateFin'], $_POST['nombrePersonne'], $_POST['commentaire'], $_POST['id_chambre'], $_POST['activites'])) {
                
                

                if(isLoggedIn()) $idClient = $_SESSION["id_client"];
                else{
                    if (isset($_POST['nom'], $_POST['prenom'], $_POST['email'])) {
                        $nom = htmlspecialchars($_POST['nom'] ?? '');
                        $prenom = htmlspecialchars($_POST['prenom'] ?? '');
                        $email = htmlspecialchars($_POST['email'] ?? '');
                                            
                        $idClient = $this->clientModel->ajoutNouveauClient($nom, $prenom, $email);
                    } 
                }

                $dateDebut = htmlspecialchars($_POST['dateDebut'] ?? '');
                $dateFin = htmlspecialchars($_POST['dateFin'] ?? '');
                $nombrePersonne = htmlspecialchars($_POST['nombrePersonne'] ?? '');
                $commentaire = htmlspecialchars($_POST['commentaire'] ?? '');
                $idChambre = htmlspecialchars($_POST['id_chambre'] ?? '');
                $idsActivite = array_map(function($v) { return htmlspecialchars($v);}, $_POST['activites']);
                
                $reservationChambreModel = new ReservationChambre();
                $idReservationChambre = $reservationChambreModel->create($idClient, $idChambre);

                $idReservation = $this->reservationModel->create($idClient, $idReservationChambre, $idsActivite, [], $dateDebut, $dateFin, $nombrePersonne, $commentaire);
            } 
        }  
        header('Content-Type: application/json');        
        echo json_encode(['success' => true, 'message' => 'Votre demande de reservation a ete envoyee.']);
    }

    public function reservationPrestation(){
        if (controlPostForm() && isLoggedIn()) {
            if (isset($_POST['id_prestation'])) {
                    
                $idClient = $_SESSION["id_client"];
                $idPrestation = $_POST['id_prestation'];

                // recupere prestation
                $prestationModel = new Prestation();
                $prestation = $prestationModel->findById($idPrestation);

                // recupere reservation
                $reservation = $this->reservationModel->findByClient($idClient);

                // créer reservation prestation

                $reservationPrestationModel = new reservationPrestation();
                $reduction  = 0;

                $reservationPrestationModel->create($reservation['id'], $idPrestation, $reduction, ($prestation["prix_unitaire"]) * (1 - $reduction / 100));
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Prestation ajoutee.']);
        }
        
    }

    public function reservationActivite(){
        if (controlPostForm() && isLoggedIn()) {
            if (isset($_POST['date'], $_POST['creneau'], $_POST['nombrePersonne'], $_POST['message'], $_POST['id_activite'])) {
                    
                $idClient = $_SESSION["id_client"];
                $date = htmlspecialchars($_POST['date'] ?? '');
                $creneau = htmlspecialchars($_POST['creneau'] ?? '');
                $nombrePersonne = htmlspecialchars($_POST['nombrePersonne'] ?? '');
                $message = htmlspecialchars($_POST['message'] ?? '');
                $idActivite = htmlspecialchars($_POST['id_activite'] ?? '');
                
                // recupere activite
                $activiteModel = new Activite();
                $activite = $activiteModel->findById($idActivite);

                // recupere reservation
                $reservation = $this->reservationModel->findByClient($idClient);

                // verifie date
                // verifie capacite
                if($reservation->estDansIntervalleTemps($reservation, $date) && activite->capaciteSuffisante($activite, $nombrePersonne)){
                    // enregistre

                    $reservationActiviteModel = new reservationActivite();
                    $this->reservationActiviteModel->create($idActivite, $date, $creneau, $nombrePersonne, $message);
                } else $_SESSION["error"] = "La demande d'activite est pas bonne";
                // redirect
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => "Demande d'activite envoyee."]);
        }
        
    }

}

?>