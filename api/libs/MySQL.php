<?php
include_once "QuerySQL.php";

class mySQL extends QuerySQL
{
    static private $link, $typeDB, $hostDB, $nameDB, $user, $pass;
    
    function __construct($typeDB, $hostDB, $nameDB, $user, $pass)
    {
        parent::__construct();
        if (!mySQL::$link) //if the connection to database is not set
        {
            mySQL::$typeDB = $typeDB; //pgsql,mysql....
            mySQL::$hostDB = $hostDB;
            mySQL::$nameDB = $nameDB;
            mySQL::$user = $user;
            mySQL::$pass = $pass;
        }
    }
    
    function __destruct()
    {
        if (mySQL::$link) //mySQL::link
        {
            mySQL::$link = null;
        }
    }
    
    public function connect()
    {
        if (!mySQL::$link)
        {
            try
            {
                mySQL::$link = new PDO(mySQL::$typeDB.":host=".mySQL::$hostDB.";dbname=".mySQL::$nameDB,  mySQL::$user, mySQL::$pass);
            }
            catch (PDOException $e)
            {
                $this->errortext .= " Error connect: ".$e->getMessage()."<br/>";
            }
        }

        return $this;
    }
    
    public function lastInsertId()
    {
        return mySQL::$link->lastInsertId();
    }
    
    public function Execution()
    {
        //echo "**".$this->getQuery();
        //print_r($this->params);
        error_log ("\n_query_ ".$this->getQuery(), 3, "/var/www/html/errors.log");
        error_log ("\n_params_ ".print_r($this->params, true), 3, "/var/www/html/errors.log");
        try
        {
            //echo $this->getQuery();
            $stmt = mySQL::$link->prepare($this->getQuery());
            if (!$stmt->execute($this->params))
            {
                return null;
            }
            //$this->getQuery();
        }
        catch (PDOException $e)
        {
            error_log ("_error_ ".$e->getMessage(), 3, "/var/www/html/errors.log");
            $this->errortext .= "Error execution: ".$e->getMessage()."<br/>";
            return null;
        }
        $this->clearQuery(true);
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //error_log ("\n_fetch_ ".$this->returnStatus."_ ".print_r($res, true), 3, "/var/www/html/errors.log");
        return ($this->returnStatus ? true : $res);
/* 
        $dd = $this->getQuery();
        $this->clearQuery(true);
        return $dd;*/
    }
}
