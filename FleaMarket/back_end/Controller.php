<?php

session_save_path(dirname(dirname(dirname(dirname(__FILE__))))."/cache/");
session_start();
require_once('Model.php');


$type = isset($_POST['type']) ? $_POST['type'] : isset($_GET['type']) ? $_GET['type'] : null;

if (!empty($type)) {
    $model = new Model();
    echo json_encode($model->$type());
    $model->closeCon();
}
