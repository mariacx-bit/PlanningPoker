<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once "Controller/Route.php";

// Page demandée dans l'URL, ou page d'accueil par défaut
$page = $_GET['page'] ?? 'IndexMain';

// Sécurité : si la route n'existe pas ou n'est pas active → on revient à IndexMain
if (!isset($routes[$page]) || !$routes[$page]['active']) {
    $page = 'IndexMain';
}

$route = $routes[$page];

// 1) HEADER
if (!empty($route['header'])) {
    require_once "View/header/" . $route['header'] . ".php";
}

// 2) MODEL
$model = null;
if (!empty($route['model'])) {
    require_once "Model/" . $route['model'] . ".php";
    $modelClass = $route['model'];
    $model = new $modelClass();   // ✅ on instancie, mais on NE L'AFFICHE PAS
}

// 3) CONTROLEUR
$controller = null;
if (!empty($route['controleur'])) {
    require_once "Controller/" . $route['controleur'] . ".php";
    $controllerClass = $route['controleur'];
    $controller = new $controllerClass();  // ✅ pareil, pas de echo

    // On essaie d'appeler une méthode du contrôleur correspondant au nom de la route
    // Exemple : route 'Inscription' → méthode inscription() dans UserController
    $action = strtolower($route['nom']);   // "Inscription" → "inscription"

    if (method_exists($controller, $action)) {
        // On peut lui passer le modèle si tu le veux, mais ce n'est pas obligatoire :
        // $controller->$action($model);
        $controller->$action();
    }
}

// 4) VUE
if (!empty($route['vue'])) {
    require_once "View/" . $route['vue'] . ".php";
}

// 5) FOOTER
if (!empty($route['footer'])) {
    require_once "View/" . $route['footer'] . ".php";
}
