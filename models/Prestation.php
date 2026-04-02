<?php
// models/Prestation.php
class Prestation
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM prestations ORDER BY nom ASC");
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM prestations WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findActives()
    {
        $stmt = $this->pdo->query("SELECT * FROM prestations WHERE actif = TRUE ORDER BY nom ASC");
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        $sql = "INSERT INTO prestations (nom, description, prix_unitaire, actif)
                VALUES (:nom, :description, :prix_unitaire, :actif)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE prestations SET
                    nom           = :nom,
                    description   = :description,
                    prix_unitaire = :prix_unitaire,
                    actif         = :actif
                WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM prestations WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
