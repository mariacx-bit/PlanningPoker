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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Planning Poker</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- TON CSS GLOBAL -->
    <link rel="stylesheet" href="View/assets/css/style.css">

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

<main class="container-fluid">

<?php
// 2) MODEL
$model = null;
if (!empty($route['model'])) {
    require_once "Model/" . $route['model'] . ".php";
    $modelClass = $route['model'];      // ➜ on récupère le nom de classe
    $model = new $modelClass();         // ➜ puis on instancie
}

// 3) CONTROLEUR
$controller = null;
if (!empty($route['controleur'])) {
    require_once "Controller/" . $route['controleur'] . ".php";
    $controllerClass = $route['controleur'];   // ➜ idem pour le contrôleur
    $controller = new $controllerClass();

    $action = strtolower($route['nom']);

    if (method_exists($controller, $action)) {
        $controller->$action();
    }
}


// 4) VUE
if (!empty($route['vue'])) {
    require_once "View/" . $route['vue'] . ".php";
}
?>

</main>

<?php
// 5) FOOTER
if (!empty($route['footer'])) {
    require_once "View/" . $route['footer'] . ".php";
}
?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
