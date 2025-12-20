<?php
require_once __DIR__ . "/../Model/PartieModel.php";
require_once __DIR__ . "/../Entities/GameRules.php";
require_once __DIR__ . "/../Entities/GameState.php";

class PartieController
{
    private PartieModel $model;

    public function __construct()
    {
        $this->model = new PartieModel();
    }

    public function partie()
    {
        $action = $_GET['action'] ?? null;

        if ($action === 'export') { $this->export(); return; }
        if ($action === 'saveBacklog') { $this->saveBacklog(); return; }
        if ($action === 'setCurrent') { $this->setCurrent(); return; }
        if ($action === 'resolve') { $this->resolve(); return; }

        $code = $_GET['code'] ?? '';
        $partie = $this->model->getPartieByCode($code);
        if (!$partie) { die("Partie introuvable"); }

        $pseudo = $_SESSION['pseudo'] ?? 'Anonyme';

        $isAnimateur = false;
        if (isset($_SESSION['user_id'])) {
            $isAnimateur = ((int)$partie['id_createur'] === (int)$_SESSION['user_id']);
        }

        $mode = $partie['mode'] ?? 'strict';
        $tour = (int)($partie['tour'] ?? 1);
        $currentIndex = (int)($partie['tache_index'] ?? 0);

        $tasks = [];
        if (!empty($partie['backlog_json'])) {
            $tasks = json_decode($partie['backlog_json'], true) ?: [];
        }

        $tacheCourante = $tasks[$currentIndex] ?? null;

        // minuteur (TIME) -> secondes
        $minuteurSeconds = $this->timeToSeconds($partie['minuteur'] ?? '00:02:00');

        $codePartie = $code;

        require __DIR__ . "/../View/Navigation/Partie.php";
    }

