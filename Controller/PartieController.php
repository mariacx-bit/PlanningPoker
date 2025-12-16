<?php
require_once "Model/PartieModel.php";
require_once "Entities/GameRules.php";

class PartieController
{
    private PartieModel $model;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->model = new PartieModel();
    }

    public function partie()
    {
        $action = $_GET['action'] ?? null;

        if ($action === 'saveBacklog' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->saveBacklogAjax(); return;
        }
        if ($action === 'resolve' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->resolveAjax(); return;
        }
        if ($action === 'export') {
            $this->exportJson(); return;
        }

        // page
        $code = $_GET['code'] ?? null;
        if (!$code) { echo "Code manquant"; return; }

        $partie = $this->model->getPartieByCode($code);
        if (!$partie) { echo "Partie introuvable"; return; }

        // pseudo session (simple)
        $pseudo = $_SESSION['pseudo'] ?? 'Anonyme';

        // animateur = créateur DB
        $isAnimateur = isset($_SESSION['user_id']) ? $this->model->isAnimateur($code, (int)$_SESSION['user_id']) : false;

        // backlog + progression
        $tasks = $this->model->getBacklog($code);
        $currentIndex = (int)($partie['tache_index'] ?? 0);
        $tour = (int)($partie['tour'] ?? 1);
        $mode = $partie['mode'] ?? 'strict';

        $tacheCourante = $tasks[$currentIndex] ?? null;

        // variables pour la vue
        $codePartie = $code;

        require __DIR__ . "/../View/Navigation/Partie.php";
    }

    private function saveBacklogAjax(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $code = $_GET['code'] ?? null;
        if (!$code) { echo json_encode(['ok'=>false,'message'=>'Code manquant']); return; }

        if (!isset($_SESSION['user_id']) || !$this->model->isAnimateur($code, (int)$_SESSION['user_id'])) {
            echo json_encode(['ok'=>false,'message'=>"Seul l'animateur peut modifier le backlog"]); return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        $backlog = $payload['backlog'] ?? null;

        if (!is_array($backlog)) { echo json_encode(['ok'=>false,'message'=>'JSON invalide']); return; }

        // normaliser des clés (title/description)
        $norm = [];
        $id = 1;
        foreach ($backlog as $t) {
            if (!is_array($t)) continue;
            $title = trim((string)($t['title'] ?? $t['titre'] ?? ''));
            if ($title === '') continue;
            $norm[] = [
                'id' => (int)($t['id'] ?? $id++),
                'title' => $title,
                'description' => (string)($t['description'] ?? $t['desc'] ?? ''),
            ];
        }

        $this->model->updateBacklog($code, $norm);
        $this->model->saveResultats($code, []); // reset résultats

        echo json_encode(['ok'=>true,'message'=>'Backlog enregistré ✅']);
    }

    private function resolveAjax(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $code = $_GET['code'] ?? null;
        if (!$code) { echo json_encode(['ok'=>false,'message'=>'Code manquant']); return; }

        if (!isset($_SESSION['user_id']) || !$this->model->isAnimateur($code, (int)$_SESSION['user_id'])) {
            echo json_encode(['ok'=>false,'message'=>"Seul l'animateur peut valider"]); return;
        }

        $partie = $this->model->getPartieByCode($code);
        if (!$partie) { echo json_encode(['ok'=>false,'message'=>'Partie introuvable']); return; }

        $tasks = $this->model->getBacklog($code);
        $i = (int)($partie['tache_index'] ?? 0);
        $tour = (int)($partie['tour'] ?? 1);
        $mode = $partie['mode'] ?? 'strict';

        if (!isset($tasks[$i])) {
            echo json_encode(['ok'=>false,'message'=>'Aucune tâche en cours']); return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        $votesIn = $payload['votes'] ?? null;

        // mapping pseudo->valeur
        $values = [];
        if (is_array($votesIn)) {
            $isAssoc = array_keys($votesIn) !== range(0, count($votesIn) - 1);
            if ($isAssoc) {
                foreach ($votesIn as $p => $v) $values[] = $v;
            } else {
                foreach ($votesIn as $row) if (is_array($row) && isset($row['valeur'])) $values[] = $row['valeur'];
            }
        }

        if (!$values) { echo json_encode(['ok'=>false,'message'=>'Votes manquants']); return; }

        $result = GameRules::decide($mode, $values, $tour);

        // pause café
        if (($result['type'] ?? '') === 'pause') {
            $this->model->updateProgress($code, $i, $tour + 1, 'pause');
            echo json_encode([
                'ok'=>true,'status'=>'pause',
                'message'=>'☕ Pause café (tout le monde a voté café)',
                'exportUrl'=>"Index.php?page=Partie&action=export&code=" . urlencode($code)
            ]);
            return;
        }

        // validée
        if (($result['type'] ?? '') === 'validated') {
            $estimate = $result['estimate'] ?? null;

            $resultats = $this->model->getResultats($code);
            $resultats[] = [
                'task' => $tasks[$i],
                'estimate' => $estimate,
                'round' => $tour,
                'mode' => $mode
            ];
            $this->model->saveResultats($code, $resultats);

            $newIndex = $i + 1;
            $finished = $newIndex >= count($tasks);
            $this->model->updateProgress($code, $newIndex, 1, $finished ? 'done' : 'running');

            echo json_encode([
                'ok'=>true,'status'=>'validated',
                'message'=>"✅ Tâche validée : $estimate",
                'finished'=>$finished,
                'exportUrl'=>$finished ? "Index.php?page=Partie&action=export&code=" . urlencode($code) : null
            ]);
            return;
        }

        // pas validée => tour suivant
        $this->model->updateProgress($code, $i, $tour + 1, 'running');

        echo json_encode([
            'ok'=>true,'status'=>'continue',
            'message'=>'Pas d’accord → nouveau tour (discussion)',
            'round'=>$tour + 1
        ]);
    }

    private function exportJson(): void
    {
        $code = $_GET['code'] ?? null;
        if (!$code) { http_response_code(400); echo "Code manquant"; return; }

        $partie = $this->model->getPartieByCode($code);
        if (!$partie) { http_response_code(404); echo "Partie introuvable"; return; }

        $tasks = $this->model->getBacklog($code);
        $resultats = $this->model->getResultats($code);

        $payload = [
            'code' => $code,
            'mode' => $partie['mode'],
            'etat' => $partie['etat'],
            'tache_index' => (int)$partie['tache_index'],
            'tour' => (int)$partie['tour'],
            'backlog' => $tasks,
            'resultats' => $resultats,
        ];

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="planning_poker_' . $code . '.json"');
        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
