<?php

declare(strict_types=1);
if (isset($_GET['redirect_to'])) {
    header('Location: '.$_GET['redirect_to']);
    die;
}

if (isset($_GET['delay'])) {
    sleep((int) $_GET['delay']);
}

echo json_encode([
    'SERVER' => $_SERVER,
    'GET' => $_GET,
    'POST' => $_POST,
    'FILES' => $_FILES,
    'INPUT' => file_get_contents('php://input'),
]);
