<?php

require_once __DIR__ . '/../database/db/JsonDB.php';


// models/ReservationPrestation.php
class ReservationPrestation
{
    private $jsondb;

    public function __construct()
    {
        $this->jsondb = new JsonDB("ReservationPrestation");
    }

    public function findAll()
    {
        // TODO : adapter manuellement (necessite un JOIN prestations)
        // SELECT rp.*, p.nom, p.prix_unitaire
        // FROM reservation_prestations rp
        // JOIN prestations p ON p.id = rp.id_prestation
        // ORDER BY rp.id_reservation ASC
    }

    public function findById($id)
    {
        return $this->jsondb->find($id);
    }

    public function findByReservation($id_reservation)
    {
        // TODO : adapter manuellement (necessite un JOIN prestations)
        // SELECT rp.*, p.nom, p.description, p.prix_unitaire
        // FROM reservation_prestations rp
        // JOIN prestations p ON p.id = rp.id_prestation
        // WHERE rp.id_reservation = ?
    }

    public function findByPrestation($id_prestation)
    {
        // TODO : adapter manuellement (necessite un JOIN reservations)
        // SELECT rp.*, r.date_debut, r.date_fin
        // FROM reservation_prestations rp
        // JOIN reservations r ON r.id = rp.id_reservation
        // WHERE rp.id_prestation = ?
        // ORDER BY r.date_debut ASC
    }

    public function create($id_reservation, $id_prestation, $reduction, $prix)
    {
        $data = [
            'id_reservation' => $id_reservation,
            'id_prestation' => $id_prestation,
            'reduction' => $reduction,
            'prix' => $prix,
        ];
        return $this->jsondb->add($data);
    }

    public function update($id, $data)
    {
        $data['id'] = $id;
        $reservationPrestation = $this->jsondb->update($id, $data);
        return $reservationPrestation;
    }

    public function delete($id)
    {
        $reservationPrestation = $this->jsondb->delete($id);
        return $reservationPrestation;
    }

    public function deleteByReservation($id_reservation)
    {
        $reservationPrestation = $this->jsondb->where('id_reservation', $id_reservation);
        foreach ($reservationPrestation as $rp) {
            $this->jsondb->delete($rp['id']);
        }
        return true;
    }

    public function calculerTotal($prix_unitaire, $quantite, $reduction)
    {
        $sous_total = $prix_unitaire * $quantite;
        return round($sous_total * (1 - $reduction / 100), 2);
    }
}

// id 
// id_reservation 
// id_prestation  
// réduction 
// prix