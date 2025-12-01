<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
require 'vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class PokerServer implements MessageComponentInterface
{
    protected $clients = []; // [codePartie => SplObjectStorage]

    public function __construct()
    {
        echo "WebSocket Planning Poker démarré...\n";
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Récupérer code & pseudo : ws://localhost:8080?code=XXX&pseudo=Y
        parse_str($conn->httpRequest->getUri()->getQuery(), $query);

        $code   = $query['code']   ?? null;
        $pseudo = $query['pseudo'] ?? 'Anonyme';

        if (!$code) {
            $conn->close();
            return;
        }

        $conn->code   = $code;
        $conn->pseudo = $pseudo;

        if (!isset($this->clients[$code])) {
            $this->clients[$code] = new \SplObjectStorage();
        }
        $this->clients[$code]->attach($conn);

        $this->broadcast($code, [
            'type'   => 'join',
            'pseudo' => $pseudo
        ]);

        echo "Nouvelle connexion sur la partie $code ($pseudo)\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);
        $code = $from->code ?? null;

        if (!$code || !isset($this->clients[$code])) {
            return;
        }

        switch ($data['type'] ?? null) {
            case 'vote':
                $this->broadcast($code, [
                    'type'   => 'vote',
                    'pseudo' => $from->pseudo,
                    'valeur' => $data['valeur'] ?? null
                ]);
                break;

            case 'reveal':
                $this->broadcast($code, [
                    'type' => 'reveal'
                ]);
                break;
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $code = $conn->code ?? null;
        if ($code && isset($this->clients[$code])) {
            $this->clients[$code]->detach($conn);
            $this->broadcast($code, [
                'type'   => 'leave',
                'pseudo' => $conn->pseudo
            ]);
            echo "Déconnexion de {$conn->pseudo} sur la partie $code\n";
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Erreur: {$e->getMessage()}\n";
        $conn->close();
    }

    protected function broadcast(string $code, array $data): void
    {
        if (!isset($this->clients[$code])) return;

        foreach ($this->clients[$code] as $client) {
            $client->send(json_encode($data));
        }
    }
}

// Création du serveur WebSocket sur le port 8080
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new PokerServer()
        )
    ),
    8080
);

$server->run();
