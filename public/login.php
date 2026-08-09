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

    $hash = md5($password);

    $gamer = $chat->findOne([
        'username' => $username,
        'password' => $hash]);

    if ($gamer) {
        session_regenerate_id(true);

        $userId = (string)$gamer['_id'];
        $sessionID = session_id();
        $_SESSION['user_id'] = (string)$gamer['_id'];
        $_SESSION['username'] = $gamer['username'];
        $_SESSION['logged_in'] = true;

        try {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $redis->setex($sessionID, 3600, $userId);

            $event = [
                'action' => 'user_is_auth',
                'session_id' => $sessionID,
                'user_id' => $userId
            ];
            $redis->publish('internal', json_encode($event));
        }catch (Exception $e){

        }

        echo json_encode([
            'status' => 'success',
            'message' => 'You are now logged in',
            'session_id' => session_id()
        ]);
    } else {
        echo json_encode(['status' => false, 'message' => 'Invalid username or password']);
    }
}