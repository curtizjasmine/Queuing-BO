<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\QueueSocket;

require dirname(__DIR__) . '/vendor/autoload.php';

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new QueueSocket()
        )
    ),
    8080 // This is the port number
);

echo "WebSocket Server running on port 8080...\n";
$server->run();