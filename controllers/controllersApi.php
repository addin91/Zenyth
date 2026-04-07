<?php
require_once  __DIR__ . '/../models/Activite.php';
require_once  __DIR__ . '/../models/Chambre.php';
require_once  __DIR__ . '/../models/Prestation.php';
require_once  __DIR__ . '/../models/Animateur.php';
require_once  __DIR__ . '/../models/Animateur.php';
require_once  __DIR__ . '/../models/DemandeActivite.php';
require_once  __DIR__ . '/../models/ActivitePrevue.php';

class controllersApi{
    public function __construct(){}

    public function recupereActivite(){
        header('Content-Type: application/json');
        $activiteModel = new Activite();
        echo json_encode($activiteModel->findAll());
    }

    public function recupereChambre(){
        header('Content-Type: application/json');
        $chambreModel = new Chambre();
        echo json_encode($chambreModel->findAll());
    }

    public function recuperePrestations(){
        header('Content-Type: application/json');
        $prestationModel = new Prestation();
        echo json_encode($prestationModel->findAll());
    }

    public function recupereAnimateur(){
        header('Content-Type: application/json');
        $prestationModel = new Animateur();
        echo json_encode($prestationModel->findAll());
    }

    public function recupereDemandesActivites(){
        header('Content-Type: application/json');
        $demandeActiviteModel = new DemandeActivite();
        echo json_encode($demandeActiviteModel->findAll());
    }


    public function recupereActivitesPrevues(){
        header('Content-Type: application/json');
        $activitePrevueModel = new ActivitePrevue();
        echo json_encode($activitePrevueModel->findAll());
    }
}


?>