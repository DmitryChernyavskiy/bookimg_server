<?php
include_once "./libs/MySQL.php";
include_once "config.php";

class users
{
    private $DB;
    function __construct()
    {
        $this->DB = new MySQL(TYPE_MYSQL_DB, HOST_MYSQL_DB, NAME_MYSQL_DB, USER_MYSQL_DB, PASS_MYSQL_DB);
    }
    
    function __destruct()
    {
        unset($this->DB);
    }
 
    public function getUsers()
    {
        
        $res = $this->DB->connect()->setTableName(PREFIX_TABLE_MYSQL_DB."users")->SetFild("name")->SetFild("pass")->execution();
        return $res;
    } 

    public function findUser($var)
    {
        $user = $var['user'];
        $pass = $var['pass'];
        if (!isset($user) || $user=="")
        {
           return null;
        };

        /*$test = ['user10'=>'777'];
        if($test[$user] == $pass)
        {
            return true;
        }*/
        
        $query = $this->DB->connect()->setTableName(PREFIX_TABLE_MYSQL_DB."users")->SetFild("name")->SetFild("pass")->setConditions("name", $user);
        if (!isset($pass) || $pass=="")
        {
            $query->setConditions("pass", $pass);
        }
        $res  = $query->execution();
        if (!$res || count($user)!=0)
        {
            return null;
        }

        return $res;
    }

    public function setUser($var)
    {
        $user = $var['user'];
        $password = $var['password'];
        $blocked = $var['blocked'];
        $email = $var['email'];
        $role = $var['role'];
        error_log ("_1_ ".print_r($var, true), 3, "/home/user10/public_html/errors.log");
        
        if (isset($user) && isset($password) && isset($email) )//&& !$this->findUser($var))
        {
            $blocked = (isset($blocked) ? $blocked : false);
            $role = (isset($role) ? $role : 'user');
            $query = $this->DB->connect()->setTableName(PREFIX_TABLE_MYSQL_DB."users")->SetFild("name", $user)->SetFild("password", $password)->SetFild("blocked", $blocked)->SetFild("email", $email)->SetFild("role", $role)->insert();
            error_log ("_4_ ".$query->getQuery(), 3, "/home/user10/public_html/errors.log");
            $res = $query->execution();
            return $res;
        }
        return null;
    }
}