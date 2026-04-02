<?php
// models/Reservation.php
class Reservation
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll()
    {
        $stmt = $this->pdo->query("SELECT r.*, c.nom, c.prenom, c.email
                                   FROM reservations r
                                   LEFT JOIN clients c ON c.id = r.id_client
                                   ORDER BY r.date_demande DESC");
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT r.*, c.nom, c.prenom, c.email
                                     FROM reservations r
                                     LEFT JOIN clients c ON c.id = r.id_client
                                     WHERE r.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByClient($id_client)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM reservations WHERE id_client = ? ORDER BY date_debut DESC");
        $stmt->execute([$id_client]);
        return $stmt->fetchAll();
    }

    public function findByStatut($statut)
    {
        $stmt = $this->pdo->prepare("SELECT r.*, c.nom, c.prenom
                                     FROM reservations r
                                     LEFT JOIN clients c ON c.id = r.id_client
                                     WHERE r.statut = ? ORDER BY r.date_debut ASC");
        $stmt->execute([$statut]);
        return $stmt->fetchAll();
    }

    public function findByPeriode($date_debut, $date_fin)
    {
        $stmt = $this->pdo->prepare("SELECT r.*, c.nom, c.prenom
                                     FROM reservations r
                                     LEFT JOIN clients c ON c.id = r.id_client
                                     WHERE r.date_debut <= ? AND r.date_fin >= ?
                                     ORDER BY r.date_debut ASC");
        $stmt->execute([$date_fin, $date_debut]);
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        $sql = "INSERT INTO reservations (id_client, date_debut, date_fin, nombre_personnes, statut, commentaire)
                VALUES (:id_client, :date_debut, :date_fin, :nombre_personnes, :statut, :commentaire)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE reservations SET
                    id_client        = :id_client,
                    date_debut       = :date_debut,
                    date_fin         = :date_fin,
                    nombre_personnes = :nombre_personnes,
                    statut           = :statut,
                    commentaire      = :commentaire
                WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function updateStatut($id, $statut)
    {
        $stmt = $this->pdo->prepare("UPDATE reservations SET statut = ? WHERE id = ?");
        return $stmt->execute([$statut, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM reservations WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function estDansIntervalleTemps($reservation, $date) {
        // Convertit en timestamp pour comparer facilement
        $timestamp = strtotime($date);
        $debutTS = strtotime($reservation["date_debut"]);
        $finTS = strtotime($reservation["date_fin"]);

        // Vérifie si la date est entre début et fin (exclu)
        return ($timestamp >= $debutTS && $timestamp < $finTS);
    }
}
