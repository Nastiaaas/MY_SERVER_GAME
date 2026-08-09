<?php

session_start();

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
    die(json_encode(['status' => false, 'message'=> 'Username already exists!']));
}

$hash = md5($password);

$result = $chat->insertOne([
    'username' => $username,
    'password' => $hash,
]);
session_regenerate_id(true);
$userId = (string)$result->getInsertedId();$sessionID = session_id();
$_SESSION['user_id'] = (string)$result->getInsertedId();
$_SESSION['username'] = $username;
$_SESSION['logged_in'] = true;

try {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->setex($sessionID, 3600, $userId);

    $event = [
        'action' => 'user_is_registered',
        'session_id' => $sessionID,
        'user_id' => $userId
    ];
    $redis->publish('internal', json_encode($event));
}catch (Exception $e){

}
echo json_encode(['status' => 'success', 'message' => 'success', 'session_id' => session_id()]);
}