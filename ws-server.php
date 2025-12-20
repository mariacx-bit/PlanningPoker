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
        echo "WebSocket Planning Poker démarré sur 0.0.0.0:8080 (path /ws)...\n";
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // ✅ en prod on passe par /ws (via reverse proxy)
        $path = $conn->httpRequest->getUri()->getPath();
        if ($path !== '/ws' && $path !== '/') {
            $conn->close();
            return;
        }


        parse_str($conn->httpRequest->getUri()->getQuery(), $query);

        $code   = $query['code']   ?? null;
        $pseudo = trim($query['pseudo'] ?? 'Anonyme');

        if (!$code) {
            echo "Connexion refusée : pas de code de partie.\n";
            $conn->close();
            return;
        }

        if ($pseudo === '') $pseudo = 'Anonyme';

        if (!isset($this->clients[$code])) {
            $this->clients[$code] = new \SplObjectStorage();
        }

        $conn->code   = $code;
        $conn->pseudo = $pseudo;

        $this->clients[$code]->attach($conn);

        echo "Nouvelle connexion : {$pseudo} sur partie {$code}\n";

        // 1) Envoyer AU NOUVEAU l'état actuel
        $conn->send(json_encode([
            'type'    => 'state',
            'code'    => $code,
            'players' => $this->getPlayers($code),
        ], JSON_UNESCAPED_UNICODE));

        // 2) Informer les autres
        $this->broadcast($code, [
            'type'   => 'join',
            'pseudo' => $pseudo,
        ], $conn);

        // 3) Pousser la liste complète à tous (robuste)
        $this->broadcast($code, [
            'type'    => 'players',
            'players' => $this->getPlayers($code),
        ]);
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $code   = $from->code   ?? null;
        $pseudo = $from->pseudo ?? 'Anonyme';

        if (!$code || !isset($this->clients[$code])) return;

        // ✅ sécurité simple : éviter messages énormes
        if (strlen($msg) > 4096) {
            $from->send(json_encode(['type' => 'error', 'message' => 'Message trop long']));
            return;
        }

        $data = json_decode($msg, true);
        if (!is_array($data) || !isset($data['type'])) {
            $from->send(json_encode(['type' => 'error', 'message' => 'Message invalide']));
            return;
        }

    switch ($data['type']) {

        case 'vote':
            // envoyer à tout le monde (y compris l'émetteur)
            $this->broadcast($code, [
                'type'   => 'vote',
                'pseudo' => $pseudo,
                'valeur' => $data['valeur'] ?? null,
            ]);
            break;

        case 'reveal':
            // envoyer à tout le monde
            $this->broadcast($code, ['type' => 'reveal']);
            break;

        // ✅ NOUVEAU : demande à tout le monde de recharger (nouvelle tâche / tour / backlog)
        case 'refresh':
            $this->broadcast($code, ['type' => 'refresh']);
            break;

        // ✅ NOUVEAU : reset des votes pour tout le monde (nouveau tour)
        case 'round_reset':
            $this->broadcast($code, ['type' => 'round_reset']);
            break;

        case 'ping':
            $from->send(json_encode(['type' => 'pong']));
            break;

        case 'timer_reset':
            $this->broadcast($code, ['type' => 'timer_reset']);
            break;

        default:
            $from->send(json_encode(['type' => 'error', 'message' => 'Type inconnu']));
            break;
    }
    }


    public function onClose(ConnectionInterface $conn)
    {
        $code   = $conn->code   ?? null;
        $pseudo = $conn->pseudo ?? 'Anonyme';

        if (!$code || !isset($this->clients[$code])) return;

        $this->clients[$code]->detach($conn);
        echo "Connexion fermée : {$pseudo} sur partie {$code}\n";

        $this->broadcast($code, [
            'type'   => 'leave',
            'pseudo' => $pseudo,
        ]);

        // Pousser la liste à jour
        $this->broadcast($code, [
            'type'    => 'players',
            'players' => $this->getPlayers($code),
        ]);

        if (count($this->clients[$code]) === 0) {
            unset($this->clients[$code]);
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Erreur WebSocket : {$e->getMessage()}\n";
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

        $json = json_encode($msg, JSON_UNESCAPED_UNICODE);

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
