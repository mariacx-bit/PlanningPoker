<?php
require_once __DIR__ . "/ModelMain.php";

class PartieModel extends ModelMain
{
    public function getPartieByCode(string $code): ?array
    {
        $sql = "SELECT * FROM partie WHERE lien = :lien LIMIT 1";
        $stmt = $this->executeReq($sql, ['lien' => $code]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getPartiesByCreateur(int $idCreateur): array
    {
        $sql = "SELECT * FROM partie WHERE id_createur = :id ORDER BY created_at DESC";
        $stmt = $this->executeReq($sql, ['id' => $idCreateur]);
        return $stmt->fetchAll();
    }

    public function saveBacklog(string $code, string $backlogJson): void
    {
        $sql = "UPDATE partie
                SET backlog_json = :b, tache_index = 0, tour = 1, etat = 'en_cours'
                WHERE lien = :lien";
        $this->executeReq($sql, ['b' => $backlogJson, 'lien' => $code]);
    }

    public function setCurrentTask(string $code, int $index): void
    {
        $sql = "UPDATE partie SET tache_index = :i, tour = 1 WHERE lien = :lien";
        $this->executeReq($sql, ['i' => $index, 'lien' => $code]);
    }

    public function updateAfterValidation(string $code, string $backlogJson, string $resultatsJson, int $nextIndex): void
    {
        $sql = "UPDATE partie
                SET backlog_json = :b, resultats = :r, tache_index = :i, tour = 1
                WHERE lien = :lien";
        $this->executeReq($sql, [
            'b' => $backlogJson,
            'r' => $resultatsJson,
            'i' => $nextIndex,
            'lien' => $code
        ]);
    }

    public function incrementRound(string $code): void
    {
        $sql = "UPDATE partie SET tour = tour + 1 WHERE lien = :lien";
        $this->executeReq($sql, ['lien' => $code]);
    }

    public function setEtat(string $code, string $etat): void
    {
        $sql = "UPDATE partie SET etat = :e WHERE lien = :lien";
        $this->executeReq($sql, ['e' => $etat, 'lien' => $code]);
    }
}
