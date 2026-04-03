<?php 

require_once '../models/Prestation.php';

$prestationModel = new Prestation();
echo json_encode($prestationModel->findAll());

?>