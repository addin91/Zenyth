<?php

require_once __DIR__ . '/../database/db/JsonDB.php';


// models/ReservationChambre.php
class ReservationChambre
{
    private $jsondb;

    public function __construct()
    {
        $this->jsondb = new JsonDB("ReservationChambre");
    }

    public function findAll()
    {
        // TODO : adapter manuellement (necessite un JOIN chambres)
        // SELECT rc.*, ch.nom_chambre, ch.type_chambre, ch.prix_nuit
        // FROM reservation_chambres rc
        // JOIN chambres ch ON ch.id = rc.id_chambre
        // ORDER BY rc.id_reservation ASC
    }

    public function findById($id)
    {
        // TODO : adapter manuellement (necessite un JOIN chambres)
        // SELECT rc.*, ch.nom_chambre, ch.type_chambre, ch.prix_nuit
        // FROM reservation_chambres rc
        // JOIN chambres ch ON ch.id = rc.id_chambre
        // WHERE rc.id = ?
        return $this->jsondb->find($id);
    }

    public function findByReservation($id_reservation)
    {
        // TODO : adapter manuellement (necessite un JOIN chambres)
        // SELECT rc.*, ch.nom_chambre, ch.type_chambre, ch.capacite, ch.prix_nuit
        // FROM reservation_chambres rc
        // JOIN chambres ch ON ch.id = rc.id_chambre
        // WHERE rc.id_reservation = ?
    }

    public function findByChambre($id_chambre)
    {
        // TODO : adapter manuellement (necessite un JOIN reservations)
        // SELECT rc.*, r.date_debut, r.date_fin, r.statut
        // FROM reservation_chambres rc
        // JOIN reservations r ON r.id = rc.id_reservation
        // WHERE rc.id_chambre = ?
        // ORDER BY r.date_debut ASC
    }

    public function exists($id_reservation, $id_chambre)
    {
        $reservationChambre = $this->jsondb->where('id_reservation', $id_reservation);
        foreach ($reservationChambre as $rc) {
            if ($rc['id_chambre'] == $id_chambre) return true;
        }
        return false;
    }

    public function create($id_reservation, $id_chambre)
    {
        $data = [
            'id_reservation' => $id_reservation,
            'id_chambre' => $id_chambre,
        ];
        $reservationChambre = $this->jsondb->add($data);
        return $reservationChambre;
    }

    public function update($id, $data)
    {
        $data['id'] = $id;
        $reservationChambre = $this->jsondb->update($id, $data);
        return $reservationChambre;
    }

    public function validerReservation($idReservationChambre)
    {
        // TODO : adapter manuellement (necessite de modifier chambres + reservations en meme temps, sans transaction)
        // 1. Recuperer id_reservation et id_chambre depuis reservation_chambre WHERE id = $idReservationChambre
        // 2. UPDATE chambre SET statut = 'occupé' WHERE id = $idChambre
        // 3. UPDATE reservation SET statut = 'validée' WHERE id = $idReservation
    }

    public function delete($id)
    {
        $reservationChambre = $this->jsondb->delete($id);
        return $reservationChambre;
    }

    public function deleteByReservation($id_reservation)
    {
        $reservationChambre = $this->jsondb->where('id_reservation', $id_reservation);
        foreach ($reservationChambre as $rc) {
            $this->jsondb->delete($rc['id']);
        }
        return true;
    }
}

// id 
// id_reservation 
// id_chambre 