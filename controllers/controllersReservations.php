<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../models/ReservationChambre.php';
require_once __DIR__ . '/../models/ReservationPrestation.php';
require_once __DIR__ . '/../models/Prestation.php';

class controllersReservations{
    private $clientModel;
    private $reservationModel;

    public function __construct(){
        $this->clientModel = new Client();
        $this->reservationModel = new Reservation();
    }

    public function recupereReservations(){
        header('Content-Type: application/json');
        if (isset($_SESSION['user_id'])) {
            $reservations = $this->reservationModel->findByClient($_SESSION['user_id']);
            echo json_encode(['success' => true, 'data' => $reservations ?: []]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Non connecte.']);
        }
    }
    

    public function reservationChambre(){
        header('Content-Type: application/json');  
        if (controlPostForm()) {
                if(isLoggedIn()) $idClient = $_SESSION["user_id"];
                else{
                        $nom = filter_input(INPUT_POST, 'nom');
                        $prenom = filter_input(INPUT_POST, 'prenom');
                        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

                        if (!$nom || !$prenom || !$email) {
                            echo json_encode(['success' => false, 'error' => 'Données invalides']);
                            return;
                        }
                                            
                        $idClient = $this->clientModel->ajoutNouveauClient($nom, $prenom, $email);
                } 

                $dateDebut = filter_input(INPUT_POST, 'dateDebut');
                $dateFin = filter_input(INPUT_POST, 'dateFin');
                $nombrePersonne = filter_input(INPUT_POST, 'nombrePersonne', FILTER_VALIDATE_INT);
                $commentaire = trim(filter_input(INPUT_POST, 'commentaire') ?? '');
                $idChambre = filter_input(INPUT_POST, 'id_chambre', FILTER_VALIDATE_INT);

                if (!$dateDebut || !$dateFin || !strtotime($dateDebut) || !strtotime($dateFin) || $nombrePersonne === false || $idChambre === false) {
                    echo json_encode(['success' => false, 'error' => 'Données invalides']);
                    return;
                }

                if (strtotime($dateFin) <= strtotime($dateDebut)) {
                    echo json_encode(['success' => false, 'error' => 'La date de fin doit être après la date de début']);
                    return;
                }

                $reservationChambreModel = new ReservationChambre();
                $idReservationChambre = $reservationChambreModel->create($idClient, $idChambre);

                $idReservation = $this->reservationModel->create($idClient, $idReservationChambre, [], [], $dateDebut, $dateFin, $nombrePersonne, $commentaire);
                echo json_encode(['success' => true, 'message' => 'Votre demande de reservation a ete envoyee.']);
            
        }
                    
    }

    public function chambresDisponibles(){
        if(isset($_GET["date_debut"], $_GET["date_fin"], $_GET['nombre_personnes'])){
            $dateDebut = $_GET['date_debut'];
            $dateFin = $_GET['date_fin'];
            $capacite = $_GET['nombre_personnes'];
            if (strtotime($dateDebut) < strtotime($dateFin)) {
                $reservationsDurantPeriode = $this->reservationModel->findByPeriode($dateDebut, $dateFin);
                $chambresOccupees = [];
                foreach($reservationsDurantPeriode as $reservation){
                    if($reservation !== "refusee"){
                        $reservationChambreModel = new ReservationChambre();
                        $idReservationChambre = $reservation["id_reservation_chambre"];
                        $reservationChambre = $reservationChambreModel->findById($idReservationChambre);
                        $chambresOccupees[] = $reservationChambre["id_chambre"];
                    }
                }
                $chambreModel = new Chambre();
                $toutesChambres = $chambreModel->findAll();

                $chambresDisponibles = array_filter($toutesChambres, function($chambre) use ($chambresOccupees) {
                    return !in_array($chambre['id'], $chambresOccupees);
                });
                $chambresDisponiblesCapaciteSuffisante = array_filter($chambresDisponibles, function($chambre) use ($capacite) {
                    return $chambre["capacite"] >= $capacite;
                });
                echo json_encode(['success' => true, 'data' => array_values($chambresDisponiblesCapaciteSuffisante)]);
                return;
            }
        }
        echo json_encode(['success' => false]);
    }   

    public function reservationPrestation(){
        if (controlPostForm() && isLoggedIn()) {
            if (isset($_POST['id_prestation'])) {
                    
                $idClient = $_SESSION["user_id"];
                $idPrestation = $_POST['id_prestation'];

                // recupere prestation
                $prestationModel = new Prestation();
                $prestation = $prestationModel->findById($idPrestation);

                // recupere reservation
                $reservations = $this->reservationModel->findByClient($idClient);
                $reservation = !empty($reservations) ? end($reservations) : null;
                if (!$reservation) {
                    echo json_encode(['success' => false, 'error' => 'Aucune réservation trouvée.']);
                    return;
                }
                if($this->reservationModel->aReservePrestation($reservation["id"], $idPrestation)){
                    echo json_encode(['success' => false, 'error' => 'Vous avez déjà réservé cette prestation']);
                    return;
                }
                // créer reservation prestation
                $reservationPrestationModel = new ReservationPrestation();
                $reduction  = 0;
                
                $idReservationPrestation = $reservationPrestationModel->create($reservation['id'], $idPrestation, $reduction, ($prestation["prix_unitaire"]) * (1 - $reduction / 100));
                $this->reservationModel->ajoutReservationPrestation($reservation["id"], $idReservationPrestation);

            }
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Prestation ajoutee.']);
        }
        
    }

    public function reservationActivite(){
        header('Content-Type: application/json');
        if (controlPostForm() && isLoggedIn()) {
                    
                $idClient = $_SESSION["user_id"];
                $date = filter_input(INPUT_POST, 'date');
                $creneau = filter_input(INPUT_POST, 'creneau');
                $nombrePersonne = filter_input(INPUT_POST, 'nombrePersonne', FILTER_VALIDATE_INT);
                $message = trim(filter_input(INPUT_POST, 'message'));
                $idActivite = filter_input(INPUT_POST, 'id_activite', FILTER_VALIDATE_INT);

                if (!$date || !$creneau || !strtotime($date) || $nombrePersonne === false || $idActivite === false) {
                    echo json_encode(['success' => false, 'error' => 'Données invalides']);
                    return;
                }
                
                // recupere activite
                $activiteModel = new Activite();
                $activite = $activiteModel->findById($idActivite);
                if (!$activite) {
                    echo json_encode(['success' => false, 'error' => 'Activité inexistante']);
                    return;
                }


                // recupere reservation
                $reservations = $this->reservationModel->findByClient($idClient);
                $reservation = !empty($reservations) ? end($reservations) : null;
                if (!$reservation) {
                    echo json_encode(['success' => false, 'error' => 'Aucune réservation trouvée']);
                    return;
                }

                $estDansIntervalle = $this->reservationModel->estDansIntervalleTemps($reservation, $date);
                $capaciteOK = $activiteModel->capaciteSuffisante($activite, $nombrePersonne);

                if (!$estDansIntervalle) {
                    echo json_encode(['success' => false, 'error' => "L'activité ne se déroule pas durant votre réservation"]);
                    return;
                }

                if (!$capaciteOK) {
                    echo json_encode(['success' => false, 'error' => "Capacité insuffisante"]);
                    return;
                }

                // enregistrement
                $reservationActiviteModel = new DemandeActivite();
                $idReservationActiviteModel = $reservationActiviteModel->create($idActivite, $date, $creneau, $nombrePersonne, $message);
                $this->reservationModel->ajoutDemandeActivite($reservation["id"], $idReservationActiviteModel);

                echo json_encode(['success' => true, 'message' => "Demande d'activité envoyée."]);
           
        
        }

    }


    public function activitesValidees(){
        if(isLoggedIn()){
            $reservations = $this->reservationModel->findByClient($_SESSION["user_id"]);
            $reservation = !empty($reservations) ? end($reservations) : null;
            if (!$reservation) {
                echo json_encode(['success' => false, 'error' => 'Aucune réservation trouvée']);
                return;
            }
            $demandeActiviteModel = new DemandeActivite();
            $demandeActivites = [];
            foreach($reservation['id_demandes_activite'] as $idDemandeActivite){
                $demandeActivite = $demandeActiviteModel->findById($idDemandeActivite);
                if($demandeActivite["statut"] === "validee") $demandeActivites[] = $demandeActivite;
            }
            echo json_encode(['success' => true, 'data' => $demandeActivites]);
            return;
        } echo json_encode(['success' => false, 'error' => "Erreur dans la requete"]);

    }

}

?>