<?php
require_once "Model/ModelMain.php";

class PartieModel extends ModelMain
{
    /* =======================c
       GET
    ======================= */

    public function getPartieByCode(string $code): ?array
    {
        $sql  = "SELECT * FROM partie WHERE lien = :lien LIMIT 1";
        $stmt = $this->executeReq($sql, ['lien' => $code]);
        $res  = $stmt->fetch();
        return $res ?: null;
    }

    public function getPartiesByCreateur(int $idCreateur): array
    {
        $sql = "SELECT *
                FROM partie
                WHERE id_createur = :id
                ORDER BY created_at DESC";

        $stmt = $this->executeReq($sql, ['id' => $idCreateur]);
        return $stmt->fetchAll();
    }

    /* =======================
       CREATE
    ======================= */

    /**
     * Crée une partie dans ta table actuelle.
     * Retourne l'ID auto-increment.
     */
    public function creerPartie(
        string $nom,
        string $code,
        int $nbJoueur,
        string $mode,
        string $minuteur,
        int $idCreateur,
        string $createur
    ): int {
        $sql = "INSERT INTO partie
                (nom, lien, nb_joueur, mode, minuteur, createur, id_createur, etat, backlog_json, resultats, tache_index, tour, created_at)
                VALUES
                (:nom, :lien, :nb_joueur, :mode, :minuteur, :createur, :id_createur, 'en_cours', NULL, NULL, 0, 1, CURDATE())";

        $this->executeReq($sql, [
            'nom'         => $nom,
            'lien'        => $code,
            'nb_joueur'   => $nbJoueur,
            'mode'        => $mode,
            'minuteur'    => $minuteur,
            'createur'    => $createur,
            'id_createur' => $idCreateur,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Génère un code unique (6 chars) et vérifie qu’il n’existe pas déjà.
     */
    public function genererCodeUnique(int $len = 6): string
    {
        do {
            $code = strtoupper(substr(bin2hex(random_bytes(8)), 0, $len));
            $exists = $this->getPartieByCode($code);
        } while ($exists);

        return $code;
    }

    /* =======================
       BACKLOG / GAME STATE
    ======================= */

    public function saveBacklog(string $code, string $backlogJson): void
    {
        $sql = "UPDATE partie
                SET backlog_json = :b, tache_index = 0, tour = 1
                WHERE lien = :lien";
        $this->executeReq($sql, [
            'b'    => $backlogJson,
            'lien' => $code
        ]);
    }

    public function saveBacklogKeepIndexTour(string $code, string $backlogJson, int $index, int $tour): void
    {
        $sql = "UPDATE partie
                SET backlog_json = :b, tache_index = :i, tour = :t
                WHERE lien = :lien";
        $this->executeReq($sql, [
            'b'    => $backlogJson,
            'i'    => $index,
            't'    => $tour,
            'lien' => $code
        ]);
    }

    public function setTourAndIndex(string $code, int $index, int $tour): void
    {
        $sql = "UPDATE partie
                SET tache_index = :i, tour = :t
                WHERE lien = :lien";
        $this->executeReq($sql, [
            'i'    => $index,
            't'    => $tour,
            'lien' => $code
        ]);
    }

    public function pickTask(string $code, int $index): void
    {
        $sql = "UPDATE partie SET tache_index = :i, tour = 1 WHERE lien = :lien";
        $this->executeReq($sql, [
            'i'    => $index,
            'lien' => $code
        ]);
    }

    public function saveResult(string $code, string $resultatsJson, int $newIndex, int $newTour): void
    {
        $sql = "UPDATE partie
                SET resultats = :r, tache_index = :idx, tour = :tour
                WHERE lien = :lien";
        $this->executeReq($sql, [
            'r'    => $resultatsJson,
            'idx'  => $newIndex,
            'tour' => $newTour,
            'lien' => $code
        ]);
    }

    public function setEtat(string $code, string $etat): void
    {
        $sql = "UPDATE partie SET etat = :e WHERE lien = :lien";
        $this->executeReq($sql, [
            'e'    => $etat,
            'lien' => $code
        ]);
    }
}
