<?php

class Personne{
    private $id_personne;
    private $civilite;
    private $prenom;
    private $nom;
    private $login;
    private $email;
    private $role;
    private $date_inscription;
    private $tel;
    private $mdp;


public function __construct($id_personne, $civilite, $prenom,$nom, $login, $email, $role, $date_inscription, $tel, $mdp){
    $this->id_personne = $id_personne;
    $this->civilite = $civilite;
    $this->prenom = $prenom;
    $this->nom = $nom;
    $this->login = $login;
    $this->email = $email;
    $this->role = $role;
    $this->date_inscription = $date_inscription;
    $this->tel = $tel;
    $this->mdp = $mdp;
}
    public function setIdPersonne( $idPersonne): void
    {
        if ($idPersonne <= 0) {
            throw new InvalidArgumentException("L'ID doit être un entier positif.");
        }
        $this->idPersonne = $idPersonne;
    }

    public function setNom( $nom): void
    {
        $this->nom = $nom;
    }

    public function setPrenom( $prenom): void
    {
        $this->prenom = $prenom;
    }

    public function setLogin($login): void
    {
        $this->login = $login;
    }

    public function setEmail($email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("L'email doit être valide.");
        }
        $this->email = $email;
    }

    public function setMdp($mdp): void
    {
        if (strlen($mdp) < 4) {
            throw new InvalidArgumentException("Le mot de passe doit comporter au moins 4 caractères.");
        }
        if (preg_match('/\s/', $mdp)) {
            throw new InvalidArgumentException("Le mot de passe ne doit pas contenir d'espaces.");
        }
        $this->mdp = password_hash($mdp, PASSWORD_DEFAULT);
    }


    public function setTel( $tel): void
    {
        if (!preg_match("/^[0-9]{10}$/", $tel)) {
            throw new InvalidArgumentException("Le numéro de téléphone doit comporter 10 chiffres.");
        }
        $this->tel = $tel;
    }

    public function getIdPersonne()
    {
        return $this->idPersonne;
    }

    public function getCivilite()
    {
        return $this->civilite;
    }

    public function getPrenom()
    {
        return $this->prenom;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function getDateInscription()
    {
        return $this->dateInscription;
    }

    public function getTel()
    {
        return $this->tel;
    }

    public function verifyPassword($password)
    {
        return password_verify($password, $this->mdp);
    }
}