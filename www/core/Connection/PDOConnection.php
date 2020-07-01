<?php

namespace cms\core\Connection;

use PDOException;

class PDOConnection implements BDDInterface{

    protected $pdo;

    public function __construct()
    {
        $this->connect();
    }

    public function connect()
    {
        try {
            $this->pdo = new \PDO(DRIVER_DB.":host=".HOST_DB.";dbname=".NAME_DB, USER_DB, PWD_DB);
        } catch(\Throwable $e) {
            echo("SQL Error : ".$e->getMessage());
        }

    }

    public function query(string $query =null, array $parameters = null)
    {
        if ($parameters) {   
            $queryPrepared = $this->pdo->prepare($query);
            try{
                $queryPrepared->execute($parameters);
            } catch(PDOException $p) {
                $p->getMessage();
            }
            return new PDOResult($queryPrepared);
        } else {
            $queryPrepared = $this->pdo->prepare($query);
            $queryPrepared->execute();
            return new PDOResult($queryPrepared);
        }
    }



}