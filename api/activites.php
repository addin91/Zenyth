<?php 

require_once '../models/Activite.php';

$activiteModel = new Activite();
echo json_encode($activiteModel->findAll());

?>