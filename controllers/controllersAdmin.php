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

    public function __construct(){
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
                $nbNuits = $this->reservationModel->nbNuit($idReservation);
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
                    ]
                );
                if($client["statut_compte"] == "invité") $this->clientModel->desactiveClient($reservation["id_client"]);

                $mailservice = new MailService();
                $mailservice->envoieMail($client["email"], "Reservation annulé", "Votre réservation a été annulé", true);
                echo json_encode(['success' => true, 'message' => "Réservation anulé"]);
                return;
            }
        }
        echo json_encode(['success' => false, 'error' => !isAdmin() ? "Autorisation manquante" : "Erreur dans la requete"]);
        return;
    }

    public function prevoirActivite(){
        if(isAdmin()){
            if(isset($_POST['id_demande_activite'], $_POST['id_animateur'], $_POST['message']) && controlPostForm()){
                $idDemandeActivite = $_POST['id_demande_activite'];
                $idAnimateur = $_POST['id_animateur'];
                $message = $_POST['message'];

                $demandeActiviteModel = new DemandeActivite();
                $demandeActivite = $demandeActiviteModel->findById($idDemandeActivite);

                $activiteModel = new Activite();
                $activite = $activiteModel->findById($demandeActivite["id_activite"]);

                $activitePrevuModel = new ActivitePrevue();
                $activitePrevus = $activitePrevuModel->findAll();
                foreach($activitePrevus as $activitePrevu){
                    if($activitePrevu["id_activite"] == $demandeActivite["id_activite"]
                        && $activitePrevu["id_animateur"] == $idAnimateur
                        && $activitePrevu["date"] == $demandeActivite["date"]
                        && $activitePrevu["creneau"] == $demandeActivite["creneau"]){
                                $activitePrevu["id_demandes_activites"][] = $idDemandeActivite;
                                $activitePrevu["capacite_restante"] -= $demandeActivite["nombre_personnes_concernees"];
                                if($activitePrevu["capacite_restante"] < 0){
                                    echo json_encode(['success' => false, 'error' => "Trop nombreux pour cette activité"]);
                                    return;
                                }
                                $activitePrevuModel->update($activitePrevu["id"], $activitePrevu);
                                $demandeActiviteModel->updateStatut($idDemandeActivite, "validee");
                                echo json_encode(['success' => true, 'message' => "Réservation accepté"]);
                                return;
                    }
                }

                $capacite_restante = $activite["capacite_max"];
                $capacite_restante-= $demandeActivite["nombre_personnes_concernees"];
                 
                if($capacite_restante < 0){
                    echo json_encode(['success' => false, 'error' => "Trop nombreux pour cette activité"]);
                    return;
                }
                $activitePrevuModel->create($demandeActivite["id_activite"], $idAnimateur, [$idDemandeActivite], $demandeActivite["date"], $demandeActivite["creneau"], $demandeActivite["message"], $capacite_restante);
                $demandeActiviteModel->updateStatut($idDemandeActivite, "validee");
                echo json_encode(['success' => true, 'message' => "Réservation accepté"]);
                return;
            }
        }
        echo json_encode(['success' => false, 'error' => !isAdmin() ? "Autorisation manquante" : "Erreur dans la requete"]);
        return;
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
                echo json_encode(['success' => true, 'message' => "Statut actif modifié"]);
                return;
            }
        }
        echo json_encode(['success' => false, 'error' => !isAdmin() ? "Autorisation manquante" : "Erreur dans la requete"]);
        return;
    }

    public function creerAnimateur(){
        if(isAdmin()){
            if(isset($_POST['nom'], $_POST['prenom'], $_POST['specialite']) && controlPostForm()){
                $nom = $_POST['nom'];
                $prenom = $_POST['prenom'];
                $specialite = $_POST['specialite'];
                $animateurModel = new Animateur();
                $animateurModel->create($nom, $prenom, $specialite);
                echo json_encode(['success' => true, 'message' => "Animateur créé"]);
                return;
            }
        }
        echo json_encode(['success' => false, 'error' => !isAdmin() ? "Autorisation manquante" : "Erreur dans la requete"]);
    }

    public function supprimerAnimateur(){
        if(isAdmin()){
            if(isset($_POST['id']) && controlPostForm()){
                $idAnimateur = $_POST['id'];
                $animateurModel = new Animateur();
                $animateurModel->delete($idAnimateur);
                echo json_encode(['success' => true, 'message' => "Animateur supprimé"]);
                return;
            }
        }
        echo json_encode(['success' => false, 'error' => !isAdmin() ? "Autorisation manquante" : "Erreur dans la requete"]);
    }

    public function recuperefactures(){
        if(isAdmin()){
            $factureModel = new Facture();
            if(isset($_GET["statut"])){
                if($_GET["statut"] == "Provisoire") $factures = $factureModel->findByStatut("Provisoire");
                else if($_GET["statut"] == "emise") $factures = $factureModel->findByStatut("emise");
                else if($_GET["statut"] == "payée") $factures = $factureModel->findByStatut("payee");
            }else $factures = $factureModel->findAll();
            
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
                foreach(($reservation["id_reservation_prestations"] ?? []) as $id) $reservationPrestations[] = $reservationPrestationModel->findById($id);
                foreach(($reservation["id_demandes_activite"] ?? []) as $id) $reservationActivites[] = $reservationActiviteModel->findById($id);
                $facture['date_debut'] = $reservation['date_debut'];
                $facture['date_fin'] = $reservation['date_fin'];
                $facture['nuits'] = $nbNuits;
                $facture['chambre'] = $chambre['nom_chambre'] ?? "";
                $facture['prix_nuit'] = $chambre['prix_nuit'] ?? "";
                $facture['prestations'] = $reservationPrestations;
                $facture['activites'] = $reservationActivites;
                $facture['montant_total'] = $factureModel->calculerMontantFinal($facture["id"]);
                $factureModel->update($facture["id"],
                    [   
                        "montant_total" => $factureModel->calculerMontantFinal($facture["id"]),
                        "id_reservations_prestation" => $reservation["id_reservation_prestations"],
                        "id_demandes_activite" => $reservation["id_demandes_activite"]
                    ]
                );
            } 
            echo json_encode(['success' => true, 'data' => $factures]);
            return;
        }  echo json_encode(['success' => false, 'error' => !isAdmin() ? "Autorisation manquante" : "Erreur dans la requete"]);
    }

    public function editerFacture(){
        if(isAdmin()){
            if(isset($_POST['id_facture'], $_POST['avoirs'], $_POST['reduction']) && controlPostForm()){
                $idFacture = $_POST['id_facture'];
                $avoirs = $_POST['avoirs'];
                $reduction = $_POST['reduction'];
                $factureModel = new Facture();
                
                $prixTotal = $factureModel->calculerMontantFinal($idFacture);
                $factureModel->update($idFacture, ["avoirs" => $avoirs, "reduction" => $reduction, "montant_total" => $prixTotal]);
                echo json_encode(['success' => true, 'message' => "Facture édité"]);
                return;
            }
        }
        echo json_encode(['success' => false, 'error' => !isAdmin() ? "Autorisation manquante" : "Erreur dans la requete"]);
    }

    public function emettreFacture(){
        if(isAdmin()){
            if(isset($_POST['id_facture']) && controlPostForm()){
                $idFacture = $_POST['id_facture'];
                $factureModel = new Facture();
                $factureModel->updateStatut($idFacture, "emise");
                echo json_encode(['success' => true, 'message' => "Facture émise"]);
                return;
            }
        }
        echo json_encode(['success' => false, 'error' => !isAdmin() ? "Autorisation manquante" : "Erreur dans la requete"]);
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

    public function refuserActvite(){
        if(isAdmin()){
            if(isset($_POST['id_demande_activite']) && controlPostForm()){
                $idDemandeActivite = $_POST['id_demande_activite'];
                $demandeActiviteModel = new DemandeActivite();
                $demandeActiviteModel->updateStatut($idDemandeActivite, "refusee");
                echo json_encode(['success' => true, 'message' => "Activite refusée"]);
                return;
            }
        }
        echo json_encode(['success' => false, 'error' => !isAdmin() ? "Autorisation manquante" : "Erreur dans la requete"]);
        return;
    }


}

?>