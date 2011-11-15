<?php

echo json_encode(array(
    'SERVER' => $_SERVER,
    'GET'    => $_GET,
    'POST'   => $_POST,
    'FILES'  => $_FILES,
    'INPUT'  => file_get_contents('php://input'),
));
