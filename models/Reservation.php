<?php

require_once __DIR__ . '/../database/db/JsonDB.php';


// models/Reservation.php
class Reservation
{
    private $jsondb;

    public function __construct()
    {
        $this->jsondb = new JsonDB("Reservation");
    }

    public function findAll()
    {
        // TODO : adapter manuellement (necessite un JOIN clients)
        // SELECT r.*, c.nom, c.prenom, c.email
        // FROM reservations r
        // LEFT JOIN clients c ON c.id = r.id_client
        // ORDER BY r.date_demande DESC
    }

    public function findById($id)
    {
        return $this->jsondb->find($id);
    }

    public function findByClient($id_client)
    {
        $reservation = $this->jsondb->where('id_client', $id_client);
        return $reservation;
    }

    public function findByStatut($statut)
    {
        // TODO : adapter manuellement (necessite un JOIN clients)
        // SELECT r.*, c.nom, c.prenom
        // FROM reservations r
        // LEFT JOIN clients c ON c.id = r.id_client
        // WHERE r.statut = ? ORDER BY r.date_debut ASC
    }

    public function findByPeriode($date_debut, $date_fin)
    {
        // TODO : adapter manuellement (necessite un filtre de chevauchement de dates + JOIN clients)
        // SELECT r.*, c.nom, c.prenom
        // FROM reservations r
        // LEFT JOIN clients c ON c.id = r.id_client
        // WHERE r.date_debut <= ? AND r.date_fin >= ?
        // ORDER BY r.date_debut ASC
    }

    public function create($id_client, $id_reservation_chambre, $id_demandes_activite, $id_reservations_prestation, $date_debut , $date_fin , $nombre_personnes , $commentaire , $date_demande)
    {
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
        $reservation = $this->jsondb->add($data);
        return $reservation;
    }

    public function update($id, $data)
    {
        $data['id'] = $id;
        $reservation = $this->jsondb->update($id, $data);
        return $reservation;
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
        $reservation = $this->jsondb->delete($id);
        return $reservation;
    }

    public function estDansIntervalleTemps($reservation, $date)
    {
        $timestamp = strtotime($date);
        $debutTS   = strtotime($reservation['date_debut']);
        $finTS     = strtotime($reservation['date_fin']);

        return ($timestamp >= $debutTS && $timestamp < $finTS);
    }
}


// id 
// id_client
// id_reservation_chambre
// [id_demande_activite]
// [id_reservation_prestation]
// date_debut 
// date_fin 
// nombre_personnes 
// statut (en_attente, validée, refusée) 
// commentaire 
// date_demande