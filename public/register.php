<?php
require __DIR__ . '/../vendor/autoload.php';

$uri = 'mongodb://127.0.0.1:27017';
$uriOptions = ['ServerSelectionTimeoutMS' => 10000];
$mongoClient = new MongoDB\Client($uri, $uriOptions);
$mongoDB = $mongoClient->getDatabase("game_data");
$chat = $mongoDB->selectCollection("users");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST["username"] ?? null);
    $password = trim($_POST["password"] ?? null);
}

if (empty($username) || empty($password)) {
    http_response_code(400); //хз
}
$existUser = $chat->findOne(['username' => $username]);
if ($existUser) {
    http_response_code(400);
}

$hash = md5($password);

$chat->insertOne([
    'username' => $username,
    'password' => $hash,
]);
http_response_code(200);
