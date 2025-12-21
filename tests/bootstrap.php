<?php
declare(strict_types=1);

// Bootstrap PHPUnit (pas d'autoload PSR-4 chez toi, donc require direct)
require_once __DIR__ . '/../Entities/GameRules.php';
require_once __DIR__ . '/../Entities/GameState.php';
require_once __DIR__ . '/../Entities/Personne.php';

// Si tu as besoin de session dans certains tests (pas ici), tu pourrais:
// if (session_status() === PHP_SESSION_NONE) session_start();
