<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$codePartie    = $codePartie ?? ($_GET['code'] ?? '');
$pseudo        = $pseudo ?? ($_SESSION['pseudo'] ?? 'Anonyme');
$mode          = $mode ?? 'strict';
$tour          = $tour ?? 1;

$tasks         = $tasks ?? [];
$currentIndex  = $currentIndex ?? 0;
$tacheCourante = $tacheCourante ?? ($tasks[$currentIndex] ?? null);

$isAnimateur   = $isAnimateur ?? false;

$codeEsc   = htmlspecialchars($codePartie, ENT_QUOTES, 'UTF-8');
$pseudoEsc = htmlspecialchars($pseudo, ENT_QUOTES, 'UTF-8');
$modeEsc   = htmlspecialchars($mode, ENT_QUOTES, 'UTF-8');
?>

<nav class="navbar sticky-top border-bottom pp-topbar">
  <div class="container-fluid px-3">
    <span class="navbar-brand fw-bold">Planning Poker</span>
    <div class="mt-2 d-flex justify-content-center gap-2 align-items-center">
        <span class="badge text-bg-light border">Temps restant</span>
        <span class="badge text-bg-primary" id="timerBadge">--:--</span>
    </div>


    <div class="d-flex flex-wrap gap-2 align-items-center">
      <span class="badge text-bg-light border">Pseudo: <?= $pseudoEsc ?></span>
      <?php if ($isAnimateur): ?><span class="badge text-bg-dark">Animateur</span><?php endif; ?>
      <span class="badge text-bg-light border">Mode: <?= $modeEsc ?></span>
      <span class="badge text-bg-light border">Tour: <?= (int)$tour ?></span>
      <span class="badge text-bg-primary">Code: <?= $codeEsc ?></span>

      <a class="btn btn-outline-primary btn-sm" target="_blank"
         href="Index.php?page=Partie&action=export&code=<?= $codeEsc ?>">
        Exporter JSON
      </a>
    </div>
  </div>
</nav>

