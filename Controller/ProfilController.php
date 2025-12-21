<?php

require_once "Model/UserModel.php";

class ProfilController
{
    private UserModel $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    public function profil()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: Index.php?page=Connexion");
            exit;
        }

        $id = (int)$_SESSION['user_id'];
        $user = $this->model->getById($id);

        if (!$user) {
            session_destroy();
            header("Location: Index.php?page=Connexion");
            exit;
        }

        $success = null;
        $error = null;

        // === UPDATE PROFIL (pseudo + email)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {

            $pseudo = trim($_POST['pseudonyme'] ?? '');
            $email  = trim($_POST['email'] ?? '');

            if ($pseudo === '' || strlen($pseudo) < 2) {
                $error = "Pseudo invalide.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Email invalide.";
            } else {
                $this->model->updateProfile($id, $pseudo, $email);

                // MAJ session (important pour header + WS)
                $_SESSION['pseudo'] = $pseudo;
                $_SESSION['email']  = $email;

                $success = "Profil mis à jour.";
                $user = $this->model->getById($id);
            }
        }

        // === UPDATE PASSWORD
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_password') {

            $current = $_POST['current_password'] ?? '';
            $new1    = $_POST['new_password'] ?? '';
            $new2    = $_POST['new_password_confirm'] ?? '';

            if (!password_verify($current, $user['password'])) {
                $error = "Mot de passe actuel incorrect.";
            } elseif ($new1 === '' || strlen($new1) < 4) {
                $error = "Nouveau mot de passe trop court.";
            } elseif ($new1 !== $new2) {
                $error = "Les mots de passe ne correspondent pas.";
            } else {
                $hash = password_hash($new1, PASSWORD_DEFAULT);
                $this->model->updatePassword($id, $hash);
                $success = "Mot de passe modifié.";
            }
        }
        // sécurité : garantir que $user existe toujours pour la vue
        if (!$user) {
            $user = ['pseudonyme'=>'', 'email'=>''];
        }

        require "View/Navigation/Profil.php";
    }
}