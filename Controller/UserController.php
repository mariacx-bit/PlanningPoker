<?php

require_once "Model/UserModel.php";
require_once "Entities/Personne.php";

class UserController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function inscription()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pseudonyme = $_POST['pseudonyme'] ?? '';
            $email = $_POST['email'] ?? '';
            $mdp   = $_POST['mdp'] ?? '';

            try {
                $personne = new Personne(
                    null,
                    $pseudonyme,
                    $email,
                    $mdp
                );

                $id = $this->userModel->inserer($personne);

                echo "Inscription réussie ! ID = $id";

            } catch (Exception $e) {
                echo "Erreur : " . $e->getMessage();
            }
        } else {
            require "View/Navigation/Inscription.php";
        }
    }
    public function connexion()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $mdp   = $_POST['mdp'] ?? '';

            // Ici, vous devriez vérifier les informations d'identification
            // contre la base de données. Ceci est un exemple simplifié.
            $sql = "SELECT * FROM user WHERE email = :email";
            $stmt = $this->userModel->executeReq($sql, ['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($mdp, $user['password'])) {
                // Connexion réussie
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['pseudo']  = $user['pseudonyme']; 
                $_SESSION['email']   = $user['email'];
                header("Location: Index.php?page=Dashboard");
                exit();
            } else {
                // Échec de la connexion
                $error = "Email ou mot de passe incorrect.";
                require "View/Navigation/Connexion.php";
            }
        } else {
            require "View/Navigation/Connexion.php";
        }
    }
}
