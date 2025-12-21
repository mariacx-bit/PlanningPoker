<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$code = htmlspecialchars($_GET['code'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<div class="container py-5" style="max-width:520px">
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
      <h3 class="fw-bold mb-3">👋 Rejoindre la partie</h3>
      <p class="text-secondary mb-4">
        Entre un pseudo pour participer au Planning Poker.
      </p>

      <?php if (!empty($errorPseudo)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorPseudo, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>

      <form method="POST" action="Index.php?page=Partie&code=<?= $code ?>">
        <div class="mb-3">
          <label class="form-label fw-bold">Ton pseudo</label>
          <input
            type="text"
            name="pseudo"
            class="form-control"
            placeholder="Ex : Alice, Bob42…"
            required
            autofocus
            maxlength="20"
          >
        </div>

        <button class="btn btn-primary w-100">🚀 Entrer dans la partie</button>
      </form>
    </div>
  </div>
</div>
