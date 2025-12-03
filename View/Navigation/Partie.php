<?php
// Code de la partie (pour rejoindre / jouer)
$code   = $_GET['code'] ?? null;
$nom    = $_SESSION['partie_nom'] ?? 'Nouvelle partie';

// On essaye d'abord de prendre le pseudo dans l'URL, sinon dans la session
$pseudo = $_GET['pseudo'] ?? ($_SESSION['pseudo'] ?? null);

// Rôle : maître de la partie ou joueur
$isHost = false;
if ($code && isset($_SESSION['est_maitre'][$code])) {
    $isHost = $_SESSION['est_maitre'][$code];
}

// est-ce qu'on doit demander un pseudo ?
$needPseudo = $code && ($pseudo === null || $pseudo === '');

// Récupération de l'état de jeu (backlog / tâche en cours)
$gameState    = null;
$currentTask  = null;
$currentIndex = 0;
$modeJeu      = 'strict';

if ($code && isset($_SESSION['game'][$code])) {
    $gameState    = $_SESSION['game'][$code];
    $modeJeu      = $gameState['mode'] ?? 'strict';
    $currentIndex = $gameState['current_index'] ?? 0;
    if (isset($gameState['tasks'][$currentIndex])) {
        $currentTask = $gameState['tasks'][$currentIndex];
    }
}

$codeSafe   = htmlspecialchars($code ?? '');
$pseudoSafe = htmlspecialchars($pseudo ?? '');
?>