<div class="container py-3">
  <div class="row g-3">

    <!-- MAIN -->
    <div class="col-lg-8">
      <div class="card pp-shadow border-0 pp-arena">
        <div class="pp-arena-inner" id="arenaInner">

          <div class="pp-center" id="centerPanel">
            <h5 class="fw-bold mb-1" id="centerStatus">Waiting for player's votes…</h5>
            <div class="text-secondary small">Les cartes restent cachées jusqu’à la révélation.</div>

            <div class="pp-task-title" id="taskTitle">
              <?= $tacheCourante ? htmlspecialchars($tacheCourante['title'] ?? ($tacheCourante['titre'] ?? ''), ENT_QUOTES, 'UTF-8') : 'Aucune tâche' ?>
            </div>
            <div class="pp-task-desc" id="taskDesc">
              <?= $tacheCourante ? htmlspecialchars($tacheCourante['description'] ?? ($tacheCourante['desc'] ?? ''), ENT_QUOTES, 'UTF-8') : 'Backlog vide ou terminé.' ?>
            </div>

            <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
              <?php if ($isAnimateur): ?>
                <button class="btn btn-primary fw-semibold" id="btnReveal">Révéler</button>
                <button class="btn btn-outline-primary fw-semibold" id="btnResolve">Valider / recalculer</button>
                <button class="btn btn-outline-secondary fw-semibold" id="btnForceReset">Nouveau tour</button>
              <?php else: ?>
                <span class="badge text-bg-light border">En attente de révélation</span>
              <?php endif; ?>
            </div>

            <div class="text-secondary small mt-2">
              Tâche <?= (int)$currentIndex + 1 ?> / <?= count($tasks) ?> · Auto : tout le monde vote → reveal + validation
            </div>
          </div>

          <div class="pp-cardsbar" id="cardsBar">
            <?php
              $deck = ['0','1','2','3','5','8','13','21','34','55','89','?','CAFE'];
              foreach ($deck as $c):
            ?>
              <div class="pp-bigcard <?= $c==='CAFE'?'cafe':'' ?>" data-card="<?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?>">
                <?= $c==='CAFE' ? '☕ Café' : htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?>
              </div>
            <?php endforeach; ?>
          </div>

        </div>
      </div>

      <div class="card pp-shadow border-0 mt-3">
        <div class="card-body">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <div class="fw-bold">Journal</div>
              <div class="small text-secondary">WS: <span class="pp-code" id="wsInfo">connecting…</span></div>
            </div>
            <div class="small text-secondary" id="voteInfo">Choisis ta carte 👇</div>
          </div>
          <div class="pp-log mt-2" id="journal"></div>
        </div>
      </div>
    </div>

    <!-- SIDE -->
    <div class="col-lg-4">
      <div class="card pp-shadow border-0">
        <div class="card-body">
        <div class="fw-bold mb-2">Backlog & tâches</div>

        <?php if ($isAnimateur): ?>
        <div class="small text-secondary mb-2">Ajoute des tâches facilement (on génère le JSON automatiquement).</div>

        <div class="row g-2">
            <div class="col-12">
            <input type="text" class="form-control" id="taskTitleInput" placeholder="Titre de la tâche (ex: Login)">
            </div>
            <div class="col-12">
            <textarea class="form-control" id="taskDescInput" rows="2" placeholder="Description (optionnel)"></textarea>
            </div>
            <div class="col-12 d-grid">
            <button class="btn btn-primary fw-semibold" id="btnAddTask">+ Ajouter la tâche</button>
            </div>
        </div>

        <div class="mt-3">
            <div class="small text-secondary mb-2">Aperçu du backlog :</div>
            <div id="backlogPreview" class="d-flex flex-column gap-2"></div>

            <div class="d-grid mt-2">
            <button class="btn btn-outline-primary fw-semibold" id="btnSaveBacklog">Enregistrer backlog</button>
            </div>
            <div class="small text-secondary mt-2" id="backlogStatus"></div>
        </div>

        <div class="accordion mt-3" id="jsonAccordion">
            <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#jsonAdvanced">
                Options avancées (coller du JSON)
                </button>
            </h2>
            <div id="jsonAdvanced" class="accordion-collapse collapse" data-bs-parent="#jsonAccordion">
                <div class="accordion-body">
                <textarea class="form-control" id="backlogInput" rows="6"
                    placeholder='[{"title":"Login","description":"Connexion utilisateur"}]'></textarea>
                <div class="d-grid mt-2">
                    <button class="btn btn-outline-secondary" id="btnLoadJsonToDraft">Charger ce JSON dans la liste</button>
                </div>
                <div class="small text-secondary mt-2">Si tu colles du JSON ici, on le convertit en liste.</div>
                </div>
            </div>
            </div>
        </div>

        <hr class="my-3">
        <?php endif; ?>

          <div class="d-flex flex-column gap-2" id="tasksList">
            <?php if (!$tasks): ?>
              <div class="text-secondary small">Aucune tâche pour l’instant.</div>
            <?php else: ?>
              <?php foreach ($tasks as $i => $t): ?>
                <?php
                  $title = $t['title'] ?? ($t['titre'] ?? '');
                  $desc  = $t['description'] ?? ($t['desc'] ?? '');
                  $current = ((int)$i === (int)$currentIndex);
                ?>
                <div class="border rounded-4 p-3 <?= $current ? 'bg-primary-subtle border-primary-subtle' : '' ?>">
                  <div class="fw-bold">#<?= (int)($t['id'] ?? ($i+1)) ?> — <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></div>
                  <?php if ($desc): ?><div class="small text-secondary mt-1"><?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

                  <?php if ($isAnimateur): ?>
                    <button class="btn btn-sm btn-outline-secondary mt-2 btnSetCurrent" data-index="<?= (int)$i ?>">
                      Mettre en cours
                    </button>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <div class="small text-secondary mt-3">
            Rappel: tour 1 = unanimité, puis mode (moyenne/strict). <code class="pp-code">☕</code> pause café si tous votent café.
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
(() => {
  const codePartie  = <?= json_encode($codePartie) ?>;
  const decisionSeconds = <?= (int)$minuteurSeconds ?>;
  const pseudo      = <?= json_encode($pseudo) ?>;
  const isAnimateur = <?= $isAnimateur ? 'true' : 'false' ?>;

  const arenaInner   = document.getElementById('arenaInner');
  const centerPanel  = document.getElementById('centerPanel');
  const centerStatus = document.getElementById('centerStatus');

  const journal   = document.getElementById('journal');
  const wsInfo    = document.getElementById('wsInfo');
  const voteInfo  = document.getElementById('voteInfo');

  const btnReveal     = document.getElementById('btnReveal');
  const btnResolve    = document.getElementById('btnResolve');
  const btnForceReset = document.getElementById('btnForceReset');

  const backlogInput   = document.getElementById('backlogInput');
  const btnSaveBacklog = document.getElementById('btnSaveBacklog');
  const backlogStatus  = document.getElementById('backlogStatus');

  const taskTitleInput = document.getElementById("taskTitleInput");
    const taskDescInput  = document.getElementById("taskDescInput");
    const btnAddTask     = document.getElementById("btnAddTask");
    const backlogPreview = document.getElementById("backlogPreview");
    const btnLoadJsonToDraft = document.getElementById("btnLoadJsonToDraft");

// backlog draft côté client
let backlogDraft = [];

let timerId = null;
let timeLeft = decisionSeconds;

function fmtTime(s){
  s = Math.max(0, s|0);
  const mm = String(Math.floor(s/60)).padStart(2,'0');
  const ss = String(s%60).padStart(2,'0');
  return `${mm}:${ss}`;
}

function renderTimer(){
  const el = document.getElementById("timerBadge");
  if (!el) return;
  el.textContent = fmtTime(timeLeft);
}

function stopTimer(){
  if (timerId) clearInterval(timerId);
  timerId = null;
}

function startTimer(){
  stopTimer();
  timeLeft = decisionSeconds;
  renderTimer();

  timerId = setInterval(async () => {
    timeLeft--;
    renderTimer();

    // Si tout le monde a voté avant la fin -> autoResolveIfReady le fera
    if (timeLeft <= 0){
      stopTimer();

      // Si animateur : reveal + resolve (même si pas tous voté)
      if (isAnimateur){
        if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'reveal'}));
        await new Promise(r => setTimeout(r, 350));

        const res = await fetch("Index.php?page=Partie&action=resolve&code=" + encodeURIComponent(codePartie), {
          method:"POST",
          headers:{"Content-Type":"application/json"},
          body: JSON.stringify({ votes: votesCourants })
        });
        const data = await res.json().catch(()=>null);

        if (data?.ok){
          if (data.status === 'validated'){
            if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'refresh'}));
            setTimeout(() => location.reload(), 500);
          } else if (data.status === 'continue'){
            if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'round_reset'}));
            setTimeout(() => resetRoundLocal(), 350);
          } else if (data.status === 'pause'){
            // pause café
          }
        }
      }
    }
  }, 1000);
}



  const joueurs = {};
  const votesCourants = {};
  let revealed = false;
  let resolving = false;

  function escapeHtml(str){
    return String(str).replace(/[&<>"']/g, s => ({
      "&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"
    }[s]));
  }
  function log(msg){
    const p = document.createElement('p');
    p.className = "mb-2";
    p.innerHTML = msg;
    journal.appendChild(p);
    journal.scrollTop = journal.scrollHeight;
  }
  function panelAnim(cls){
    centerPanel.classList.remove('revealAnim','disagreeAnim','successAnim');
    void centerPanel.offsetWidth;
    centerPanel.classList.add(cls);
  }

  function initials(name){
    const parts = String(name || '').trim().split(/\s+/);
    const a = (parts[0]?.[0] || '?').toUpperCase();
    const b = (parts[1]?.[0] || '').toUpperCase();
    return (a + b).slice(0,2);
  }
  function ensurePlayer(p){
    if (!joueurs[p]) joueurs[p] = { hasVoted:false };
  }
  function formatCard(v){
    if (v == null) return '';
    const up = String(v).toUpperCase();
    if (up === 'CAFE' || up === 'CAFÉ') return '☕';
    return String(v);
  }

  function clearPlayersDOM(){
    document.querySelectorAll('.pp-player').forEach(el => el.remove());
  }

  function renderCircle(players){
    clearPlayersDOM();
    players.forEach(p => ensurePlayer(p));

    const n = players.length || 1;
   const w = arenaInner.clientWidth;
const h = arenaInner.clientHeight;

const radius = Math.max(120, Math.min(220, (Math.min(w, h) / 2) - 160));


    players.forEach((p, idx) => {
      const angle = (Math.PI * 2) * (idx / n) - Math.PI/2;
      const x = Math.cos(angle) * radius;
      const y = Math.sin(angle) * radius;

      const voted = !!joueurs[p].hasVoted;
      const cardVal = votesCourants[p];
      const showFront = revealed && (cardVal !== undefined);

      const el = document.createElement('div');
      el.className = 'pp-player';
      el.style.transform = `translate(calc(-50% + ${x}px), calc(-50% + ${y}px))`;

      el.innerHTML = `
        <div class="pp-avatar">${escapeHtml(initials(p))}</div>
        <div class="flex-grow-1" style="min-width:0">
          <div class="fw-bold text-truncate">${escapeHtml(p)}</div>
          <div class="small text-secondary d-flex align-items-center gap-2">
            <span class="pp-dot ${voted ? 'voted' : ''}"></span>
            <span>${voted ? 'voted' : 'waiting'}</span>
          </div>
        </div>

        <div class="pp-mini ${showFront ? 'flipped' : ''}">
          <div class="back">🂠</div>
          <div class="front">${escapeHtml(formatCard(cardVal))}</div>
        </div>
      `;

      arenaInner.appendChild(el);
    });
  }

  function allVoted(){
    const players = Object.keys(joueurs);
    if (!players.length) return false;
    return players.every(p => joueurs[p]?.hasVoted);
  }

  function resetRoundLocal(){
    revealed = false;
    resolving = false;
    for (const k in votesCourants) delete votesCourants[k];
    for (const p in joueurs) joueurs[p].hasVoted = false;
    startTimer();
    document.querySelectorAll('.pp-bigcard').forEach(c => c.classList.remove('selected'));
    centerStatus.textContent = "Waiting for player's votes…";
    voteInfo.textContent = "Choisis ta carte 👇";
    renderCircle(Object.keys(joueurs));
  }

  // WS URL dev/prod
  const scheme = (location.protocol === "https:") ? "wss://" : "ws://";
  let wsUrl = scheme + location.host + "/ws?code=" + encodeURIComponent(codePartie) +
              "&pseudo=" + encodeURIComponent(pseudo);

  if (location.hostname === "localhost" || location.hostname === "127.0.0.1") {
    wsUrl = "ws://localhost:8080?code=" + encodeURIComponent(codePartie) +
            "&pseudo=" + encodeURIComponent(pseudo);
  }

  const ws = new WebSocket(wsUrl);

