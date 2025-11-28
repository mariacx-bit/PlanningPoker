<h1>Connexion</h1>
<?php if (!empty($error)): ?>
    <p style="color: red;"><?php echo $error; ?></p>
<?php endif; ?>

<form action="Index.php?page=Connexion" method="POST">
    <label for="email">email :</label>
    <input type="text" name="email" id="email" required>
    <br>

    <label for="mdp">Mot de passe :</label>
    <input type="password" name="mdp" id="mdp" required>
    <br>

    <button type="submit">Se connecter</button>

    <p>Pas encore de compte ? <a href="Index.php?page=Inscription">Inscrivez-vous ici</a>.</p>

</form>

