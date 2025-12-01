<?php
$code   = $_GET['code'] ?? null;
$nom    = $_SESSION['partie_nom'] ?? 'Nouvelle partie';

// On essaye d'abord de prendre le pseudo dans l'URL, sinon dans la session
$pseudo = $_GET['pseudo'] ?? ($_SESSION['pseudo'] ?? null);

$isHost = false;
if ($code && isset($_SESSION['est_maitre'][$code])) {
    $isHost = $_SESSION['est_maitre'][$code];
}

// est-ce qu'on doit demander un pseudo ?
$needPseudo = $code && ($pseudo === null || $pseudo === '');
?>

<?php if (!$code): ?>
    <!-- MODE CREATION DE PARTIE -->
    <div class="container mt-5">
        <h2>Créer une partie de Planning Poker</h2>

        <form action="Index.php?page=Partie" method="POST" class="mt-4">

            <div class="mb-3">
                <label class="form-label">Nom de la partie</label>
                <input type="text" name="nom" class="form-control" placeholder="Ex : Sprint 24" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Nombre max de joueurs</label>
                <input type="number" name="nb_joueur" class="form-control" value="10" min="2">
            </div>

            <div class="mb-3">
                <label class="form-label">Mode</label>
                <select name="mode" class="form-select">
                    <option value="standard">Standard</option>
                    <option value="fibonacci">Fibonacci</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Minuteur (optionnel)</label>
                <input type="time" name="minuteur" class="form-control" value="00:02:00">
            </div>

            <button type="submit" class="btn btn-primary">Créer la partie</button>
        </form>
    </div>

