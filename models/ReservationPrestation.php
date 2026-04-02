<?php
// models/ReservationPrestation.php
class ReservationPrestation
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll()
    {
        $stmt = $this->pdo->query("SELECT rp.*, p.nom, p.prix_unitaire
                                   FROM reservation_prestations rp
                                   JOIN prestations p ON p.id = rp.id_prestation
                                   ORDER BY rp.id_reservation ASC");
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT rp.*, p.nom, p.prix_unitaire
                                     FROM reservation_prestations rp
                                     JOIN prestations p ON p.id = rp.id_prestation
                                     WHERE rp.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByReservation($id_reservation)
    {
        $stmt = $this->pdo->prepare("SELECT rp.*, p.nom, p.description, p.prix_unitaire
                                     FROM reservation_prestations rp
                                     JOIN prestations p ON p.id = rp.id_prestation
                                     WHERE rp.id_reservation = ?");
        $stmt->execute([$id_reservation]);
        return $stmt->fetchAll();
    }

    public function findByPrestation($id_prestation)
    {
        $stmt = $this->pdo->prepare("SELECT rp.*, r.date_debut, r.date_fin
                                     FROM reservation_prestations rp
                                     JOIN reservations r ON r.id = rp.id_reservation
                                     WHERE rp.id_prestation = ?
                                     ORDER BY r.date_debut ASC");
        $stmt->execute([$id_prestation]);
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        $sql = "INSERT INTO reservation_prestations (id_reservation, id_prestation, quantite, reduction, total)
                VALUES (:id_reservation, :id_prestation, :quantite, :reduction, :total)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE reservation_prestations SET
                    id_reservation = :id_reservation,
                    id_prestation  = :id_prestation,
                    quantite       = :quantite,
                    reduction      = :reduction,
                    total          = :total
                WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM reservation_prestations WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function deleteByReservation($id_reservation)
    {
        $stmt = $this->pdo->prepare("DELETE FROM reservation_prestations WHERE id_reservation = ?");
        return $stmt->execute([$id_reservation]);
    }

    public function calculerTotal($prix_unitaire, $quantite, $reduction)
    {
        $sous_total = $prix_unitaire * $quantite;
        return round($sous_total * (1 - $reduction / 100), 2);
    }
}
