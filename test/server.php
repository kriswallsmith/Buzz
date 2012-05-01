<?php

if (@$_GET['emulate'] == 'header') {
    echo "HTTP/1.1 500 Fake error\r\nContent-length: 7\r\n\r\nContent";
    exit;
}

echo json_encode(array(
    'SERVER' => $_SERVER,
    'GET'    => $_GET,
    'POST'   => $_POST,
    'FILES'  => $_FILES,
    'INPUT'  => file_get_contents('php://input'),
));
