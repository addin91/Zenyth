<?php

require_once __DIR__ . '/../database/db/JsonDB.php';

class ActivitePrevue
{
    private $jsondb;

    public function __construct(){
        $this->jsondb = new JsonDB("ActivitePrevue");
    }

    public function findAll(){
        return $this->jsondb->selectAll();
    }

    public function create($id_activite, $id_animateur, $id_demandes_activites, $date, $creneau, $message, $capacite_restante){
        $data = [
            "id_activite" => $id_activite,
            "id_animateur" => $id_animateur,
            "id_demandes_activites" => $id_demandes_activites,
            "date" => $date,
            "creneau" => $creneau,
            "message" => $message,
            "capacite_restante" => $capacite_restante,
        ];
        return $this->jsondb->add($data);
    }

    public function update($id, $data){
        $data['id'] = $id;
        $activitePrevue = $this->jsondb->update($id, $data);
        return $activitePrevue;
    }

    public function updateCapaciteRestante($id, $capacite_restante){
        $activitePrevue = $this->jsondb->find($id);
        $activitePrevue['capacite_restante'] = $capacite_restante;
        $activitePrevue = $this->jsondb->update($id, $activitePrevue);
        return $activitePrevue;
    }

    public function delete($id){
        $activitePrevue = $this->jsondb->delete($id);
        return $activitePrevue;
    }
}

?>