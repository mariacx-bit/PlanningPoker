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
            "email"      => $p->getEmail(),
            "mdp"        => $p->getMdp()
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /** ===== PROFIL ===== */

    public function getById(int $id): ?array
    {
        $sql = "SELECT id, pseudonyme, email, password
                FROM user
                WHERE id = :id
                LIMIT 1";
        $stmt = $this->executeReq($sql, ['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function updateProfile(int $id, string $pseudo, string $email): void
    {
        $sql = "UPDATE user
                SET pseudonyme = :p, email = :e
                WHERE id = :id";
        $this->executeReq($sql, [
            'p'  => $pseudo,
            'e'  => $email,
            'id' => $id
        ]);
    }

    public function updatePassword(int $id, string $hash): void
    {
        $sql = "UPDATE user SET password = :p WHERE id = :id";
        $this->executeReq($sql, [
            'p'  => $hash,
            'id' => $id
        ]);
    }
}