<?php

require_once __DIR__ . '/../database/db/JsonDB.php';


// models/Prestation.php
class Prestation
{
    private $jsondb;

    public function __construct()
    {
        $this->jsondb = new JsonDB("Prestation");
    }

    public function findAll()
    {
        $prestation = $this->jsondb->selectAll();
        return $prestation;
    }

    public function findById($id)
    {
        $prestation = $this->jsondb->find($id);
        return $prestation;
    }

    public function findActives()
    {
        $prestation = $this->jsondb->where('actif', true);
        return $prestation;
    }

    public function create($nom, $description , $prix_unitaire, $actif)
    {
        $data = [
            'nom' => $nom,
            'description' => $description,
            'prix_unitaire' => $prix_unitaire,
            'actif' => $actif,
        ];
        return $this->jsondb->add($data);
    }

    public function update($id, $data)
    {
        $data['id'] = $id;
        $prestation = $this->jsondb->update($id, $data);
        return $prestation;
    }

    public function delete($id)
    {
        $prestation = $this->jsondb->delete($id);
        return $prestation;
    }
}


// id
// nom 
// description 
// prix_unitaire 
// actif