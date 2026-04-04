<?php 

header('Content-Type: application/json');
require_once '../database/db/JsonDB.php';
require_once '../models/Chambre.php';

$chambreModel = new Chambre();
echo json_encode($chambreModel->findAll());

?>