ws.onopen = () => {
  wsInfo.textContent = "connected";
  log("<span class='text-success small'>✅ WS connecté</span>");
  startTimer();
};
  ws.onclose= () => { wsInfo.textContent = "closed"; log("<span class='text-warning small'>⚠️ WS fermé</span>"); };
  ws.onerror= () => { wsInfo.textContent = "error"; log("<span class='text-danger small'>❌ WS error</span>"); };

  async function autoResolveIfReady(){
    if (!isAnimateur || resolving || !allVoted()) return;

    resolving = true;

    if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'reveal'}));
    await new Promise(r => setTimeout(r, 450));

    const res = await fetch("Index.php?page=Partie&action=resolve&code=" + encodeURIComponent(codePartie), {
      method:"POST",
      headers:{"Content-Type":"application/json"},
      body: JSON.stringify({ votes: votesCourants })
    });

    const data = await res.json().catch(()=>null);
    if (!data || !data.ok){
      log("<span class='text-danger small'>❌ Resolve error</span>");
      resolving = false;
      return;
    }

    if (data.status === 'validated'){
      centerStatus.textContent = "✅ Validated";
      panelAnim('successAnim');
      if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'refresh'}));
      setTimeout(() => location.reload(), 650);
      return;
    }

    if (data.status === 'continue'){
      centerStatus.textContent = "❌ Not agreed → new round";
      panelAnim('disagreeAnim');
      if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'round_reset'}));
      setTimeout(() => resetRoundLocal(), 500);
      return;
    }

    if (data.status === 'pause'){
      centerStatus.textContent = "☕ Coffee break";
      log("<span class='small'>☕ Pause café</span>");
      resolving = false;
      return;
    }

    resolving = false;
  }

  ws.onmessage = (ev) => {
    let data;
    try { data = JSON.parse(ev.data); } catch { return; }

    switch(data.type){
      case 'state':
      case 'players': {
        const players = data.players || [];
        players.forEach(p => ensurePlayer(p));
        renderCircle(players);
        break;
      }

      case 'join':
        ensurePlayer(data.pseudo);
        log("➕ <b>"+escapeHtml(data.pseudo)+"</b> a rejoint");
        renderCircle(Object.keys(joueurs));
        break;

      case 'leave':
        log("➖ <b>"+escapeHtml(data.pseudo)+"</b> a quitté");
        renderCircle(Object.keys(joueurs));
        break;

      case 'vote': {
        const p = data.pseudo;
        ensurePlayer(p);
        joueurs[p].hasVoted = true;
        votesCourants[p] = data.valeur ?? null;

        centerStatus.textContent = allVoted() ? "All votes received" : "Waiting for player's votes…";
        renderCircle(Object.keys(joueurs));
        autoResolveIfReady();
        break;
      }

      case 'reveal':
        revealed = true;
        centerStatus.textContent = "Revealing votes…";
        panelAnim('revealAnim');
        renderCircle(Object.keys(joueurs));
        break;

      case 'round_reset':
        log("<span class='small'>🔁 Nouveau tour</span>");
        resetRoundLocal();
        break;

      case 'refresh':
        location.reload();
        break;

      case 'error':
        log("<span class='text-danger small'>❌ "+escapeHtml(data.message || 'error')+"</span>");
        break;
    }
  };

  // Click cards
  document.querySelectorAll('.pp-bigcard').forEach(el => {
    el.addEventListener('click', () => {
      const v = el.getAttribute('data-card');

      document.querySelectorAll('.pp-bigcard').forEach(c => c.classList.remove('selected'));
      el.classList.add('selected');

      voteInfo.textContent = "Vote sélectionné : " + (v === 'CAFE' ? '☕ Café' : v);

      ensurePlayer(pseudo);
      joueurs[pseudo].hasVoted = true;
      votesCourants[pseudo] = v;

      renderCircle(Object.keys(joueurs));

      if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({ type:'vote', valeur: v }));
      autoResolveIfReady();
    });
  });

  // Animateur: boutons manuels
  if (btnReveal){
    btnReveal.addEventListener('click', () => {
      if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'reveal'}));
    });
  }
  if (btnResolve){
    btnResolve.addEventListener('click', async () => {
      const res = await fetch("Index.php?page=Partie&action=resolve&code=" + encodeURIComponent(codePartie), {
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body: JSON.stringify({ votes: votesCourants })
      });
      const data = await res.json().catch(()=>null);
      if (!data || !data.ok){ log("<span class='text-danger small'>❌ resolve</span>"); return; }

      if (data.status === 'validated'){
        if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'refresh'}));
        setTimeout(() => location.reload(), 350);
      } else if (data.status === 'continue'){
        if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'round_reset'}));
        setTimeout(() => resetRoundLocal(), 350);
      }
    });
  }
  if (btnForceReset){
    btnForceReset.addEventListener('click', () => {
      if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'round_reset'}));
      resetRoundLocal();
    });
  }

 if (btnSaveBacklog) {
  btnSaveBacklog.addEventListener("click", async () => {
    try {
      // priorité à la liste user-friendly
      let payload = backlogDraft;

      // si liste vide, on tente le JSON avancé (si rempli)
      if ((!payload || !payload.length) && backlogInput && backlogInput.value.trim() !== "") {
        const parsed = JSON.parse(backlogInput.value);
        payload = parsed;
      }

      if (!payload || !payload.length) {
        backlogStatus.textContent = "Ajoute au moins une tâche.";
        return;
      }

      const res = await fetch("Index.php?page=Partie&action=saveBacklog&code=" + encodeURIComponent(codePartie), {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ backlog: payload })
      });

      const data = await res.json();
      backlogStatus.textContent = data.message || "OK";

      if (data.ok) {
        if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({ type: "refresh" }));
        setTimeout(() => location.reload(), 300);
      }
    } catch (e) {
      backlogStatus.textContent = "Erreur lors de l'envoi du backlog.";
    }
  });

  // init affichage
  renderBacklogDraft();
}

  function renderBacklogDraft() {
  if (!backlogPreview) return;
  backlogPreview.innerHTML = "";

  if (!backlogDraft.length) {
    backlogPreview.innerHTML = `<div class="text-secondary small">Aucune tâche ajoutée.</div>`;
    return;
  }

  backlogDraft.forEach((t, i) => {
    const div = document.createElement("div");
    div.className = "border rounded-4 p-2 bg-light d-flex justify-content-between align-items-start gap-2";
    div.innerHTML = `
      <div class="flex-grow-1">
        <div class="fw-bold">${escapeHtml(t.title)}</div>
        ${t.description ? `<div class="small text-secondary mt-1">${escapeHtml(t.description)}</div>` : ``}
      </div>
      <button class="btn btn-sm btn-outline-danger" data-del="${i}">Supprimer</button>
    `;
    backlogPreview.appendChild(div);
  });

  backlogPreview.querySelectorAll("[data-del]").forEach(btn => {
    btn.addEventListener("click", () => {
      const idx = parseInt(btn.getAttribute("data-del"), 10);
      backlogDraft.splice(idx, 1);
      renderBacklogDraft();
    });
  });
}

