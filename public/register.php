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

if (empty($username) || empty($password)) {
    die(json_encode(['status' => false]));
}
$existUser = $chat->findOne(['username' => $username]);
if ($existUser) {
    die(json_encode(['status' => false]));
}

$hash = md5($password);

$chat->insertOne([
    'username' => $username,
    'password' => $hash,
]);
echo json_encode(['status' => 'success', 'message' => 'success']);
}