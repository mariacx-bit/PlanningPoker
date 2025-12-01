<?php

require_once "Model/PartieModel.php";

class PartieController
{
    private PartieModel $partieModel;

    public function __construct()
    {
        $this->partieModel = new PartieModel();
    }

    public function partie()
    {
        // 1) CREATION DE PARTIE (host) : POST sans "join" et sans code dans l'URL
        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && empty($_GET['code'])
            && empty($_POST['join'])) {

            $nom       = trim($_POST['nom'] ?? 'Partie sans nom');
            $mode      = $_POST['mode'] ?? 'standard';
            $nbJoueur  = (int) ($_POST['nb_joueur'] ?? 10);
            $minuteur  = $_POST['minuteur'] ?? '00:00:00';

            // Générer un code unique
            $code = bin2hex(random_bytes(4));
            while ($this->partieModel->getPartieByCode($code) !== null) {
                $code = bin2hex(random_bytes(4));
            }

            // Créer la partie
            $idPartie = $this->partieModel->creerPartie(
                $nom,
                $code,
                $nbJoueur,
                $mode,
                $minuteur,
                $_SESSION['user_id'],      // ID du créateur
                $_SESSION['pseudo']        // Pseudonyme affiché
            );

            // Pseudo de l’animateur (tu peux ici mettre le login de l'user connecté)
            if (!isset($_SESSION['pseudo']) || $_SESSION['pseudo'] === '') {
                $_SESSION['pseudo'] = 'Host';
            }

            // Marquer cette session comme maître de cette partie
            if (!isset($_SESSION['est_maitre'])) {
                $_SESSION['est_maitre'] = [];
            }
            $_SESSION['est_maitre'][$code] = true;

            // Infos de partie
            $_SESSION['partie_id']   = $idPartie;
            $_SESSION['partie_code'] = $code;
            $_SESSION['partie_nom']  = $nom;

            // Redirection vers la partie (on peut aussi passer le pseudo dans l'URL si on veut)
            header("Location: Index.php?page=Partie&code=" . urlencode($code));
            exit;
        }

        // 2) REJOINDRE AVEC UN PSEUDO (invité) : POST avec "join"
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join'])) {

            $code   = $_GET['code'] ?? $_POST['code'] ?? null;
            $pseudo = trim($_POST['pseudo'] ?? '');

            if (!$code) {
                header("Location: Index.php?page=IndexMain");
                exit;
            }

            if ($pseudo === '') {
                $_SESSION['join_error'] = "Le pseudonyme est obligatoire.";
                header("Location: Index.php?page=Partie&code=" . urlencode($code));
                exit;
            }

            // On mémorise le pseudo dans la session
            $_SESSION['pseudo'] = $pseudo;

            // Cet utilisateur n’est pas maître pour cette partie
            if (!isset($_SESSION['est_maitre'])) {
                $_SESSION['est_maitre'] = [];
            }
            if (!isset($_SESSION['est_maitre'][$code])) {
                $_SESSION['est_maitre'][$code] = false;
            }

            // On repasse le pseudo aussi dans l'URL pour être sûr
            header("Location: Index.php?page=Partie&code=" . urlencode($code) . "&pseudo=" . urlencode($pseudo));
            exit;
        }

        // 3) AFFICHAGE (GET)

        $code = $_GET['code'] ?? null;

        // Pas de code → écran "Créer une partie"
        if (!$code) {
            return;
        }

        $partie = $this->partieModel->getPartieByCode($code);

        if (!$partie) {
            http_response_code(404);
            echo "<div class='container mt-5'>
                    <div class='alert alert-danger text-center'>
                        Partie introuvable pour le code <strong>" . htmlspecialchars($code) . "</strong>.
                    </div>
                  </div>";
            return;
        }

        $_SESSION['partie_id']   = $partie['id'];
        $_SESSION['partie_code'] = $partie['lien'];
        $_SESSION['partie_nom']  = $partie['nom'];
    }
}
