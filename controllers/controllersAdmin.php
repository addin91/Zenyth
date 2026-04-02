<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Reservation.php';

class controllersAdmin{
    private $pdo;
    private $clientModel;
    private $reservationModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->clientModel = new Client($pdo);
        $this->reservationModel = new Reservation($pdo);
    }

    public function accepteReservationChambre($idReservationChambre){
        $reservationChambreModel = new reservationChambre($this->pdo);
        $reservationChambreModel.validerReservation($idReservationChambre);
    }

    public function prevoirActivite(){
        
    }
}

?>