function addTaskToDraft(title, description) {
  const t = (title || "").trim();
  const d = (description || "").trim();
  if (!t) return false;
  backlogDraft.push({ title: t, description: d });
  return true;
}

if (btnAddTask) {
  btnAddTask.addEventListener("click", () => {
    const ok = addTaskToDraft(taskTitleInput.value, taskDescInput.value);
    if (!ok) return;

    taskTitleInput.value = "";
    taskDescInput.value = "";
    renderBacklogDraft();
  });
}

// Enter = ajouter (sur le champ titre)
if (taskTitleInput) {
  taskTitleInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      btnAddTask?.click();
    }
  });
}

if (btnLoadJsonToDraft) {
  btnLoadJsonToDraft.addEventListener("click", () => {
    try {
      const parsed = JSON.parse(backlogInput.value);
      if (!Array.isArray(parsed)) throw new Error("Not array");

      backlogDraft = parsed.map(x => ({
        title: (x.title || x.titre || "").toString(),
        description: (x.description || x.desc || "").toString()
      })).filter(x => x.title.trim() !== "");

      renderBacklogDraft();
      backlogStatus.textContent = "JSON chargé dans la liste.";
    } catch (e) {
      backlogStatus.textContent = "JSON invalide.";
    }
  });
}



  // Set current task (si implémenté côté controller)
  document.querySelectorAll('.btnSetCurrent').forEach(btn => {
    btn.addEventListener('click', async () => {
      const index = parseInt(btn.getAttribute('data-index'), 10);
      const res = await fetch("Index.php?page=Partie&action=setCurrent&code=" + encodeURIComponent(codePartie), {
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body: JSON.stringify({ index })
      });
      const data = await res.json().catch(()=>null);
      if (data?.ok){
        if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'refresh'}));
        setTimeout(()=>location.reload(), 250);
      }
    });
  });
    const w = arenaInner.clientWidth;
    const h = arenaInner.clientHeight;

    // rayon basé sur la plus petite dimension
    const radius = Math.max(140, Math.min(260, (Math.min(w, h) / 2) - 120));


})();
</script>