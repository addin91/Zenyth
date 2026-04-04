<?php 

header('Content-Type: application/json');
require_once '../database/db/JsonDB.php';
require_once '../models/Activite.php';

$activiteModel = new Activite();
echo json_encode($activiteModel->findAll());

?>