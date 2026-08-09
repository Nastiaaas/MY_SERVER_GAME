<?php
require __DIR__ . '/../vendor/autoload.php';

$uri = 'mongodb://127.0.0.1:27017';
$uriOptions = ['ServerSelectionTimeoutMS' => 10000];
$mongoClient = new MongoDB\Client($uri, $uriOptions);
$mongoDB = $mongoClient->getDatabase("game_data");
$chat = $mongoDB->selectCollection("users");


header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $hash = md5($password);

    $gamer = $chat->findOne([
        'username' => $username,
        'password' => $hash]);

    if ($gamer) {
        echo json_encode(['status' => true]);
        session_start();
    } else {
        echo json_encode(['status' => false]);
    }
}