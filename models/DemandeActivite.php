<?php

require_once __DIR__ . '/../database/db/JsonDB.php';


// models/DemandeActivite.php
class DemandeActivite
{
    private $jsondb;

    public function __construct()
    {
        $this->jsondb = new JsonDB("DemandeActivite");
    }

    public function findAll()
    {
        // TODO : adapter manuellement (necessite un JOIN activites + reservations + clients)
        // SELECT da.*, a.nom AS nom_activite, a.type,
        //        r.date_debut, r.date_fin, c.nom AS nom_client, c.prenom AS prenom_client
        // FROM demandes_activites da
        // JOIN activites a ON a.id = da.id_activite
        // JOIN reservations r ON r.id = da.id_reservation
        // LEFT JOIN clients c ON c.id = r.id_client
        // ORDER BY da.date ASC, da.creneau ASC
    }

    public function findById($id)
    {
        return $this->jsondb->find($id);
    }

    public function findByReservation($id_reservation)
    {
        // TODO : adapter manuellement (necessite un JOIN activites)
        // SELECT da.*, a.nom AS nom_activite, a.type, a.prix
        // FROM demandes_activites da
        // JOIN activites a ON a.id = da.id_activite
        // WHERE da.id_reservation = ?
        // ORDER BY da.date ASC, da.creneau ASC
    }

    public function findByActivite($id_activite)
    {
        // TODO : adapter manuellement (necessite un JOIN reservations)
        // SELECT da.*, r.date_debut, r.date_fin
        // FROM demandes_activites da
        // JOIN reservations r ON r.id = da.id_reservation
        // WHERE da.id_activite = ?
        // ORDER BY da.date ASC
    }

    public function findByDate($date)
    {
        // TODO : adapter manuellement (necessite un JOIN activites)
        // SELECT da.*, a.nom AS nom_activite
        // FROM demandes_activites da
        // JOIN activites a ON a.id = da.id_activite
        // WHERE da.date = ?
        // ORDER BY da.creneau ASC
    }


    public function findByStatut($statut)
    {
        return $this->jsondb->where("statut", $statut);
    }

    public function create($id_activite, $date, $creneau, $nombre_personnes_concernees, $message)
    {
        $data = [
            'id_activite' => $id_activite,
            'date' => $date,
            'creneau' => $creneau,
            'nombre_personnes_concernees' => $nombre_personnes_concernees,
            'message' => $message,
            'statut' => "en_attente"
        ];
        return $this->jsondb->add($data);
    }

    public function update($id, $data)
    {
        $data['id'] = $id;
        $demandeActivite = $this->jsondb->update($id, $data);
        return $demandeActivite;
    }

    public function updateStatut($id, $statut)
    {
        $reservation = $this->jsondb->find($id);
        $reservation['statut'] = $statut;
        $reservation = $this->jsondb->update($id, $reservation);
        return $reservation;
    }


    public function delete($id)
    {
        $demandeActivite = $this->jsondb->delete($id);
        return $demandeActivite;
    }

    public function deleteByReservation($id_reservation)
    {
        $demandeActivite = $this->jsondb->where('id_reservation', $id_reservation);
        foreach ($demandeActivite as $da) {
            $this->jsondb->delete($da['id']);
        }
        return true;
    }
}

// id  
// id_activite 
// date 
// créneau 
// nombre_personnes_concernées 
// message 
// statut (en_attente, validée, refusée)