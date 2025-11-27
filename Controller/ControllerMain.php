<?php
session_start();

include_once("Model/ModelMain.php");

$model = new ModelMain();
$vehicles = $model->getVehicles();

echo "<pre>";
print_r($vehicles);
echo "</pre>";

?>