<?php
require_once "Model/PartieModel.php";
require_once "Entities/GameRules.php";

class PartieController
{
    private PartieModel $model;

    public function __construct()
    {
        $this->model = new PartieModel();
    }

    public function partie(): void
    {
        $action = $_GET['action'] ?? null;

        if ($action === 'saveBacklog') { $this->saveBacklog(); return; }
        if ($action === 'pick')        { $this->pick(); return; }
        if ($action === 'resolve')     { $this->resolve(); return; }
        if ($action === 'export')      { $this->export(); return; }

        $this->renderPartie();
    }

    /* =========================
        HTML
    ========================= */

    private function renderPartie(): void
{
    $code = $_GET['code'] ?? '';
    if ($code === '') { echo "Code manquant"; return; }

    $partie = $this->model->getPartieByCode($code);
    if (!$partie) { echo "Partie introuvable"; return; }

    /**
     * ✅ DEMANDE PSEUDO POUR LES EXTERNES
     * - Si pas de pseudo en session => afficher formulaire JoinPartie
     * - Quand POST pseudo => stocker en session + redirect vers Partie&code=...
     */
    if (!isset($_SESSION['pseudo']) || trim((string)$_SESSION['pseudo']) === '') {

        // Soumission du pseudo
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pseudo'])) {
            $p = trim((string)$_POST['pseudo']);

            // validation simple
            if ($p === '') {
                $errorPseudo = "Pseudo obligatoire.";
                require "View/Navigation/JoinPartie.php";
                return;
            }
            if (mb_strlen($p) < 2) {
                $errorPseudo = "Pseudo trop court (min 2 caractères).";
                require "View/Navigation/JoinPartie.php";
                return;
            }
            if (mb_strlen($p) > 20) {
                $errorPseudo = "Pseudo trop long (max 20 caractères).";
                require "View/Navigation/JoinPartie.php";
                return;
            }

            $_SESSION['pseudo'] = $p;

            header("Location: Index.php?page=Partie&code=" . urlencode($code));
            exit;
        }

        // Affichage formulaire
        $errorPseudo = null;
        require "View/Navigation/JoinPartie.php";
        return;
    }

    // ✅ à partir d'ici : pseudo OK
    $pseudo = (string)($_SESSION['pseudo'] ?? 'Anonyme');

    // Attention : animateur = createur en BDD (pseudo)
    $isAnimateur = ($pseudo !== '' && ($partie['createur'] ?? '') === $pseudo);

    $mode = $partie['mode'] ?? 'strict';
    $tour = (int)($partie['tour'] ?? 1);
    $currentIndex = (int)($partie['tache_index'] ?? 0);

    $tasks = $this->decodeBacklog($partie['backlog_json'] ?? '');

    if ($currentIndex < 0) $currentIndex = 0;
    if (count($tasks) > 0 && $currentIndex >= count($tasks)) $currentIndex = 0;

    $tacheCourante = $tasks[$currentIndex] ?? null;

    $minuteurStr = (string)($partie['minuteur'] ?? '00:02:00');
    $minuteurSeconds = $this->timeToSeconds($minuteurStr);

    $codePartie = $code; // variable pour la vue

