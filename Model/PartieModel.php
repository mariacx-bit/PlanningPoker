<?php
require_once "Model/ModelMain.php";

class PartieModel extends ModelMain
{
    public function getPartieByCode(string $code): ?array
    {
        $sql  = "SELECT * FROM partie WHERE lien = :lien LIMIT 1";
        $stmt = $this->executeReq($sql, ['lien' => $code]);
        $res  = $stmt->fetch();
        return $res ?: null;
    }

    public function saveBacklog(string $code, string $backlogJson): void
    {
        $sql = "UPDATE partie
                SET backlog_json = :b, tache_index = 0, tour = 1
                WHERE lien = :lien";
        $this->executeReq($sql, [
            'b' => $backlogJson,
            'lien' => $code
        ]);
    }

    public function pickTask(string $code, int $index): void
    {
        $sql = "UPDATE partie SET tache_index = :i, tour = 1 WHERE lien = :lien";
        $this->executeReq($sql, [
            'i' => $index,
            'lien' => $code
        ]);
    }

    public function getPartiesByCreateur(int $idCreateur): array
    {
        $sql = "SELECT *
                FROM partie
                WHERE id_createur = :id
                ORDER BY created_at DESC";

        $stmt = $this->executeReq($sql, [
            'id' => $idCreateur
        ]);

        return $stmt->fetchAll();
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


    public function saveResult(string $code, string $resultatsJson, int $newIndex, int $newTour): void
    {
        $sql = "UPDATE partie
                SET resultats = :r, tache_index = :idx, tour = :tour
                WHERE lien = :lien";
        $this->executeReq($sql, [
            'r' => $resultatsJson,
            'idx' => $newIndex,
            'tour' => $newTour,
            'lien' => $code
        ]);
    }
}
