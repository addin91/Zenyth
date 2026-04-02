<?php
// models/Facture.php
class Facture
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll()
    {
        $stmt = $this->pdo->query("SELECT f.*, r.date_debut, r.date_fin,
                                          c.nom, c.prenom, c.email
                                   FROM factures f
                                   JOIN reservations r ON r.id = f.id_reservation
                                   LEFT JOIN clients c ON c.id = r.id_client
                                   ORDER BY f.date_emission DESC");
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT f.*, r.date_debut, r.date_fin,
                                            c.nom, c.prenom, c.email
                                     FROM factures f
                                     JOIN reservations r ON r.id = f.id_reservation
                                     LEFT JOIN clients c ON c.id = r.id_client
                                     WHERE f.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByReservation($id_reservation)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM factures WHERE id_reservation = ?");
        $stmt->execute([$id_reservation]);
        return $stmt->fetch();
    }

    public function findByStatut($statut)
    {
        $stmt = $this->pdo->prepare("SELECT f.*, c.nom, c.prenom
                                     FROM factures f
                                     JOIN reservations r ON r.id = f.id_reservation
                                     LEFT JOIN clients c ON c.id = r.id_client
                                     WHERE f.statut = ?
                                     ORDER BY f.date_emission DESC");
        $stmt->execute([$statut]);
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        $sql = "INSERT INTO factures (id_reservation, montant_total, avoirs, reductions, montant_final, statut, date_emission)
                VALUES (:id_reservation, :montant_total, :avoirs, :reductions, :montant_final, :statut, :date_emission)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE factures SET
                    montant_total  = :montant_total,
                    avoirs         = :avoirs,
                    reductions     = :reductions,
                    montant_final  = :montant_final,
                    statut         = :statut,
                    date_emission  = :date_emission
                WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function updateStatut($id, $statut)
    {
        $date_emission = ($statut === 'emise') ? date('Y-m-d H:i:s') : null;
        $stmt = $this->pdo->prepare("UPDATE factures SET statut = ?, date_emission = ? WHERE id = ?");
        return $stmt->execute([$statut, $date_emission, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM factures WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function calculerMontantFinal($montant_total, $avoirs, $reductions)
    {
        return round(max(0, $montant_total - $avoirs - $reductions), 2);
    }
}
