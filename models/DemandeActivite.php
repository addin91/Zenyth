<?php
// models/DemandeActivite.php
class DemandeActivite
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll()
    {
        $stmt = $this->pdo->query("SELECT da.*, a.nom AS nom_activite, a.type,
                                          r.date_debut, r.date_fin, c.nom AS nom_client, c.prenom AS prenom_client
                                   FROM demandes_activites da
                                   JOIN activites a ON a.id = da.id_activite
                                   JOIN reservations r ON r.id = da.id_reservation
                                   LEFT JOIN clients c ON c.id = r.id_client
                                   ORDER BY da.date ASC, da.creneau ASC");
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT da.*, a.nom AS nom_activite, a.prix,
                                            r.date_debut, r.date_fin
                                     FROM demandes_activites da
                                     JOIN activites a ON a.id = da.id_activite
                                     JOIN reservations r ON r.id = da.id_reservation
                                     WHERE da.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByReservation($id_reservation)
    {
        $stmt = $this->pdo->prepare("SELECT da.*, a.nom AS nom_activite, a.type, a.prix
                                     FROM demandes_activites da
                                     JOIN activites a ON a.id = da.id_activite
                                     WHERE da.id_reservation = ?
                                     ORDER BY da.date ASC, da.creneau ASC");
        $stmt->execute([$id_reservation]);
        return $stmt->fetchAll();
    }

    public function findByActivite($id_activite)
    {
        $stmt = $this->pdo->prepare("SELECT da.*, r.date_debut, r.date_fin
                                     FROM demandes_activites da
                                     JOIN reservations r ON r.id = da.id_reservation
                                     WHERE da.id_activite = ?
                                     ORDER BY da.date ASC");
        $stmt->execute([$id_activite]);
        return $stmt->fetchAll();
    }

    public function findByDate($date)
    {
        $stmt = $this->pdo->prepare("SELECT da.*, a.nom AS nom_activite
                                     FROM demandes_activites da
                                     JOIN activites a ON a.id = da.id_activite
                                     WHERE da.date = ?
                                     ORDER BY da.creneau ASC");
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        $sql = "INSERT INTO demandes_activites (id_reservation, id_activite, date, creneau, nombre_personnes_concernees, message)
                VALUES (:id_reservation, :id_activite, :date, :creneau, :nombre_personnes_concernees, :message)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE demandes_activites SET
                    id_reservation             = :id_reservation,
                    id_activite                = :id_activite,
                    date                       = :date,
                    creneau                    = :creneau,
                    nombre_personnes_concernees = :nombre_personnes_concernees,
                    message                    = :message
                WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM demandes_activites WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function deleteByReservation($id_reservation)
    {
        $stmt = $this->pdo->prepare("DELETE FROM demandes_activites WHERE id_reservation = ?");
        return $stmt->execute([$id_reservation]);
    }
}