<div class="container mt-5">

    <?php if (!$code): ?>
        <!-- AUCUN CODE : PAGE DE CREATION / REJOINDRE -->

        <div class="row">
            <!-- Colonne création -->
            <div class="col-md-7 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h3 class="fw-bold mb-3">Créer une nouvelle partie 🃏</h3>
                        <p class="text-muted mb-4">
                            Configure une session de planning poker, colle ton backlog en JSON
                            et invite tes coéquipiers avec le code généré.
                        </p>

                        <?php if (!empty($_SESSION['create_error'])): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($_SESSION['create_error']) ?>
                            </div>
                            <?php unset($_SESSION['create_error']); ?>
                        <?php endif; ?>

                        <form action="Index.php?page=Partie" method="POST" class="mt-2">
                            <div class="mb-3">
                                <label class="form-label">Nom de la partie</label>
                                <input type="text" name="nom" class="form-control"
                                       placeholder="Ex : Sprint 24 – Backlog principal" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nombre maximum de joueurs</label>
                                <input type="number" name="nb_joueur" class="form-control" value="10" min="2">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mode de jeu</label>
                                <select name="mode" class="form-select">
                                    <option value="strict">Unanimité (strict)</option>
                                    <option value="moyenne">Moyenne</option>
                                </select>
                                <div class="form-text">
                                    Le premier tour de chaque tâche se joue toujours à l'unanimité.
                                    Les tours suivants utilisent le mode choisi.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Backlog (JSON)</label>
                                <textarea name="backlog_json" class="form-control" rows="8" placeholder='[
    { "title": "Écran de login", "description": "Formulaire d\'authentification" },
    { "title": "Dashboard", "description": "Liste des user stories" }
]'></textarea>
                                <div class="form-text">
                                    Colle ici la liste des fonctionnalités à estimer, au format JSON.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Minuteur par tour (optionnel)</label>
                                <input type="time" name="minuteur" class="form-control" value="00:02:00">
                            </div>

                            <button type="submit" class="btn btn-primary">
                                Créer la partie
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Colonne rejoindre -->
            <div class="col-md-5 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <h4 class="fw-semibold mb-3">Rejoindre une partie existante 👥</h4>
                        <p class="text-muted">
                            Entre le code de la partie donné par l'animateur pour la rejoindre.
                        </p>

                        <form method="GET" action="Index.php?page=Partie" class="mt-3">
                            <div class="mb-3">
                                <label class="form-label">Code de la partie</label>
                                <input type="text" name="code" class="form-control"
                                       placeholder="Ex : ABC123" required>
                            </div>

                            <button type="submit" class="btn btn-outline-primary w-100">
                                Continuer
                            </button>
                        </form>

                        <div class="mt-4 small text-muted">
                            Tu choisiras ton pseudo sur l'écran suivant.
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>

        <?php if ($needPseudo): ?>
            <!-- ON A UN CODE MAIS PAS ENCORE DE PSEUDO : FORMULAIRE DE PSEUDO -->

            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h3 class="fw-bold mb-3">Rejoindre la partie <?= $codeSafe ?> 🎯</h3>
                            <p class="text-muted">
                                Choisis un pseudonyme, il sera visible par les autres joueurs.
                            </p>

                            <?php if (!empty($_SESSION['join_error'])): ?>
                                <div class="alert alert-danger">
                                    <?= htmlspecialchars($_SESSION['join_error']) ?>
                                </div>
                                <?php unset($_SESSION['join_error']); ?>
                            <?php endif; ?>

                            <form method="POST" action="Index.php?page=Partie&code=<?= $codeSafe ?>">
                                <div class="mb-3">
                                    <label class="form-label">Pseudonyme</label>
                                    <input type="text" name="pseudo" class="form-control"
                                           placeholder="Ex : Alice, Bob..." required>
                                </div>

                                <button type="submit" name="join" value="1" class="btn btn-primary w-100">
                                    Rejoindre la partie
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- ECRAN PRINCIPAL DE LA PARTIE -->

            <div class="mb-4">
                <h2 class="fw-bold mb-1">
                    Partie <?= htmlspecialchars($nom) ?> <span class="text-muted fs-5">· Code : <?= $codeSafe ?></span>
                </h2>
                <p class="text-muted mb-0">
                    Connecté en tant que <strong><?= $pseudoSafe ?></strong>
                    <?= $isHost ? '· <span class="badge bg-primary">Animateur</span>' : '' ?>
                    · Mode : <strong><?= htmlspecialchars($modeJeu) ?></strong>
                </p>
            </div>

            <div class="row">
                <!-- Colonne gauche : joueurs -->
                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-3">
                            <h5 class="fw-semibold mb-3">Joueurs connectés</h5>
                            <ul class="list-group list-group-flush small" id="liste-joueurs">
                                <!-- Rempli dynamiquement par WebSocket -->
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Colonne centrale : cartes & état -->
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body p-3">
                            <h5 id="etat-partie" class="mb-2">En attente des votes...</h5>

                            <?php if ($currentTask): ?>
                                <p class="mb-1">
                                    <strong>Tâche actuelle :</strong>
                                    <?= htmlspecialchars($currentTask['title'] ?? '') ?>
                                </p>
                                <?php if (!empty($currentTask['description'])): ?>
                                    <p class="text-muted small mb-0">
                                        <?= nl2br(htmlspecialchars($currentTask['description'])) ?>
                                    </p>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="mb-1 text-muted small">
                                    Aucune tâche courante (backlog vide ou terminé).
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card pp-card">
                        <div class="card-body p-3">
                            <h5 class="fw-semibold mb-3">Choisis ta carte</h5>
                            <div class="d-flex flex-wrap gap-2 mb-3" id="cartes-container">
                                <?php
                                // Jeu de cartes de type Fibonacci + 'CAFÉ'
                                $cards = [0, 1, 2, 3, 5, 8, 13, 21, 34, 'CAFÉ'];
                                foreach ($cards as $c):
                                    $label = is_numeric($c) ? (string)$c : '☕ Café';
                                    $value = is_numeric($c) ? (string)$c : 'CAFÉ';
                                ?>
                                    <button type="button" class="pp-vote-card carte-vote" data-carte="<?= htmlspecialchars($value) ?>"> <?= htmlspecialchars($label) ?></button>
                                <?php endforeach; ?>
                            </div>

                            <?php if ($isHost): ?>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <small class="text-muted">
                                        Tu es l'animateur : tu peux lancer la révélation des votes
                                        une fois que tout le monde a choisi.
                                    </small>
                                    <?php
                                    $hasTasks = !empty($gameState['tasks'] ?? []);
                                    ?>
                                    <button
                                        type="button"
                                        id="btn-reveler"
                                        class="btn btn-sm pp-btn-reveal text-white"
                                        <?= $hasTasks ? '' : 'disabled' ?>>
                                        Révéler les votes
                                    </button>
                                </div>
                            <?php else: ?>
                                <small class="text-muted">
                                    En attente de la révélation des votes par l'animateur.
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Journal -->
                    <div class="card shadow-sm border-0 mt-3">
                        <div class="card-body p-3">
                            <h6 class="fw-semibold mb-2">Journal de la partie</h6>
                            <div id="journal" class="small" style="max-height: 200px; overflow-y: auto;">
                                <!-- messages ajoutés en JS -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Colonne droite : Tâches -->
                <div class="col-md-3 mb-3">
              <?php if ($isHost): ?>
                <div class="card shadow-sm border-0 h-100 pp-card">
                    <div class="card-body p-3">
                        <h5 class="fw-semibold mb-3">Backlog</h5>

                        <?php if (!empty($_SESSION['task_error'])): ?>
                            <div class="alert alert-danger small">
                                <?= htmlspecialchars($_SESSION['task_error']) ?>
                            </div>
                            <?php unset($_SESSION['task_error']); ?>
                        <?php endif; ?>

                        <?php $tasks = $gameState['tasks'] ?? []; ?>

                        <?php if (!empty($tasks)): ?>
                            <ul class="list-group small mb-3">
                                <?php foreach ($tasks as $i => $t):
                                    $title    = htmlspecialchars($t['title'] ?? ('Tâche ' . ($i + 1)));
                                    $status   = $t['status'] ?? 'pending';
                                    $estimate = $t['estimate'] ?? null;
                                    $isCurrent = ($i === $currentIndex);
                                ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center
                                        <?= $isCurrent ? 'list-group-item-primary' : '' ?>">
                                        <span><?= $title ?></span>
                                        <span class="badge bg-<?= $status === 'validated' ? 'success' : 'secondary' ?>">
                                            <?php if ($status === 'validated'): ?>
                                                <?= $estimate !== null ? htmlspecialchars((string)$estimate) : 'OK' ?>
                                            <?php else: ?>
                                                En attente
                                            <?php endif; ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="alert alert-info small mb-3">
                                Aucune tâche définie pour l'instant. Ajoute des user stories ci-dessous.
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="Index.php?page=Partie&code=<?= $codeSafe ?>" class="small">
                            <input type="hidden" name="action" value="addTask">
                            <div class="mb-2">
                                <label class="form-label">Nouvelle tâche</label>
                                <input type="text" name="task_title" class="form-control form-control-sm"
                                    placeholder="Ex : Écran d'authentification" required>
                            </div>
                            <div class="mb-2">
                                <textarea name="task_description" class="form-control form-control-sm"
                                        rows="2" placeholder="Description (optionnel)"></textarea>
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                Ajouter la tâche
                            </button>
                        </form>
                    </div>
                </div>
                    <?php else: ?>
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body p-3">
                                <h5 class="fw-semibold mb-3">Tâches</h5>
                                <?php if ($currentTask): ?>
                                    <p class="small mb-1">
                                        <strong>Tâche en cours :</strong>
                                        <?= htmlspecialchars($currentTask['title'] ?? '') ?>
                                    </p>
                                    <?php if (!empty($currentTask['description'])): ?>
                                        <p class="small text-muted">
                                            <?= nl2br(htmlspecialchars($currentTask['description'])) ?>
                                        </p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="small text-muted mb-0">
                                        Aucune tâche en cours. L'animateur contrôle le backlog.
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SCRIPT DE JEU -->
            <script>
                (function () {
                    const codePartie = "<?= $codeSafe ?>";
                    const pseudo     = "<?= $pseudoSafe ?>";
                    const isHost     = <?= $isHost ? 'true' : 'false' ?>;

                    const etatPartie   = document.getElementById("etat-partie");
                    const journal      = document.getElementById("journal");
                    const joueursListe = document.getElementById("liste-joueurs");
                    const btnReveler   = document.getElementById("btn-reveler");
                    const cartes       = document.querySelectorAll(".carte-vote");

                    const joueurs      = {};   // pseudo => { li, aVote }
                    const votesCourants = {};  // pseudo => valeur

                    let ws = null;

                    function log(msgHtml) {
                        if (!journal) return;
                        const p = document.createElement("p");
                        p.innerHTML = msgHtml;
                        journal.appendChild(p);
                        journal.scrollTop = journal.scrollHeight;
                    }

                    function majEtatVote(pseudoJoueur, aVote) {
                        const j = joueurs[pseudoJoueur];
                        if (!j) return;
                        j.aVote = aVote;
                        const badge = j.li.querySelector("span.badge");
                        if (aVote) {
                            j.li.classList.add("list-group-item-success");
                            if (badge) {
                                badge.textContent = "A voté";
                                badge.classList.remove("bg-secondary");
                                badge.classList.add("bg-success");
                            }
                        } else {
                            j.li.classList.remove("list-group-item-success");
                            if (badge) {
                                badge.textContent = "En attente";
                                badge.classList.remove("bg-success");
                                badge.classList.add("bg-secondary");
                            }
                        }
                    }

                    function ajouterJoueur(pseudoJoueur) {
                        if (joueurs[pseudoJoueur]) return;
                        const li = document.createElement("li");
                        li.className = "list-group-item pp-player-pill";
                        li.innerHTML = `
                            <span>${pseudoJoueur}</span>
                            <span class="badge bg-secondary">En attente</span>
                        `;
                        joueursListe.appendChild(li);
                        joueurs[pseudoJoueur] = { li, aVote: false };
                    }

                    function enleverJoueur(pseudoJoueur) {
                        const j = joueurs[pseudoJoueur];
                        if (!j) return;
                        j.li.remove();
                        delete joueurs[pseudoJoueur];
                        delete votesCourants[pseudoJoueur];
                    }

                    // Connexion WebSocket (si serveur en place)
                    function initWebSocket() {
                        try {
                            const wsHost = window.location.hostname || "localhost";
                            ws = new WebSocket("ws://" + wsHost + ":8080?code=" + encodeURIComponent(codePartie) + "&pseudo=" + encodeURIComponent(pseudo));

                        } catch (e) {
                            console.warn("WebSocket non disponible", e);
                            log("<span class='text-danger'>Impossible de se connecter au serveur temps réel.</span>");
                            return;
                        }

                        ws.onopen = function () {
                            log("Connecté au serveur temps réel.");
                        };

                        ws.onmessage = function (event) {
                            let data;
                            try {
                                data = JSON.parse(event.data);
                            } catch (e) {
                                return;
                            }

                            switch (data.type) {
                                case "join":
                                    ajouterJoueur(data.pseudo);
                                    log(`<strong>${data.pseudo}</strong> a rejoint la partie.`);
                                    break;

                                case "leave":
                                    enleverJoueur(data.pseudo);
                                    log(`<strong>${data.pseudo}</strong> a quitté la partie.`);
                                    break;

                                case "vote":
                                    votesCourants[data.pseudo] = data.valeur;
                                    majEtatVote(data.pseudo, true);
                                    log(`<strong>${data.pseudo}</strong> a voté.`);
                                    break;

                                case "reveal":
                                    log("L'animateur a révélé les votes (mise à jour en cours côté serveur).");
                                    break;
                            }

                        };

                        ws.onclose = function () {
                            log("<span class='text-muted'>Connexion temps réel fermée.</span>");
                        };

                        ws.onerror = function () {
                            log("<span class='text-danger'>Erreur sur la connexion temps réel.</span>");
                        };
                    }

                    // Envoi d'un vote
                   function envoyerVote(valeur) {
                        votesCourants[pseudo] = valeur;
                        majEtatVote(pseudo, true);
                        log(`Vous avez voté : <strong>${valeur}</strong>`);

                        // highlight bouton sélectionné
                        document.querySelectorAll(".carte-vote").forEach(btn => {
                            btn.classList.toggle("pp-selected", btn.getAttribute("data-carte") === valeur);
                        });

                        if (ws && ws.readyState === WebSocket.OPEN) {
                            ws.send(JSON.stringify({ type: "vote", valeur }));
                        }
                    }
                


                    // Révélation des votes → appel AJAX vers le contrôleur PHP
                    async function revelerVotes() {
                        if (!isHost) return;

                        const votes = Object.entries(votesCourants).map(([p, v]) => ({
                            pseudo: p,
                            valeur: v
                        }));

                        if (votes.length === 0) {
                            log("<span class='text-warning'>Aucun vote à révéler.</span>");
                            return;
                        }

                        try {
                            const resp = await fetch(
                                `Index.php?page=Partie&code=${encodeURIComponent(codePartie)}&action=resolve`,
                                {
                                    method: "POST",
                                    headers: { "Content-Type": "application/json" },
                                    body: JSON.stringify({ votes })
                                }
                            );

                            const result = await resp.json();

                            if (!result.ok) {
                                log(`<span class='text-danger'>Erreur serveur : ${result.error}</span>`);
                                return;
                            }

                            switch (result.status) {
                                case "pause":
                                    etatPartie.textContent = "Pause café ☕ – l'état de la partie a été sauvegardé.";
                                    log("<strong>Pause café :</strong> vous pouvez télécharger le JSON.");
                                    if (result.exportUrl) {
                                        window.location.href = result.exportUrl;
                                    }
                                    break;

                                case "validated":
                                    const t = result.task;
                                    etatPartie.textContent =
                                        `Tâche validée : "${t.title}" → estimation ${result.estimate}`;
                                    log(`Tâche <strong>${t.title}</strong> validée avec estimation <strong>${result.estimate}</strong>.`);

                                    if (result.finished && result.exportUrl) {
                                        log("<strong>Backlog terminé.</strong> Téléchargement du récap JSON...");
                                        window.location.href = result.exportUrl;
                                    }

                                    // Réinitialiser l'état de vote
                                    for (const p in votesCourants) {
                                        delete votesCourants[p];
                                        majEtatVote(p, false);
                                    }
                                    break;

                                case "continue":
                                    etatPartie.textContent = "Pas d'unanimité, nouveau tour de vote.";
                                    log(`<strong>Nouveau tour :</strong> ${result.reason}`);

                                    for (const p in votesCourants) {
                                        delete votesCourants[p];
                                        majEtatVote(p, false);
                                    }
                                    break;
                            }

                            // Notifier les autres clients via WebSocket
                            if (ws && ws.readyState === WebSocket.OPEN) {
                                ws.send(JSON.stringify({ type: "reveal" }));
                            }

                        } catch (e) {
                            console.error(e);
                            log("<span class='text-danger'>Erreur réseau lors de la révélation des votes.</span>");
                        }
                    }

                    // Listeners cartes
                    cartes.forEach(btn => {
                        btn.addEventListener("click", () => {
                            const valeur = btn.getAttribute("data-carte");
                            envoyerVote(valeur);
                        });
                    });

                    if (btnReveler) {
                        btnReveler.addEventListener("click", revelerVotes);
                    }

                    // Démarrage
                    initWebSocket();
                    ajouterJoueur(pseudo);
                })();
            </script>

        <?php endif; ?>

    <?php endif; ?>

</div>
