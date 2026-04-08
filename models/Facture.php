<?php

require_once __DIR__ . '/../database/db/JsonDB.php';

require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../models/Chambre.php';
require_once __DIR__ . '/../models/ReservationChambre.php';
require_once __DIR__ . '/../models/ReservationPrestation.php';
require_once __DIR__ . '/../models/Prestation.php';
require_once __DIR__ . '/../models/DemandeActivite.php';
require_once __DIR__ . '/../models/Activite.php';


class Facture
{
    private $jsondb;

    public function __construct(){
        $this->jsondb = new JsonDB("Facture");
    }

    public function findAll(){
        return $this->jsondb->selectAll();
    }

    public function findById($id){
        return $this->jsondb->find($id);
    }

    public function findByReservation($id_reservation){
        $facture = $this->jsondb->where('id_reservation', $id_reservation);
        return !empty($facture) ? reset($facture) : null;    
    }

    public function findByStatut($statut){
        return $this->jsondb->where('statut', $statut);
    }

    public function create($id_client, $id_reservation, $id_reservation_chambre, $id_reservations_prestation = [], $id_demandes_activite = [], $montant_total, $avoirs, $reduction){
        $data = [
            'id_client' => $id_client,
            'id_reservation' => $id_reservation,
            'id_reservation_chambre' => $id_reservation_chambre,
            'id_reservations_prestation' => $id_reservations_prestation,
            'id_demandes_activite' => $id_demandes_activite,
            'montant_total' => $montant_total,
            'avoirs' => $avoirs,
            'reduction' => $reduction,
            'statut' => "Provisoire",
            'date_emission' => null,
        ];
        return $this->jsondb->add($data);
    }

    public function update($id, $data){
        $data['id'] = $id;
        $facture = $this->jsondb->update($id, $data);
        return $facture;
    }

    public function updateStatut($id, $statut){
        $facture = $this->jsondb->find($id);
        $facture['statut']        = $statut;
        $facture['date_emission'] = ($statut === 'emise') ? date('Y-m-d H:i:s') : null;
        $facture = $this->jsondb->update($id, $facture);
        return $facture;
    }

    public function delete($id){
        $facture = $this->jsondb->delete($id);
        return $facture;
    }

    public function calculerMontantFinal($id){
        $facture = $this->findById($id);
        $reservationModel = new Reservation();
        $reservationChambreModel = new ReservationChambre();
        $prixChambre = $reservationChambreModel->prixTotalReservationChambre($facture["id_reservation_chambre"], $reservationModel->nbNuit($facture["id_reservation"]));
        $reservationPrestationModel = new ReservationPrestation();
        
        $prixTotalPrestation = 0;
        foreach($facture["id_reservations_prestation"] as $id){
            $reservationPrestation = $reservationPrestationModel->findById($id);
            $prixTotalPrestation += $reservationPrestation["prix"];
        }

        $prixTotalActivite = 0;
        $demandeActiviteModel = new DemandeActivite();
        foreach($facture["id_demandes_activite"] as $id){
            $prixTotalActivite += $demandeActiviteModel->prixActivite($id);
        }

        $montant_total = $prixChambre + $prixTotalPrestation + $prixTotalActivite;
        $prixTotal = ($montant_total - max(0, $facture["avoirs"] ?? 0)) * (($facture["reduction"]??-1 >= 0 && $facture["reduction"]??-1 <= 100) ? (1 - $facture["reduction"] / 100) : 1); 
        return round(max(0, $prixTotal), 2);
    }
}

?>