<?php

require_once "Model/ModelMain.php";
require_once "Entities/Personne.php";

class UserModel extends ModelMain
{
    public function inserer(Personne $p): int
    {
        $sql = "INSERT INTO user (pseudonyme, email, password)
                VALUES (:pseudonyme, :email, :mdp)";

        $this->executeReq($sql, [
            "pseudonyme" => $p->getPseudonyme(),
            "email" => $p->getEmail(),
            "mdp"   => $p->getMdp()
        ]);

        return (int)$this->pdo->lastInsertId();
    }
}
