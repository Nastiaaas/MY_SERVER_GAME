<!DOCTYPE html>
<?php

require __DIR__ . '/../vendor/autoload.php';

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$event = [
        'action' => 'init',
];
$channel = 'internal';
$redis->publish($channel, json_encode($event));

$uri = 'mongodb://127.0.0.1:27017';
$uriOptions = ['ServerSelectionTimeoutMS' => 10000];
$mongoClient = new MongoDB\Client($uri, $uriOptions);

$err = '';
try {
    $mongoClient->selectDatabase('admin')->command(['ping' => 1]);
    $err = 'good';

    $mongoDB = $mongoClient->getDatabase("game_data");
    $chat = $mongoDB->selectCollection("users");
}catch (\Exception $e){
    $err = 'error' . $e->getMessage();
}
// ToDo
// $messages = $chat->find([], ['sort' => ['_id' => -1]]);
?>
<html lang="en">
<head>
    <title><!-- To1Do --></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.7.1.js"
            integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="js/main.js"></script>
    <link href="css/main.css" rel="stylesheet">
</head>
<body>
    <p>MongoDB: <?php echo $err; ?></p>

    <div style="display: flex; gap: 50px;">

        <div>
            <h2>Login</h2>
            <form method="POST" action="login.php">
                <div><input type="text" name="username" placeholder="username" required></div>
                <div style="margin-top: 5px;"><input type="password" name="password" placeholder="password" required></div>
                <div style="margin-top: 5px;"><button type="submit">login</button></div>
            </form>
        </div>

        <div>
            <h2>Register</h2>
            <form method="POST" action="register.php">
                <div><input type="text" name="username" placeholder="username" required></div>
                <div style="margin-top: 5px;"><input type="password" name="password" placeholder="password" required></div>
                <div style="margin-top: 5px;"><button type="submit">Register</button></div>
            </form>
        </div>
    </div>
</body>
</html>