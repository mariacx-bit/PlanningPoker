<?php

class Commentaire{
    private $id_comment;
    private $commentaire;
    private $datecommenataire;
    private $note;
    private $id_vehicule;
    private $id_personne;

    public function __CONSTRUCT($id_comment,$commentaire, $datecommenataire, $note, $id_personne, $id_vehicule){
        $this->id_comment = $id_comment;
        $this->commentaire = $commentaire;
        $this->datecommenataire = $datecommenataire;
        $this->note = $note;
        $this->id_personne = $id_personne;
        $this->id_vehicule = $id_vehicule;
    }

    public function setIdComment($id_comment){
        $this->id_comment = $id_comment;
    }

    public function setCommentaire($commentaire){
        $this->commentaire = $commentaire;
    }

    public function setDateCommentaire($datecommenataire){
        $this->datecommenataire = $datecommenataire;
    }

    public function setNote($note){
        $this->note = $note;
    }

    public function setIdVehicule($id_vehicule){
        $this->id_vehicule = $id_vehicule;
    }

    public function setIdPersonne($id_personne){
        $this->id_personne = $id_personne;
    }

    public function getIdComment(){
        return $this->id_comment;
    }

    public function getCommentaire(){
        return $this->commentaire;
    }

    public function getDateCommentaire(){
        return $this->datecommenataire;
    }

    public function getNote(){
        return $this->note;
    }

    public function getIdVehicule(){
        return $this->id_vehicule;
    }

    public function getIdPersonne(){
        return $this->id_personne;
    }
}