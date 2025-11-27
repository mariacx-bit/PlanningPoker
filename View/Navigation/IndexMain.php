<main>

    <h1>Bienvenue sur Loc Cars</h1>
    <p>La meilleure plateforme pour louer une voiture en toute simplicité.</p>

    <div class="banner">
        <h1>Louez une voiture dès maintenant !</h1>
        <p>Explorez nos offres exclusives et trouvez le véhicule parfait pour vos besoins.</p>
    </div>

    <div class="container">
        <h2>Nos véhicules disponibles</h2>
        <div class="car-list">
            <div class="car-item">
                <img src="https://via.placeholder.com/250x150" alt="Car 1">
                <h3>Renault Clio</h3>
                <p>Prix : 50€/jour</p>
            </div>
            <div class="car-item">
                <img src="https://via.placeholder.com/250x150" alt="Car 2">
                <h3>Peugeot 308</h3>
                <p>Prix : 65€/jour</p>
            </div>
            <div class="car-item">
                <img src="https://via.placeholder.com/250x150" alt="Car 3">
                <h3>BMW X5</h3>
                <p>Prix : 120€/jour</p>
            </div>
            <div class="car-item">
                <img src="https://via.placeholder.com/250x150" alt="Car 4">
                <h3>Tesla Model 3</h3>
                <p>Prix : 150€/jour</p>
            </div>
        </div>
    </div>


    <div class="vehicles-container">
        <?php if (!empty($vehicles)): ?>
            <?php foreach ($vehicles as $vehicle): ?>
                <div class="vehicle-card">
                    <img src="View/assets/img/<?php echo htmlspecialchars($vehicle->getPhoto()); ?>" alt="Photo du véhicule">
                    <h3><?php echo htmlspecialchars($vehicle->getMarque()) . " " . htmlspecialchars($vehicle->getModele()); ?></h3>
                    <p>Matricule : <?php echo htmlspecialchars($vehicle->getMatricule()); ?></p>
                    <p>Prix journalier : <?php echo htmlspecialchars($vehicle->getPrixJournalier()); ?> €</p>
                    <p>Type : <?php echo htmlspecialchars($vehicle->getTypeVehicule()); ?></p>
                    <p>Disponible : <?php echo $vehicle->estDisponible() ? 'Oui' : 'Non'; ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun véhicule disponible pour le moment.</p>
        <?php endif; ?>
    </div>

    <?php foreach ($vehicles as $vehicle): ?>
        <pre><?php print_r($vehicle); ?></pre>
    <?php endforeach; ?>

    <tbody>
    <?php foreach ($vehicules as $vehicule): ?>
        <tr>
            <td><?= htmlspecialchars($vehicule['marque']); ?></td>
            <td><?= htmlspecialchars($vehicule['modele']); ?></td>
            <td><?= htmlspecialchars($vehicule['matricule']); ?></td>
            <td><?= htmlspecialchars($vehicule['prix_journalier']); ?></td>
            <td><?= htmlspecialchars($vehicule['type_vehicule']); ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>


    </body>
    </html>
</main>