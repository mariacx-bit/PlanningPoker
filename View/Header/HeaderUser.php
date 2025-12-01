<?php
$pseudo = $_SESSION['pseudo'] ?? "Utilisateur";
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm border-bottom">
    <div class="container py-2">

        <!-- Logo -->
        <a class="navbar-brand fw-bold fs-4 text-primary" href="Index.php?page=Dashboard">
            🃏 Planning Poker
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navUser">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navUser">
            <ul class="navbar-nav ms-auto align-items-center">

                <li class="nav-item me-3">
                    <a class="nav-link fw-semibold" href="Index.php?page=Dashboard">Dashboard</a>
                </li>

                <li class="nav-item me-3">
                    <a class="btn btn-outline-primary" href="Index.php?page=Partie">
                        + Nouvelle partie
                    </a>
                </li>

                <!-- Profil dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle fw-bold text-dark" href="#" id="profilMenu"
                       role="button" data-bs-toggle="dropdown">
                        👤 <?= htmlspecialchars($pseudo) ?>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="Index.php?page=Profil">Mon profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="Index.php?page=Logout">Déconnexion</a></li>
                    </ul>
                </li>

            </ul>
        </div>

    </div>
</nav>
