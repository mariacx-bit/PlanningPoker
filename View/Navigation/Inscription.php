<h1>Inscription</h1>
<form action="Index.php?page=Inscription" method="POST">
    <label for="civilite">Civilité :</label>
    <select name="civilite" id="civilite" required>
        <option value="M.">M.</option>
        <option value="Mme">Mme</option>
    </select>
    <br>

    <label for="prenom">Prénom :</label>
    <input type="text" name="prenom" id="prenom" required>
    <br>

    <label for="nom">Nom :</label>
    <input type="text" name="nom" id="nom" required>
    <br>

    <label for="login">Login :</label>
    <input type="text" name="login" id="login" required>
    <br>

    <label for="email">Email :</label>
    <input type="email" name="email" id="email" required>
    <br>

    <label for="tel">Téléphone :</label>
    <input type="text" name="tel" id="tel" required>
    <br>

    <label for="mdp">Mot de passe :</label>
    <input type="password" name="mdp" id="mdp" required>
    <br>

    <button type="submit">S'inscrire</button>

    <p>Déjà un compte ? <a href="Index.php?page=Connexion">Connectez-vous ici</a>.</p>
</form>
