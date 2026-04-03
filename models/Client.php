<?php
// models/Client.php
class Client
{
    private $jsondb;

    public function __construct()
    {
        $this->jsondb = new JsonDB("client");
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
        return $client[0] ?? null;
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
            'statut_compte' => $statut_compte,
        ];
        $client = $this->jsondb->add($data);
        return $client;
    }

    public function definiMotDePasseClient($id)
    {
        if (!clientExiste($id)) {
            die("Client introuvable");
        }

        $motDePasseClair = genererMotDePasse(10);
        $motDePasseHash  = password_hash($motDePasseClair, PASSWORD_DEFAULT);

        $client = $this->jsondb->find($id);
        $client['password'] = $motDePasseHash;
        $this->jsondb->update($id, $client);

        return $motDePasseClair;
    }

    public function changementPassword($id, $newPassword)
    {
        if (!clientExiste($id)) {
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
        if (!clientExiste($id)) {
            die("Client introuvable");
        }

        $client = $this->jsondb->find($id);
        $client['statut_compte'] = 'actif';
        $client = $this->jsondb->update($id, $client);
        return $client;
    }

    public function desactiveClient($id)
    {
        if (!clientExiste($id)) {
            die("Client introuvable");
        }

        $client = $this->jsondb->find($id);
        $client['statut_compte'] = 'inactif';
        $client = $this->jsondb->update($id, $client);
        return $client;
    }

    public function authentification($email, $password)
    {
        $client = $this->jsondb->where('email', $email);
        $client = $client[0] ?? null;

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
        // TODO : adapter manuellement
        // SELECT COUNT(*) FROM client WHERE id = :id
    }

    private function genererMotDePasse($longueur = 12)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle($chars), 0, $longueur);
    }
}
