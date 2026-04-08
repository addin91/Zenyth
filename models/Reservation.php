<?php

require_once __DIR__ . '/../database/db/JsonDB.php';

class Reservation
{
    private $jsondb;

    public function __construct(){
        $this->jsondb = new JsonDB("Reservation");
    }

    public function findAll(){
        return $this->jsondb->selectAll();
    }

    public function findById($id){
        return $this->jsondb->find($id);
    }

    public function findByClient($id_client){
        $reservation = $this->jsondb->where('id_client', $id_client);
        return $reservation;
    }

    public function findByStatut($statut){
        return $this->jsondb->where('statut', $statut);
    }

    public function findByPeriode($date_debut, $date_fin){
        $debutDemande = strtotime($date_debut);
        $finDemande   = strtotime($date_fin);

        $reservations = $this->jsondb->selectAll();
        $resultat = [];

        foreach ($reservations as $reservation) {
            if (!isset($reservation['date_debut'], $reservation['date_fin'])) {
                continue;
            }
            $resDebut = strtotime($reservation['date_debut']);
            $resFin   = strtotime($reservation['date_fin']);

            if ($debutDemande < $resFin && $finDemande > $resDebut) {
                $resultat[] = $reservation;
            }
        }

        return $resultat;
    }

    public function create($id_client, $id_reservation_chambre, $id_demandes_activite, $id_reservations_prestation, $date_debut , $date_fin , $nombre_personnes , $commentaire){
        $data = [
            'id_client' => $id_client,
            'id_reservation_chambre' => $id_reservation_chambre,
            'id_reservation_prestations' => $id_reservations_prestation,
            'id_demandes_activite' => $id_demandes_activite,
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'nombre_personnes' => $nombre_personnes,
            'statut' => "en_attente",
            'commentaire' => $commentaire,
            'date_demande' => date('Y-m-d H:i:s')
        ];
        return $this->jsondb->add($data);
    }

    public function update($id, $data){
        $data['id'] = $id;
        $reservation = $this->jsondb->update($id, $data);
        return $reservation;
    }

    public function updateStatut($id, $statut){
        $reservation = $this->jsondb->find($id);
        $reservation['statut'] = $statut;
        $reservation = $this->jsondb->update($id, $reservation);
        return $reservation;
    }

    public function aReservePrestation($id, $idReservationPrestation){
        $reservation = $this->jsondb->find($id);
        return in_array($idReservationPrestation, $reservation["id_reservation_prestations"]);
    }
    
    public function aDemandeActivite($id, $idDemandeActivite){
        $reservation = $this->jsondb->find($id);
        return in_array($idDemandeActivite, $reservation["id_demandes_activite"]);
    }

    public function ajoutReservationPrestation($id, $idReservationPrestation){
        $reservation = $this->jsondb->find($id);
        if(!$this->aReservePrestation($id, $idReservationPrestation)) array_push($reservation["id_reservation_prestations"], $idReservationPrestation);
        return $this->update($id, $reservation);
    }

    public function ajoutDemandeActivite($id, $idDemandeActivite){
        $reservation = $this->jsondb->find($id);
        if(!$this->aDemandeActivite($id, $idDemandeActivite)) array_push($reservation["id_demandes_activite"], $idDemandeActivite);
        return $this->update($id, $reservation);
    }

    public function validerReservation($idReservation){
        $reservation = $this->jsondb->find($idReservation);
        if (!$reservation) return false;

        $this->updateStatut($idReservation, 'validée');

        $reservationChambreModel = new ReservationChambre();
        $reservationChambre = $reservationChambreModel->findById($reservation['id_reservation_chambre']);

        $chambreModel = new Chambre();

        $chambreModel->updateStatut($reservationChambre['id_chambre'], 'occupé');

        return true;
    }

    public function delete($id){
        $reservation = $this->jsondb->delete($id);
        return $reservation;
    }

    public function estDansIntervalleTemps($reservation, $date){
        $timestamp = strtotime($date);
        $debutTS   = strtotime($reservation['date_debut']);
        $finTS     = strtotime($reservation['date_fin']);

        return ($timestamp >= $debutTS && $timestamp < $finTS);
    }

    public function nbNuit($id){
        $reservation = $this->findById($id);
        $debut = new DateTime($reservation['date_debut']);
        $fin = new DateTime($reservation['date_fin']);
        $interval = $debut->diff($fin);
        return $interval->days;
    }
}

?>