<?php

class UserModel extends ModelGenerique{

    public function getUsersByRole(string $role){
        $res = $this->executeReq("SELECT * FROM personne WHERE role = :role", [
            "role" => $role
        ]);

        $tab = [];

        while($u = $res->fetch()){
            extract($u);
            $tab[] = new Personne($id_personne,$civilite, $prenom,$nom, $login, $email,  $role,$dateInscription,$tel,$mdp);
        }

        return $tab;
    }

    public function userById(int $id){
        $stmt = $this->executeReq("SELECT * FROM personne WHERE id_personne = :id", ["id" => $id]);


        extract($stmt->fetch());

        return new Personne($id_personne,$civilite, $prenom,$nom, $login, $email,  $role,$dateInscription,$tel,$mdp);
    }

    public function inserer(Personne $p){
        $query = "INSERT INTO personne VALUES(NULL, :civilite, :prenom, :nom, :login, :email, 'CLIENT',  now(),:tel, :mdp)";

        $this->executeReq($query, [
            "civilite" => $p->getCivilite(),
            "prenom" => $p->getPrenom(),
            "nom" => $p->getNom(),
            "login"  => $p->getLogin(),
            "email"  => $p->getEmail(),
            "tel"  => $p->getTel(),
            "pass"   => password_hash($p->getMdp(), PASSWORD_DEFAULT)
        ]);

        //RECUP DERNIER ID INSERE
        $lastId = $this->pdo->lastInsertId();

        header("location: ");
        exit;
    }

    public function login(string $login, string $mdp){
        $query = "SELECT * FROM personne WHERE login = ?";

        $stmt = $this->pdo->prepare($query);

        $stmt->execute([$login]);

        //SI LOGIN EST TROUVE DANS BD
        if($stmt->rowCount() != 0){
            $res = $stmt->fetch();
            //TEST SUR MDP
            if( password_verify($mdp, $res['mdp']) ){
                extract($res);
                $p = new Personne($id_personne, $prenom, $login, $mdp, $role, $dateInscription);

                //SESSION
                $_SESSION['user'] = serialize($p);

                return $_SESSION['user'];
            }
        }

    }
}