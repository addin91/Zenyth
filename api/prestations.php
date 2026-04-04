<?php 

header('Content-Type: application/json');
require_once '../database/db/JsonDB.php';
require_once '../models/Prestation.php';

$prestationModel = new Prestation();
echo json_encode($prestationModel->findAll());

?>