<?php
// models/Activite.php
class Activite
{
    private $jsondb;

    public function __construct()
    {
        $this->jsondb = new JsonDB("activite");
    }

    public function findAll()
    {
        $activite = $this->jsondb->selectAll();
        return $activite;
    }

    public function findById($id)
    {
        $activite = $this->jsondb->find($id);
        return $activite;
    }

    public function findActives()
    {
        $activite = $this->jsondb->where('actif', true);
        return $activite;
    }

    public function findByType($type)
    {
        $activite = $this->jsondb->where('actif', true);
        $activite = $this->jsondb->whereData($activite, 'type', $type);
        return $activite;
    }

    public function create($nom, $type, $duree, $capacite_min, $capacite_max, $prix, $actif)
    {
        $data = [
            'nom'          => $nom,
            'type'         => $type,
            'duree'        => $duree,
            'capacite_min' => $capacite_min,
            'capacite_max' => $capacite_max,
            'prix'         => $prix,
            'actif'        => $actif,
        ];
        $activite = $this->jsondb->add($data);
        return $activite;
    }

    public function update($id, $nom, $type, $duree, $capacite_min, $capacite_max, $prix, $actif)
    {
        $data = [
            'id'           => $id,
            'nom'          => $nom,
            'type'         => $type,
            'duree'        => $duree,
            'capacite_min' => $capacite_min,
            'capacite_max' => $capacite_max,
            'prix'         => $prix,
            'actif'        => $actif,
        ];
        $activite = $this->jsondb->update($id, $data);
        return $activite;
    }

    public function delete($id)
    {
        $activite = $this->jsondb->delete($id);
        return $activite;
    }

    public function capaciteSuffisante($activite, $nbPersonne)
    {
        return ($activite['capacite_max'] <= $nbPersonne);
    }
}
