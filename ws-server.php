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
    /**
     * @var array<string,\SplObjectStorage>
     *  [codePartie => SplObjectStorage de connexions]
     */
    protected $clients = [];

    public function __construct()
    {
        echo "WebSocket Planning Poker démarré...\n";
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Récupérer code & pseudo : ws://host:8080?code=XXX&pseudo=Y
        parse_str($conn->httpRequest->getUri()->getQuery(), $query);

        $code   = $query['code']   ?? null;
        $pseudo = $query['pseudo'] ?? 'Anonyme';

        if (!$code) {
            echo "Connexion refusée : pas de code de partie.\n";
            $conn->close();
            return;
        }

        if (!isset($this->clients[$code])) {
            $this->clients[$code] = new \SplObjectStorage();
        }

        // On stocke quelques infos sur la connexion
        $conn->code   = $code;
        $conn->pseudo = $pseudo;

        $this->clients[$code]->attach($conn);

        echo "Nouvelle connexion : {$pseudo} sur partie {$code}\n";

        // Annonce aux autres joueurs
        $this->broadcast($code, [
            'type'   => 'join',
            'pseudo' => $pseudo
        ], $conn);
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $code   = $from->code   ?? null;
        $pseudo = $from->pseudo ?? 'Anonyme';

        if (!$code || !isset($this->clients[$code])) {
            return;
        }

        $data = json_decode($msg, true);
        if (!is_array($data) || !isset($data['type'])) {
            return;
        }

        switch ($data['type']) {
            case 'vote':
                // Relayer aux autres joueurs de la même partie
                $this->broadcast($code, [
                    'type'   => 'vote',
                    'pseudo' => $pseudo,
                    'valeur' => $data['valeur'] ?? null
                ], $from);
                break;

            case 'reveal':
                // Notification simple
                $this->broadcast($code, [
                    'type' => 'reveal'
                ], $from);
                break;
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $code   = $conn->code   ?? null;
        $pseudo = $conn->pseudo ?? 'Anonyme';

        if ($code && isset($this->clients[$code])) {
            $this->clients[$code]->detach($conn);

            echo "Connexion fermée : {$pseudo} sur partie {$code}\n";

            // Informer les autres joueurs
            $this->broadcast($code, [
                'type'   => 'leave',
                'pseudo' => $pseudo
            ]);

            // Si plus personne dans cette partie, on nettoie
            if (count($this->clients[$code]) === 0) {
                unset($this->clients[$code]);
            }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Erreur WebSocket : {$e->getMessage()}\n";
        $conn->close();
    }

    /**
     * Envoie un message JSON à tous les clients d'une partie.
     *
     * @param string                   $code
     * @param array                    $msg
     * @param ConnectionInterface|null $except ne pas envoyer à cette connexion
     */
    protected function broadcast(string $code, array $msg, ConnectionInterface $except = null): void
    {
        if (!isset($this->clients[$code])) {
            return;
        }

        $json = json_encode($msg);

        foreach ($this->clients[$code] as $client) {
            if ($except && $client === $except) {
                continue;
            }
            $client->send($json);
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
