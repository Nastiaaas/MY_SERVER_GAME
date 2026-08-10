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
} catch (\Exception $e) {
    $err = 'error' . $e->getMessage();
}
?>
<html lang="en">
<head>
    <title>Maze Game</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.7.1.js"
            integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="js/main.js"></script>
    <link href="css/main.css" rel="stylesheet">
</head>
<body>
<!--
    <p>MongoDB: <?php echo $err; ?></p>
    <p>Redis: <?php echo $Rstatus; ?></p>
-->

<?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
    <!-- Убран класс hidden, чтобы экран отображался при авторизации -->
    <div id="start-screen" class="modal-overlay">
        <div class="modal-card modal-card-dark text-center">
            <h1 class="game-title">MAZE GAME</h1>
            <p class="game-instructions">
                Use <span class="highlight-text">WASD</span> keys to navigate the maze.
            </p>
            <a href="game.php" style="text-decoration: none;">
                <button class="btn btn-submit">PLAY</button>
            </a>
        </div>
    </div>
<?php else: ?>
    <div id="login" class="modal-overlay">
        <div class="modal-card text-center">
            <h1 class="modal-title">Login</h1>
            <form method="post" action="login.php" class="form-layout">
                <div class="form-group">
                    <label for="login-username" class="form-label">Username:</label>
                    <input class="form-input" type="text" id="login-username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="login-password" class="form-label">Password:</label>
                    <input class="form-input" type="password" id="login-password" name="password" required>
                </div>

                <!-- Исправлено: type="submit" для отправки данных на login.php -->
                <button type="submit" class="btn btn-submit">Send</button>
            </form>
            <p class="footer-text">
                Don't have an account? <a href="#" onclick="showRegister" class="link">Register here</a>
            </p>
        </div>
    </div>

    <div id="registration" class="modal-overlay hidden">
        <div class="modal-card text-left">
            <h1 class="modal-title">Registration</h1>
            <form method="post" action="register.php" class="form-layout">

                <div class="form-group">
                    <label for="reg-username" class="form-label">Username:</label>
                    <input class="form-input" type="text" id="reg-username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="reg-password" class="form-label">Password:</label>
                    <input class="form-input" type="password" id="reg-password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="password2" class="form-label">Retype password:</label>
                    <input class="form-input" type="password" id="password2" name="password2" required>
                </div>
                <button type="submit" class="btn btn-submit">Send</button>
            </form>
            <p class="footer-text">
                Already have an account? <a href="#" id="showLogin" class="link">Login here</a>
            </p>
        </div>
    </div>
<?php endif; ?>
</body>
</html>