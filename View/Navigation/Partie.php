<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$codePartie       = $codePartie ?? ($_GET['code'] ?? '');
$pseudo           = $pseudo ?? ($_SESSION['pseudo'] ?? 'Anonyme');
$isAnimateur      = $isAnimateur ?? false;
$mode             = $mode ?? 'strict';
$tour             = (int)($tour ?? 1);
$currentIndex     = (int)($currentIndex ?? 0);
$tasks            = is_array($tasks ?? null) ? $tasks : [];
$tacheCourante    = $tacheCourante ?? ($tasks[$currentIndex] ?? null);
$minuteurSeconds  = (int)($minuteurSeconds ?? 120);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$codeEsc   = h($codePartie);
$pseudoEsc = h($pseudo);
$totalIssues = count($tasks);
?>
<style>
  body{ background:#f6f7fb; }

  /* ===== Topbar ===== */
  .pp-appbar{
    position: sticky; top:0; z-index:1000;
    backdrop-filter: blur(8px);
    background: rgba(255,255,255,.92);
    border-bottom: 1px solid rgba(15,23,42,.06);
    padding: 10px 14px;                /* ↓ plus compact */
    display:flex; align-items:center; justify-content:space-between; gap:10px;
  }
  .pp-brand{ font-weight:900; font-size: 20px; letter-spacing: -.3px; } /* ↓ */
  .pp-chip{
    display:inline-flex; align-items:center; gap:8px;
    border:1px solid rgba(15,23,42,.10);
    padding: 5px 9px;                  /* ↓ */
    border-radius: 999px;
    background:#fff;
    font-size: 12px;                   /* ↓ */
    font-weight:800;
    line-height: 1;
  }
  .pp-chip.dark{ background:#111827; border-color:#111827; color:#fff; }
  .pp-chip.primary{ background:#2563eb; border-color:#2563eb; color:#fff; }
  .pp-code{ background:#eef2ff; border-radius: 8px; padding: 2px 6px; font-weight:900; }

  /* ===== Layout ===== */
  .pp-layout{
    display:grid;
    grid-template-columns: 1fr 390px;  /* ↓ */
    gap: 14px;                         /* ↓ */
    padding: 14px;                     /* ↓ */
    height: calc(100vh - 60px);
    min-height: 600px;
  }

  /* ===== ARENA ===== */
  .pp-arena{
    height: 100%;
    border-radius: 22px;               /* ↓ */
    background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
    box-shadow: 0 10px 26px rgba(15, 23, 42, .06);
    border: 1px solid rgba(15,23,42,.06);
    overflow:hidden;
    display:flex;
    flex-direction: column;
    min-height: 540px;
  }
  .pp-arena-stage{
    position: relative;
    flex: 1 1 auto;
    min-height: 380px;                 /* ↓ */
    padding: 10px 18px;                /* ✅ padding pour éviter "trop à gauche" */
    overflow: hidden;                  /* ✅ empêche de couper à l’extérieur */
  }

  /* ===== Center bubble ===== */
  .pp-centerBubble{
    position:absolute;
    left:50%; top:50%;
    transform: translate(-50%,-50%);
    width: min(520px, 92%);            /* ↓ */
    background:#dbeafe;
    border: 1px solid rgba(37,99,235,.22);
    border-radius: 22px;               /* ↓ */
    padding: 18px 16px;                /* ↓ */
    text-align:center;
    z-index: 2;
  }
  .pp-centerBubble.coffee{
    background:#fff7ed;
    border-color:#fed7aa;
  }
  .pp-centerBubble h4{ font-weight: 900; margin-bottom: 6px; font-size: 22px; } /* ↓ */
  .pp-centerBubble .smalltext{ color:#334155; opacity:.85; font-weight: 800; font-size: 13px; } /* ↓ */
  .pp-currentIssue{ margin-top: 8px; font-weight: 900; font-size: 16px; } /* ↓ */
  .pp-currentDesc{ margin-top: 6px; color:#475569; white-space: pre-wrap; font-size: 13px; } /* ↓ */
  .pp-centerActions{
    margin-top: 14px;
    display:flex;
    justify-content:center;
    gap: 10px;
    flex-wrap: wrap;
  }
  .pp-btn{
    border: 1px solid rgba(15,23,42,.18);
    background:#fff;
    padding: 9px 12px;                 /* ↓ */
    border-radius: 12px;
    font-weight: 900;
    cursor:pointer;
    font-size: 14px;                   /* ↓ */
  }
  .pp-btn.primary{ background:#2563eb; border-color:#2563eb; color:#fff; }
  .pp-btn:disabled{ opacity:.55; cursor:not-allowed; }

  /* ===== Players rows (recentrer + empêcher sortie) ===== */
 /* ===== Players rows (CENTRÉ PROPREMENT) ===== */
.pp-playerRow{
  position:absolute;
  left:50%;
  transform: translateX(-50%);
  width: calc(100% - 48px); /* ✅ respecte le padding visuel */
  max-width: 100%;
  display:flex;
  justify-content:center;
  align-items:center;
  gap: 14px;
  padding: 10px 0;
  z-index: 1;
  pointer-events:none;

  flex-wrap: wrap;          /* si trop de joueurs */
}

.pp-playerRow.top{ top: 8px; }
.pp-playerRow.bottom{ bottom: 8px; }
.pp-arena-stage{
  position: relative;
  overflow: hidden; /* évite que ça parte hors cadre */
}


  .pp-player{
    width: 130px;                      /* ↓ */
    display:flex;
    flex-direction: column;
    align-items:center;
    gap: 5px;
    text-align:center;
  }
  .pp-cardSmall{
    width: 46px; height: 66px;         /* ↓ */
    border-radius: 12px;               /* ↓ */
    border: 2px solid #bfdbfe;
    background:#fff;
    position: relative;
    box-shadow: 0 8px 18px rgba(2,6,23,.08);
    transform-style: preserve-3d;
    transition: transform .35s ease;
  }
  .pp-cardSmall .front, .pp-cardSmall .back{
    position:absolute; inset:0;
    display:flex; align-items:center; justify-content:center;
    backface-visibility:hidden;
    border-radius: 12px;
    font-weight: 900;
    font-size: 16px;                   /* ↓ */
  }
  .pp-cardSmall .back{
    background: linear-gradient(135deg,#1d4ed8,#60a5fa);
    color:#fff;
  }
  .pp-cardSmall .front{
    transform: rotateY(180deg);
    color:#0f172a;
    background:#fff;
  }
  .pp-cardSmall.flipped{ transform: rotateY(180deg); }

  .pp-name{ font-weight:900; font-size: 14px; max-width: 130px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; } /* ↓ + safe */
  .pp-statusLine{
    font-size: 12px;                   /* ↓ */
    color:#64748b;
    font-weight: 800;
    display:flex;
    align-items:center;
    gap: 8px;
    justify-content:center;
  }
  .pp-voteDot{
    width: 7px; height: 7px;           /* ↓ */
    border-radius: 999px;
    background:#cbd5e1;
    display:inline-block;
  }
  .pp-voteDot.voted{ background:#2563eb; }

  /* ===== Deck ===== */
  .pp-deck{
    flex: 0 0 auto;
    border-top: 1px solid rgba(15,23,42,.08);
    background:#fff;
    padding: 10px 12px;                /* ↓ */
    display:flex;
    justify-content:center;
    gap: 8px;                          /* ↓ */
    flex-wrap: wrap;
  }
  .pp-cardPick{
    width: 54px; height: 72px;         /* ↓ */
    border-radius: 14px;
    border: 2px solid #bfdbfe;
    background:#fff;
    display:flex; align-items:center; justify-content:center;
    font-weight: 900; font-size: 16px; /* ↓ */
    cursor:pointer; user-select:none;
    transition: transform .08s ease, box-shadow .2s ease, border-color .2s ease;
  }
  .pp-cardPick:hover{ box-shadow: 0 8px 18px rgba(2,6,23,.08); }
  .pp-cardPick:active{ transform: scale(.98); }
  .pp-cardPick.selected{ outline: 3px solid rgba(37,99,235,.35); border-color:#60a5fa; }
  .pp-cardPick.disabled{ opacity:.5; pointer-events:none; }

  /* ===== Journal ===== */
  .pp-journal{
    flex: 0 0 auto;
    border-top: 1px solid rgba(15,23,42,.06);
    background:#ffffff;
    padding: 8px 12px;                 /* ↓ */
    font-size: 12px;                   /* ↓ */
  }
  .pp-journal .log{
    margin-top: 6px;
    max-height: 80px;                  /* ↓ */
    overflow:auto;
  }

  /* ===== SIDE ===== */
  .pp-side{
    height: 100%;
    border-radius: 22px;               /* ↓ */
    background:#fff;
    box-shadow: 0 10px 26px rgba(15, 23, 42, .06);
    border: 1px solid rgba(15,23,42,.06);
    overflow:hidden;
    display:flex;
    flex-direction: column;
  }
  .pp-sideHeader{
    padding: 12px 12px 10px 12px;      /* ↓ */
    border-bottom: 1px solid rgba(15,23,42,.06);
    flex: 0 0 auto;
  }
  .pp-sideHeader .title{ font-weight: 900; font-size: 16px; } /* ↓ */
  .pp-issues-scroll{
    flex: 1 1 auto;
    overflow:auto;
    padding: 10px;
    background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
  }
  .pp-issueCard{
    border: 1px solid rgba(15,23,42,.10);
    background:#fff;
    border-radius: 14px;               /* ↓ */
    padding: 10px;                     /* ↓ */
    margin-bottom: 10px;
  }
  .pp-issueCard.active{
    border-color: rgba(37,99,235,.35);
    background: rgba(219,234,254,.55);
  }
  .pp-issueCard.validated{
    border-color: rgba(34,197,94,.35);
    background: rgba(220,252,231,.55);
  }
  .pp-issueId{ font-weight:900; font-size: 12px; color:#64748b; }
  .pp-issueTitle{ font-weight:900; font-size: 15px; } /* ↓ */
  .pp-issueDesc{ color:#64748b; font-size: 12px; margin-top: 2px; }
  .pp-issueActions{
    margin-top: 10px;
    display:flex;
    align-items:center;
    justify-content: space-between;
    gap: 10px;
  }
  .pp-issueBtn{
    border: 1px solid rgba(15,23,42,.14);
    background:#fff;
    padding: 8px 10px;                 /* ↓ */
    border-radius: 12px;
    font-weight: 900;
    cursor:pointer;
    font-size: 13px;                   /* ↓ */
    white-space: nowrap;
  }
  .pp-issueBtn.primary{ background:#2563eb; border-color:#2563eb; color:#fff; }
  .pp-issueBtn:disabled{ opacity:.6; cursor:not-allowed; }

  .pp-eye{
    width: 32px; height: 32px;         /* ↓ */
    border-radius: 12px;
    display:flex; align-items:center; justify-content:center;
    border:1px solid rgba(15,23,42,.10);
    background:#fff;
    cursor:pointer;
    user-select:none;
    font-size: 14px;
  }
  .pp-estimateBox{
    min-width: 42px;
    padding: 6px 10px;
    border-radius: 12px;
    border:1px solid rgba(15,23,42,.10);
    background:#fff;
    font-weight: 900;
    text-align:center;
    font-size: 13px;
  }

  /* Anim */
  .pp-pop{ animation: ppPop .18s ease-out; }
  .pp-shake{ animation: ppShake .35s ease-in-out; }
  @keyframes ppPop{ from{ transform: translate(-50%,-50%) scale(.98); opacity:.65 } to{ transform: translate(-50%,-50%) scale(1); opacity:1 } }
  @keyframes ppShake{ 0%,100%{ transform: translate(-50%,-50%) translateX(0) } 25%{ transform: translate(-50%,-50%) translateX(-10px) } 75%{ transform: translate(-50%,-50%) translateX(10px) } }

  @media (max-width: 1200px){
    .pp-layout{ grid-template-columns: 1fr 340px; }
  }
  @media (max-width: 992px){
    .pp-layout{
      grid-template-columns: 1fr;
      height: auto;
      min-height: 0;
    }
    .pp-side{ height: 520px; }
    .pp-arena{ min-height: 640px; }
  }
</style>

<div class="pp-appbar">
  <div class="d-flex align-items-center gap-2">
    <div class="pp-brand">Planning Poker</div>
  </div>

  <div class="d-flex align-items-center gap-2">
    <span class="pp-chip">
      <span class="text-secondary">Temps</span>
      <span class="pp-chip primary" id="timerBadge">--:--</span>
    </span>
  </div>

  <div class="d-flex flex-wrap align-items-center gap-2">
    <span class="pp-chip">👤 <?= $pseudoEsc ?></span>
    <?php if ($isAnimateur): ?><span class="pp-chip dark">Animateur</span><?php endif; ?>
    <span class="pp-chip">Mode: <?= h($mode) ?></span>
    <span class="pp-chip">Tour: <?= (int)$tour ?></span>
    <span class="pp-chip primary">Code: <?= $codeEsc ?></span>

    <a class="btn btn-outline-primary btn-sm" target="_blank"
       href="Index.php?page=Partie&action=export&code=<?= $codeEsc ?>">
      Export JSON
    </a>
  </div>
</div>

<div class="pp-layout">

  <!-- LEFT -->
  <div class="pp-arena">
    <div class="pp-arena-stage">
      <div class="pp-playerRow top" id="rowTop"></div>

      <div class="pp-centerBubble" id="centerBubble">
        <h4 id="centerTitle">Pick your cards!</h4>
        <div class="smalltext" id="centerSubtitle">Waiting for player's votes…</div>

        <div class="pp-currentIssue" id="centerIssue">
          <?php if ($tacheCourante): ?>
            <?= h($tacheCourante['title'] ?? '') ?>
          <?php else: ?>
            Aucune tâche
          <?php endif; ?>
        </div>

        <div class="pp-currentDesc" id="centerDesc">
          <?php if ($tacheCourante): ?>
            <?= h($tacheCourante['description'] ?? '') ?>
          <?php else: ?>
            Backlog vide ou terminé.
          <?php endif; ?>
        </div>

        <?php if ($isAnimateur): ?>
          <div class="pp-centerActions">
            <button class="pp-btn" id="btnNewRound">Vote again</button>
            <button class="pp-btn primary" id="btnResolve">Validate</button>
            <button class="pp-btn" id="btnResume" style="display:none;">Reprendre</button>
          </div>
        <?php endif; ?>
      </div>

      <div class="pp-playerRow bottom" id="rowBottom"></div>
    </div>

    <div class="pp-deck" id="deck">
      <?php
        $deck = ['0','1','2','3','5','8','13','21','34','55','89','?','CAFE'];
        foreach ($deck as $c):
      ?>
        <div class="pp-cardPick <?= $c==='CAFE'?'cafe':'' ?>" data-card="<?= h($c) ?>">
          <?= $c==='CAFE' ? '☕' : h($c) ?>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="pp-journal">
      WS: <span class="pp-code" id="wsInfo">connecting…</span>
      <div class="log" id="journal"></div>
    </div>
  </div>

  <!-- RIGHT -->
  <div class="pp-side">
    <div class="pp-sideHeader">
      <div class="title">Issues</div>
      <div class="text-secondary small"><?= (int)$totalIssues ?> issues</div>

      <?php if ($isAnimateur): ?>
        <div class="mt-3">
          <div class="card border-0" style="background:#f8fafc">
            <div class="card-body p-2">
              <div class="small text-secondary mb-2" style="font-weight:800">Add another issue</div>

              <input type="text" class="form-control mb-2" id="newTaskTitle" placeholder="Titre (ex: Login)">
              <textarea class="form-control mb-2" id="newTaskDesc" rows="2" placeholder="Description (optionnel)"></textarea>

              <div class="d-grid">
                <button class="btn btn-primary btn-sm" id="btnAddTask">+ Ajouter la tâche</button>
              </div>

              <div class="small text-secondary mt-2" id="addTaskStatus"></div>
            </div>
          </div>

          <button class="btn btn-sm btn-outline-secondary w-100 mt-2" data-bs-toggle="collapse" data-bs-target="#jsonBox">
            Backlog JSON (option)
          </button>

          <div class="collapse mt-2" id="jsonBox">
            <textarea class="form-control" id="backlogInput" rows="6"
              placeholder='[{"title":"Login","description":"Connexion utilisateur"}]'><?= h(json_encode($tasks, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ?></textarea>
            <div class="d-grid mt-2">
              <button class="btn btn-primary btn-sm" id="btnSaveBacklog">Enregistrer backlog JSON</button>
            </div>
            <div class="small text-secondary mt-2" id="backlogStatus"></div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="pp-issues-scroll" id="issuesScroll">
      <div id="issuesList">
        <?php if (!$tasks): ?>
          <div class="text-secondary small">Aucune tâche.</div>
        <?php else: ?>
          <?php foreach ($tasks as $i => $t): ?>
            <?php
              $status   = $t['status'] ?? 'pending';
              $isActive = ($i === $currentIndex && $status !== 'validated');
              $id       = $t['id'] ?? ($i + 1);
              $title    = $t['title'] ?? '';
              $desc     = $t['description'] ?? '';
              $estimate = $t['estimate'] ?? null;

              $buttonLabel = 'Vote this issue';
              if ($status === 'validated') $buttonLabel = 'Vote again';
              if ($isActive) $buttonLabel = 'Voting now…';
            ?>
            <div class="pp-issueCard <?= $isActive ? 'active' : '' ?> <?= $status==='validated'?'validated':'' ?>" data-index="<?= (int)$i ?>">
              <div class="d-flex justify-content-between align-items-start gap-2">
                <div style="min-width:0">
                  <div class="pp-issueId">PP-<?= (int)$id ?></div>
                  <div class="pp-issueTitle"><?= h($title) ?></div>
                  <?php if (trim((string)$desc) !== ''): ?>
                    <div class="pp-issueDesc"><?= h($desc) ?></div>
                  <?php endif; ?>
                </div>

                <div class="dropdown">
                  <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">⋯</button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li><button class="dropdown-item btnPickTask" data-index="<?= (int)$i ?>">Débattre</button></li>
                  </ul>
                </div>
              </div>

              <div class="pp-issueActions">
                <button class="pp-issueBtn <?= $isActive ? 'primary' : '' ?> btnPickTask"
                        data-index="<?= (int)$i ?>"
                        <?= $isActive ? 'disabled' : '' ?>>
                  <?= h($buttonLabel) ?>
                </button>

                <div class="d-flex align-items-center gap-2">
                  <?php if ($isAnimateur): ?>
                    <div class="pp-eye btnRevealEye" title="Reveal" data-index="<?= (int)$i ?>">👁</div>
                  <?php else: ?>
                    <div class="pp-eye" style="opacity:.5" title="Reveal (animateur)">👁</div>
                  <?php endif; ?>

                  <div class="pp-estimateBox" title="Estimation">
                    <?= $estimate === null ? '—' : h($estimate) ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

<script>
(() => {
  const codePartie  = <?= json_encode($codePartie) ?>;
  const pseudo      = <?= json_encode($pseudo) ?>;
  const isAnimateur = <?= $isAnimateur ? 'true' : 'false' ?>;
  const decisionSeconds = <?= (int)$minuteurSeconds ?>;

  const wsInfo = document.getElementById('wsInfo');
  const journal = document.getElementById('journal');
  const timerBadge = document.getElementById('timerBadge');

  const rowTop = document.getElementById('rowTop');
  const rowBottom = document.getElementById('rowBottom');

  const centerBubble = document.getElementById('centerBubble');
  const centerTitle = document.getElementById('centerTitle');
  const centerSubtitle = document.getElementById('centerSubtitle');

  const btnResolve = document.getElementById('btnResolve');
  const btnNewRound = document.getElementById('btnNewRound');
  const btnResume = document.getElementById('btnResume');

  const backlogInput = document.getElementById('backlogInput');
  const btnSaveBacklog = document.getElementById('btnSaveBacklog');
  const backlogStatus = document.getElementById('backlogStatus');

  const newTaskTitle = document.getElementById('newTaskTitle');
  const newTaskDesc  = document.getElementById('newTaskDesc');
  const btnAddTask   = document.getElementById('btnAddTask');
  const addTaskStatus= document.getElementById('addTaskStatus');

  const players = [];
  const voted = {};
  const votes = {};
  let revealed = false;
  let resolving = false;
  let coffeePaused = false;

  function log(msg){
    const div = document.createElement('div');
    div.innerHTML = msg;
    journal.appendChild(div);
    journal.scrollTop = journal.scrollHeight;
  }
  function esc(s){
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
  }
  function fmtCard(v){
    if (v == null) return '';
    const u = String(v).toUpperCase();
    if (u === 'CAFE' || u === 'CAFÉ') return '☕';
    return String(v);
  }
  function allVoted(){
    if (!players.length) return false;
    return players.every(p => voted[p]);
  }
  function allCoffee(){
    if (!players.length) return false;
    return players.every(p => {
      if (!voted[p]) return false;
      const v = String(votes[p] ?? '').toUpperCase();
      return (v === 'CAFE' || v === 'CAFÉ');
    });
  }
  function anyVoteStarted(){
    return Object.values(voted).some(Boolean);
  }
  function setDeckEnabled(enabled){
    document.querySelectorAll('.pp-cardPick').forEach(x => {
      if (enabled) x.classList.remove('disabled');
      else x.classList.add('disabled');
    });
  }

  function renderPlayers(){
    rowTop.innerHTML = '';
    rowBottom.innerHTML = '';

    const topList = [];
    const bottomList = [];
    players.forEach((p, i) => (i % 2 === 0 ? topList : bottomList).push(p));

    const mk = (p) => {
      const hasVoted = !!voted[p];
      const card = votes[p];
      const show = revealed && (card !== undefined);

      return `
        <div class="pp-player">
          <div class="pp-cardSmall ${show ? 'flipped' : ''}">
            <div class="back">🂠</div>
            <div class="front">${esc(fmtCard(card))}</div>
          </div>
          <div class="pp-name">${esc(p)}</div>
          <div class="pp-statusLine">
            <span class="pp-voteDot ${hasVoted ? 'voted' : ''}"></span>
            ${hasVoted ? 'voted' : 'waiting'}
          </div>
        </div>
      `;
    };

    rowTop.innerHTML = topList.map(mk).join('');
    rowBottom.innerHTML = bottomList.map(mk).join('');
  }

  // timer
  let timerId = null;
  let timeLeft = decisionSeconds;
  function fmtTime(s){
    s = Math.max(0, s|0);
    const mm = String(Math.floor(s/60)).padStart(2,'0');
    const ss = String(s%60).padStart(2,'0');
    return `${mm}:${ss}`;
  }
  function renderTimer(){ timerBadge.textContent = fmtTime(timeLeft); }
  function stopTimer(){ if (timerId) clearInterval(timerId); timerId = null; }
  function startTimer(){
    if (coffeePaused) return;
    stopTimer();
    timeLeft = decisionSeconds;
    renderTimer();

    timerId = setInterval(async () => {
      if (coffeePaused) { stopTimer(); return; }
      timeLeft--;
      renderTimer();

      if (allVoted()){
        stopTimer();
        if (isAnimateur) await resolveNow();
      }
      if (timeLeft <= 0){
        stopTimer();
        if (isAnimateur) await resolveNow();
        else centerSubtitle.textContent = "⏱ Temps écoulé — en attente de l'animateur…";
      }
    }, 1000);
  }

  function setCoffeePauseUI(){
    coffeePaused = true;
    stopTimer();

    centerBubble.classList.add('coffee');
    centerTitle.textContent = "☕ Pause café";
    centerSubtitle.textContent = isAnimateur
      ? "Tout le monde a voté café.\nClique sur Reprendre pour relancer un tour."
      : "Tout le monde a voté café.\nEn attente de l'animateur pour reprendre…";

    setDeckEnabled(false);

    if (btnResolve) { btnResolve.disabled = true; btnResolve.textContent = "Pause ☕"; }
    if (btnNewRound) btnNewRound.disabled = true;

    document.querySelectorAll('.btnPickTask').forEach(b => b.disabled = true);
    if (btnAddTask) btnAddTask.disabled = true;
    if (btnSaveBacklog) btnSaveBacklog.disabled = true;

    if (isAnimateur && btnResume){
      btnResume.style.display = "inline-block";
      btnResume.disabled = false;
    }

    log("☕ Pause café activée.");
  }
  function leaveCoffeePause(){
    coffeePaused = false;
    centerBubble.classList.remove('coffee');
    if (btnResume) btnResume.style.display = "none";

    setDeckEnabled(true);
    document.querySelectorAll('.btnPickTask').forEach(b => b.disabled = false);
    if (btnAddTask) btnAddTask.disabled = false;
    if (btnSaveBacklog) btnSaveBacklog.disabled = false;

    if (btnResolve){ btnResolve.disabled = false; btnResolve.textContent = "Validate"; }
    if (btnNewRound) btnNewRound.disabled = false;
  }

  function resetRoundLocal(withLog=true){
    revealed = false;
    resolving = false;

    players.forEach(p => voted[p] = false);
    for (const k in votes) delete votes[k];

    document.querySelectorAll('.pp-cardPick').forEach(x => x.classList.remove('selected'));

    centerTitle.textContent = "Pick your cards!";
    centerSubtitle.textContent = "Waiting for player's votes…";

    centerBubble.classList.add('pp-pop');
    setTimeout(()=>centerBubble.classList.remove('pp-pop'), 200);

    renderPlayers();
    startTimer();
    if (withLog) log("🔁 Nouveau tour.");
  }

  async function resolveNow(){
    if (coffeePaused) return;
    if (resolving) return;
    resolving = true;

    if (!anyVoteStarted()){
      log("<span class='text-warning'>⚠️ Aucun vote pour l'instant.</span>");
      resolving = false;
      return;
    }

    if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'reveal'}));
    await new Promise(r => setTimeout(r, 250));

    const res = await fetch("Index.php?page=Partie&action=resolve&code=" + encodeURIComponent(codePartie), {
      method:"POST",
      headers:{"Content-Type":"application/json"},
      body: JSON.stringify({ votes })
    });

    const raw = await res.text();
    let data = null;
    try { data = JSON.parse(raw); } catch {}

    if (!data || !data.ok){
      log("<span class='text-danger'>Resolve error: " + esc(data?.message ?? raw) + "</span>");
      centerBubble.classList.add('pp-shake');
      setTimeout(()=>centerBubble.classList.remove('pp-shake'), 380);
      resolving = false;
      return;
    }

    if (data.status === 'pause'){
      setCoffeePauseUI();
      if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'coffee_pause'}));
      return;
    }

    if (data.status === 'validated'){
      centerSubtitle.textContent = "✅ Validated";
      log("✅ Validé — estimation: <b>" + esc(data.estimate ?? '—') + "</b>");
      if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'refresh'}));
      setTimeout(() => location.reload(), 200);
      return;
    }

    if (data.status === 'continue'){
      centerSubtitle.textContent = "Pas d’accord — nouveau tour…";
      log("❌ Pas d’accord — on revote.");
      if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'round_reset'}));
      setTimeout(() => resetRoundLocal(false), 180);
      resolving = false;
      return;
    }

    resolving = false;
  }

  // ws url
  const scheme = (location.protocol === "https:") ? "wss://" : "ws://";
  let wsUrl = scheme + location.host + "/ws?code=" + encodeURIComponent(codePartie) +
              "&pseudo=" + encodeURIComponent(pseudo);

  if (location.hostname === "localhost" || location.hostname === "127.0.0.1") {
    wsUrl = "ws://localhost:8080?code=" + encodeURIComponent(codePartie) +
            "&pseudo=" + encodeURIComponent(pseudo);
  }

  const ws = new WebSocket(wsUrl);

  ws.onopen = () => { wsInfo.textContent = "connected"; log("✅ WS connected"); startTimer(); };
  ws.onclose = () => { wsInfo.textContent = "closed"; log("⚠️ WS closed"); };
  ws.onerror = () => { wsInfo.textContent = "error"; log("❌ WS error"); };

  ws.onmessage = (ev) => {
    let data;
    try{ data = JSON.parse(ev.data); } catch { return; }

    switch(data.type){
      case 'state':
      case 'players': {
        const ps = Array.isArray(data.players) ? data.players : [];
        ps.forEach(p => {
          if (!players.includes(p)) players.push(p);
          if (voted[p] === undefined) voted[p] = false;
        });
        renderPlayers();
        break;
      }
      case 'join':
        if (!players.includes(data.pseudo)) players.push(data.pseudo);
        if (voted[data.pseudo] === undefined) voted[data.pseudo] = false;
        log("➕ " + esc(data.pseudo) + " joined");
        renderPlayers();
        break;
      case 'leave':
        log("➖ " + esc(data.pseudo) + " left");
        renderPlayers();
        break;
      case 'vote':
        voted[data.pseudo] = true;
        votes[data.pseudo] = data.valeur ?? null;

        if (allCoffee()){
          setCoffeePauseUI();
          if (isAnimateur && ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'coffee_pause'}));
          break;
        }

        centerSubtitle.textContent = allVoted() ? "All votes received" : "Waiting for player's votes…";
        renderPlayers();
        break;

      case 'reveal':
        revealed = true;
        centerTitle.textContent = "Revealing…";
        centerSubtitle.textContent = "Votes are visible";
        centerBubble.classList.add('pp-pop');
        setTimeout(()=>centerBubble.classList.remove('pp-pop'), 180);
        renderPlayers();
        break;

      case 'round_reset':
        leaveCoffeePause();
        log("🔁 Vote again");
        resetRoundLocal();
        break;

      case 'coffee_pause':
        setCoffeePauseUI();
        break;

      case 'refresh':
        location.reload();
        break;
    }
  };

  // deck pick
  document.querySelectorAll('.pp-cardPick').forEach(card => {
    card.addEventListener('click', () => {
      if (coffeePaused) return;

      const v = card.getAttribute('data-card');
      document.querySelectorAll('.pp-cardPick').forEach(x => x.classList.remove('selected'));
      card.classList.add('selected');

      voted[pseudo] = true;
      votes[pseudo] = v;

      if (!players.includes(pseudo)) players.push(pseudo);
      renderPlayers();

      if (ws.readyState === WebSocket.OPEN){
        ws.send(JSON.stringify({type:'vote', valeur:v}));
      }

      if (allCoffee()){
        setCoffeePauseUI();
        if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'coffee_pause'}));
      }
    });
  });

  if (btnNewRound){
    btnNewRound.addEventListener('click', () => {
      if (coffeePaused) return;
      if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'round_reset'}));
      resetRoundLocal();
    });
  }
  if (btnResolve){
    btnResolve.addEventListener('click', async () => {
      if (coffeePaused) return;
      await resolveNow();
    });
  }
  if (btnResume){
    btnResume.addEventListener('click', () => {
      if (!isAnimateur) return;
      leaveCoffeePause();
      if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'round_reset'}));
      resetRoundLocal();
    });
  }

  // pick task
  document.querySelectorAll(".btnPickTask").forEach(btn => {
    btn.addEventListener("click", async () => {
      if (coffeePaused) return;

      const index = parseInt(btn.getAttribute("data-index"), 10);

      if (anyVoteStarted()){
        const ok = confirm("Un vote est en cours. Changer de tâche va annuler ce tour. Continuer ?");
        if (!ok) return;
        if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'round_reset'}));
        resetRoundLocal(false);
      }

      const res = await fetch("Index.php?page=Partie&action=pick&code=" + encodeURIComponent(codePartie), {
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body: JSON.stringify({ index })
      });

      const raw = await res.text();
      let data = null;
      try { data = JSON.parse(raw); } catch {}

      if (data?.ok){
        log("🎯 Nouvelle tâche sélectionnée.");
        if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'refresh'}));
        setTimeout(() => location.reload(), 180);
      } else {
        alert(data?.message || raw || "Erreur pick");
      }
    });
  });

  // reveal eye
  document.querySelectorAll(".btnRevealEye").forEach(btn => {
    btn.addEventListener("click", () => {
      if (!isAnimateur) return;
      if (coffeePaused) return;
      if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'reveal'}));
    });
  });

  // save backlog json
  if (btnSaveBacklog){
    btnSaveBacklog.addEventListener("click", async () => {
      if (coffeePaused) return;

      backlogStatus.textContent = "";
      try{
        const parsed = JSON.parse(backlogInput.value || "[]");
        if (!Array.isArray(parsed)) throw new Error("bad");

        const res = await fetch("Index.php?page=Partie&action=saveBacklog&code=" + encodeURIComponent(codePartie), {
          method:"POST",
          headers:{"Content-Type":"application/json"},
          body: JSON.stringify({ backlog: parsed })
        });

        const raw = await res.text();
        let data = null;
        try { data = JSON.parse(raw); } catch {}

        backlogStatus.textContent = data?.message || "OK";
        if (data?.ok){
          if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'refresh'}));
          setTimeout(() => location.reload(), 180);
        }
      } catch(e){
        backlogStatus.textContent = "JSON invalide";
      }
    });
  }

  // add task
  if (btnAddTask){
    btnAddTask.addEventListener("click", async () => {
      if (coffeePaused) return;

      const title = (newTaskTitle.value || "").trim();
      const description = (newTaskDesc.value || "").trim();
      if (!title){
        addTaskStatus.textContent = "Titre obligatoire.";
        return;
      }

      btnAddTask.disabled = true;
      addTaskStatus.textContent = "Ajout…";

      try{
        let current = [];
        try{
          current = JSON.parse(backlogInput.value || "[]");
          if (!Array.isArray(current)) current = [];
        } catch { current = []; }

        current.push({ title, description });

        const res = await fetch("Index.php?page=Partie&action=saveBacklog&code=" + encodeURIComponent(codePartie), {
          method:"POST",
          headers:{"Content-Type":"application/json"},
          body: JSON.stringify({ backlog: current })
        });

        const raw = await res.text();
        let data = null;
        try { data = JSON.parse(raw); } catch {}

        if (!data?.ok){
          addTaskStatus.textContent = data?.message || raw || "Erreur lors de l'ajout.";
          btnAddTask.disabled = false;
          return;
        }

        if (backlogInput) backlogInput.value = JSON.stringify(current, null, 2);
        newTaskTitle.value = "";
        newTaskDesc.value  = "";
        addTaskStatus.textContent = "✅ Tâche ajoutée.";

        if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({type:'refresh'}));
        setTimeout(() => location.reload(), 180);

      } catch(e){
        addTaskStatus.textContent = "Erreur: " + (e?.message || e);
      } finally {
        btnAddTask.disabled = false;
      }
    });
  }

})();
</script>
