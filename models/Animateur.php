<?php

require_once __DIR__ . '/../database/db/JsonDB.php';


// models/Animateur.php
class Animateur
{
    private $jsondb;

    public function __construct()
    {
        $this->jsondb = new JsonDB("Animateur");
    }

    public function findAll()
    {
        $animateur = $this->jsondb->selectAll();
        return $animateur;
    }

    public function findById($id)
    {
        $animateur = $this->jsondb->find($id);
        return $animateur;
    }

    public function findActifs()
    {
        $animateur = $this->jsondb->where('actif', true);
        return $animateur;
    }

    public function findBySpecialite($specialite)
    {
        // TODO : adapter manuellement (necessite un LIKE %specialite%)
        // SELECT * FROM animateurs WHERE specialite LIKE ? ORDER BY nom, prenom ASC
    }

    public function create($id_animateur, $nom, $prenom, $specialite, $actif)
    {
        $data = [
            'id_animateur' => $id_animateur,
            'nom' => $nom,
            'prenom' => $prenom,
            'specialite' => $specialite,
            'actif' => $actif,
        ];
        return $this->jsondb->add($data);
    }

    public function update($id, $data)
    {
        $data['id'] = $id;
        $animateur = $this->jsondb->update($id, $data);
        return $animateur;
    }

    public function delete($id)
    {
        $animateur = $this->jsondb->delete($id);
        return $animateur;
    }

    public function getDisplayName($animateur)
    {
        return trim(($animateur['prenom'] ?? '') . ' ' . ($animateur['nom'] ?? ''));
    }
}

//id_animateur 
// nom 
// prénom 
// spécialité 
// actif