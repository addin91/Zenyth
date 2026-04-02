<?php
// models/Animateur.php
class Animateur
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM animateurs ORDER BY nom, prenom ASC");
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM animateurs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findActifs()
    {
        $stmt = $this->pdo->query("SELECT * FROM animateurs WHERE actif = TRUE ORDER BY nom, prenom ASC");
        return $stmt->fetchAll();
    }

    public function findBySpecialite($specialite)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM animateurs WHERE specialite LIKE ? ORDER BY nom, prenom ASC");
        $stmt->execute(['%' . $specialite . '%']);
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        $sql = "INSERT INTO animateurs (nom, prenom, specialite, actif)
                VALUES (:nom, :prenom, :specialite, :actif)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE animateurs SET
                    nom       = :nom,
                    prenom    = :prenom,
                    specialite = :specialite,
                    actif     = :actif
                WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM animateurs WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getDisplayName($animateur)
    {
        return trim(($animateur['prenom'] ?? '') . ' ' . ($animateur['nom'] ?? ''));
    }
}
