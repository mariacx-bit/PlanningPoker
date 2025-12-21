<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class PokerServer implements MessageComponentInterface
{
    /** @var array<string, \SplObjectStorage> */
    protected array $clients = [];

    public function __construct()
    {
        echo "WebSocket Planning Poker démarré sur 0.0.0.0:8080...\n";
    }

    public function onOpen(ConnectionInterface $conn)
    {
        parse_str($conn->httpRequest->getUri()->getQuery(), $query);

        $code   = $query['code'] ?? null;
        $pseudo = trim($query['pseudo'] ?? 'Anonyme');
        if ($pseudo === '') $pseudo = 'Anonyme';

        if (!$code) { $conn->close(); return; }

        if (!isset($this->clients[$code])) {
            $this->clients[$code] = new \SplObjectStorage();
        }

        $conn->code   = $code;
        $conn->pseudo = $pseudo;

        $this->clients[$code]->attach($conn);

        // état au nouveau
        $conn->send(json_encode([
            'type'    => 'state',
            'code'    => $code,
            'players' => $this->getPlayers($code),
        ]));

        // informer les autres
        $this->broadcast($code, ['type'=>'join','pseudo'=>$pseudo], $conn);

        // liste complète à tous
        $this->broadcast($code, ['type'=>'players','players'=>$this->getPlayers($code)]);
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $code   = $from->code ?? null;
        $pseudo = $from->pseudo ?? 'Anonyme';
        if (!$code || !isset($this->clients[$code])) return;

        $data = json_decode($msg, true);
        if (!is_array($data) || !isset($data['type'])) return;

        switch ($data['type']) {
            case 'vote':
                $this->broadcast($code, [
                    'type'   => 'vote',
                    'pseudo' => $pseudo,
                    'valeur' => $data['valeur'] ?? null
                ], $from);
                break;

            case 'reveal':
                $this->broadcast($code, ['type'=>'reveal'], $from);
                break;

            case 'round_reset':
                $this->broadcast($code, ['type'=>'round_reset'], $from);
                break;

            case 'refresh':
                $this->broadcast($code, ['type'=>'refresh'], $from);
                break;

            case 'coffee_pause':
                $this->broadcast($code, ['type'=>'coffee_pause'], $from);
                break;

            case 'ping':
                $from->send(json_encode(['type'=>'pong']));
                break;
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $code   = $conn->code ?? null;
        $pseudo = $conn->pseudo ?? 'Anonyme';

        if ($code && isset($this->clients[$code])) {
            $this->clients[$code]->detach($conn);

            $this->broadcast($code, ['type'=>'leave','pseudo'=>$pseudo]);

            if (isset($this->clients[$code])) {
                $this->broadcast($code, [
                    'type'    => 'players',
                    'players' => $this->getPlayers($code),
                ]);
            }

            if (count($this->clients[$code]) === 0) {
                unset($this->clients[$code]);
            }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Erreur WS: {$e->getMessage()}\n";
        $conn->close();
    }

    /** @return string[] */
    protected function getPlayers(string $code): array
    {
        if (!isset($this->clients[$code])) return [];
        $players = [];
        foreach ($this->clients[$code] as $client) {
            $players[] = $client->pseudo ?? 'Anonyme';
        }
        return array_values(array_unique($players));
    }

    protected function broadcast(string $code, array $msg, ConnectionInterface $except = null): void
    {
        if (!isset($this->clients[$code])) return;
        $json = json_encode($msg);
        foreach ($this->clients[$code] as $client) {
            if ($except && $client === $except) continue;
            $client->send($json);
        }
    }
}

$server = IoServer::factory(
    new HttpServer(new WsServer(new PokerServer())),
    8080,
    '0.0.0.0'
);
$server->run();
