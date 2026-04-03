<?php
// models/Reservation.php
class Reservation
{
    private $jsondb;

    public function __construct()
    {
        $this->jsondb = new JsonDB("reservation");
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
        // TODO : adapter manuellement (necessite un JOIN clients)
        // SELECT r.*, c.nom, c.prenom, c.email
        // FROM reservations r
        // LEFT JOIN clients c ON c.id = r.id_client
        // WHERE r.id = ?
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

    public function create($data)
    {
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
