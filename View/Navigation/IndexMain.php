<main>

<div class="container">
    <div class="text-center py-5">
        <h1 class="display-4">Bienvenue sur Planning Poker</h1>
        <p class="lead mt-3">Estimez plus vite en équipe grâce aux cartes digitales.</p>
        <?php if (empty($_SESSION['user_id'])): ?>
                <a href="Index.php?page=Inscription" class="btn btn-primary btn-lg mt-3">Créer un compte</a>
                <a href="Index.php?page=Connexion" class="btn btn-outline-light btn-lg mt-3">Se connecter</a>
            <?php else: ?>
                <a href="Index.php?page=Dashboard" class="btn btn-primary btn-lg mt-3">Créer une patie</a>
            <?php endif; ?>
        
    </div>
</div>
    
</main>