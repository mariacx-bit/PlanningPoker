<?php
include_once("Model/BDD.php");

include_once("Entities/Vehicule.php");

class ModelMain extends BDD
{
    public function getVehicles()
    {
        // Utilisation directe de $this->pdo
        $query = "SELECT * FROM vehicule";
        $stmt = $this->pdo->prepare($query); // Préparer la requête
        $stmt->execute(); // Exécuter la requête

        $vehicles = [];
        while ($row = $stmt->fetch()) {
            // Si tu as une classe Vehicule, crée des objets Vehicule
            $vehicles[] = new Vehicule(
                $row['id_vehicule'],
                $row['marque'],
                $row['modele'],
                $row['matricule'],
                $row['prix_journalier'],
                $row['type_vehicule'],
                $row['statut_dispo'],
                $row['photo']
            );
        }
        return $vehicles;
    }
}
