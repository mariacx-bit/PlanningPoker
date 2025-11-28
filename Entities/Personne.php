<?php

class Personne
{
    private ?int $id;
    private string $pseudonyme;
    private string $email;
    private string $tel;
    private string $mdp; // hashé

    public function __construct(
        ?int $id,
        string $pseudonyme,
        string $email,
        string $mdp,
        string $tel = '',
        bool $mdpDejaHash = false
    ) {
        $this->id         = $id;
        $this->pseudonyme = $pseudonyme;
        $this->email      = $email;
        $this->tel        = $tel;

        if ($mdpDejaHash) {
            $this->mdp = $mdp;
        } else {
            $this->setMdp($mdp);
        }
    }

    public function getId(): ?int          { return $this->id; }
    public function getPseudonyme(): string{ return $this->pseudonyme; }
    public function getEmail(): string     { return $this->email; }

    public function setTel(string $tel): void { $this->tel = $tel; }
    public function getTel(): string          { return $this->tel; }

    public function setMdp(string $mdp): void
    {
        if (strlen($mdp) < 4) {
            throw new InvalidArgumentException("Mot de passe trop court");
        }
        $this->mdp = password_hash($mdp, PASSWORD_DEFAULT);
    }

    public function getMdp(): string { return $this->mdp; }

    public function verifyPassword(string $pwd): bool
    {
        return password_verify($pwd, $this->mdp);
    }
}
