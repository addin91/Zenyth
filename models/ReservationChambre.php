<?php
// models/ReservationChambre.php
class ReservationChambre
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll()
    {
        $stmt = $this->pdo->query("SELECT rc.*, ch.nom_chambre, ch.type_chambre, ch.prix_nuit
                                   FROM reservation_chambres rc
                                   JOIN chambres ch ON ch.id = rc.id_chambre
                                   ORDER BY rc.id_reservation ASC");
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT rc.*, ch.nom_chambre, ch.type_chambre, ch.prix_nuit
                                     FROM reservation_chambres rc
                                     JOIN chambres ch ON ch.id = rc.id_chambre
                                     WHERE rc.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByReservation($id_reservation)
    {
        $stmt = $this->pdo->prepare("SELECT rc.*, ch.nom_chambre, ch.type_chambre, ch.capacite, ch.prix_nuit
                                     FROM reservation_chambres rc
                                     JOIN chambres ch ON ch.id = rc.id_chambre
                                     WHERE rc.id_reservation = ?");
        $stmt->execute([$id_reservation]);
        return $stmt->fetchAll();
    }

    public function findByChambre($id_chambre)
    {
        $stmt = $this->pdo->prepare("SELECT rc.*, r.date_debut, r.date_fin, r.statut
                                     FROM reservation_chambres rc
                                     JOIN reservations r ON r.id = rc.id_reservation
                                     WHERE rc.id_chambre = ?
                                     ORDER BY r.date_debut ASC");
        $stmt->execute([$id_chambre]);
        return $stmt->fetchAll();
    }

    public function exists($id_reservation, $id_chambre)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM reservation_chambres WHERE id_reservation = ? AND id_chambre = ?");
        $stmt->execute([$id_reservation, $id_chambre]);
        return $stmt->fetch() !== false;
    }

    public function create($data)
    {
        $sql = "INSERT INTO reservation_chambres (id_reservation, id_chambre)
                VALUES (:id_reservation, :id_chambre)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE reservation_chambres SET
                    id_reservation = :id_reservation,
                    id_chambre     = :id_chambre
                WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function validerReservation($idReservationChambre){
        try {
            $this->pdo->beginTransaction();

            // 1. Récupérer la ligne dans reservation_chambre
            $sql = "SELECT id_reservation, id_chambre 
                    FROM reservation_chambre 
                    WHERE id = :id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $idReservationChambre]);

            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            // Vérifier si existe
            if (!$data) {
                throw new Exception("Relation introuvable");
            }

            $idReservation = $data['id_reservation'];
            $idChambre = $data['id_chambre'];

            // 2. Mettre la chambre en occupée
            $sqlChambre = "UPDATE chambre SET statut = 'occupé' WHERE id = :id";
            $stmtChambre = $this->pdo->prepare($sqlChambre);
            $stmtChambre->execute([':id' => $idChambre]);

            // 3. Mettre la réservation en validée
            $sqlReservation = "UPDATE reservation 
                            SET statut = 'validée' 
                            WHERE id = :id";

            $stmtRes = $this->pdo->prepare($sqlReservation);
            $stmtRes->execute([':id' => $idReservation]);

            // 4. Commit
            $this->pdo->commit();

            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM reservation_chambres WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function deleteByReservation($id_reservation)
    {
        $stmt = $this->pdo->prepare("DELETE FROM reservation_chambres WHERE id_reservation = ?");
        return $stmt->execute([$id_reservation]);
    }
}