    private function jsonIn(): array
    {
        $raw = file_get_contents("php://input");
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function jsonOut(array $out)
    {
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode($out);
    }

    private function saveBacklog()
    {
        $code = $_GET['code'] ?? '';
        $partie = $this->model->getPartieByCode($code);
        if (!$partie) { $this->jsonOut(['ok'=>false,'message'=>'Partie introuvable']); return; }

        // seulement animateur
        if ((int)$partie['id_createur'] !== (int)($_SESSION['user_id'] ?? 0)) {
            $this->jsonOut(['ok'=>false,'message'=>'Non autorisé']); return;
        }

        $in = $this->jsonIn();
        $backlog = $in['backlog'] ?? null;
        if (!is_array($backlog)) { $this->jsonOut(['ok'=>false,'message'=>'Backlog invalide']); return; }

        // normalise + status/estimate
        $normalized = [];
        $id = 1;
        foreach ($backlog as $t) {
            $title = trim((string)($t['title'] ?? $t['titre'] ?? ''));
            if ($title === '') continue;
            $normalized[] = [
                'id' => $t['id'] ?? $id++,
                'title' => $title,
                'description' => (string)($t['description'] ?? $t['desc'] ?? ''),
                'status' => $t['status'] ?? 'pending',
                'estimate' => $t['estimate'] ?? null,
            ];
        }

        $this->model->saveBacklog($code, json_encode($normalized, JSON_UNESCAPED_UNICODE));

        $this->jsonOut(['ok'=>true,'message'=>'Backlog enregistré']);
    }

    private function setCurrent()
    {
        $code = $_GET['code'] ?? '';
        $partie = $this->model->getPartieByCode($code);
        if (!$partie) { $this->jsonOut(['ok'=>false,'message'=>'Partie introuvable']); return; }

        if ((int)$partie['id_createur'] !== (int)($_SESSION['user_id'] ?? 0)) {
            $this->jsonOut(['ok'=>false,'message'=>'Non autorisé']); return;
        }

        $in = $this->jsonIn();
        $index = (int)($in['index'] ?? 0);

        $tasks = json_decode($partie['backlog_json'] ?? '[]', true) ?: [];
        if (!isset($tasks[$index])) { $this->jsonOut(['ok'=>false,'message'=>'Index invalide']); return; }

        $this->model->setCurrentTask($code, $index);
        $this->jsonOut(['ok'=>true,'message'=>'Tâche active changée']);
    }

    private function resolve()
    {
        $code = $_GET['code'] ?? '';
        $partie = $this->model->getPartieByCode($code);
        if (!$partie) { $this->jsonOut(['ok'=>false,'message'=>'Partie introuvable']); return; }

        if ((int)$partie['id_createur'] !== (int)($_SESSION['user_id'] ?? 0)) {
            $this->jsonOut(['ok'=>false,'message'=>'Non autorisé']); return;
        }

        $tasks = json_decode($partie['backlog_json'] ?? '[]', true) ?: [];
        $currentIndex = (int)($partie['tache_index'] ?? 0);
        $tour = (int)($partie['tour'] ?? 1);
        $mode = (string)($partie['mode'] ?? 'strict');

        if (!isset($tasks[$currentIndex])) {
            $this->jsonOut(['ok'=>false,'message'=>'Aucune tâche active']); return;
        }

        $in = $this->jsonIn();
        $votes = $in['votes'] ?? [];
        if (!is_array($votes)) $votes = [];

        // votes = {pseudo: valeur}
        $voteValues = array_values($votes);

        $decision = GameRules::decide($mode, $voteValues, $tour);

        if ($decision['type'] === 'pause') {
            $this->model->setEtat($code, 'pause');
            $this->jsonOut(['ok'=>true,'status'=>'pause','reason'=>$decision['reason']]);
            return;
        }

        if ($decision['type'] === 'continue') {
            $this->model->incrementRound($code);
            $this->jsonOut(['ok'=>true,'status'=>'continue','reason'=>$decision['reason']]);
            return;
        }

        // validated
        $estimate = $decision['estimate'];

        $tasks[$currentIndex]['status'] = 'validated';
        $tasks[$currentIndex]['estimate'] = $estimate;

        // resultats JSON
        $resultats = json_decode($partie['resultats'] ?? '{}', true);
        if (!is_array($resultats)) $resultats = [];
        $taskId = $tasks[$currentIndex]['id'] ?? $currentIndex;
        $resultats[(string)$taskId] = $estimate;

        // next pending task
        $nextIndex = $this->findNextPendingIndex($tasks, $currentIndex);

        // si plus rien -> terminé
        if ($nextIndex === -1) {
            $this->model->updateAfterValidation(
                $code,
                json_encode($tasks, JSON_UNESCAPED_UNICODE),
                json_encode($resultats, JSON_UNESCAPED_UNICODE),
                $currentIndex
            );
            $this->model->setEtat($code, 'termine');
            $this->jsonOut(['ok'=>true,'status'=>'validated','estimate'=>$estimate,'finished'=>true]);
            return;
        }

        $this->model->updateAfterValidation(
            $code,
            json_encode($tasks, JSON_UNESCAPED_UNICODE),
            json_encode($resultats, JSON_UNESCAPED_UNICODE),
            $nextIndex
        );

        $this->jsonOut(['ok'=>true,'status'=>'validated','estimate'=>$estimate,'finished'=>false,'nextIndex'=>$nextIndex]);
    }

    private function export()
    {
        $code = $_GET['code'] ?? '';
        $partie = $this->model->getPartieByCode($code);
        if (!$partie) { die("Partie introuvable"); }

        $out = [
            'lien' => $partie['lien'],
            'mode' => $partie['mode'],
            'etat' => $partie['etat'],
            'tour' => (int)$partie['tour'],
            'tache_index' => (int)$partie['tache_index'],
            'backlog' => json_decode($partie['backlog_json'] ?? '[]', true) ?: [],
            'resultats' => json_decode($partie['resultats'] ?? '{}', true) ?: [],
        ];

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="planning_poker_' . $code . '.json"');
        echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function findNextPendingIndex(array $tasks, int $current): int
    {
        for ($i = $current + 1; $i < count($tasks); $i++) {
            if (($tasks[$i]['status'] ?? 'pending') !== 'validated') return $i;
        }
        for ($i = 0; $i < $current; $i++) {
            if (($tasks[$i]['status'] ?? 'pending') !== 'validated') return $i;
        }
        return -1;
    }

    private function timeToSeconds(string $time): int
    {
        // accepte "HH:MM:SS" ou "MM:SS"
        $parts = explode(':', $time);
        if (count($parts) === 3) {
            return ((int)$parts[0])*3600 + ((int)$parts[1])*60 + (int)$parts[2];
        }
        if (count($parts) === 2) {
            return ((int)$parts[0])*60 + (int)$parts[1];
        }
        return 120;
    }
}
