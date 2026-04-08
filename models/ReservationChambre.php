<?php

require_once __DIR__ . '/../database/db/JsonDB.php';
require_once __DIR__ . '/Chambre.php';


// models/ReservationChambre.php
class ReservationChambre
{
    private $jsondb;

    public function __construct(){
        $this->jsondb = new JsonDB("ReservationChambre");
    }

    public function findById($id){
        return $this->jsondb->find($id);
    }

    public function findByClient($id_client){
        return $this->jsondb->where("id_client", $id_client);
    }

    public function exists($id_reservation, $id_chambre){
        $reservationChambre = $this->jsondb->where('id_reservation', $id_reservation);
        foreach ($reservationChambre as $rc) {
            if ($rc['id_chambre'] == $id_chambre) return true;
        }
        return false;
    }

    public function create($id_client, $id_chambre){
        $data = [
            'id_client' => $id_client,
            'id_chambre' => $id_chambre,
        ];
        return $this->jsondb->add($data);
    }

    public function update($id, $data){
        $data['id'] = $id;
        $reservationChambre = $this->jsondb->update($id, $data);
        return $reservationChambre;
    }

    public function delete($id){
        $reservationChambre = $this->jsondb->delete($id);
        return $reservationChambre;
    }

    public function deleteByReservation($id_reservation){
        $reservationChambre = $this->jsondb->where('id_reservation', $id_reservation);
        foreach ($reservationChambre as $rc) {
            $this->jsondb->delete($rc['id']);
        }
        return true;
    }

    public function prixTotalReservationChambre($id, $nbNuit){
        $reservationChambre = $this->findById($id);
        $chambreModel = new Chambre();
        $chambre = $chambreModel->findById($reservationChambre['id_chambre']);
        return $chambre["prix_nuit"] * $nbNuit;
    }
}


?>