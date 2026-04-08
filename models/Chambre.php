<?php

require_once __DIR__ . '/../database/db/JsonDB.php';

class Chambre
{
    private $jsondb;

    public function __construct(){
        $this->jsondb = new JsonDB("Chambre");
    }

    public function findAll(){
        $chambre = $this->jsondb->selectAll();
        return $chambre;
    }

    public function findById($id){
        $chambre = $this->jsondb->find($id);
        return $chambre;
    }

    public function findByStatut($statut){
        $chambre = $this->jsondb->where('statut', $statut);
        return $chambre;
    }

    public function findByType($type_chambre){
        $chambre = $this->jsondb->where('type_chambre', $type_chambre);
        return $chambre;
    }

    public function create($nom_chambre, $type_chambre, $capacite, $prix_nuit, $statut){
        $data = [
            'nom_chambre' => $nom_chambre,
            'type_chambre' => $type_chambre,
            'capacite' => $capacite,
            'prix_nuit' => $prix_nuit,
            'statut' => $statut,
        ];
        return $this->jsondb->add($data);
    }

    public function update($id, $data){
        $data['id'] = $id;
        $chambre = $this->jsondb->update($id, $data);
        return $chambre;
    }

    public function updateStatut($id, $statut){
        $chambre = $this->jsondb->find($id);
        $chambre['statut'] = $statut;
        $chambre = $this->jsondb->update($id, $chambre);
        return $chambre;
    }

    public function delete($id){
        $chambre = $this->jsondb->delete($id);
        return $chambre;
    }
}

?>