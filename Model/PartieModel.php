<?php
require_once "Model/ModelMain.php";

class PartieModel extends ModelMain
{
    public function creerPartie(
        string $nom,
        string $code,
        int $nbJoueur,
        string $mode,
        string $minuteur,
        int $idCreateur,
        string $createur,
        array $backlog = []
    ): int {
        $sql = "INSERT INTO partie
            (nom, lien, nb_joueur, mode, minuteur, createur, id_createur, etat, backlog_json, resultats, tache_index, tour, created_at)
            VALUES
            (:nom, :lien, :nb_joueur, :mode, :minuteur, :createur, :id_createur, 'running', :backlog_json, :resultats, 0, 1, CURDATE())";

        $this->executeReq($sql, [
            'nom'         => $nom,
            'lien'        => $code,
            'nb_joueur'   => $nbJoueur,
            'mode'        => $mode,
            'minuteur'    => $minuteur,
            'createur'    => $createur,
            'id_createur' => $idCreateur,
            'backlog_json'=> json_encode($backlog, JSON_UNESCAPED_UNICODE),
            'resultats'   => json_encode([], JSON_UNESCAPED_UNICODE),
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function getPartieByCode(string $code): ?array
    {
        $sql  = "SELECT * FROM partie WHERE lien = :lien LIMIT 1";
        $stmt = $this->executeReq($sql, ['lien' => $code]);
        $row  = $stmt->fetch();
        return $row ?: null;
    }

    public function updateBacklog(string $code, array $backlog): void
    {
        $this->executeReq(
            "UPDATE partie SET backlog_json = :b, tache_index = 0, tour = 1, etat='running' WHERE lien = :lien",
            ['b' => json_encode($backlog, JSON_UNESCAPED_UNICODE), 'lien' => $code]
        );
    }

    public function getBacklog(string $code): array
    {
        $p = $this->getPartieByCode($code);
        if (!$p || empty($p['backlog_json'])) return [];
        $decoded = json_decode($p['backlog_json'], true);
        return is_array($decoded) ? $decoded : [];
    }

    public function getResultats(string $code): array
    {
        $p = $this->getPartieByCode($code);
        if (!$p || empty($p['resultats'])) return [];
        $decoded = json_decode($p['resultats'], true);
        return is_array($decoded) ? $decoded : [];
    }

    public function saveResultats(string $code, array $resultats): void
    {
        $this->executeReq(
            "UPDATE partie SET resultats = :r WHERE lien = :lien",
            ['r' => json_encode($resultats, JSON_UNESCAPED_UNICODE), 'lien' => $code]
        );
    }

    public function updateProgress(string $code, int $tacheIndex, int $tour, string $etat = 'running'): void
    {
        $this->executeReq(
            "UPDATE partie SET tache_index = :i, tour = :t, etat = :etat WHERE lien = :lien",
            ['i' => $tacheIndex, 't' => $tour, 'etat' => $etat, 'lien' => $code]
        );
    }

    public function isAnimateur(string $code, int $userId): bool
    {
        $p = $this->getPartieByCode($code);
        return $p && (int)$p['id_createur'] === (int)$userId;
    }
}
