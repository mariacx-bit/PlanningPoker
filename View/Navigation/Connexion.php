<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">

            <div class="card shadow">
                <div class="card-body">

                    <h3 class="text-center mb-4">Connexion</h3>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger text-center">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form action="Index.php?page=Connexion" method="POST">

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input
                                type="email"
                                class="form-control"
                                name="email"
                                id="email"
                                required
                                autofocus
                            >
                        </div>

                        <div class="mb-3">
                            <label for="mdp" class="form-label">Mot de passe</label>
                            <input
                                type="password"
                                class="form-control"
                                name="mdp"
                                id="mdp"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Se connecter
                        </button>

                        <p class="text-center mt-3 mb-0">
                            Pas encore de compte ?
                            <a href="Index.php?page=Inscription">S'inscrire</a>
                        </p>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
