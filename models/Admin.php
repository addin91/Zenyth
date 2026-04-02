<?php
// models/Admin.php
class Admin
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM admin ORDER BY nom, prenom ASC");
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM admin WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM admin WHERE email LIKE ? ORDER BY nom, prenom ASC");
        $stmt->execute(['%' . $email . '%']);
        return $stmt->fetchAll();
    }

    public function create($nom, $prenom, $email, $mot_de_passe){
        $sql = "INSERT INTO admin (nom, prenom, email, mot_de_passe)
                VALUES (:nom, :prenom, :email, :mot_de_passe)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ":nom" => $nom, 
            ":prenom" => $prenom, 
            ":email" => $email, 
            ":mot_de_passe" => $mot_de_passe, 
            ]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $nom, $prenom, $email, $mot_de_passe){
        $sql = "UPDATE admin SET
                    nom       = :nom,
                    prenom    = :prenom,
                    email = :email,
                    mot_de_passe     = :mot_de_passe
                WHERE id = :id";
        $data = [
            ":id" => $id, 
            ":nom" => $nom, 
            ":prenom" => $prenom, 
            ":email" => $email, 
            ":mot_de_passe" => $mot_de_passe, 
        ];
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM admin WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getDisplayName($animateur)
    {
        return trim(($animateur['prenom'] ?? '') . ' ' . ($animateur['nom'] ?? ''));
    }
}
