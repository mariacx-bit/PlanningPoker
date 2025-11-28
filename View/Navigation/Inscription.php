<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">

            <div class="card shadow">
                <div class="card-body">

                    <h3 class="text-center mb-4">Inscription</h3>

                    <form action="Index.php?page=Inscription" method="POST">

                        <div class="mb-3">
                            <label for="pseudonyme" class="form-label">pseudonyme</label>
                            <input type="text" class="form-control" name="pseudonyme" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="mdp" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" name="mdp" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            S'inscrire
                        </button>

                        <p class="text-center mt-3">
                            Déjà un compte ?
                            <a href="Index.php?page=Connexion">Connexion</a>
                        </p>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
