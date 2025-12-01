<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
    <?php
    $pseudo = $_SESSION['pseudo'] ?? "Utilisateur";
    ?>
    <h2 class="fw-bold">Bienvenue, <?= htmlspecialchars($pseudo) ?> 👋</h2>

        <a href="Index.php?page=Partie" class="btn btn-primary btn-lg shadow-sm">
            + Créer une nouvelle partie
        </a>
    </div>

    <div class="card shadow-sm border-0 p-4">
        <h4 class="mb-3 fw-semibold">Vos parties</h4>

        <?php if (empty($parties)): ?>

            <div class="alert alert-info text-center">
                Vous n'avez créé aucune partie pour le moment.
            </div>

        <?php else: ?>

        <div class="list-group">

            <?php foreach ($parties as $p): ?>
                <?php
                    $nom  = htmlspecialchars($p['nom']);
                    $code = htmlspecialchars($p['lien']);
                    $mode = htmlspecialchars($p['mode']);
                ?>

                <a href="Index.php?page=Partie&code=<?= $code ?>" 
                   class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center">

                    <div>
                        <div class="fw-bold"><?= $nom ?></div>
                        <small class="text-muted">Code : <?= $code ?> · Mode : <?= $mode ?></small>
                    </div>

                    <button class="btn btn-outline-primary">Ouvrir</button>
                </a>

            <?php endforeach; ?>

        </div>

        <?php endif; ?>
    </div>
</div>
