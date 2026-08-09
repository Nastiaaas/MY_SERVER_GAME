<?php

require __DIR__ . '/../vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Clue\React\Redis\Factory;

use React\EventLoop\Loop;
use React\Socket\SocketServer;

// connection data
class Client {
    private ConnectionInterface $conn;
    private ?string $user_id = null;
    private bool $isTrusted = false;
    public function __construct(ConnectionInterface $conn)
    {
        $this->conn = $conn;
    }
    public function setConn(ConnectionInterface $conn): void {
        $this->conn = $conn;
    }
    public function getConn(): ConnectionInterface {
        return $this->conn;
    }
    public function setIsTrusted(bool $isTrusted): void {
        $this->isTrusted = $isTrusted;
    }
    public function getIsTrusted(): bool {
        return $this->isTrusted;
    }
    public function setUserId(string $userId): void {
        $this->user_id = $userId;
    }
    public function getUserId(): ?string {
        return $this->user_id;
    }

}
const URI = 'mongodb://127.0.0.1:27017';
const URI_OPTIONS = ['ServerSelectionTimeoutMS' => 10000];

class GameServer implements MessageComponentInterface {
    private $redisPub;
    private array $clients = [];
    private MongoDB\Database $mongoDB;
    private MongoDB\Collection $users;

    public function __construct($redisSub, $redisPub) {
        $mongoGamer = new MongoDB\Client(URI, URI_OPTIONS);
        try {
            $this->mongoDB = $mongoGamer->getDatabase("game_data");
            $this->users = $this->mongoDB->selectCollection("gamers");
        } catch (MongoDB\Driver\Exception\RuntimeException $e) {
            printf("Failed to ping the MongoDB server: %s\n", $e->getMessage());
        }

        $this->redisPub = $redisPub;
        $redisSub->subscribe('internal');

        $redisSub->on('message', function ($channel, $message) {
            echo "Новое сообщение из Redis (Канал: {$channel}): {$message}\n";
            $msg = var_export(json_decode($message, true), true);
        });

        echo "Server started\n";

        Loop::get()->addPeriodicTimer(30, function () {
            $this->heartbeatPing();
        });

        echo "ping is heree";
    }

    private function heartbeatPing(): void
    {
        $pingmsg = json_encode(['type' => 'ping']);
        foreach ($this->clients as $client) {
            if ($client->getIsTrusted()) {
                $client->getConn()->send($pingmsg);
            }
        }
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        $client = new Client($conn);
        $this->clients[$conn->resourceId] = $client;

        $webString = $conn->httpRequest->getUri()->getQuery();
        parse_str($webString, $webArray);
        $sessionID = $webArray['session_id'] ?? null;


        if (!$sessionID) {
            echo "error";
            $conn->close();
            return;
        }

        $this->redisPub->get($sessionID)->then(function ($userId) use ($conn, $client,$sessionID) {
            if($userId !== null && $userId !== false) {
                $client->setUserId((string)$userId);
                $client->setIsTrusted(true);
                echo "success";

    } else {
                echo "error";
                $conn->close();
            }
        },
            function (\Exception $e) use ($conn) {
            echo "error";
            $conn->close();
        }
        );
}

    public function onMessage(ConnectionInterface $from, $msg): void {
        $client = $this->clients[$from->resourceId] ?? null;

        if (!$client || !$client->getIsTrusted()) {
            echo "ignore";
            return;
        }
    }

    public function onClose(ConnectionInterface $conn): void {
        unset($this->clients[$conn->resourceId]);
    }

    public function onError(ConnectionInterface $conn, \Exception $e): void {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
}

Loop::get()->futureTick(function () {

    $factory = new Clue\React\Redis\Factory(Loop::get());
    $redisSub = $factory->createLazyClient('127.0.0.1:6379');
    $redisPub = $factory->createLazyClient('127.0.0.1:6379');

    $gameServer = new GameServer($redisSub, $redisPub);

    $socket = new SocketServer('0.0.0.0:8080');

    new IoServer(
        new HttpServer(
            new WsServer($gameServer)
        ),
        $socket,
        Loop::get()
    );

    echo "WebSocket server running on port 8080\n";

});

Loop::run();