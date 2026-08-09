<!DOCTYPE html>
<?php

session_start();

require __DIR__ . '/../vendor/autoload.php';

$Rstatus = '';

try {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);

    $event = [
            'action' => 'init',
    ];
    $channel = 'internal';
    $redis->publish($channel, json_encode($event));
    $Rstatus = 'success';

} catch (Exception $exception) {
    $Rstatus = $exception->getMessage();
}
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
    <p>Redis: <?php echo $Rstatus; ?></p>

    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>

        <div style="padding: 20px; border: 2px solid green; display: inline-block;">
            <h2>Добро пожаловать, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <p><strong>Ваш User ID:</strong> <?php echo htmlspecialchars($_SESSION['user_id']); ?></p>
            <p><strong>Ваш Session ID:</strong> <?php echo session_id(); ?></p>
            <p>Статус: Авторизован</p>
        </div>

        <script>
            const sessionId = "<?php echo session_id(); ?>";

            const currecntIp = window.location.hostname;

            const ws = new WebSocket("ws://" + currecntIp + ":8080?session_id=" + sessionId);

            ws.onopen = function(e) {
                console.log("Websocket good!");
            };

            ws.onmessage = function(event) {
                console.log("message: ", event.data);
            };

            ws.onerror = function(error) {
                console.error("Error.");
            };

            ws.onclose = function(event) {
                console.log("Connection closed. code:", event.code);
            };
        </script>

    <?php else: ?>

    <div style="display: flex; gap: 50px;">
        <div id = "auth">
        <div id = "login">
            <h2>Login</h2>
            <form method="POST" action="login.php">
                <div><input type="text" name="username" placeholder="username" required></div>
                <div style="margin-top: 5px;"><input type="password" name="password" placeholder="password" required></div>
                <div style="margin-top: 5px;"><button type="submit">login</button></div>
            </form>
            <p style="margin-top: 5px;">No account? <a href = "#" id="showRegister"> Register-></a></p>
        </div>

        <div id = "register" style="display: none;">
            <h2>Register</h2>
            <form method="POST" action="register.php">
                <div><input type="text" name="username" placeholder="username" required></div>
                <div style="margin-top: 5px;"><input type="password" name="password" placeholder="password" required></div>
                <div style="margin-top: 5px;"><button type="submit">Register</button></div>
            </form>
            <p style="margin-top: 5px;"><a href = "#" id="showLogin"><- back to login</a></p>
        </div>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>