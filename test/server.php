<?php

if (isset($_GET['redirect_to'])) {
    header('Location: '.$_GET['redirect_to']);
    die;
}

if (isset($_GET['delay'])) {
    sleep($_GET['delay']);
}

echo json_encode(array(
    'SERVER' => $_SERVER,
    'GET'    => $_GET,
    'POST'   => $_POST,
    'FILES'  => $_FILES,
    'INPUT'  => file_get_contents('php://input'),
));