<?php else: ?>

    <?php
    $codeSafe   = htmlspecialchars($code);
    $pseudoSafe = htmlspecialchars($pseudo ?? '');
    ?>

    <?php if ($needPseudo): ?>
        <!-- FORMULAIRE POUR CHOISIR UN PSEUDO (INVITÉ) -->
        <div class="container mt-5">
            <h3>Rejoindre la partie #<?= $codeSafe ?></h3>

            <?php if (!empty($_SESSION['join_error'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($_SESSION['join_error']) ?>
                </div>
                <?php unset($_SESSION['join_error']); ?>
            <?php endif; ?>

            <form action="Index.php?page=Partie&code=<?= $codeSafe ?>" method="POST" class="mt-4">
                <input type="hidden" name="join" value="1">
                <input type="hidden" name="code" value="<?= $codeSafe ?>">

                <div class="mb-3">
                    <label class="form-label">Choisissez un pseudonyme</label>
                    <input type="text" name="pseudo" class="form-control" required placeholder="Ex : Mina, Bartosz...">
                </div>

                <button type="submit" class="btn btn-success">Rejoindre la partie</button>
            </form>
        </div>

    <?php else: ?>
        <!-- MODE PARTIE (PSEUDO OK) -->

        <div class="container-fluid mt-4">
            <div class="row">
                <!-- Colonne gauche : joueurs -->
                <div class="col-md-3 mb-3">
                    <h5>Joueurs</h5>
                    <ul id="liste-joueurs" class="list-group">
                        <!-- les joueurs connectés seront ajoutés ici en JS -->
                    </ul>
                </div>

                <!-- Colonne centrale : salle -->
                <div class="col-md-6 mb-3 text-center">
                    <h4><?= htmlspecialchars($nom) ?> <small class="text-muted">(#<?= $codeSafe ?>)</small></h4>

                    <p class="mt-3 mb-1">Lien à partager :</p>
                    <input type="text" class="form-control mb-4"
                           value="http://localhost/PlanningPoker/Index.php?page=Partie&code=<?= $codeSafe ?>" readonly>

                    <div class="card p-4 mb-4">
                        <h5 id="etat-partie" class="mb-3">En attente des votes...</h5>
                        <div id="votes-log" class="small text-muted text-start" style="max-height: 120px; overflow-y: auto;"></div>

                        <?php if ($isHost): ?>
                            <button class="btn btn-warning mt-3" onclick="revelerVotes()">
                                Révéler les votes
                            </button>
                        <?php else: ?>
                            <p class="text-muted mt-3">En attente que l'animateur révèle les votes...</p>
                        <?php endif; ?>
                    </div>

                    <h5>Choisissez votre carte</h5>
                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                        <?php foreach ([0,1,2,3,5,8,13,21,34,55,89] as $val): ?>
                            <button class="btn btn-outline-primary btn-lg" onclick="envoyerVote(<?= $val ?>)">
                                <?= $val ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Colonne droite : Tâches -->
                <div class="col-md-3 mb-3">
                    <?php if ($isHost): ?>
                        <h5>Tâches de la session</h5>
                        <div class="alert alert-info small mb-2">
                            Ici, toi (animateur) tu pourras gérer la liste des user stories à estimer.
                        </div>
                        <!-- TODO: liste des tâches pour le host -->
                    <?php else: ?>
                        <h5>Tâches</h5>
                        <div class="alert alert-secondary small">
                            Seul l'animateur de la partie peut gérer les tâches.<br>
                            Tu peux quand même voter sur celles qui sont en cours.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
            const codePartie = "<?= $codeSafe ?>";
            const pseudo     = "<?= $pseudoSafe ?>";
            const isHost     = <?= $isHost ? 'true' : 'false' ?>;

            const ws = new WebSocket("ws://localhost:8080?code=" + codePartie + "&pseudo=" + encodeURIComponent(pseudo));

            const listeJoueurs = document.getElementById('liste-joueurs');
            const votesLog     = document.getElementById('votes-log');
            const etatPartie   = document.getElementById('etat-partie');

            const joueurs = {}; // pseudo => { li, aVote }

            function logVote(msg) {
                votesLog.innerHTML += `<div>${msg}</div>`;
                votesLog.scrollTop = votesLog.scrollHeight;
            }

            function ajouterJoueur(pseudo) {
                if (joueurs[pseudo]) return;
                const li = document.createElement('li');
                li.className = "list-group-item d-flex justify-content-between align-items-center";
                li.innerHTML = `<span>${pseudo}</span><span class="badge bg-secondary vote-state">En attente</span>`;
                listeJoueurs.appendChild(li);
                joueurs[pseudo] = { li, aVote: false };
            }

            function majEtatVote(pseudo, aVote) {
                if (!joueurs[pseudo]) ajouterJoueur(pseudo);
                joueurs[pseudo].aVote = aVote;
                const badge = joueurs[pseudo].li.querySelector('.vote-state');
                if (aVote) {
                    badge.textContent = "A voté";
                    badge.className = "badge bg-success vote-state";
                } else {
                    badge.textContent = "En attente";
                    badge.className = "badge bg-secondary vote-state";
                }
            }

            ws.onopen = () => {
                logVote(`<strong>Connecté à la partie.</strong>`);
                ajouterJoueur(pseudo);
            };

            ws.onclose = () => {
                logVote(`<strong>Déconnecté du serveur.</strong>`);
            };

            ws.onerror = () => {
                logVote(`<span class="text-danger">Erreur WebSocket.</span>`);
            };

            ws.onmessage = event => {
                const data = JSON.parse(event.data);

                switch (data.type) {
                    case 'join':
                        ajouterJoueur(data.pseudo);
                        logVote(`<strong>${data.pseudo}</strong> a rejoint la partie.`);
                        break;

                    case 'leave':
                        logVote(`<strong>${data.pseudo}</strong> a quitté la partie.`);
                        break;

                    case 'vote':
                        majEtatVote(data.pseudo, true);
                        logVote(`<strong>${data.pseudo}</strong> a voté.`);
                        break;

                    case 'reveal':
                        etatPartie.textContent = "Votes révélés ! (affichage détaillé à implémenter)";
                        logVote(`<strong>Votes révélés !</strong>`);
                        break;
                }
            };

            function envoyerVote(valeur) {
                if (ws.readyState !== WebSocket.OPEN) return;
                ws.send(JSON.stringify({ type: "vote", valeur }));
                majEtatVote(pseudo, true);
                logVote(`Vous avez voté : <strong>${valeur}</strong>`);
            }

            function revelerVotes() {
                if (!isHost) return;
                if (ws.readyState !== WebSocket.OPEN) return;
                ws.send(JSON.stringify({ type: "reveal" }));
            }
        </script>

    <?php endif; ?>

<?php endif; ?>
