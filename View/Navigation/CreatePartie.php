<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<div class="container py-5" style="max-width:720px">

  <div class="card shadow-sm border-0 rounded-4">
    <div class="card-body p-4">

      <h2 class="fw-bold mb-3">🎴 Créer une partie de Planning Poker</h2>
      <p class="text-secondary mb-4">
        Configure ta partie puis invite ton équipe avec le code généré.
      </p>

      <form method="POST" action="Index.php?page=CreatePartie">

        <!-- Nom -->
        <div class="mb-3">
          <label class="form-label fw-bold">Nom de la partie</label>
          <input
            type="text"
            name="nom"
            class="form-control"
            placeholder="Ex : Sprint 12 – Backend"
            required
          >
        </div>

        <!-- Mode -->
        <div class="mb-3">
          <label class="form-label fw-bold">Règle de décision</label>
          <select name="mode" class="form-select">
            <option value="strict">Unanimité stricte</option>
            <option value="moyenne">Moyenne (après 1er tour)</option>
          </select>
          <div class="form-text">
            Le premier tour est toujours en unanimité.
          </div>
        </div>

        <!-- Minuteur -->
        <div class="mb-3">
          <label class="form-label fw-bold">Temps par décision</label>
          <select name="minuteur" class="form-select">
            <option value="00:01:00">1 minute</option>
            <option value="00:02:00" selected>2 minutes</option>
            <option value="00:03:00">3 minutes</option>
            <option value="00:05:00">5 minutes</option>
          </select>
        </div>

        <!-- Nombre joueurs (indicatif) -->
        <div class="mb-4">
          <label class="form-label fw-bold">Nombre de joueurs attendu</label>
          <input
            type="number"
            name="nb_joueur"
            class="form-control"
            min="2"
            max="20"
            value="4"
          >
        </div>

        <!-- Boutons -->
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary px-4">
            🚀 Créer la partie
          </button>
          <a href="Index.php?page=Dashboard" class="btn btn-outline-secondary">
            Annuler
          </a>
        </div>

      </form>

    </div>
  </div>
</div>
