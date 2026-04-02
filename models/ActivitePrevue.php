<?php
// models/ActivitePrevue.php
class ActivitePrevue
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll()
    {
        $stmt = $this->pdo->query("SELECT ap.*, a.nom AS nom_activite, a.type,
                                          an.nom AS nom_animateur, an.prenom AS prenom_animateur
                                   FROM activites_prevues ap
                                   JOIN activites a ON a.id = ap.id_activite
                                   LEFT JOIN animateurs an ON an.id = ap.id_animateur
                                   ORDER BY ap.date ASC, ap.creneau ASC");
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT ap.*, a.nom AS nom_activite, a.capacite_max,
                                            an.nom AS nom_animateur, an.prenom AS prenom_animateur
                                     FROM activites_prevues ap
                                     JOIN activites a ON a.id = ap.id_activite
                                     LEFT JOIN animateurs an ON an.id = ap.id_animateur
                                     WHERE ap.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByActivite($id_activite)
    {
        $stmt = $this->pdo->prepare("SELECT ap.*, an.nom AS nom_animateur, an.prenom AS prenom_animateur
                                     FROM activites_prevues ap
                                     LEFT JOIN animateurs an ON an.id = ap.id_animateur
                                     WHERE ap.id_activite = ?
                                     ORDER BY ap.date ASC, ap.creneau ASC");
        $stmt->execute([$id_activite]);
        return $stmt->fetchAll();
    }

    public function findByAnimateur($id_animateur)
    {
        $stmt = $this->pdo->prepare("SELECT ap.*, a.nom AS nom_activite
                                     FROM activites_prevues ap
                                     JOIN activites a ON a.id = ap.id_activite
                                     WHERE ap.id_animateur = ?
                                     ORDER BY ap.date ASC, ap.creneau ASC");
        $stmt->execute([$id_animateur]);
        return $stmt->fetchAll();
    }

    public function findByDate($date)
    {
        $stmt = $this->pdo->prepare("SELECT ap.*, a.nom AS nom_activite,
                                            an.nom AS nom_animateur, an.prenom AS prenom_animateur
                                     FROM activites_prevues ap
                                     JOIN activites a ON a.id = ap.id_activite
                                     LEFT JOIN animateurs an ON an.id = ap.id_animateur
                                     WHERE ap.date = ?
                                     ORDER BY ap.creneau ASC");
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        $sql = "INSERT INTO activites_prevues (id_activite, date, creneau, id_animateur, message, capacite_restante)
                VALUES (:id_activite, :date, :creneau, :id_animateur, :message, :capacite_restante)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE activites_prevues SET
                    id_activite       = :id_activite,
                    date              = :date,
                    creneau           = :creneau,
                    id_animateur      = :id_animateur,
                    message           = :message,
                    capacite_restante = :capacite_restante
                WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function updateCapaciteRestante($id, $capacite_restante)
    {
        $stmt = $this->pdo->prepare("UPDATE activites_prevues SET capacite_restante = ? WHERE id = ?");
        return $stmt->execute([$capacite_restante, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM activites_prevues WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
