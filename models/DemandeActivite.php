<?php

require_once __DIR__ . '/../database/db/JsonDB.php';
require_once __DIR__ . '/../models/Activite.php';

class DemandeActivite
{
    private $jsondb;

    public function __construct(){
        $this->jsondb = new JsonDB("DemandeActivite");
    }

    public function findAll(){
        return $this->jsondb->selectAll();
    }

    public function findById($id){
        return $this->jsondb->find($id);
    }


    public function findByStatut($statut){
        return $this->jsondb->where("statut", $statut);
    }

    public function create($id_activite, $date, $creneau, $nombre_personnes_concernees, $message){
        $data = [
            'id_activite' => $id_activite,
            'date' => $date,
            'creneau' => $creneau,
            'nombre_personnes_concernees' => $nombre_personnes_concernees,
            'message' => $message,
            'statut' => "en_attente"
        ];
        return $this->jsondb->add($data);
    }

    public function update($id, $data){
        $data['id'] = $id;
        $demandeActivite = $this->jsondb->update($id, $data);
        return $demandeActivite;
    }

    public function updateStatut($id, $statut){
        $reservation = $this->jsondb->find($id);
        $reservation['statut'] = $statut;
        $reservation = $this->jsondb->update($id, $reservation);
        return $reservation;
    }


    public function delete($id){
        $demandeActivite = $this->jsondb->delete($id);
        return $demandeActivite;
    }

    public function deleteByReservation($id_reservation){
        $demandeActivite = $this->jsondb->where('id_reservation', $id_reservation);
        foreach ($demandeActivite as $da) {
            $this->jsondb->delete($da['id']);
        }
        return true;
    }

    public function prixActivite($id){
        $demande = $this->findById($id);
        $activiteModel = new Activite();
        $activite = $activiteModel->findById($demande["id_activite"]);
        if($demande["statut"] == "validee") return ((int) $activite["prix"]) * ((int) $demande["nombre_personnes_concernees"]);
        return 0;
    }
}


?>