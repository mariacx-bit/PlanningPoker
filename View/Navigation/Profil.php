<?php
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<div class="container py-5" style="max-width:600px">

  <div class="card shadow-sm">
    <div class="card-body">

      <h3 class="mb-3 text-center">👤 Mon profil</h3>

      <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= h($success) ?></div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
      <?php endif; ?>

      <!-- Infos -->
      <form method="POST" class="mb-4">
        <input type="hidden" name="action" value="update_profile">

        <div class="mb-3">
          <label class="form-label">Pseudo</label>
          <input class="form-control" name="pseudonyme" value="<?= h($user['pseudonyme']) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input class="form-control" type="email" name="email" value="<?= h($user['email']) ?>" required>
        </div>

        <button class="btn btn-primary w-100">
          💾 Mettre à jour mes infos
        </button>
      </form>

      <hr>

      <!-- Password -->
      <form method="POST">
        <input type="hidden" name="action" value="update_password">

        <div class="mb-3">
          <label class="form-label">Mot de passe actuel</label>
          <input type="password" class="form-control" name="current_password" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Nouveau mot de passe</label>
          <input type="password" class="form-control" name="new_password" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Confirmer</label>
          <input type="password" class="form-control" name="new_password_confirm" required>
        </div>

        <button class="btn btn-outline-primary w-100">
          🔐 Changer le mot de passe
        </button>
      </form>

    </div>
  </div>
</div>