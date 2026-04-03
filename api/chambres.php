<?php 

require_once '../models/Chambre.php';

$chambreModel = new Chambre();
echo json_encode($chambreModel->findAll());

?>