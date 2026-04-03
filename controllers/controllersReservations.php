<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Reservation.php';

class controllersReservations{
    private $pdo;
    private $clientModel;
    private $reservationModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->clientModel = new Client($pdo);
        $this->reservationModel = new Reservation($pdo);
    }

    public function reservationChambre(){
        if (controlPostForm()) {
            if (isset($_POST['dateDebut'], $_POST['dateFin'], $_POST['typeChambre'], $_POST['nombrePersonne'], $_POST['commentaire'], $_POST['id_chambre'])) {
                    
                if(isLoggedIn()) $idClient = $_SESSION["id_client"];
                else{
                    if (isset($_POST['nom'], $_POST['prenom'], $_POST['email'])) {
                        $nom = htmlspecialchars($_POST['nom'] ?? '');
                        $prenom = htmlspecialchars($_POST['prenom'] ?? '');
                        $email = htmlspecialchars($_POST['email'] ?? '');
                                            
                        $idClient = $this->clientModel.ajoutNouveauClient($nom, $prenom, $email);
                    } 
                }

                $dateDebut = htmlspecialchars($_POST['dateDebut'] ?? '');
                $dateFin = htmlspecialchars($_POST['dateFin'] ?? '');
                $typeChambre = htmlspecialchars($_POST['typeChambre'] ?? '');
                $nombrePersonne = htmlspecialchars($_POST['nombrePersonne'] ?? '');
                $commentaire = htmlspecialchars($_POST['commentaire'] ?? '');
                $idChambre = htmlspecialchars($_POST['id_chambre'] ?? '');
                
                $data = [
                    ":id_client" => $idClient, 
                    ":date_debut" => $dateDebut, 
                    ":date_fin" => $dateFin, 
                    ":nombre_personnes" => $nombrePersonne, 
                    ":statut" => "en_attente", 
                    ":commentaire" => $commentaire
                ];
                $idReservation = $this->reservationModel.create($data);

                $reservationChambreModel = new reservationChambre($this->pdo);
                $data = [
                    ":id_reservation" => $idReservation, 
                    ":id_chambre" => $idChambre
                ];
                $this->reservationChambreModel.create($data);
            }
        }
        // redirection
    }

    public function reservationPrestation(){
        if (controlPostForm() && isLoggedIn()) {
            if (isset($_POST['id_prestation'])) {
                    
                $idClient = $_SESSION["id_client"];
                $idPrestation = $_POST['id_prestation'];

                // recupere prestation
                $prestationModel = new Prestation($this->pdo);
                $prestation = $prestationModel.findById($idPrestation);

                // recupere reservation
                $reservation = $this->reservationModel.findByClient($idClient);

                // créer reservation prestation

                $reservationPrestationModel = new reservationPrestation($this->pdo);
                $qte = 0;
                $reduction  = 0;
                $data = [
                    ":id_reservation" => $reservation['id'], 
                    ":id_prestation" => $idPrestation,
                    ":quantite" => $qte,
                    ":reduction" => $reduction,
                    ":total" => ($prestation["prix_unitaire"] * $qte) * (1 - $reduction / 100)
                ];
                $this->reservationPrestationModel.create($data);
            }
        }
        // redirection
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
                $activiteModel = new Activite($this->pdo);
                $activite = $activiteModel.findById($idActivite);

                // recupere reservation
                $reservation = $this->reservationModel.findByClient($idClient);

                // verifie date
                // verifie capacite
                if($reservation.estDansIntervalleTemps($reservation, $date) && activite.capaciteSuffisante($activite, $nombrePersonne)){
                    // enregistre
                    $data = [
                        ":id_reservation" => $idReservation, 
                        ":id_activite" => $idActivite, 
                        ":date" => $date, 
                        ":creneau" => $creneau, 
                        ":nombre_personnes" => $nombrePersonne, 
                        ":message" => $message
                    ];

                    $reservationActiviteModel = new reservationActivite($this->pdo);
                    $this->reservationActiviteModel.create($data);
                } else $_SESSION["error"] = "La demande d'activite est pas bonne";
                // redirect
            }
        }
        // redirection
    }

}

?>