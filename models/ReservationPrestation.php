<?php

require_once __DIR__ . '/../database/db/JsonDB.php';

class ReservationPrestation
{
    private $jsondb;

    public function __construct(){
        $this->jsondb = new JsonDB("ReservationPrestation");
    }


    public function findById($id){
        return $this->jsondb->find($id);
    }

    public function create($id_reservation, $id_prestation, $reduction, $prix){
        $data = [
            'id_reservation' => $id_reservation,
            'id_prestation' => $id_prestation,
            'reduction' => $reduction,
            'prix' => $prix,
        ];
        return $this->jsondb->add($data);
    }

    public function update($id, $data){
        $data['id'] = $id;
        $reservationPrestation = $this->jsondb->update($id, $data);
        return $reservationPrestation;
    }

    public function delete($id){
        $reservationPrestation = $this->jsondb->delete($id);
        return $reservationPrestation;
    }

    public function deleteByReservation($id_reservation){
        $reservationPrestation = $this->jsondb->where('id_reservation', $id_reservation);
        foreach ($reservationPrestation as $rp) {
            $this->jsondb->delete($rp['id']);
        }
        return true;
    }

    public function calculerTotal($prix_unitaire, $quantite, $reduction){
        $sous_total = $prix_unitaire * $quantite;
        return round($sous_total * (1 - $reduction / 100), 2);
    }
}

?>