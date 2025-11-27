<?php

include_once("Model/UserModel.php");
include_once("Entities/Personne.php"); // Inclure la classe Personne

class UserController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // Méthode pour gérer l'inscription
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo "Donnée reçue : " . $_POST['test'];
            exit();
        }
    }

}
