<?php
// models/Client.php
class Client
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM clients ORDER BY nom, prenom ASC");
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM clients WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findByStatut($statut)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM clients WHERE statut_compte = ? ORDER BY nom, prenom ASC");
        $stmt->execute([$statut]);
        return $stmt->fetchAll();
    }

    public function ajoutNouveauClient($nom, $prenom, $email)
    {
        $sql = "INSERT INTO clients (nom, prenom, email, statut_compte) VALUES (:nom, :prenom, :email, :statut_compte)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':statut_compte' => $statut_compte,
        ]);
        return $this->pdo->lastInsertId();
    }

    public function definiMotDePasseClient($id){

        if (!clientExiste($id)) {
            die("Client introuvable");
        }
        
        $motDePasseClair = genererMotDePasse(10);
        $motDePasseHash = password_hash($motDePasseClair, PASSWORD_DEFAULT);

        $sql = "UPDATE client SET password = :password WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':password' => $motDePasseHash,
            ':id' => $id,
        ]);

        return $motDePasseClair;
    }

    public function changementPassword($id, $newPassword){

        if (!clientExiste($id)) {
            die("Client introuvable");
        }

        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);


        $sql = "UPDATE client SET password = :password WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':password' => $newPasswordHash,
            ':id' => $id,
        ]);
    }


    public function activeClient($id){

        if (!clientExiste($id)) {
                die("Client introuvable");
        }

        $sql = "UPDATE client SET statut_compte = :statut_compte WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $id,
            ':statut_compte' => 'actif'
        ]);

    }

    public function desactiveClient($id){

        if (!clientExiste($id)) {
            die("Client introuvable");
        }

        $sql = "UPDATE client SET statut_compte = :statut_compte WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $id,
            ':statut_compte' => 'inactif'
        ]);

    }

    public function authentification($email, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM client WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public function getDisplayName($client){
        return trim(($client['prenom'] ?? '') . ' ' . ($client['nom'] ?? ''));
    }

    private function clientExiste($id) {
        $sql = "SELECT COUNT(*) FROM client WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetchColumn() > 0;
    }

    private function genererMotDePasse($longueur = 12) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle($chars), 0, $longueur);
    }
}
