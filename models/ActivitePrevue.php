<?php

require_once __DIR__ . '/../database/db/JsonDB.php';


// models/ActivitePrevue.php
class ActivitePrevue
{
    private $jsondb;

    public function __construct()
    {
        $this->jsondb = new JsonDB("ActivitePrevue");
    }

    public function findAll()
    {
        // TODO : adapter manuellement (necessite un JOIN activites + animateurs)
        // SELECT ap.*, a.nom AS nom_activite, a.type,
        //        an.nom AS nom_animateur, an.prenom AS prenom_animateur
        // FROM activites_prevues ap
        // JOIN activites a ON a.id = ap.id_activite
        // LEFT JOIN animateurs an ON an.id = ap.id_animateur
        // ORDER BY ap.date ASC, ap.creneau ASC
    }

    public function findById($id)
    {
        // TODO : adapter manuellement (necessite un JOIN activites + animateurs)
        // SELECT ap.*, a.nom AS nom_activite, a.capacite_max,
        //        an.nom AS nom_animateur, an.prenom AS prenom_animateur
        // FROM activites_prevues ap
        // JOIN activites a ON a.id = ap.id_activite
        // LEFT JOIN animateurs an ON an.id = ap.id_animateur
        // WHERE ap.id = ?
    }

    public function findByActivite($id_activite)
    {
        // TODO : adapter manuellement (necessite un JOIN animateurs)
        // SELECT ap.*, an.nom AS nom_animateur, an.prenom AS prenom_animateur
        // FROM activites_prevues ap
        // LEFT JOIN animateurs an ON an.id = ap.id_animateur
        // WHERE ap.id_activite = ?
        // ORDER BY ap.date ASC, ap.creneau ASC
    }

    public function findByAnimateur($id_animateur)
    {
        // TODO : adapter manuellement (necessite un JOIN activites)
        // SELECT ap.*, a.nom AS nom_activite
        // FROM activites_prevues ap
        // JOIN activites a ON a.id = ap.id_activite
        // WHERE ap.id_animateur = ?
        // ORDER BY ap.date ASC, ap.creneau ASC
    }

    public function findByDate($date)
    {
        // TODO : adapter manuellement (necessite un JOIN activites + animateurs)
        // SELECT ap.*, a.nom AS nom_activite,
        //        an.nom AS nom_animateur, an.prenom AS prenom_animateur
        // FROM activites_prevues ap
        // JOIN activites a ON a.id = ap.id_activite
        // LEFT JOIN animateurs an ON an.id = ap.id_animateur
        // WHERE ap.date = ?
        // ORDER BY ap.creneau ASC
    }

    public function create($id_activite, $id_animateur, $id_demandes_actvites, $date, $creneau, $message, $capacite_restante)
    {
        $data = [
            "id_activite" => $id_activite,
            "id_animateur" => $id_animateur,
            "id_demandes_actvites" => $id_demandes_actvites,
            "date" => $date,
            "creneau" => $creneau,
            "message" => $message,
            "capacite_restante" => $capacite_restante,
        ];
        $activitePrevue = $this->jsondb->add($data);
        return $activitePrevue;
    }

    public function update($id, $data)
    {
        $data['id'] = $id;
        $activitePrevue = $this->jsondb->update($id, $data);
        return $activitePrevue;
    }

    public function updateCapaciteRestante($id, $capacite_restante)
    {
        $activitePrevue = $this->jsondb->find($id);
        $activitePrevue['capacite_restante'] = $capacite_restante;
        $activitePrevue = $this->jsondb->update($id, $activitePrevue);
        return $activitePrevue;
    }

    public function delete($id)
    {
        $activitePrevue = $this->jsondb->delete($id);
        return $activitePrevue;
    }
}

// id
// id_activite
// id_animateur
// [demandes_actvites]
// date
// creneau
// message
// capacite_restante
