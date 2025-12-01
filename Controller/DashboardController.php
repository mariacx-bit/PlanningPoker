<?php

require_once "Model/PartieModel.php";

class DashboardController
{
    private PartieModel $partieModel;

    public function __construct()
    {
        $this->partieModel = new PartieModel();
    }

    public function dashboard()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: Index.php?page=Connexion");
            exit;
        }

        $idUser = $_SESSION['user_id'];

        // Récupérer toutes les parties du créateur
        $parties = $this->partieModel->getPartiesByCreateur($idUser);

        require "View/Navigation/Dashboard.php";
    }
}
