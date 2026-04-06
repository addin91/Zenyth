<?php

require_once __DIR__ . '/../database/db/JsonDB.php';

// models/Client.php
class Client
{
    private $jsondb;

    public function __construct()
    {
        $this->jsondb = new JsonDB("Client");
    }

    public function findAll()
    {
        $client = $this->jsondb->selectAll();
        return $client;
    }

    public function findById($id)
    {
        $client = $this->jsondb->find($id);
        return $client;
    }

    public function findByEmail($email)
    {
        $client = $this->jsondb->where('email', $email);
        if (!empty($client)) return reset($client);
        return null;

    }

    public function findByStatut($statut)
    {
        $client = $this->jsondb->where('statut_compte', $statut);
        return $client;
    }

    public function ajoutNouveauClient($nom, $prenom, $email)
    {
        $data = [
            'nom'           => $nom,
            'prenom'        => $prenom,
            'email'         => $email,
            'statut_compte' => "invité",
            'date_creation' => date('Y-m-d H:i:s')
        ];
        $client = $this->jsondb->add($data);
        return $client;
    }

    public function definiMotDePasseClient($id)
    {
        if (!$this->clientExiste($id)) {
            die("Client introuvable");
        }

        $motDePasseClair = $this->genererMotDePasse(10);
        $motDePasseHash  = password_hash($motDePasseClair, PASSWORD_DEFAULT);

        $client = $this->jsondb->find($id);
        $client['password'] = $motDePasseHash;
        $this->jsondb->update($id, $client);

        return $motDePasseClair;
    }

    public function changementMotDePasse($id, $newPassword)
    {
        if (!$this->clientExiste($id)) {
            die("Client introuvable");
        }

        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $client = $this->jsondb->find($id);
        $client['password'] = $newPasswordHash;
        $client = $this->jsondb->update($id, $client);
        return $client;
    }

    public function activeClient($id)
    {
        if (!$this->clientExiste($id)) {
            die("Client introuvable");
        }

        $client = $this->jsondb->find($id);
        $client['statut_compte'] = 'actif';
        $client = $this->jsondb->update($id, $client);
        return $client;
    }

    public function desactiveClient($id)
    {
        if (!$this->clientExiste($id)) {
            die("Client introuvable");
        }

        $client = $this->jsondb->find($id);
        $client['statut_compte'] = 'inactif';
        $client = $this->jsondb->update($id, $client);
        return $client;
    }

    public function authentification($email, $password)
    {
        $clients = $this->jsondb->whereData($this->jsondb->where('email', $email), "statut_compte", "actif");
        $client = reset($clients);

        if ($client && password_verify($password, $client['password'])) {
            return $client;
        }
        return false;
    }

    public function getDisplayName($client)
    {
        return trim(($client['prenom'] ?? '') . ' ' . ($client['nom'] ?? ''));
    }

    private function clientExiste($id)
    {
        return $this->jsondb->find($id) != null;
    }

    private function genererMotDePasse($longueur = 12)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle($chars), 0, $longueur);
    }
}


// id_client 
// nom 
// prénom 
// email 
// mot_de_passe 
// statut_compte (invité, actif, inactif) 
// date_creation