<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);


// Inclure le fichier des routes
include_once("Controller/Route.php");

// Définition par défaut des composants de la page
$header = "HeaderIndex";
$model = "ModelMain";
$controleur = "ControllerMain";
$vue = "IndexMain";
$js = "Main";
$footer = "Footer";

// Vérification de la route demandée
if (isset($_GET['page'])) {
    $page = htmlspecialchars($_GET['page']); // Sécurisation du paramètre 'page'
    if (isset($routes[$page]) && $routes[$page]['active'] === true) {
        $header = $routes[$page]['header'];
        $model = $routes[$page]['model'];
        $controleur = $routes[$page]['controleur'];
        $vue = $routes[$page]['vue'];
        $footer = $routes[$page]['footer'];
        $js = $routes[$page]['js'];
    } else {
        // Page non trouvée ou inactive
        http_response_code(404);
        echo "<h1>Erreur 404 : Page non trouvée</h1>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Location de voitures</title>

    <!-- Inclusion du fichier Head -->
    <?php
    if (file_exists("Controller/Head.php")) {
        include_once("Controller/Head.php");
    } else {
        echo "<!-- Fichier Head.php manquant -->";
    }
    ?>
</head>
<body>
<!-- Inclusion de l'en-tête -->
<?php
if ($header != null && file_exists("View/Header/" . $header . ".php")) {
    include_once("View/Header/" . $header . ".php");
} else {
    echo "<!-- Fichier d'en-tête manquant -->";
}
?>

<!-- Inclusion du contrôleur -->
<?php
if ($controleur != null && file_exists("Controller/" . $controleur . ".php")) {
    include_once("Controller/" . $controleur . ".php");
} else {
    echo "<!-- Contrôleur manquant -->";
}
?>

<!-- Inclusion du modèle -->
<?php
if ($model != null && file_exists("Model/" . $model . ".php")) {
    include_once("Model/" . $model . ".php");
} else {
    echo "<!-- Modèle manquant -->";
}
?>

<!-- Contenu principal -->
<?php
if ($vue != null && file_exists("View/Navigation/" . $vue . ".php")) {
    include_once("View/Navigation/" . $vue . ".php");
} else {
    echo "<!-- Fichier de vue manquant -->";
}
?>

<!-- Inclusion du fichier JS -->
<script src="View/assets/js/<?php echo htmlspecialchars($js); ?>.js"></script>
</body>

<!-- Inclusion du pied de page -->
<?php
if ($footer != null && file_exists("View/" . $footer . ".php")) {
    include_once("View/" . $footer . ".php");
} else {
    echo "<!-- Fichier Footer manquant -->";
}
?>
</html>
