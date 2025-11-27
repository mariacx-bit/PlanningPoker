<?php
class Vehicule{
    private $id_vehicule;
    private $marque;
    private $modele;
    private $matricule;
    private $prix_journalier;
    private $type_vehicule;
    private $statut_dispo;
    private $photo;

    public function __CONSTRUCT( $id_vehicule, $marque, $modele, $matricule, $prix_journalier,$type_vehicule,$statut_dispo,$photo ){
        $this->id_vehicule = $id_vehicule;
        $this->marque = $marque;
        $this->modele = $modele;
        $this->matricule = $matricule;
        $this->prix_journalier = $prix_journalier;
        $this->type_vehicule = $type_vehicule;
        $this->statut_dispo = $statut_dispo;
        $this->photo = $photo;
    }
    public function setIdVehicule( $idVehicule): void
    {
        if ($idVehicule <= 0) {
            throw new InvalidArgumentException("L'ID du véhicule doit être un entier positif.");
        }
        $this->idVehicule = $idVehicule;
    }

    public function setMarque( $marque): void
    {
        $this->marque = $marque;
    }

    public function setModele( $modele): void
    {
        $this->modele = $modele;
    }

    public function setMatricule( $matricule): void
    {
        $this->matricule = $matricule;
    }

    public function setPrixJournalier( $prixJournalier): void
    {
        if ($prixJournalier < 100 or $prixJournalier > 350) {
            throw new InvalidArgumentException("Le prix journalier doit être compris entre 100 et 350 €.");
        }
        $this->prixJournalier = $prixJournalier;
    }

    public function setTypeVehicule( $typeVehicule): void
    {
        $this->typeVehicule = $typeVehicule;
    }

    public function setStatutDispo( $statutDispo): void
    {
        $this->statutDispo = $statutDispo;
    }

    public function setPhoto( $photo): void
    {
        $this->photo = $photo;
    }



    public function getIdVehicule()
    {
        return $this->idVehicule;
    }

    public function getMarque()
    {
        return $this->marque;
    }

    public function getModele()
    {
        return $this->modele;
    }

    public function getMatricule()
    {
        return $this->matricule;
    }

    public function getPrixJournalier()
    {
        return $this->prixJournalier;
    }

    public function getTypeVehicule()
    {
        return $this->typeVehicule;
    }

    public function getStatutDispo()
    {
        return $this->statutDispo;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function estDisponible()
    {
        return $this->statutDispo;
    }

    public function afficherDetails()
    {
        return sprintf(
            "Véhicule: %s %s [%s]\nType: %s\nPrix par jour: %.2f €\nDisponible: %s\nPhoto: %s",
            $this->marque,
            $this->modele,
            $this->matricule,
            $this->typeVehicule,
            $this->prixJournalier,
            $this->statutDispo ? 'Oui' : 'Non',
            $this->photo
        );
    }
}