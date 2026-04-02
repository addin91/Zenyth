<?php
// models/Chambre.php
class Chambre
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM chambres ORDER BY nom_chambre ASC");
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM chambres WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByStatut($statut)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM chambres WHERE statut = ? ORDER BY nom_chambre ASC");
        $stmt->execute([$statut]);
        return $stmt->fetchAll();
    }

    public function findByType($type_chambre)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM chambres WHERE type_chambre = ? ORDER BY nom_chambre ASC");
        $stmt->execute([$type_chambre]);
        return $stmt->fetchAll();
    }

    public function findDisponibles($date_debut, $date_fin)
    {
        $sql = "SELECT ch.*
                FROM chambres ch
                WHERE ch.id NOT IN (
                    SELECT rc.id_chambre
                    FROM reservation_chambres rc
                    JOIN reservations r ON r.id = rc.id_reservation
                    WHERE r.statut != 'refusee'
                      AND r.date_debut <= :date_fin
                      AND r.date_fin   >= :date_debut
                )
                ORDER BY ch.nom_chambre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':date_debut' => $date_debut, ':date_fin' => $date_fin]);
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        $sql = "INSERT INTO chambres (nom_chambre, type_chambre, capacite, prix_nuit, statut)
                VALUES (:nom_chambre, :type_chambre, :capacite, :prix_nuit, :statut)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE chambres SET
                    nom_chambre  = :nom_chambre,
                    type_chambre = :type_chambre,
                    capacite     = :capacite,
                    prix_nuit    = :prix_nuit,
                    statut       = :statut
                WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function updateStatut($id, $statut)
    {
        $stmt = $this->pdo->prepare("UPDATE chambres SET statut = ? WHERE id = ?");
        return $stmt->execute([$statut, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM chambres WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
