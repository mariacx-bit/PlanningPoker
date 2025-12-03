<?php

require_once "Model/ModelMain.php";

class PartieModel extends ModelMain
{
    /**
     * Crée une nouvelle partie en base.
     *
     * Table suggérée 'partie' :
     *  - id (INT, PK, AI)
     *  - nom (VARCHAR)
     *  - lien (VARCHAR) : code de la partie
     *  - nb_joueur (INT)
     *  - mode (VARCHAR)
     *  - minuteur (TIME ou VARCHAR)
     *  - id_createur (INT)
     *  - pseudo_createur (VARCHAR)
     *  - created_at (DATETIME)
     */
    public function creerPartie(
        string $nom,
        string $code,
        int $nbJoueur,
        string $mode,
        string $minuteur,
        int $idCreateur,
        string $pseudoCreateur
    ): int {
        $sql = "INSERT INTO partie 
                (nom, lien, nb_joueur, mode, minuteur, id_createur, pseudo_createur, created_at)
                VALUES 
                (:nom, :lien, :nb_joueur, :mode, :minuteur, :id_createur, :pseudo_createur, NOW())";

        $this->executeReq($sql, [
            'nom'            => $nom,
            'lien'           => $code,
            'nb_joueur'      => $nbJoueur,
            'mode'           => $mode,
            'minuteur'       => $minuteur,
            'id_createur'    => $idCreateur,
            'pseudo_createur'=> $pseudoCreateur
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Récupère une partie via son code (lien).
     */
    public function getPartieByCode(string $code): ?array
    {
        $sql  = "SELECT * FROM partie WHERE lien = :lien LIMIT 1";
        $stmt = $this->executeReq($sql, ['lien' => $code]);
        $res  = $stmt->fetch();

        return $res ?: null;
    }

    /**
     * (optionnel) Récupérer les parties d'un utilisateur.
     */
    public function getPartiesByCreateur(int $idCreateur): array
    {
        $sql  = "SELECT * FROM partie WHERE id_createur = :id ORDER BY created_at DESC";
        $stmt = $this->executeReq($sql, ['id' => $idCreateur]);
        return $stmt->fetchAll();
    }
}
