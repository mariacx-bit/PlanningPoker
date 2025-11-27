<?php
$routes = array(
    // Main
    'IndexMain' => array(
        'nom' => 'IndexMain',
        'header' => 'HeaderIndex',
        'controleur' => 'ControleurMain',
        'model' => 'ModelMain',
        'vue' => 'IndexMain',
        'js' => 'Main',
        'footer' => null,
        'visible' => true,
        'active' => true
    ),

    'Inscription' => array(
        'nom' => 'Inscription',
        'header' => 'HeaderIndex',
        'controleur' => 'UserController',
        'model' => 'UserModel',
        'vue' => 'Inscription',
        'js' => 'Main',
        'footer' => null,
        'visible' => true,
        'active' => true
    ),

    // Page de connexion
    'Connexion' => [
        'nom' => 'Connexion',
        'header' => 'HeaderIndex',
        'controleur' => 'UserController',
        'model' => 'UserModel',
        'vue' => 'Connexion',
        'js' => 'Main',
        'footer' => null,
        'visible' => true,
        'active' => true
    ],
    // Page de déconnexion
    'Logout' => [
        'nom' => 'logout',
        'header' => 'HeaderIndex',

        'controleur' => 'UserController',
        'model' => 'UserModel',
        'vue' => 'Logout',
        'js' => 'Main',
        'footer' => null,
        'visible' => true,
        'active' => true
    ],



       // Page de déconnexion
    'IndexClient' => [
        'nom' => 'IndexClient',
        'header' => 'HeaderIndex',
        'controleur' => 'UserController',
        'model' => 'UserModel',
        'vue' => 'Logout',
        'js' => 'Main',
        'footer' => null,
        'visible' => true,
        'active' => true
    ],



);
?>
