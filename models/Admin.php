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
            'mot_de_passe' => password_hash($mot_de_passe, PASSWORD_DEFAULT),
        ];
        return $this->jsondb->add($data);
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

    public function authentification($email, $password)
    {
        $admins = $this->jsondb->where('email', $email);
        $admin = reset($admins);

        if ($admin && password_verify($password, $admin['mot_de_passe'])) {
            return $admin;
        }
        return false;
    }

    public function getDisplayName($admin)
    {
        return trim(($admin['prenom'] ?? '') . ' ' . ($admin['nom'] ?? ''));
    }
}
