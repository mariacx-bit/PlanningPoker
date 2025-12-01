<?php
$routes = array(
    'IndexMain' => array(
        'nom'        => 'IndexMain',
        'header'     => 'HeaderIndex',
        'controleur' => 'ControllerMain',        
        'model'      => 'ModelMain',
        'vue'        => 'Navigation/IndexMain', 
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
        'vue'        => 'Navigation/Inscription', 
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

    'Partie' => [
        'nom'        => 'Partie',
        'header'     => 'HeaderIndex',
        'controleur' => 'PartieController',
        'model'      => 'PartieModel',
        'vue'        => 'Navigation/Partie',  
        'js'         => 'Main',
        'footer'     => 'Footer',
        'visible'    => true,
        'active'     => true
    ],

    'Dashboard' => [
        'nom'        => 'Dashboard',
        'header'     => 'HeaderUser',
        'controleur' => 'DashboardController',
        'model'      => 'PartieModel',
        'vue'        => 'Navigation/Dashboard',
        'js'         => null,
        'footer'     => null,
        'visible'    => true,
        'active'     => true
    ],

);
