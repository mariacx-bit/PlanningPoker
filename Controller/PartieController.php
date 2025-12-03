<?php

require_once "Model/PartieModel.php";
require_once "Entities/GameRules.php";
require_once "Entities/GameState.php";

class PartieController
{
    private PartieModel $partieModel;

    public function __construct()
    {
        $this->partieModel = new PartieModel();
    }

    public function partie()
    {
        // 1) AJAX : résolution d'un tour (votes -> décision)
        if (isset($_GET['action']) && $_GET['action'] === 'resolve' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->resolveRoundAjax();
            return;
        }

        // 2) Export JSON de l'état de la partie
        if (isset($_GET['action']) && $_GET['action'] === 'export') {
            $this->exportJson();
            return;
        }

        // 3) Ajout d'une tâche par l'animateur (formulaire colonne de droite)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'addTask') {
            $this->ajouterTacheDepuisForm();
            return;
        }

        $code = $_GET['code'] ?? null;

        // 4) Création de partie (host)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['join']) && !$code) {
            $this->creerPartieDepuisForm();
            return;
        }

        // 5) Rejoindre une partie (choix pseudo)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join']) && $code) {
            $this->rejoindrePartieDepuisForm($code);
            return;
        }

        // 6) Si on arrive ici avec un code, charger la partie en session
        if ($code) {
            $this->chargerPartieEnSession($code);
        }

        // La vue Partie.php gère l'affichage (création / rejoindre / jeu)
        require "View/Navigation/Partie.php";
    }


    private function creerPartieDepuisForm(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: Index.php?page=Connexion");
            exit;
        }

        $nom        = trim($_POST['nom'] ?? '');
        $nbJoueur   = (int)($_POST['nb_joueur'] ?? 0);
        $modeJeu    = $_POST['mode'] ?? 'strict'; // 'strict' ou 'moyenne'
        $minuteur   = $_POST['minuteur'] ?? '00:02:00';
        $backlogJson = $_POST['backlog_json'] ?? '[]';

        if ($nom === '' || $nbJoueur < 2) {
            $_SESSION['create_error'] = "Merci de renseigner un nom et un nombre de joueurs valide.";
            require "View/Navigation/Partie.php";
            return;
        }

        // Code de partie
        $code = strtoupper(substr(md5(uniqid('', true)), 0, 6));

        $idCreateur     = (int)$_SESSION['user_id'];
        $pseudoCreateur = $_SESSION['pseudo'] ?? 'Animateur';

        // Enregistrement en BDD
        $idPartie = $this->partieModel->creerPartie(
            $nom,
            $code,
            $nbJoueur,
            $modeJeu,
            $minuteur,
            $idCreateur,
            $pseudoCreateur
        );

        // Backlog → état de partie
        $tasks = json_decode($backlogJson, true);
        if (!is_array($tasks)) {
            $_SESSION['create_error'] = "Backlog JSON invalide, la partie est créée mais sans tâches.";
            $tasks = [];
        }

        $players = [$pseudoCreateur];
        $state   = GameState::createFromBacklog($tasks, $players, $modeJeu);

        if (!isset($_SESSION['game'])) {
            $_SESSION['game'] = [];
        }
        $_SESSION['game'][$code] = $state;

        // Infos en session
        $_SESSION['partie_id']   = $idPartie;
        $_SESSION['partie_code'] = $code;
        $_SESSION['partie_nom']  = $nom;
        $_SESSION['est_maitre'][$code] = true;

        header("Location: Index.php?page=Partie&code=" . urlencode($code));
        exit;
    }

    private function rejoindrePartieDepuisForm(string $code): void
    {
        $pseudo = trim($_POST['pseudo'] ?? '');
        if ($pseudo === '') {
            $_SESSION['join_error'] = "Merci de choisir un pseudonyme.";
            header("Location: Index.php?page=Partie&code=" . urlencode($code));
            exit;
        }

        $_SESSION['pseudo'] = $pseudo;

        header("Location: Index.php?page=Partie&code=" . urlencode($code) . "&pseudo=" . urlencode($pseudo));
        exit;
    }

    private function ajouterTacheDepuisForm(): void
    {
        $code = $_GET['code'] ?? ($_SESSION['partie_code'] ?? null);

        if (!$code || !isset($_SESSION['game'][$code])) {
            $_SESSION['task_error'] = "Partie introuvable. Impossible d'ajouter une tâche.";
            header("Location: Index.php?page=Partie");
            exit;
        }

        $titre = trim($_POST['task_title'] ?? '');
        $desc  = trim($_POST['task_description'] ?? '');

        if ($titre === '') {
            $_SESSION['task_error'] = "Le titre de la tâche est obligatoire.";
            header("Location: Index.php?page=Partie&code=" . urlencode($code));
            exit;
        }

        $state = $_SESSION['game'][$code];

        if (!isset($state['tasks']) || !is_array($state['tasks'])) {
            $state['tasks'] = [];
        }

        // Trouver un nouvel ID (max + 1)
        $maxId = 0;
        foreach ($state['tasks'] as $t) {
            if (isset($t['id']) && $t['id'] > $maxId) {
                $maxId = $t['id'];
            }
        }
        $newId = $maxId + 1;

        $state['tasks'][] = [
            'id'          => $newId,
            'title'       => $titre,
            'description' => $desc,
            'estimate'    => null,
            'status'      => 'pending'
        ];

        $_SESSION['game'][$code] = $state;

        header("Location: Index.php?page=Partie&code=" . urlencode($code));
        exit;
    }

    private function chargerPartieEnSession(string $code): void
    {
        $partie = $this->partieModel->getPartieByCode($code);

        if (!$partie) {
            http_response_code(404);
            echo "<div class='container mt-5'>
                    <div class='alert alert-danger text-center'>
                        Partie introuvable pour le code <strong>" . htmlspecialchars($code) . "</strong>.
                    </div>
                  </div>";
            exit;
        }

        $_SESSION['partie_id']   = $partie['id'];
        $_SESSION['partie_code'] = $partie['lien'];
        $_SESSION['partie_nom']  = $partie['nom'];

        if (!isset($_SESSION['game'][$code])) {
            $state = GameState::createFromBacklog([], [], $partie['mode']);
            $_SESSION['game'][$code] = $state;
        }
    }

    private function resolveRoundAjax(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $code = $_GET['code'] ?? ($_SESSION['partie_code'] ?? null);
        if (!$code || !isset($_SESSION['game'][$code])) {
            echo json_encode(['ok' => false, 'error' => 'Partie introuvable côté serveur.']);
            return;
        }

        $raw     = file_get_contents('php://input');
        $payload = json_decode($raw, true);

        if (!is_array($payload) || empty($payload['votes']) || !is_array($payload['votes'])) {
            echo json_encode(['ok' => false, 'error' => 'Votes manquants ou invalides.']);
            return;
        }

        $state = $_SESSION['game'][$code];
        $mode  = $state['mode'] ?? 'strict';

        $currentIndex = $state['current_index'] ?? 0;
        if (empty($state['tasks']) || !isset($state['tasks'][$currentIndex])) {
            echo json_encode([
                'ok'    => false,
                'error' => 'Aucune tâche en cours. Ajoute au moins une tâche dans le backlog avant de révéler les votes.'
            ]);
            return;
        }

        $values = [];
        foreach ($payload['votes'] as $vote) {
            if (!isset($vote['valeur'])) continue;
            $values[] = $vote['valeur'];
        }

        $round = ($state['tasks'][$currentIndex]['round'] ?? 0) + 1;

        $result = GameRules::decide($mode, $values, $round);

        if ($result['type'] === 'pause') {
            $state['tasks'][$currentIndex]['round'] = $round;
            $_SESSION['game'][$code] = $state;

            echo json_encode([
                'ok'        => true,
                'status'    => 'pause',
                'message'   => 'Tous les joueurs ont choisi la carte café.',
                'exportUrl' => 'Index.php?page=Partie&code=' . urlencode($code) . '&action=export'
            ]);
            return;
        }

        if ($result['type'] === 'validated') {
            $state['tasks'][$currentIndex]['round']    = $round;
            $state['tasks'][$currentIndex]['estimate'] = $result['estimate'];
            $state['tasks'][$currentIndex]['status']   = 'validated';

            $state['current_index'] = $currentIndex + 1;

            $_SESSION['game'][$code] = $state;

            $finished = $state['current_index'] >= count($state['tasks']);

            echo json_encode([
                'ok'        => true,
                'status'    => 'validated',
                'estimate'  => $result['estimate'],
                'reason'    => $result['reason'],
                'task'      => $state['tasks'][$currentIndex],
                'finished'  => $finished,
                'exportUrl' => $finished
                    ? 'Index.php?page=Partie&code=' . urlencode($code) . '&action=export'
                    : null
            ]);
            return;
        }

        // sinon : on continue
        $state['tasks'][$currentIndex]['round'] = $round;
        $_SESSION['game'][$code] = $state;

        echo json_encode([
            'ok'     => true,
            'status' => 'continue',
            'reason' => $result['reason'],
            'round'  => $round
        ]);
    }

    private function exportJson(): void
    {
        $code = $_GET['code'] ?? ($_SESSION['partie_code'] ?? null);
        if (!$code || !isset($_SESSION['game'][$code])) {
            http_response_code(404);
            echo "Partie introuvable.";
            return;
        }

        $state = $_SESSION['game'][$code];
        $json  = GameState::toJson($state);

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename=\"planning_poker_' . $code . '.json\"');
        echo $json;
    }
}
