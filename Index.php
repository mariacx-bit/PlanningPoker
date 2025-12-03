<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "Controller/Route.php";

$page = $_GET['page'] ?? 'IndexMain';

if (!isset($routes[$page]) || !$routes[$page]['active']) {
    $page = 'IndexMain';
}

$route = $routes[$page];

/**
 * ➊ INTERCEPTION DES APPELS AJAX JSON
 *    (avant d'envoyer le moindre HTML)
 */
$actionParam = $_GET['action'] ?? null;

if ($page === 'Partie' && in_array($actionParam, ['resolve', 'export'], true)) {
    require_once "Controller/PartieController.php";
    $controller = new PartieController();
    // la méthode partie() s'occupe déjà de router vers resolve/export
    $controller->partie();
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<?php require_once "Controller/Head.php"; ?>

</head>
<body>

<?php
// 1) HEADER
if (!empty($route['header'])) {

    if (isset($_SESSION['user_id'])) {
        require "View/Header/HeaderUser.php";
    } else {
        require "View/Header/HeaderIndex.php";
    }

}
?>
<main class="py-4">
<?php
// ROUTAGE NORMAL (HTML)
$controller = null;
if (!empty($route['controleur'])) {
    require_once "Controller/" . $route['controleur'] . ".php";
    $controllerClass = $route['controleur'];
    $controller = new $controllerClass();
}

$action = strtolower($route['nom']);

if ($controller && method_exists($controller, $action)) {
    $controller->$action();
} else {
    if (!empty($route['vue'])) {
        require "View/" . $route['vue'] . ".php";
    } else {
        echo "<div class='container mt-5'><div class='alert alert-danger'>Vue non définie.</div></div>";
    }
}
?>
</main>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
