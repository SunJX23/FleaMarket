<?php

require_once('Model.php');


$type = $_POST['type'] || $_GET['type'] || null;

if (!empty($type)) {
    $model = new Model();
    $model -> $type();
}