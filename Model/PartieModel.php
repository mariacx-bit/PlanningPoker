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
        string $pseudoCreateur
    ) {
        $sql = "INSERT INTO partie 
                (nom, lien, nb_joueur, mode, minuteur, createur, id_createur)
                VALUES 
                (:nom, :lien, :nb_joueur, :mode, :minuteur, :createur, :id_createur)";

        $this->executeReq($sql, [
            "nom"         => $nom,
            "lien"        => $code,
            "nb_joueur"   => $nbJoueur,
            "mode"        => $mode,
            "minuteur"    => $minuteur,
            "createur"    => $pseudoCreateur,
            "id_createur" => $idCreateur
        ]);

        return $this->pdo->lastInsertId();
    }

    public function getPartiesByCreateur(int $idCreateur)
    {
        $stmt = $this->executeReq(
            "SELECT * FROM partie WHERE id_createur = :id ORDER BY id DESC",
            ["id" => $idCreateur]
        );
        return $stmt->fetchAll();
    }

    public function getPartieByCode(string $code)
    {
        $stmt = $this->executeReq("SELECT * FROM partie WHERE lien = :code", ["code" => $code]);
        return $stmt->fetch();
    }
}
