<?php
// models/Facture.php
class Facture
{
    private $jsondb;

    public function __construct()
    {
        $this->jsondb = new JsonDB("facture");
    }

    public function findAll()
    {
        // TODO : adapter manuellement (necessite un JOIN reservations + clients)
        // SELECT f.*, r.date_debut, r.date_fin, c.nom, c.prenom, c.email
        // FROM factures f
        // JOIN reservations r ON r.id = f.id_reservation
        // LEFT JOIN clients c ON c.id = r.id_client
        // ORDER BY f.date_emission DESC
    }

    public function findById($id)
    {
        return $this->jsondb->find($id);
    }

    public function findByReservation($id_reservation)
    {
        $facture = $this->jsondb->where('id_reservation', $id_reservation);
        return $facture[0] ?? null;
    }

    public function findByStatut($statut)
    {
        // TODO : adapter manuellement (necessite un JOIN reservations + clients)
        // SELECT f.*, c.nom, c.prenom
        // FROM factures f
        // JOIN reservations r ON r.id = f.id_reservation
        // LEFT JOIN clients c ON c.id = r.id_client
        // WHERE f.statut = ?
        // ORDER BY f.date_emission DESC
    }

    public function create($data)
    {
        $facture = $this->jsondb->add($data);
        return $facture;
    }

    public function update($id, $data)
    {
        $data['id'] = $id;
        $facture = $this->jsondb->update($id, $data);
        return $facture;
    }

    public function updateStatut($id, $statut)
    {
        $facture = $this->jsondb->find($id);
        $facture['statut']        = $statut;
        $facture['date_emission'] = ($statut === 'emise') ? date('Y-m-d H:i:s') : null;
        $facture = $this->jsondb->update($id, $facture);
        return $facture;
    }

    public function delete($id)
    {
        $facture = $this->jsondb->delete($id);
        return $facture;
    }

    public function calculerMontantFinal($montant_total, $avoirs, $reductions)
    {
        return round(max(0, $montant_total - $avoirs - $reductions), 2);
    }
}
