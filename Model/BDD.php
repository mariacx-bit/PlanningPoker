<?php


include_once("Config.php"); // Chemin vers Config.php


abstract class BDD
{
    protected PDO $pdo;

    public function __construct()
    {
        $dsn = "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME;
        $user = Config::DB_USER;
        $password = Config::DB_PASSWORD;

        try {
            $this->pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }


    public function user(){
        return unserialize($_SESSION['user']);
    }

    public function executeReq(string $query, $data = []){
        $stmt = $this->pdo->prepare($query);

        foreach($data as $cle => $valeur){
            $data[$cle] = htmlentities($valeur);
        }
        $stmt->execute($data);

        return $stmt;
    }
}
