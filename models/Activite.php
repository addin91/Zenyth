<?php
// models/Activite.php
class Activite
{
    private $pdo;

    public function __construct($pdo){
        $this->pdo = $pdo;
    }

    public function findAll(){
        $stmt = $this->pdo->query("SELECT * FROM activites ORDER BY nom ASC");
        return $stmt->fetchAll();
    }

    public function findById($id){
        $stmt = $this->pdo->prepare("SELECT * FROM activites WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findActives()
    {
        $stmt = $this->pdo->query("SELECT * FROM activites WHERE actif = TRUE ORDER BY nom ASC");
        return $stmt->fetchAll();
    }

    public function findByType($type)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM activites WHERE type = ? AND actif = TRUE ORDER BY nom ASC");
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        $sql = "INSERT INTO activites (nom, type, duree, capacite_min, capacite_max, prix, actif)
                VALUES (:nom, :type, :duree, :capacite_min, :capacite_max, :prix, :actif)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE activites SET
                    nom          = :nom,
                    type         = :type,
                    duree        = :duree,
                    capacite_min = :capacite_min,
                    capacite_max = :capacite_max,
                    prix         = :prix,
                    actif        = :actif
                WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM activites WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function capaciteSuffisante($activite, $nbPersonne){
        return (activite["capacite_max"] <= nbPersonne);
    }
}
