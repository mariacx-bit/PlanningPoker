<?php
$routes = array(
    // Main
    'IndexMain' => array(
        'nom'        => 'IndexMain',
        'header'     => 'HeaderIndex',
        'controleur' => 'ControllerMain',         // ⬅️ nom du fichier + classe
        'model'      => 'ModelMain',
        'vue'        => 'Navigation/IndexMain',   // ⬅️ dossier Navigation
        'js'         => 'Main',
        'footer'     => null,
        'visible'    => true,
        'active'     => true
    ),

    'Inscription' => array(
        'nom'        => 'Inscription',
        'header'     => 'HeaderIndex',
        'controleur' => 'UserController',
        'model'      => 'UserModel',
        'vue'        => 'Navigation/Inscription', // ⬅️ dans View/Navigation
        'js'         => 'Main',
        'footer'     => null,
        'visible'    => true,
        'active'     => true
    ),

    'Connexion' => [
        'nom'        => 'Connexion',
        'header'     => 'HeaderIndex',
        'controleur' => 'UserController',
        'model'      => 'UserModel',
        'vue'        => 'Navigation/Connexion',
        'js'         => 'Main',
        'footer'     => null,
        'visible'    => true,
        'active'     => true
    ],

    'Logout' => [
        'nom'        => 'Logout',
        'header'     => 'HeaderIndex',
        'controleur' => 'UserController',
        'model'      => 'UserModel',
        'vue'        => 'Navigation/Logout',
        'js'         => 'Main',
        'footer'     => null,
        'visible'    => true,
        'active'     => true
    ],

    'IndexClient' => [
        'nom'        => 'IndexClient',
        'header'     => 'HeaderIndex',
        'controleur' => 'UserController',
        'model'      => 'UserModel',
        'vue'        => 'Navigation/IndexClient',
        'js'         => 'Main',
        'footer'     => null,
        'visible'    => true,
        'active'     => true
    ],
);
