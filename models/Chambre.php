<?php
// models/Chambre.php
class Chambre
{
    private $jsondb;

    public function __construct()
    {
        $this->jsondb = new JsonDB("chambre");
    }

    public function findAll()
    {
        $chambre = $this->jsondb->selectAll();
        return $chambre;
    }

    public function findById($id)
    {
        $chambre = $this->jsondb->find($id);
        return $chambre;
    }

    public function findByStatut($statut)
    {
        $chambre = $this->jsondb->where('statut', $statut);
        return $chambre;
    }

    public function findByType($type_chambre)
    {
        $chambre = $this->jsondb->where('type_chambre', $type_chambre);
        return $chambre;
    }

    public function findDisponibles($date_debut, $date_fin)
    {
        // TODO : adapter manuellement (necessite une sous-requete avec JOIN reservation_chambres + reservations)
        // SELECT ch.*
        // FROM chambres ch
        // WHERE ch.id NOT IN (
        //     SELECT rc.id_chambre
        //     FROM reservation_chambres rc
        //     JOIN reservations r ON r.id = rc.id_reservation
        //     WHERE r.statut != 'refusee'
        //       AND r.date_debut <= :date_fin
        //       AND r.date_fin   >= :date_debut
        // )
        // ORDER BY ch.nom_chambre ASC
    }

    public function create($data)
    {
        $chambre = $this->jsondb->add($data);
        return $chambre;
    }

    public function update($id, $data)
    {
        $data['id'] = $id;
        $chambre = $this->jsondb->update($id, $data);
        return $chambre;
    }

    public function updateStatut($id, $statut)
    {
        $chambre = $this->jsondb->find($id);
        $chambre['statut'] = $statut;
        $chambre = $this->jsondb->update($id, $chambre);
        return $chambre;
    }

    public function delete($id)
    {
        $chambre = $this->jsondb->delete($id);
        return $chambre;
    }
}