    require "View/Navigation/Partie.php";
}


    /* =========================
        JSON helpers
    ========================= */

    private function jsonIn(): array
    {
        $raw = file_get_contents("php://input");
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function jsonOut(array $payload): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function mustBeAnimateur(array $partie): bool
    {
        $pseudo = $_SESSION['pseudo'] ?? '';
        return $pseudo !== '' && ($partie['createur'] ?? '') === $pseudo;
    }

    public function createpartie()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: Index.php?page=Connexion");
            exit;
        }

        // si POST => créer en base puis redirect vers Partie avec ?code=
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? 'Ma partie');
            $mode = $_POST['mode'] ?? 'strict';
            $nb = (int)($_POST['nb_joueur'] ?? 2);
            $minuteur = $_POST['minuteur'] ?? '00:02:00';

            $code = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $pseudo = $_SESSION['pseudo'] ?? 'Anonyme'; // adapte à ton projet
            $idCreateur = (int)$_SESSION['user_id'];

            $this->model->creerPartie($nom, $code, $nb, $mode, $minuteur, $idCreateur, $pseudo);

            header("Location: Index.php?page=Partie&code=" . urlencode($code));
            exit;
        }

        // sinon => afficher le formulaire
        require "View/Navigation/CreatePartie.php";
    }


    /* =========================
        IMPORTANT: decode backlog
        (ton BDD->executeReq() fait htmlentities)
    ========================= */

    private function decodeBacklog(string $raw): array
    {
        $raw = trim((string)$raw);
        if ($raw === '') return [];

        $raw = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = json_decode($raw, true);

        if (is_array($decoded)) return $decoded;

        error_log("decodeBacklog failed: " . json_last_error_msg() . " | raw=" . substr($raw, 0, 200));
        return [];
    }

    private function timeToSeconds(string $t): int
    {
        $parts = explode(':', $t);
        if (count($parts) !== 3) return 120;
        return ((int)$parts[0]) * 3600 + ((int)$parts[1]) * 60 + ((int)$parts[2]);
    }

    /* =========================
        ACTION: saveBacklog
        ✅ IDs uniques côté serveur
        ✅ Reset index/tour
    ========================= */

 private function saveBacklog(): void
{
    $code = $_GET['code'] ?? '';
    $partie = $this->model->getPartieByCode($code);

    if (!$partie) $this->jsonOut(['ok'=>false,'message'=>'Partie introuvable']);
    if (!$this->mustBeAnimateur($partie)) $this->jsonOut(['ok'=>false,'message'=>'Non autorisé']);

    $in = $this->jsonIn();
    $incoming = $in['backlog'] ?? null;

    if (!is_array($incoming) || count($incoming) === 0) {
        $this->jsonOut(['ok'=>false,'message'=>'Backlog vide ou invalide']);
    }

    // backlog existant (garde status/estimate)
    $existing = $this->decodeBacklog($partie['backlog_json'] ?? '');
    if (!is_array($existing)) $existing = [];

    // Indexation des tâches existantes par clé "title|description" (simple et efficace)
    $existingMap = [];
    $maxId = 0;
    foreach ($existing as $t) {
        if (!is_array($t)) continue;
        $title = trim((string)($t['title'] ?? ''));
        $desc  = trim((string)($t['description'] ?? ''));
        $key = mb_strtolower($title.'|'.$desc);

        $existingMap[$key] = $t;

        $tid = (int)($t['id'] ?? 0);
        if ($tid > $maxId) $maxId = $tid;
    }

    $added = 0;
    foreach ($incoming as $t) {
        if (!is_array($t)) continue;

        $title = trim((string)($t['title'] ?? $t['titre'] ?? ''));
        if ($title === '') continue;

        $desc = trim((string)($t['description'] ?? $t['desc'] ?? ''));

        $key = mb_strtolower($title.'|'.$desc);

        // si la tâche existe déjà, on ne touche pas (on garde estimate/status)
        if (isset($existingMap[$key])) {
            continue;
        }

        // sinon on l'ajoute
        $maxId++;
        $existingMap[$key] = [
            'id'          => $maxId,
            'title'       => $title,
            'description' => $desc,
            'status'      => 'pending',
            'estimate'    => null
        ];
        $added++;
    }

    // Reconstituer la liste dans l'ordre:
    // 1) ordre original existant
    // 2) puis les nouvelles à la fin
    $merged = [];

    // garder l’ordre des existantes
    foreach ($existing as $t) {
        $title = trim((string)($t['title'] ?? ''));
        $desc  = trim((string)($t['description'] ?? ''));
        $key = mb_strtolower($title.'|'.$desc);
        if (isset($existingMap[$key])) {
            $merged[] = $existingMap[$key];
            unset($existingMap[$key]);
        }
    }

    // le reste = nouvelles tâches
    foreach ($existingMap as $k => $t) {
        // si déjà ajouté dans merged on ignore
        // (ici, existingMap ne contient plus les existantes déjà poussées)
        $merged[] = $t;
    }

    // ⚠️ On NE reset PAS index/tour si la partie est déjà en cours
    $index = (int)($partie['tache_index'] ?? 0);
    $tour  = (int)($partie['tour'] ?? 1);

    // auto-fix index si invalide après merge (cas extrême)
    if ($index < 0) $index = 0;
    if ($index >= count($merged) && count($merged) > 0) $index = 0;

    $this->model->saveBacklogKeepIndexTour(
        $code,
        json_encode($merged, JSON_UNESCAPED_UNICODE),
        $index,
        $tour
    );

    $this->jsonOut([
        'ok'      => true,
        'message' => $added > 0 ? "Backlog mis à jour (+$added)" : "Aucune nouvelle tâche",
        'added'   => $added,
        'count'   => count($merged)
    ]);
}

    /* =========================
        ACTION: pick
        (animateur sélectionne une tâche)
    ========================= */

    private function pick(): void
    {
        $code = $_GET['code'] ?? '';
        $partie = $this->model->getPartieByCode($code);

        if (!$partie) $this->jsonOut(['ok'=>false,'message'=>'Partie introuvable']);
        if (!$this->mustBeAnimateur($partie)) $this->jsonOut(['ok'=>false,'message'=>'Non autorisé']);

        $tasks = $this->decodeBacklog($partie['backlog_json'] ?? '');

        $in = $this->jsonIn();
        $index = (int)($in['index'] ?? -1);

        if ($index < 0 || $index >= count($tasks)) {
            $this->jsonOut(['ok'=>false,'message'=>'Index tâche invalide']);
        }

        $this->model->pickTask($code, $index);

        $this->jsonOut([
            'ok'=>true,
            'message'=>'Tâche sélectionnée',
            'index'=>$index
        ]);
    }

    /* =========================
        ACTION: resolve
        ✅ FIX: si tache_index invalide -> auto-repair
    ========================= */

    private function resolve(): void
    {
        try {
            $code = $_GET['code'] ?? '';
            $partie = $this->model->getPartieByCode($code);

            if (!$partie) $this->jsonOut(['ok'=>false,'message'=>'Partie introuvable']);
            if (!$this->mustBeAnimateur($partie)) $this->jsonOut(['ok'=>false,'message'=>'Non autorisé']);

            $mode  = $partie['mode'] ?? 'strict';
            $tour  = (int)($partie['tour'] ?? 1);
            $index = (int)($partie['tache_index'] ?? 0);

            $tasks = $this->decodeBacklog($partie['backlog_json'] ?? '');

            // ✅ AUTO-REPAIR index si invalide
            if (!isset($tasks[$index])) {

                $fixedIndex = null;

                // 1) tâche pending
                foreach ($tasks as $i => $t) {
                    if (($t['status'] ?? 'pending') !== 'validated') { $fixedIndex = $i; break; }
                }

                // 2) fallback première tâche
                if ($fixedIndex === null && count($tasks) > 0) {
                    $fixedIndex = 0;
                }

                if ($fixedIndex === null) {
                    $this->jsonOut(['ok'=>false,'message'=>'Backlog vide']);
                }

                $index = (int)$fixedIndex;
                $this->model->setTourAndIndex($code, $index, 1);
                $tour = 1;
            }

            $in = $this->jsonIn();
            $votesMap = $in['votes'] ?? null;

            if (!is_array($votesMap) || count($votesMap) === 0) {
                $this->jsonOut(['ok'=>false,'message'=>'Votes manquants']);
            }

            $votesList = [];
            foreach ($votesMap as $p => $v) {
                $votesList[] = (string)$v;
            }

            $decision = GameRules::decide($mode, $votesList, $tour);
            $type = $decision['type'] ?? 'continue';

            if ($type === 'pause') {
                $this->jsonOut([
                    'ok'     => true,
                    'status' => 'pause',
                    'message'=> $decision['reason'] ?? 'pause café'
                ]);
            }

            if ($type === 'continue') {
                $this->model->setTourAndIndex($code, $index, $tour + 1);

                $this->jsonOut([
                    'ok'     => true,
                    'status' => 'continue',
                    'tour'   => $tour + 1,
                    'message'=> $decision['reason'] ?? 'Pas d’accord → nouveau tour'
                ]);
            }

            // validated
            $estimate = $decision['estimate'] ?? null;
            $tasks[$index]['status'] = 'validated';
            $tasks[$index]['estimate'] = $estimate;

            // prochaine pending
            $nextIndex = null;
            foreach ($tasks as $i => $t) {
                if (($t['status'] ?? '') !== 'validated') { $nextIndex = $i; break; }
            }

            if ($nextIndex === null) {
                // fini
                $this->model->saveBacklogKeepIndexTour(
                    $code,
                    json_encode($tasks, JSON_UNESCAPED_UNICODE),
                    $index,
                    1
                );

                $this->jsonOut([
                    'ok'       => true,
                    'status'   => 'validated',
                    'estimate' => $estimate,
                    'finished' => true,
                    'message'  => $decision['reason'] ?? 'Validé'
                ]);
            }

            $this->model->saveBacklogKeepIndexTour(
                $code,
                json_encode($tasks, JSON_UNESCAPED_UNICODE),
                (int)$nextIndex,
                1
            );

            $this->jsonOut([
                'ok'        => true,
                'status'    => 'validated',
                'estimate'  => $estimate,
                'nextIndex' => $nextIndex,
                'message'   => $decision['reason'] ?? 'Validé'
            ]);

        } catch (\Throwable $e) {
            $this->jsonOut(['ok'=>false,'message'=>'Exception: '.$e->getMessage()]);
        }
    }

    /* =========================
        ACTION: export
    ========================= */

    private function export(): void
    {
        $code = $_GET['code'] ?? '';
        $partie = $this->model->getPartieByCode($code);

        if (!$partie) { echo "Partie introuvable"; return; }

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="planning_poker_'.$code.'.json"');

        $raw = $partie['backlog_json'] ?? '[]';
        echo html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        exit;
    }
}
