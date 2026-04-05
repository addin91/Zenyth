<?php

require_once __DIR__ . '/../database/db/JsonDB.php';


// models/Admin.php
class Admin
{
    private $jsondb;

    public function __construct()
    {
        $this->jsondb = new JsonDB("Admin");
    }

    public function findAll()
    {
        $admin = $this->jsondb->selectAll();
        return $admin;
    }

    public function findById($id)
    {
        $admin = $this->jsondb->find($id);
        return $admin;
    }

    public function findByEmail($email)
    {
        // TODO : adapter manuellement (necessite un LIKE %email%)
        // SELECT * FROM admin WHERE email LIKE ? ORDER BY nom, prenom ASC
    }

    public function create($nom, $prenom, $email, $mot_de_passe)
    {
        $data = [
            'nom'          => $nom,
            'prenom'       => $prenom,
            'email'        => $email,
            'mot_de_passe' => $mot_de_passe,
        ];
        $admin = $this->jsondb->add($data);
        return $admin;
    }

    public function update($id, $data)
    {
        $admin = $this->jsondb->update($id, $data);
        return $admin;
    }

    public function delete($id)
    {
        $admin = $this->jsondb->delete($id);
        return $admin;
    }

    public function getDisplayName($animateur)
    {
        return trim(($animateur['prenom'] ?? '') . ' ' . ($animateur['nom'] ?? ''));
    }
}
